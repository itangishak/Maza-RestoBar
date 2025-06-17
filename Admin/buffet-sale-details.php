<?php
session_start();
require_once 'connection.php';

// Check if user is logged in and has Boss privilege
if (!isset($_SESSION['UserId'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'Boss') {
    header('Location: dashboard.php?error=access_denied');
    exit();
}

// Check if sale_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: buffet-reporting.php?error=invalid_sale');
    exit();
}

$sale_id = $_GET['id'];
$error_message = '';
$success_message = '';
$sale_data = null; // Initialize to avoid undefined variable errors
$cancellation_data = null;

// Check for messages
if (isset($_GET['success'])) {
    $success_message = "Buffet sale details retrieved successfully.";
}
if (isset($_GET['error'])) {
    $error_message = "An error occurred: " . htmlspecialchars($_GET['error']);
}

// Get sale details
try {
    // Get the main sale record
    $sale_query = "SELECT bs.*, 
                   DATE_FORMAT(bs.sale_date, '%d/%m/%Y') AS formatted_date,
                   bs.price
                   FROM buffet_sale_items bs
                   WHERE bs.buffet_item_id = ?";

    $sale_stmt = $conn->prepare($sale_query);
    if (!$sale_stmt) {
        // Log the detailed error
        error_log("SQL Preparation Error in buffet-sale-details.php: " . $conn->error);
        throw new Exception("Database error when preparing statement: " . $conn->error);
    }
    $sale_stmt->bind_param('i', $sale_id);
    if (!$sale_stmt->execute()) {
        // Log the detailed error
        error_log("SQL Execution Error in buffet-sale-details.php: " . $sale_stmt->error);
        throw new Exception("Database error when executing query: " . $sale_stmt->error);
    }
    $sale_result = $sale_stmt->get_result();

    if ($sale_result->num_rows === 0) {
        // Redirect with error if sale not found
        header('Location: buffet-reporting.php?error=sale_not_found');
        exit();
    }

    $sale_data = $sale_result->fetch_assoc();
    
    // Create cancellation_reasons table if it doesn't exist
    $create_table_query = "CREATE TABLE IF NOT EXISTS cancellation_reasons (
        id INT(11) NOT NULL AUTO_INCREMENT,
        buffet_item_id INT(11) NOT NULL,
        user_id INT(11) NOT NULL,
        reason TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conn->query($create_table_query);
    
    // Get any cancellation details if this sale was canceled
    if ($sale_data['status'] === 'canceled') {
        $cancellation_query = "SELECT 
                                cr.reason,
                                cr.created_at AS cancel_date,
                                CONCAT(u.firstname, ' ', u.lastname) AS canceled_by
                            FROM cancellation_reasons cr
                            LEFT JOIN user u ON cr.user_id = u.UserId
                            WHERE cr.buffet_item_id = ?
                            LIMIT 1";
                            
        $cancel_stmt = $conn->prepare($cancellation_query);
        if (!$cancel_stmt) {
            throw new Exception("Failed to prepare cancellation query: " . $conn->error);
        }
        $cancel_stmt->bind_param('i', $sale_id);
        $cancel_stmt->execute();
        $cancellation_result = $cancel_stmt->get_result();
        $cancellation_data = $cancellation_result->fetch_assoc();
    }
    
} catch (Exception $e) {
    // Log the error
    error_log("Error retrieving sale details: " . $e->getMessage());
    $error_message = "Error retrieving sale details: " . $e->getMessage();
    
    // Set sale_data to null to prevent undefined variable errors
    $sale_data = null;
    $cancellation_data = null;
    
    // Show an immediate error using SweetAlert for consistency
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Database Error',
                text: 'Error retrieving sale details: " . addslashes($e->getMessage()) . "',
                icon: 'error',
                confirmButtonText: 'Return to Buffet Reporting'
            }).then(() => {
                window.location.href = 'buffet-reporting.php?error=db_error';
            });
        });
    </script>";
    // Continue rendering the page with empty data rather than exit() to allow SweetAlert to show
}

// Format status for display
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buffet Sale Details</title>
    <?php include_once './header.php'; ?>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebarboss.php'; ?>

    <div class="container main-container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="buffet-reporting.php">Buffet Reporting</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sale Details #<?php echo $sale_id; ?></li>
            </ol>
        </nav>
        
        <!-- SweetAlert will be triggered by JavaScript -->

        <!-- Sale Details Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Buffet Sale Details
                    <span class="float-end">
                        <?php if ($sale_data && $sale_data['status'] === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                        <?php elseif ($sale_data): ?>
                            <span class="badge bg-danger">Canceled</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Unknown</span>
                        <?php endif; ?>
                    </span>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Sale ID:</th>
                                <td><?php echo $sale_data ? $sale_data['buffet_item_id'] : 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Sale Date:</th>
                                <td><?php echo $sale_data && isset($sale_data['formatted_date']) ? $sale_data['formatted_date'] : 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Time of Day:</th>
                                <td><?php echo $sale_data && isset($sale_data['time_of_day']) ? $sale_data['time_of_day'] : 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <th>Price:</th>
                                <td><?php echo $sale_data && isset($sale_data['price']) ? number_format($sale_data['price'], 2) . ' BIF' : 'N/A'; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Created By:</th>
                                <td><?php echo $sale_data && isset($sale_data['created_by_username']) ? $sale_data['created_by_username'] : 'Unknown'; ?></td>
                            </tr>
                            <tr>
                                <th>Created On:</th>
                                <td><?php echo $sale_data && isset($sale_data['created_at']) ? date('d/m/Y H:i:s', strtotime($sale_data['created_at'])) : 'Unknown'; ?></td>
                            </tr>
                            <?php if ($sale_data && $sale_data['status'] === 'canceled' && isset($cancellation_data)): ?>
                            <tr>
                                <th>Canceled By:</th>
                                <td><?php echo $cancellation_data['canceled_by'] ?? 'Unknown'; ?></td>
                            </tr>
                            <tr>
                                <th>Canceled On:</th>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($cancellation_data['cancel_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>Reason:</th>
                                <td><?php echo $cancellation_data['reason'] ?? 'Not specified'; ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="buffet-reporting.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Buffet Reporting
                    </a>
                    <?php if ($sale_data['status'] === 'active'): ?>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i> Cancel Sale
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <?php if ($sale_data && $sale_data['status'] === 'active'): ?>
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Cancel Buffet Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="cancelForm" action="buffet-sale-cancel.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="buffet_item_id" value="<?php echo $sale_id; ?>">
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label">Cancellation Reason</label>
                            <textarea class="form-control" id="cancelReason" name="reason" rows="3" required></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Warning: Cancelling this sale cannot be undone. Please provide a detailed reason.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php include_once './footer.php'; ?>
    
    <!-- SweetAlert Notifications Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize form validation
            const cancelForm = document.getElementById('cancelForm');
            if (cancelForm) {
                cancelForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const reason = document.getElementById('cancelReason').value.trim();
                    if (!reason) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Please provide a cancellation reason',
                            icon: 'error'
                        });
                        return false;
                    }
                    
                    // Confirm cancellation with SweetAlert
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You are about to cancel this buffet sale. This action cannot be undone!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, cancel it!',
                        cancelButtonText: 'No, keep it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // If we're offline, store data for later
                            if (!navigator.onLine) {
                                const offlineData = {
                                    buffet_item_id: <?php echo $sale_id; ?>,
                                    reason: reason,
                                    timestamp: new Date().getTime(),
                                    action: 'cancel_buffet_sale'
                                };
                                localStorage.setItem('pending_buffet_cancel', JSON.stringify(offlineData));
                                
                                Swal.fire({
                                    title: 'Offline Mode',
                                    text: 'Your request has been saved and will be processed when you are back online.',
                                    icon: 'info'
                                }).then(() => {
                                    // Redirect back to reporting
                                    window.location.href = 'buffet-reporting.php';
                                });
                            } else {
                                // If online, submit the form
                                cancelForm.submit();
                            }
                        }
                    });
                });
            }
            
            // Check for success or error parameters in URL
            const urlParams = new URLSearchParams(window.location.search);
            
            // Handle success messages
            if (urlParams.has('success')) {
                let successMessage = 'Operation completed successfully';
                
                switch(urlParams.get('success')) {
                    case 'cancel':
                        successMessage = 'Buffet sale has been successfully canceled';
                        break;
                    case 'details':
                        successMessage = 'Buffet sale details retrieved successfully';
                        break;
                    default:
                        successMessage = 'Operation completed successfully';
                }
                
                Swal.fire({
                    title: 'Success!',
                    text: successMessage,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
            
            // Handle error messages
            if (urlParams.has('error')) {
                let errorMessage = 'An error occurred';
                
                switch(urlParams.get('error')) {
                    case 'sale_not_found':
                        errorMessage = 'The requested sale was not found';
                        break;
                    case 'invalid_sale':
                        errorMessage = 'Invalid sale information';
                        break;
                    case 'access_denied':
                        errorMessage = 'You do not have permission to access this feature';
                        break;
                    case 'db_error':
                        errorMessage = 'A database error occurred';
                        break;
                    default:
                        errorMessage = 'Error: ' + urlParams.get('error');
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
            
            <?php if (!empty($error_message)): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo addslashes($error_message); ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
            // Display PHP-generated success message
            Swal.fire({
                title: 'Success!',
                text: '<?php echo addslashes($success_message); ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>
            
            // Handle the cancel form submission with offline support
            if (document.getElementById('cancelForm')) {
                document.getElementById('cancelForm').addEventListener('submit', function(e) {
                    // Store the form data in localStorage if needed for offline processing
                    const buffetItemId = document.querySelector('input[name="buffet_item_id"]').value;
                    const reason = document.querySelector('textarea[name="reason"]').value;
                    
                    if (!reason.trim()) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Error!',
                            text: 'Please provide a cancellation reason',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                    
                    // Confirm before submitting
                    e.preventDefault();
                    Swal.fire({
                        title: 'Confirm Cancellation',
                        text: 'Are you sure you want to cancel this buffet sale? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, cancel it!',
                        cancelButtonText: 'No, keep it'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Submit the form
                            this.submit();
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
$status_labels = [
    'active' => '<span class="badge bg-success">Active</span>',
    'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
    'adjusted' => '<span class="badge bg-warning">Adjusted</span>'
];

$status_display = isset($status_labels[$sale_data['status']]) ? $status_labels[$sale_data['status']] : $sale_data['status'];

include 'header.php';
include 'sidebarboss.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Buffet Sale Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="buffet-reporting.php">Buffet Reporting</a></li>
                        <li class="breadcrumb-item active">Sale Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    <?php 
                    switch ($_GET['success']) {
                        case 'cancel':
                            echo 'Buffet sale has been cancelled successfully.';
                            break;
                        default:
                            echo 'Operation completed successfully.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    <?php 
                    switch ($_GET['error']) {
                        case 'db_error':
                            echo 'Database error occurred. Please try again.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-utensils mr-1"></i>
                                Buffet Sale #<?php echo htmlspecialchars($sale_id); ?>
                            </h3>
                            <div class="card-tools">
                                <?php if ($sale_data['status'] === 'active'): ?>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#cancelSaleModal">
                                        <i class="fas fa-times-circle"></i> Cancel Sale
                                    </button>
                                <?php endif; ?>
                                <a href="buffet-reporting.php" class="btn btn-default btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Reporting
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 35%">Sale Date:</th>
                                            <td><?php echo htmlspecialchars($sale_data['formatted_sale_date']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Time of Day:</th>
                                            <td><?php echo htmlspecialchars($sale_data['time_of_day']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Created At:</th>
                                            <td><?php echo htmlspecialchars($sale_data['formatted_created_at']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Created By:</th>
                                            <td><?php echo htmlspecialchars($sale_data['created_by_username']); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 35%">Status:</th>
                                            <td><?php echo $status_display; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Number of Dishes:</th>
                                            <td><?php echo htmlspecialchars($sale_data['dishes_count']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Price Per Dish:</th>
                                            <td><?php echo number_format($sale_data['price_per_dish']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Price:</th>
                                            <td><strong><?php echo number_format($sale_data['total_price']); ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Components Section -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                Components Used
                            </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($components)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No components found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($components as $index => $component): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($component['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($component['quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($component['unit_name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adjustments Section (if any) -->
            <?php if (!empty($adjustments)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history mr-1"></i>
                                    Sale Adjustments
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date & Time</th>
                                            <th>Adjusted By</th>
                                            <th>Type</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($adjustments as $index => $adjustment): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($adjustment['formatted_created_at']); ?></td>
                                                <td><?php echo htmlspecialchars($adjustment['adjusted_by_username']); ?></td>
                                                <td>
                                                    <?php if ($adjustment['adjustment_type'] === 'cancel'): ?>
                                                        <span class="badge bg-danger">Cancellation</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($adjustment['adjustment_type'])); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($adjustment['reason']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Cancel Sale Modal -->
<?php if ($sale_data && $sale_data['status'] === 'active'): ?>
<div class="modal fade" id="cancelSaleModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cancel Buffet Sale</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="buffet-sale-cancel.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale_id); ?>">
                    
                    <p class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Warning: Cancelling this sale will restore inventory items to stock. This action cannot be undone.
                    </p>
                    
                    <div class="form-group">
                        <label for="reason">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>

<script>
$(function() {
    // Initialize DataTables if there are tables
    if ($('.table-striped').length > 0) {
        $('.table-striped').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    }
});
</script>

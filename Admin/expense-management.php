<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check if user has Boss privilege
if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'Boss') {
    // Redirect to dashboard with access denied message
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Initialize variables
$success_message = $error_message = '';
$categories = ['Electricity', 'Water', 'Rent', 'Salary', 'Equipment', 'Maintenance', 'Supplies', 'Other'];
$current_date = date('Y-m-d');

// Create expenses table if it doesn't exist
try {
    $check_table_sql = "SHOW TABLES LIKE 'expenses'";
    $table_result = $conn->query($check_table_sql);
    
    if ($table_result->num_rows == 0) {
        // Table doesn't exist, create it
        $create_table_sql = "CREATE TABLE `expenses` (
            `expense_id` int(11) NOT NULL AUTO_INCREMENT,
            `description` varchar(255) DEFAULT NULL,
            `amount` decimal(10,2) NOT NULL,
            `expense_date` date NOT NULL,
            `category` varchar(255) DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`expense_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if (!$conn->query($create_table_sql)) {
            throw new Exception("Error creating expenses table: " . $conn->error);
        }
        
        $success_message = "Expenses table created successfully. You can now start adding expenses.";
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("Expense table creation error: " . $e->getMessage());
}

// Handle form submission for new expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_expense') {
    // Retrieve and sanitize form data
    $description = trim($_POST['description'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $expense_date = $_POST['expense_date'] ?? $current_date;
    $category = $_POST['category'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    
    // Basic validation
    if (empty($description) || $amount <= 0 || empty($expense_date) || empty($category)) {
        $error_message = "Please fill in all required fields with valid values.";
    } else {
        try {
            // Insert into expenses table
            $stmt = $conn->prepare("INSERT INTO expenses (description, amount, expense_date, category, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdsss", $description, $amount, $expense_date, $category, $notes);
            
            if ($stmt->execute()) {
                $success_message = "Expense added successfully.";
                // Clear form data after successful submission
                $description = $amount = $notes = '';
                $expense_date = $current_date;
                $category = '';
            } else {
                throw new Exception("Error adding expense: " . $stmt->error);
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            error_log("Expense management error: " . $e->getMessage());
        }
    }
}

// No need to fetch expenses here anymore - using expense-history.php instead
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-key="sidebar.expenses">Expense Management</title>
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }
        .sidebar.collapsed {
            width: 70px;
            transition: width 0.3s;
        }
        .main-container.full {
            margin-left: 0 !important;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebarboss.php'; ?>

    <div class="container main-container">
        <!-- Form Card for New Expense -->
        <div class="card">
            <div class="card-header">
                <h4 data-key="sidebar.expenses">Add New Expense</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="expenseForm">
                    <input type="hidden" name="action" value="add_expense">
                    
                    <div class="row mb-3">
                        <!-- Description -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="description" name="description" 
                                       value="<?php echo isset($description) ? htmlspecialchars($description) : ''; ?>" required>
                                <small class="text-muted">Brief description of the expense</small>
                            </div>
                        </div>
                        
                        <!-- Amount -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount" class="form-label">Amount (BIF)</label>
                                <input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01"
                                       value="<?php echo isset($amount) ? $amount : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <!-- Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_date" class="form-label">Expense Date</label>
                                <input type="date" class="form-control" id="expense_date" name="expense_date" 
                                       value="<?php echo isset($expense_date) ? $expense_date : $current_date; ?>" required>
                            </div>
                        </div>
                        
                        <!-- Category -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo (isset($category) && $category === $cat) ? 'selected' : ''; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="form-group mb-4">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($notes) ? htmlspecialchars($notes) : ''; ?></textarea>
                    </div>
                    
                    <div class="text-center mt-4 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Save Expense
                        </button>
                        <button type="reset" class="btn btn-secondary btn-lg ms-2">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Link to Expense History -->
        <div class="text-center mt-4 mb-3">
            <a href="expense-history.php" class="btn btn-outline-primary">
                <i class="bi bi-list-ul"></i> View Expense History
            </a>
        </div>
    </div>

    <?php include_once './footer.php'; ?>
    
    <script>
        // Sidebar toggling logic
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');
        const mainContainer = document.querySelector('.main-container');
        const cards = document.querySelectorAll('.card');
        
        function applyCardStyles() {
            if (cards && cards.length > 0) {
                cards.forEach(card => {
                    if (!sidebar.classList.contains('collapsed')) {
                        card.style.width = '87%';
                        card.style.marginLeft = '20%';
                    } else {
                        card.style.width = '100%';
                        card.style.marginLeft = '5%';
                    }
                });
            }
        }
        
        // Apply styles on page load
        if (sidebar && cards) {
            applyCardStyles();
        }
        
        // Toggle sidebar and update styles on click
        if (toggleSidebarBtn && sidebar && mainContainer) {
            toggleSidebarBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                mainContainer.classList.toggle('full');
                applyCardStyles();
            });
        }
        
        // SweetAlert Notifications
        document.addEventListener('DOMContentLoaded', function() {
            // Check for URL parameters for success/error messages
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Operation completed successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else if (urlParams.has('error')) {
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred: ' + urlParams.get('error'),
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
            
            <?php if (!empty($success_message)): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo addslashes($success_message); ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?php echo addslashes($error_message); ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Handle edit expense button clicks
            $('.edit-expense').on('click', function() {
                const expenseId = $(this).data('id');
                // Redirect to edit page or show edit modal
                // This would typically open a modal or redirect to an edit page
                Swal.fire({
                    title: 'Edit Expense',
                    text: 'This feature is coming soon. Edit expense ID: ' + expenseId,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            });
            
            // Handle delete expense button clicks
            $('.delete-expense').on('click', function() {
                const expenseId = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You are about to delete this expense. This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send delete request
                        // This would typically be an AJAX call to a PHP endpoint
                        Swal.fire(
                            'Deleted!',
                            'This feature is coming soon. Delete expense ID: ' + expenseId,
                            'success'
                        );
                    }
                });
            });
            
            // Handle view notes clicks
            $('.view-notes').on('click', function() {
                const notes = $(this).attr('title');
                Swal.fire({
                    title: 'Expense Notes',
                    text: notes,
                    icon: 'info',
                    confirmButtonText: 'Close'
                });
            });
        });
    </script>
</body>
</html>

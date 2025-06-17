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
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Fetch expense data from database
try {
    $expenses_query = "SELECT expense_id, description, amount, expense_date, 
                      category, notes, DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as formatted_date 
                      FROM expenses 
                      ORDER BY expense_date DESC, created_at DESC";
    $expenses_result = $conn->query($expenses_query);
    $expenses = [];
    
    if ($expenses_result && $expenses_result->num_rows > 0) {
        while ($row = $expenses_result->fetch_assoc()) {
            $expenses[] = $row;
        }
    }
} catch (Exception $e) {
    $error_message = "Error fetching expenses: " . $e->getMessage();
    error_log($error_message);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-key="sidebar.expensesList">Expense History</title>

    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        }
        .sidebar.collapsed {
            width: 70px;
            transition: width 0.3s;
        }
        .main-container.full {
            margin-left: 0 !important;
        }
        .badge-category {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once 'sidebarboss.php'; ?>

    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 data-key="sidebar.expensesList">Expense History</h4>
                <div class="d-flex justify-content-end">
                    <a href="expense-management.php" class="btn btn-primary btn-sm me-2">
                        <i class="bi bi-plus-circle"></i> Add New Expense
                    </a>
                    <button class="btn btn-secondary btn-sm" id="exportBtn">
                        <i class="bi bi-file-earmark-excel"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table id="expenseTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Amount (BIF)</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($expenses) && count($expenses) > 0): ?>
                                <?php foreach ($expenses as $expense): ?>
                                    <tr>
                                        <td><?php echo $expense['expense_id']; ?></td>
                                        <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                        <td>
                                            <?php
                                            $category_class = '';
                                            switch($expense['category']) {
                                                case 'Electricity':
                                                    $category_class = 'bg-warning';
                                                    break;
                                                case 'Water':
                                                    $category_class = 'bg-info';
                                                    break;
                                                case 'Rent':
                                                    $category_class = 'bg-danger';
                                                    break;
                                                case 'Salary':
                                                    $category_class = 'bg-primary';
                                                    break;
                                                case 'Equipment':
                                                    $category_class = 'bg-secondary';
                                                    break;
                                                case 'Maintenance':
                                                    $category_class = 'bg-dark';
                                                    break;
                                                case 'Supplies':
                                                    $category_class = 'bg-success';
                                                    break;
                                                default:
                                                    $category_class = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $category_class; ?> badge-category">
                                                <?php echo htmlspecialchars($expense['category']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($expense['expense_date'])); ?></td>
                                        <td class="text-end">
                                            <?php echo number_format($expense['amount'], 2, '.', ','); ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($expense['notes'])): ?>
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($expense['notes']); ?>">
                                                    <i class="bi bi-info-circle"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-expense" 
                                                    data-id="<?php echo $expense['expense_id']; ?>"
                                                    data-bs-toggle="tooltip" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-expense" 
                                                    data-id="<?php echo $expense['expense_id']; ?>"
                                                    data-bs-toggle="tooltip" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-3 text-muted">
                                        <i class="bi bi-info-circle me-1"></i> No expenses found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include_once './footer.php'; ?>
    
    <!-- Edit Expense Modal -->
    <div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editExpenseModalLabel">Edit Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editExpenseForm">
                    <div class="modal-body">
                        <input type="hidden" name="expense_id" id="edit_expense_id">
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="edit_description" name="description" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_amount" class="form-label">Amount (BIF)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="edit_amount" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_expense_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <select class="form-select" id="edit_category" name="category" required>
                                <option value="">Select category</option>
                                <option value="Electricity">Electricity</option>
                                <option value="Water">Water</option>
                                <option value="Rent">Rent</option>
                                <option value="Salary">Salary</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Supplies">Supplies</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
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
        
        $(document).ready(function() {
            // Initialize DataTable
            $('#expenseTable').DataTable({
                responsive: true,
                order: [[3, 'desc'], [0, 'desc']], // Sort by date (col 3) then ID (col 0)
                language: {
                    search: "Search expenses:",
                    lengthMenu: "Show _MENU_ expenses per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ expenses",
                    emptyTable: "No expenses found in the database",
                    zeroRecords: "No matching expenses found"
                }
            });
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Edit expense button click
            $('.edit-expense').on('click', function() {
                const expenseId = $(this).data('id');
                
                // Fetch expense details
                $.ajax({
                    url: 'get_expense.php',
                    type: 'POST',
                    data: {expense_id: expenseId},
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            $('#edit_expense_id').val(response.expense_id);
                            $('#edit_description').val(response.description);
                            $('#edit_amount').val(response.amount);
                            $('#edit_expense_date').val(response.expense_date);
                            $('#edit_category').val(response.category);
                            $('#edit_notes').val(response.notes);
                            $('#editExpenseModal').modal('show');
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to fetch expense details. Please try again.', 'error');
                    }
                });
            });
            
            // Handle edit form submission
            $('#editExpenseForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                
                $.ajax({
                    url: 'update_expense.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        $('#editExpenseModal').modal('hide');
                        
                        if(response.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        $('#editExpenseModal').modal('hide');
                        Swal.fire('Error!', 'Failed to update expense. Please try again.', 'error');
                    }
                });
            });
            
            // Delete expense button click
            $('.delete-expense').on('click', function() {
                const expenseId = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_expense.php',
                            type: 'POST',
                            data: {expense_id: expenseId},
                            dataType: 'json',
                            success: function(response) {
                                if(response.status === 'success') {
                                    Swal.fire({
                                        title: 'Deleted!', 
                                        text: 'Expense has been deleted.', 
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'An error occurred while deleting the expense.', 'error');
                            }
                        });
                    }
                });
            });
            
            // Export button click
            $('#exportBtn').on('click', function() {
                Swal.fire({
                    title: 'Coming Soon!',
                    text: 'Export functionality will be added in the next update.',
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
</body>
</html>
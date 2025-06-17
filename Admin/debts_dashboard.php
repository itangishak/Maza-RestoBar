<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check if user has 'Boss' privilege - case-insensitive and check both capitalization versions
$isBoss = (
    (isset($_SESSION['Privilege']) && (strtolower($_SESSION['Privilege']) === 'boss' || $_SESSION['Privilege'] === 'Boss')) || 
    (isset($_SESSION['privilege']) && (strtolower($_SESSION['privilege']) === 'boss' || $_SESSION['privilege'] === 'Boss'))
);
// For debugging
error_log('User ID: ' . $_SESSION['UserId'] . ', Privilege: ' . ($_SESSION['Privilege'] ?? $_SESSION['privilege'] ?? 'none') . ', isBoss: ' . ($isBoss ? 'true' : 'false'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Title will be translated -->
    <title data-key="report.debtManagement"></title>
   
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar.collapsed {
            width: 70px;
            transition: width 0.3s;
        }
        .main-container.full {
            margin-left: 0 !important;
        }
        /* Custom styles for DataTables buttons */
        div.dt-buttons {
            margin-bottom: 1rem;
        }
        .dt-button {
            margin-right: 5px;
        }
        /* Make sure the text in badges is readable */
        .badge {
            font-size: 0.875rem;
            padding: 0.35em 0.65em;
        }
        /* Add spacing between tables and controls */
        .dataTables_wrapper {
            margin-bottom: 1.5rem;
            width: 100%;
        }
        /* Ensure action buttons are consistently sized */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        /* Make sure tables have consistent width */
        #unpaidDebtsTable, #historicDebtsTable {
            width: 100% !important;
        }
        /* Ensure table containers have the same width */
        .tab-pane {
            width: 100%;
        }
        /* Ensure tab content has consistent layout */
        .tab-content, .tab-pane {
            display: block;
            width: 100%;
        }
        /* Fix DataTables responsive width */
        table.dataTable {
            width: 100% !important;
        }
        /* Ensure table header and body have consistent width */
        .dataTables_scrollHeadInner, .dataTables_scrollHeadInner table {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <!-- Heading translated -->
                <h4 data-key="report.debtManagement"></h4>
                <!-- Button translated; note the button text will be set via textContent -->
                <!-- Any authenticated user can add debts - no boss privilege required -->
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDebtModal" data-key="report.addDebt"></button>
            </div>
            <div class="card-body">
                <!-- Tabs navigation -->
                <ul class="nav nav-tabs mb-3" id="debtsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="unpaid-tab" data-bs-toggle="tab" data-bs-target="#unpaid-debts" 
                                type="button" role="tab" aria-controls="unpaid-debts" aria-selected="true">
                            Active Debts (Unpaid)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historic-tab" data-bs-toggle="tab" data-bs-target="#historic-debts" 
                                type="button" role="tab" aria-controls="historic-debts" aria-selected="false">
                            Paid Debts History
                        </button>
                    </li>
                </ul>
                
                <!-- Tabs content -->
                <div class="tab-content" id="debtsTabsContent">
                    <!-- Unpaid Debts Tab -->
                    <div class="tab-pane fade show active" id="unpaid-debts" role="tabpanel" aria-labelledby="unpaid-tab">
                        <table id="unpaidDebtsTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th data-key="report.customer"></th>
                            <th data-key="report.amount"></th>
                            <th data-key="report.createdAt"></th>
                            <th data-key="report.dueDate"></th>
                            <th data-key="report.status"></th>
                            <th data-key="report.notes"></th>
                                    <th data-key="report.createdBy"></th>
                            <th data-key="report.action"></th>
                        </tr>
                    </thead>
                </table>
            </div>
                    
                    <!-- Historic/Paid Debts Tab - Now available to all users -->
                    <div class="tab-pane fade" id="historic-debts" role="tabpanel" aria-labelledby="historic-tab">
                        <div class="d-flex justify-content-end mb-3">
                            <button id="refresh-historic" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                        <table id="historicDebtsTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th data-key="report.customer"></th>
                                    <th data-key="report.amount"></th>
                                    <th data-key="report.createdAt"></th>
                                    <th data-key="report.dueDate"></th>
                                    <th data-key="report.status"></th>
                                    <th data-key="report.notes"></th>
                                    <th data-key="report.createdBy"></th>
                                    <th data-key="report.action"></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Debt Modal -->
    <div class="modal fade" id="addDebtModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addDebtForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalAddDebtTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="customer_id" data-key="report.customer"></label>
                        <div class="d-flex align-items-center mb-2">
                            <select class="form-control me-2" id="customer_id" name="customer_id" required></select>
                            <a href="customer_dashboard.php" class="btn btn-outline-secondary btn-sm" target="_blank" data-key="report.addNew"></a>
                        </div>

                        <label for="amount" data-key="report.amountLabel"></label>
                        <input type="number" step="0.01" class="form-control mb-2" id="amount" name="amount" required>

                        <label for="due_date" data-key="report.dueDateLabel"></label>
                        <input type="date" class="form-control mb-2" id="due_date" name="due_date" value="<?php echo date('Y-m-d'); ?>">

                        <!-- Status field hidden, using default 'pending' -->
                        <input type="hidden" id="status" name="status" value="pending">

                        <label for="notes" data-key="report.notesLabel"></label>
                        <textarea class="form-control mb-2" id="notes" name="notes"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.close"></button>
                        <button type="submit" class="btn btn-primary" data-key="report.addDebtBtn"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Debt Modal -->
    <div class="modal fade" id="editDebtModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editDebtForm">
                    <input type="hidden" id="edit_debt_id" name="debt_id">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalEditDebtTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="edit_customer_id" data-key="report.customer"></label>
                        <div class="d-flex align-items-center mb-2">
                            <select class="form-control me-2" id="edit_customer_id" name="customer_id" required></select>
                            <a href="customer_dashboard.php" class="btn btn-outline-secondary btn-sm" target="_blank" data-key="report.addNew"></a>
                        </div>

                        <label for="edit_amount" data-key="report.amountLabel"></label>
                        <input type="number" step="0.01" class="form-control mb-2" id="edit_amount" name="amount" required>

                        <label for="edit_due_date" data-key="report.dueDateLabel"></label>
                        <input type="date" class="form-control mb-2" id="edit_due_date" name="due_date" value="<?php echo date('Y-m-d'); ?>">

                        <!-- Status field hidden in edit form as well -->
                        <input type="hidden" id="edit_status" name="status" value="pending">

                        <label for="edit_notes" data-key="report.notesLabel"></label>
                        <textarea class="form-control mb-2" id="edit_notes" name="notes"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.close"></button>
                        <button type="submit" class="btn btn-primary" data-key="report.saveChanges"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include_once './footer.php'; ?>

    <!-- Debug Modal -->
    <div class="modal fade" id="debugModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Session Debug Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>User ID:</strong> <?php echo $_SESSION['UserId'] ?? 'Not set'; ?>
                    </div>
                    <div class="mb-3">
                        <strong>Privilege:</strong> <?php echo $_SESSION['Privilege'] ?? $_SESSION['privilege'] ?? 'Not set'; ?> 
                        (isBoss: <?php echo $isBoss ? 'true' : 'false'; ?>)
                    </div>
                    <div class="mb-3">
                        <strong>Raw Session Data:</strong>
                        <pre><?php print_r($_SESSION); ?></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title payment-modal-title">Record Debt Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Left column: Payment form -->
                        <div class="col-md-6">
                            <form id="paymentForm">
                                <input type="hidden" id="payment_debt_id" name="debt_id">
                                
                                <div class="mb-3">
                                    <div class="alert alert-info">
                                        <strong>Customer:</strong> <span id="payment_customer"></span><br>
                                        <strong>Total Debt:</strong> <span id="payment_total_amount"></span> BIF<br>
                                        <strong>Already Paid:</strong> <span id="payment_already_paid">0.00</span> BIF<br>
                                        <strong>Remaining:</strong> <span id="payment_remaining">0.00</span> BIF
                                    </div>
                                </div>

                                <div id="payment_input_section">
                                    <div class="mb-3">
                                        <label for="paid_amount" class="form-label">Amount to Pay</label>
                                        <input type="number" step="0.01" class="form-control" id="paid_amount" 
                                            name="paid_amount" required min="0.01">
                                        <div class="form-text">Enter the amount being paid today.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">Payment Method</label>
                                        <select class="form-control" id="payment_method" name="method" required>
                                            <option value="cash">Cash</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="electronic">Electronic Payment</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="payment_notes" name="notes" rows="2"></textarea>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">Record Payment</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Right column: Payment history -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Payment History</h5>
                            <div id="payment-history-container" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-striped" id="payment-history-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="payment-history">
                                        <!-- Payment history will be loaded here -->
                                    </tbody>
                                </table>
                                <div id="no-payments-message" class="text-center py-3 text-muted">
                                    No payment history available
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Edit Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPaymentForm">
                    <input type="hidden" id="edit_payment_id" name="payment_id">
                    <input type="hidden" id="edit_payment_debt_id" name="debt_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_paid_amount" class="form-label">Payment Amount</label>
                            <input type="number" step="0.01" class="form-control" id="edit_paid_amount" 
                                name="paid_amount" required min="0.01">
                        </div>

                        <div class="mb-3">
                            <label for="edit_payment_method" class="form-label">Payment Method</label>
                            <select class="form-control" id="edit_payment_method" name="method" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="electronic">Electronic Payment</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_payment_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_payment_notes" name="notes" rows="2"></textarea>
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

    <!-- JS Libraries should be included in footer.php for offline use -->

    <script>
    $(document).ready(function () {
        // Check if user is boss (PHP variable passed to JavaScript)
        const isBoss = <?php echo $isBoss ? 'true' : 'false'; ?>;
        
        // Debug function available in console if needed
        window.showDebugModal = function() {
            $('#debugModal').modal('show');
            console.log('Debug info:');
            console.log('User is boss:', isBoss);
        };
        
        // Initialize Unpaid Debts DataTable
        let unpaidTable = $('#unpaidDebtsTable').DataTable({
            ajax: {
                url: 'fetch_debts.php',
                data: function (d) {
                    d.type = 'unpaid'; // Send parameter to filter unpaid debts
                }
            },
            responsive: true,
            width: '100%',
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-sm btn-secondary'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-sm btn-success'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-danger'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info'
                }
            ],
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            pageLength: 10,
            columns: [
                { data: 'customer_name', title: 'Customer' },
                { 
                    data: 'amount', 
                    title: 'Amount',
                    className: 'text-end',
                    render: function(data, type, row) {
                        // Format as currency for display
                        if (type === 'display') {
                            return parseFloat(data).toLocaleString('en-US', {
                                style: 'currency',
                                currency: 'BIF',
                                minimumFractionDigits: 2
                            });
                        }
                        return data;
                    }
                },
                { 
                    data: 'created_at',
                    title: 'Created At',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display' && row.created_at_formatted) {
                            return row.created_at_formatted;
                        }
                        return data || 'N/A';
                    }
                },
                { 
                    data: 'due_date',
                    title: 'Due Date',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display' && row.due_date_formatted) {
                            return row.due_date_formatted;
                        }
                        return data || 'N/A';
                    }
                },
                { 
                    data: 'status',        
                    title: 'Status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        // Format status with color-coded badges
                        if (type === 'display') {
                            let statusClass = '';
                            let statusText = data.charAt(0).toUpperCase() + data.slice(1); // Capitalize first letter
                            
                            switch(data) {
                                case 'pending':
                                    statusClass = 'bg-warning';
                                    break;
                                case 'partial':
                                    statusClass = 'bg-info';
                                    break;
                                case 'paid':
                                    statusClass = 'bg-success';
                                    break;
                                case 'overdue':
                                    statusClass = 'bg-danger';
                                    break;
                            }
                            
                            return `<span class="badge ${statusClass}">${statusText}</span>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'notes',
                    title: 'Notes',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '';
                            
                            // Truncate notes if longer than 30 chars and add tooltip
                            if (data.length > 30) {
                                return `<span data-bs-toggle="tooltip" title="${data.replace(/"/g, '&quot;')}">${data.substring(0, 30)}...</span>`;
                            }
                            return data;
                        }
                        return data;
                    }
                },
                { 
                    data: 'created_by_name',
                    title: 'Created By',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            if (row.created_at_formatted) {
                                return `<span data-bs-toggle="tooltip" title="Created: ${row.created_at_formatted}">${data}</span>`;
                            }
                            return data;
                        }
                        return data || '';
                    }
                },
                {
                    data: null,
                    title: 'Action',
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        // Edit button with icon
                        const editBtn = `
                            <button class="btn btn-sm btn-outline-primary edit-btn me-1" data-id="${row.debt_id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>`;
                        
                        // Pay button with icon
                        const payBtn = `
                            <button class="btn btn-sm btn-outline-success pay-btn me-1" data-id="${row.debt_id}" 
                                data-amount="${row.amount}" data-customer="${row.customer_name}" title="Record Payment">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>`;
                        
                        // Delete button with icon
                        const deleteBtn = `
                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${row.debt_id}" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>`;
                        
                        return editBtn + payBtn + deleteBtn;
                    }
                }
            ],
            order: [[2, 'asc']], // Sort by due date asc by default
            // Initialize tooltips after table draw
            drawCallback: function() {
                // Initialize all tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
        
        // Initialize Historic Debts DataTable (now for all users)
        let historicTable = $('#historicDebtsTable').DataTable({
            ajax: {
                url: 'fetch_debts.php',
                data: function (d) {
                    d.type = 'historic'; // Send parameter to filter historic/paid debts
                }
            },
            responsive: true,
            width: '100%',
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-sm btn-secondary'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-sm btn-success'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-danger'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info'
                }
            ],
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            pageLength: 10,
            columns: [
                { data: 'customer_name', title: 'Customer' },
                { 
                    data: 'amount', 
                    title: 'Amount',
                    className: 'text-end',
                    render: function(data, type, row) {
                        // Format as currency for display
                        if (type === 'display') {
                            return parseFloat(data).toLocaleString('en-US', {
                                style: 'currency',
                                currency: 'BIF',
                                minimumFractionDigits: 2
                            });
                        }
                        return data;
                    }
                },
                { 
                    data: 'created_at',
                    title: 'Created At',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display' && row.created_at_formatted) {
                            return row.created_at_formatted;
                        }
                        return data || 'N/A';
                    }
                },
                { 
                    data: 'due_date',
                    title: 'Due Date',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display' && row.due_date_formatted) {
                            return row.due_date_formatted;
                        }
                        return data || 'N/A';
                    }
                },
                { 
                    data: 'status',        
                    title: 'Status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        // Format status with color-coded badges
                        if (type === 'display') {
                            let statusClass = '';
                            let statusText = data.charAt(0).toUpperCase() + data.slice(1); // Capitalize first letter
                            
                            switch(data) {
                                case 'pending':
                                    statusClass = 'bg-warning';
                                    break;
                                case 'partial':
                                    statusClass = 'bg-info';
                                    break;
                                case 'paid':
                                    statusClass = 'bg-success';
                                    break;
                                case 'overdue':
                                    statusClass = 'bg-danger';
                                    break;
                            }
                            
                            return `<span class="badge ${statusClass}">${statusText}</span>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'notes',
                    title: 'Notes',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '';
                            
                            // Truncate notes if longer than 30 chars and add tooltip
                            if (data.length > 30) {
                                return `<span data-bs-toggle="tooltip" title="${data.replace(/"/g, '&quot;')}">${data.substring(0, 30)}...</span>`;
                            }
                            return data;
                        }
                        return data;
                    }
                },
                { 
                    data: 'created_by_name',
                    title: 'Created By',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            if (row.created_at_formatted) {
                                return `<span data-bs-toggle="tooltip" title="Created: ${row.created_at_formatted}">${data}</span>`;
                            }
                            return data;
                        }
                        return data || '';
                    }
                },
                {
                    data: null,
                    title: 'Action',
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        // View payment history button
                        const viewBtn = `
                            <button class="btn btn-sm btn-outline-primary pay-btn me-1" data-id="${row.debt_id}" 
                                data-amount="${row.amount}" data-customer="${row.customer_name}" title="View Payment History">
                                <i class="fas fa-history"></i>
                            </button>`;
                        
                        // Delete button with icon (only for boss users)
                        const deleteBtn = isBoss ? `
                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${row.debt_id}" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>` : '';
                        
                        return viewBtn + deleteBtn;
                    }
                }
            ],
            order: [[2, 'desc']], // Sort by due date desc by default
            // Initialize tooltips after table draw
            drawCallback: function() {
                // Initialize all tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
        
        // Tab switching - refresh datatable when tab is shown
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).attr("href");
            if (target === "#unpaid-debts") {
                unpaidTable.ajax.reload();
            } else if (target === "#historic-debts") {
                historicTable.ajax.reload();
            }
        });

        // Sidebar toggling logic
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');
        const mainContainer = document.querySelector('.main-container');
        const card = document.querySelector('.card');

        if (sidebar && card) {
            if (!sidebar.classList.contains('collapsed')) {
                card.style.width = '87%';
                card.style.marginLeft = '20%';
            } else {
                card.style.width = '100%';
                card.style.marginLeft = '5%';
            }
        }

        if (toggleSidebarBtn && sidebar && mainContainer && card) {
            toggleSidebarBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                mainContainer.classList.toggle('full');

                if (!sidebar.classList.contains('collapsed')) {
                    card.style.width = '87%';
                    card.style.marginLeft = '20%';
                } else {
                    card.style.width = '100%';
                    card.style.marginLeft = '5%';
                }
            });
        }

        // Load customers into dropdown for Add form
        function loadCustomersAdd() {
            $.get('fetch_customer.php', function (response) {
                const dropdown = $('#customer_id');
                dropdown.empty();
                if (response.status === 'success') {
                    response.data.forEach(cust => {
                        dropdown.append(new Option(cust.full_name, cust.customer_id));
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        }

        // Load customers into dropdown for Edit form
        function loadCustomersEdit(selectedId) {
            $.get('fetch_customer.php', function (response) {
                const dropdown = $('#edit_customer_id');
                dropdown.empty();
                if (response.status === 'success') {
                    response.data.forEach(cust => {
                        dropdown.append(new Option(cust.full_name, cust.customer_id));
                    });
                    $('#edit_customer_id').val(selectedId);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        }

        // Show Add Modal -> load customers
        $('#addDebtModal').on('show.bs.modal', function () {
            loadCustomersAdd();
        });

        // Add Debt
        $('#addDebtForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'add_debt.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                        unpaidTable.ajax.reload();
                        $('#addDebtForm')[0].reset();
                       
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                    Swal.fire('Error', 'Failed to add debt. Please try again.', 'error');
                }
            });
        });

        // Edit Debt (open modal)
        $('#unpaidDebtsTable').on('click', '.edit-btn', function () {
            const id = $(this).data('id');
            
            // Log the current boss status to console for debugging
            console.log('Edit button clicked. User is boss:', isBoss);
            
            $.get(`fetch_debt_by_id.php?id=${id}`, function (response) {
                if (response.status === 'success') {
                    const data = response.data;
                    $('#edit_debt_id').val(data.debt_id);
                    $('#edit_amount').val(data.amount);
                    $('#edit_due_date').val(data.due_date || '<?php echo date("Y-m-d"); ?>');
                    $('#edit_status').val(data.status || 'pending');
                    $('#edit_notes').val(data.notes);
                    loadCustomersEdit(data.customer_id);
                    
                    // Show debug info for privilege detection in modal
                    console.log("Edit modal opening with isBoss:", isBoss);
                    console.log("Session privilege:", '<?php echo $_SESSION["Privilege"] ?? "Not set"; ?>');
                    
                    // Force recheck privilege status directly from PHP session
                    const phpPrivilegeCheck = '<?php echo (
                        (isset($_SESSION["Privilege"]) && (strtolower($_SESSION["Privilege"]) === "boss" || $_SESSION["Privilege"] === "Boss")) || 
                        (isset($_SESSION["privilege"]) && (strtolower($_SESSION["privilege"]) === "boss" || $_SESSION["privilege"] === "Boss"))
                    ) ? "true" : "false"; ?>';
                    
                    console.log("Direct PHP privilege check:", phpPrivilegeCheck);
                    
                    // Use both JS variable and direct PHP check to be extra safe
                    const hasBossAccess = isBoss || phpPrivilegeCheck === 'true';
                    
                    // Disable customer and amount fields for non-boss users
                    if (!hasBossAccess) {
                        $('#edit_customer_id').prop('disabled', true);
                        $('#edit_amount').prop('disabled', true);
                        
                        // Add a note to inform the user
                        if (!$('#permission-note').length) {
                            const note = '<div id="permission-note" class="alert alert-info mt-2">Only users with Boss privilege can edit Customer and Amount fields</div>';
                            $('#edit_amount').after(note);
                        }
                    } else {
                        $('#edit_customer_id').prop('disabled', false);
                        $('#edit_amount').prop('disabled', false);
                        $('#permission-note').remove();
                    }
                    
                    $('#editDebtModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        });

        // Update Debt
        $('#editDebtForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'update_debt.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                        unpaidTable.ajax.reload();
                        $('#editDebtForm')[0].reset();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                    Swal.fire('Error', 'Failed to update debt. Please try again.', 'error');
                }
            });
        });

        // Delete Debt
        $('#unpaidDebtsTable').on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_debt.php', { id: id }, function (response) {
                        Swal.fire(response.status, response.message, response.status);
                        unpaidTable.ajax.reload();
                    }, 'json');
                }
            });
        });
        
        // Handle Pay Debt button click
        $('#unpaidDebtsTable').on('click', '.pay-btn', function() {
            const debtId = $(this).data('id');
            const amount = parseFloat($(this).data('amount'));
            const customer = $(this).data('customer');
            
            // Set modal title for recording payment
            $('.payment-modal-title').text('Record Debt Payment');
            
            // Set payment modal values
            $('#payment_debt_id').val(debtId);
            $('#payment_customer').text(customer);
            $('#payment_total_amount').text(amount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            
            // Show payment inputs for unpaid debts
            $('#payment_input_section').show();
            
            // Clear previous values
            $('#payment_method').val('cash');
            $('#payment_notes').val('');
            
            // Load payment history and update the remaining amount
            $.ajax({
                url: 'fetch_debt_payments.php',
                type: 'GET',
                data: { debt_id: debtId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Update payment totals
                        const totalPaid = parseFloat(response.total_paid) || 0;
                        const remaining = amount - totalPaid;
                        
                        $('#payment_already_paid').text(totalPaid.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        $('#payment_remaining').text(remaining.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        // Set the paid amount field to the remaining amount by default
                        $('#paid_amount').val(remaining > 0 ? remaining.toFixed(2) : 0);
                        $('#paid_amount').attr('max', remaining);
                        
                        // Display payment history
                        const payments = response.data || [];
                        const historyTable = $('#payment-history');
                        historyTable.empty();
                        
                        if (payments.length > 0) {
                            // Hide no payments message
                            $('#no-payments-message').hide();
                            $('#payment-history-table').show();
                            
                            // Add each payment to the table
                            payments.forEach(payment => {
                                const row = `
                                    <tr>
                                        <td>${payment.formatted_date}</td>
                                        <td>${parseFloat(payment.paid_amount).toLocaleString('en-US', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        })} BIF</td>
                                        <td>${payment.formatted_method}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-payment-btn" 
                                                data-id="${payment.payment_id}" 
                                                data-debt-id="${debtId}"
                                                data-amount="${payment.paid_amount}"
                                                data-method="${payment.method}"
                                                data-notes="${payment.notes || ''}"
                                                title="Edit Payment">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-payment-btn" 
                                                data-id="${payment.payment_id}" 
                                                data-debt-id="${debtId}"
                                                title="Delete Payment">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                historyTable.append(row);
                            });
                        } else {
                            // Show no payments message
                            $('#no-payments-message').show();
                            $('#payment-history-table').hide();
                        }
                    } else {
                        console.error('Failed to load payment history:', response.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
            
            // Show modal
            $('#paymentModal').modal('show');
        });
        
        // Handle Payment Form Submission
        $('#paymentForm').on('submit', function(e) {
            e.preventDefault();
            
            const paidAmount = parseFloat($('#paid_amount').val());
            const remainingAmount = parseFloat($('#payment_remaining').text().replace(/,/g, ''));
            
            // Validate payment amount
            if (paidAmount <= 0) {
                Swal.fire('Error', 'Payment amount must be greater than zero', 'error');
                return;
            }
            
            if (paidAmount > remainingAmount) {
                Swal.fire('Error', 'Payment amount cannot exceed the remaining debt', 'error');
                return;
            }
            
            // Submit the payment
            $.ajax({
                url: 'add_debt_payment.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#paymentModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                        // Refresh both tables in case status changed from unpaid to paid
                        unpaidTable.ajax.reload();
                        historicTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire('Error', 'Failed to record payment. Please try again.', 'error');
                }
            });
        });
        
        // Handle Edit Payment button click
        $(document).on('click', '.edit-payment-btn', function(e) {
            e.stopPropagation(); // Prevent event bubbling
            
            const paymentId = $(this).data('id');
            const debtId = $(this).data('debt-id');
            const amount = $(this).data('amount');
            const method = $(this).data('method');
            const notes = $(this).data('notes');
            
            // Set form values
            $('#edit_payment_id').val(paymentId);
            $('#edit_payment_debt_id').val(debtId);
            $('#edit_paid_amount').val(amount);
            $('#edit_payment_method').val(method);
            $('#edit_payment_notes').val(notes);
            
            // Hide payment modal and show edit modal
            $('#paymentModal').modal('hide');
            $('#editPaymentModal').modal('show');
        });
        
        // Handle Edit Payment Form Submission
        $('#editPaymentForm').on('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const paidAmount = parseFloat($('#edit_paid_amount').val());
            if (paidAmount <= 0) {
                Swal.fire('Error', 'Payment amount must be greater than zero', 'error');
                return;
            }
            
            // Submit the edited payment
            $.ajax({
                url: 'update_debt_payment.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#editPaymentModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                        
                        // Reload both tables in case status changed
                        unpaidTable.ajax.reload();
                        historicTable.ajax.reload();
                        
                        // If payment modal is still open, refresh its data
                        if ($('#paymentModal').hasClass('show')) {
                            // Get the debt ID and refresh payment history
                            const debtId = $('#payment_debt_id').val();
                            refreshPaymentHistory(debtId);
                        }
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire('Error', 'Failed to update payment. Please try again.', 'error');
                }
            });
        });
        
        // Handle Delete Payment button click
        $(document).on('click', '.delete-payment-btn', function(e) {
            e.stopPropagation(); // Prevent event bubbling
            
            const paymentId = $(this).data('id');
            const debtId = $(this).data('debt-id');
            
            Swal.fire({
                title: 'Delete Payment',
                text: 'Are you sure you want to delete this payment? This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit delete request
                    $.ajax({
                        url: 'delete_debt_payment.php',
                        type: 'POST',
                        data: { payment_id: paymentId, debt_id: debtId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted', response.message, 'success');
                                
                                // Reload both tables in case status changed
                                unpaidTable.ajax.reload();
                                historicTable.ajax.reload();
                                
                                // If payment modal is still open, refresh its data
                                if ($('#paymentModal').hasClass('show')) {
                                    refreshPaymentHistory(debtId);
                                }
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            console.error('AJAX Error:', xhr.responseText);
                            Swal.fire('Error', 'Failed to delete payment. Please try again.', 'error');
                        }
                    });
                }
            });
        });
        
        // Helper function to refresh payment history in the modal
        function refreshPaymentHistory(debtId) {
            $.ajax({
                url: 'fetch_debt_payments.php',
                type: 'GET',
                data: { debt_id: debtId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const totalAmount = parseFloat($('#payment_total_amount').text().replace(/,/g, ''));
                        const totalPaid = parseFloat(response.total_paid) || 0;
                        const remaining = totalAmount - totalPaid;
                        
                        // Update displayed values
                        $('#payment_already_paid').text(totalPaid.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        $('#payment_remaining').text(remaining.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        // Update payment field
                        $('#paid_amount').val(remaining > 0 ? remaining.toFixed(2) : 0);
                        $('#paid_amount').attr('max', remaining);
                        
                        // Display payment history
                        const payments = response.data || [];
                        const historyTable = $('#payment-history');
                        historyTable.empty();
                        
                        if (payments.length > 0) {
                            // Hide no payments message
                            $('#no-payments-message').hide();
                            $('#payment-history-table').show();
                            
                            // Add each payment to the table
                            payments.forEach(payment => {
                                const row = `
                                    <tr>
                                        <td>${payment.formatted_date}</td>
                                        <td>${parseFloat(payment.paid_amount).toLocaleString('en-US', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        })} BIF</td>
                                        <td>${payment.formatted_method}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-payment-btn" 
                                                data-id="${payment.payment_id}" 
                                                data-debt-id="${debtId}"
                                                data-amount="${payment.paid_amount}"
                                                data-method="${payment.method}"
                                                data-notes="${payment.notes || ''}"
                                                title="Edit Payment">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-payment-btn" 
                                                data-id="${payment.payment_id}" 
                                                data-debt-id="${debtId}"
                                                title="Delete Payment">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                historyTable.append(row);
                            });
                        } else {
                            // Show no payments message
                            $('#no-payments-message').show();
                            $('#payment-history-table').hide();
                        }
                    }
                }
            });
        }

        // Add click handler for the payment history button in historicTable
        $('#historicDebtsTable').on('click', '.pay-btn', function() {
            const debtId = $(this).data('id');
            const amount = parseFloat($(this).data('amount'));
            const customer = $(this).data('customer');
            
            // Set modal title for viewing history
            $('.payment-modal-title').text('Payment History');
            
            // Set payment modal values
            $('#payment_debt_id').val(debtId);
            $('#payment_customer').text(customer);
            $('#payment_total_amount').text(amount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            
            // Hide payment inputs for paid debts
            $('#payment_input_section').hide();
            
            // Load payment history
            $.ajax({
                url: 'fetch_debt_payments.php',
                type: 'GET',
                data: { debt_id: debtId },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Update payment totals
                        const totalPaid = parseFloat(response.total_paid) || 0;
                        
                        $('#payment_already_paid').text(totalPaid.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        
                        $('#payment_remaining').text('0.00');
                        
                        // Display payment history
                        const payments = response.data || [];
                        const historyTable = $('#payment-history');
                        historyTable.empty();
                        
                        if (payments.length > 0) {
                            // Hide no payments message
                            $('#no-payments-message').hide();
                            $('#payment-history-table').show();
                            
                            // Add each payment to the table
                            payments.forEach(payment => {
                                const row = `
                                    <tr>
                                        <td>${payment.formatted_date}</td>
                                        <td>${parseFloat(payment.paid_amount).toLocaleString('en-US', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        })} BIF</td>
                                        <td>${payment.formatted_method}</td>
                                        <td>
                                            ${isBoss ? `
                                            <button class="btn btn-sm btn-outline-primary edit-payment-btn" 
                                                data-id="${payment.payment_id}" 
                                                data-debt-id="${debtId}"
                                                data-amount="${payment.paid_amount}"
                                                data-method="${payment.method}"
                                                data-notes="${payment.notes || ''}"
                                                title="Edit Payment">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-payment-btn" 
                                                data-id="${payment.payment_id}" 
                                                data-debt-id="${debtId}"
                                                title="Delete Payment">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            ` : ''}
                                        </td>
                                    </tr>
                                `;
                                historyTable.append(row);
                            });
                        } else {
                            // Show no payments message
                            $('#no-payments-message').show();
                            $('#payment-history-table').hide();
                        }
                    } else {
                        console.error('Failed to load payment history:', response.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                }
            });
            
            // Show modal
            $('#paymentModal').modal('show');
        });
        
        // Delete Debt for historic table
        $('#historicDebtsTable').on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete this paid debt and all its payment records. This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_debt.php', { id: id }, function (response) {
                        Swal.fire(response.status, response.message, response.status);
                        historicTable.ajax.reload();
                    }, 'json');
                }
            });
        });

        // Refresh historic table button
        $('#refresh-historic').on('click', function() {
            historicTable.ajax.reload();
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Table refreshed',
                showConfirmButton: false,
                timer: 1500
            });
        });
    });
    </script>

    <!-- Translation Script (with button fix) -->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const lang = localStorage.getItem('userLang') || 'en';
        fetch(`../languages/${lang}.json`)
          .then(response => response.json())
          .then(data => translatePage(data));
      });

      function translatePage(translations) {
        document.querySelectorAll('[data-key]').forEach(el => {
          const key = el.getAttribute('data-key');
          if (translations[key]) {
            if (el.tagName === 'INPUT') {
              el.value = translations[key];
            } else {
              el.textContent = translations[key];
            }
          }
        });
      }

      function switchLanguage(lang) {
        localStorage.setItem('userLang', lang);
        location.reload();
      }
    </script>
</body>
</html>

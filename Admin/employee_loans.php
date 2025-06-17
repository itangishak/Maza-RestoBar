<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check if user has 'Boss' privilege
$isBoss = (
    (isset($_SESSION['Privilege']) && (strtolower($_SESSION['Privilege']) === 'boss' || $_SESSION['Privilege'] === 'Boss')) || 
    (isset($_SESSION['privilege']) && (strtolower($_SESSION['privilege']) === 'boss' || $_SESSION['privilege'] === 'Boss'))
);

if (!$isBoss) {
    header("Location: dashboard.php?error=access_denied");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="report.employeeLoans"></title>
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        table.dataTable {
            width: 100% !important;
        }
        .dataTables_scrollHeadInner, .dataTables_scrollHeadInner table {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebar.php'; ?>

    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 data-key="report.employeeLoans"></h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLoanModal" data-key="report.addLoan">Add Loan</button>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs mb-3" id="loansTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-loans" 
                                type="button" role="tab" aria-controls="active-loans" aria-selected="true">
                            Active Loans
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="repaid-tab" data-bs-toggle="tab" data-bs-target="#repaid-loans" 
                                type="button" role="tab" aria-controls="repaid-loans" aria-selected="false">
                            Repaid Loans
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="loansTabsContent">
                    <!-- Active Loans Tab -->
                    <div class="tab-pane fade show active" id="active-loans" role="tabpanel" aria-labelledby="active-tab">
                        <table id="activeLoansTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Loan Amount</th>
                                    <th>Date</th>
                                    <th>Purpose</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <!-- Repaid Loans Tab -->
                    <div class="tab-pane fade" id="repaid-loans" role="tabpanel" aria-labelledby="repaid-tab">
                        <table id="repaidLoansTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Loan Amount</th>
                                    <th>Date</th>
                                    <th>Purpose</th>
                                    <th>Repaid Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Loan Modal -->
    <div class="modal fade" id="addLoanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addLoanForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalAddLoanTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                <?php
                                $query = "SELECT e.employee_id, u.firstname, u.lastname FROM employees e JOIN user u ON e.user_id = u.UserId";
                                $result = $conn->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="'.$row['employee_id'].'">'.$row['firstname'].' '.$row['lastname'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="loan_amount" class="form-label">Loan Amount</label>
                            <input type="number" class="form-control" id="loan_amount" name="loan_amount" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="loan_date" class="form-label">Loan Date</label>
                            <input type="date" class="form-control" id="loan_date" name="loan_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose</label>
                            <textarea class="form-control" id="purpose" name="purpose"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="report.saveLoan">Save Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once './footer.php'; ?>

    <script>
    $(document).ready(function() {
        // Initialize DataTables
        const activeTable = $('#activeLoansTable').DataTable({
            ajax: {
                url: 'get_employee_loans.php',
                data: { status: 'active' },
                dataSrc: ''
            },
            columns: [
                { data: 'employee_name' },
                { data: 'loan_amount', render: $.fn.dataTable.render.number(',', '.', 2, 'BIF') },
                { data: 'loan_date' },
                { data: 'purpose' },
                { data: 'outstanding_balance', render: $.fn.dataTable.render.number(',', '.', 2, 'BIF') },
                { 
                    data: 'status',
                    render: function(data, type, row) {
                        let badgeClass = 'badge ';
                        if (data === 'active') badgeClass += 'bg-primary';
                        else if (data === 'defaulted') badgeClass += 'bg-danger';
                        return '<span class="'+badgeClass+'">'+data.charAt(0).toUpperCase() + data.slice(1)+'</span>';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `<button class="btn btn-sm btn-outline-secondary view-repayments-btn" data-id="${row.loan_id}">View</button>`;
                    }
                }
            ]
        });

        const repaidTable = $('#repaidLoansTable').DataTable({
            ajax: {
                url: 'get_employee_loans.php',
                data: { status: 'repaid' },
                dataSrc: ''
            },
            columns: [
                { data: 'employee_name' },
                { data: 'loan_amount', render: $.fn.dataTable.render.number(',', '.', 2, 'BIF') },
                { data: 'loan_date' },
                { data: 'purpose' },
                { data: 'repaid_date' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `<button class="btn btn-sm btn-outline-secondary view-repayments-btn" data-id="${row.loan_id}">View</button>`;
                    }
                }
            ]
        });

        // Form submission for adding new loan
        $('#addLoanForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'add_employee_loan.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
            
                        activeTable.ajax.reload();
                        Swal.fire('Success', response.message, 'success');
                        $('#addLoanForm')[0].reset();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'An error occurred while processing your request', 'error');
                }
            });
        });

        // View repayments button click
        $('table').on('click', '.view-repayments-btn', function() {
            const loanId = $(this).data('id');
            // Implement repayment history view logic here
            Swal.fire({
                title: 'Repayment History',
                html: 'Loading repayment history...',
                showConfirmButton: false
            });

            $.ajax({
                url: 'get_loan_repayments.php',
                type: 'GET',
                data: { loan_id: loanId },
                success: function(response) {
                    if (response.success) {
                        let html = '<table class="table"><thead><tr><th>Amount</th><th>Date</th><th>Method</th></tr></thead><tbody>';
                        response.data.forEach(repayment => {
                            html += `<tr>
                                <td>${repayment.repayment_amount.toFixed(2)} BIF</td>
                                <td>${repayment.repayment_date}</td>
                                <td>${repayment.repayment_method}</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        
                        Swal.fire({
                            title: 'Repayment History',
                            html: html,
                            width: '800px'
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to load repayment history', 'error');
                }
            });
        });
    });
    </script>
      <script>
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
  </script>
</body>
</html>

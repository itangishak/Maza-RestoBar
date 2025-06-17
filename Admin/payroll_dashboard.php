<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

$isBoss = (isset($_SESSION['Privilege']) && (strtolower($_SESSION['Privilege']) === 'boss' || $_SESSION['Privilege'] === 'Boss'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Title will be translated -->
    <title data-key="report.payrollManagement"></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS Libraries (DataTables, Bootstrap, SweetAlert2, etc.) -->
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        /* Adjust styling if sidebar toggling is used */
        .sidebar.collapsed {
            width: 70px;
            transition: width 0.3s;
        }
        .main-container.full {
            margin-left: 0 !important;
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
                <!-- Header translated -->
                <h4 data-key="report.payrollManagement">Payroll Management</h4>
                <!-- Button translated using data-key -->
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPayrollModal" id="addPayrollBtn">Add Payroll</button>
            </div>
            <div class="card-body">
                <table id="payrollTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th data-key="report.employee">Employee</th>
                            <th data-key="report.payPeriodStart">Pay Period Start</th>
                            <th data-key="report.payPeriodEnd">Pay Period End</th>
                            <th data-key="report.grossPay">Gross Pay</th>
                            <th data-key="report.deductions">Deductions</th>
                            <th data-key="report.netPay">Net Pay</th>
                            <th data-key="report.paymentDate">Payment Date</th>
                            <th data-key="report.notes">Notes</th>
                            <th data-key="report.actionWithPdf">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Payroll Modal -->
    <div class="modal fade" id="addPayrollModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addPayrollForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalAddPayrollTitle">Add Payroll</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="employee_id" data-key="report.employeeLabel">Employee</label>
                        <select class="form-control mb-2" id="employee_id" name="employee_id" required onchange="calculateNetPay(this.value)">
                            <!-- Options loaded dynamically via loadEmployees() -->
                        </select>

                        <label for="pay_period_start" data-key="report.payPeriodStartLabel">Pay Period Start</label>
                        <input type="date" class="form-control mb-2" id="pay_period_start" name="pay_period_start" required>

                        <label for="pay_period_end" data-key="report.payPeriodEndLabel">Pay Period End</label>
                        <input type="date" class="form-control mb-2" id="pay_period_end" name="pay_period_end" required>

                        <label for="gross_pay" data-key="report.grossPayLabel">Gross Pay</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="gross_pay" name="gross_pay" required readonly>

                        <label for="bonus" data-key="report.bonusLabel">Bonus</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="bonus" name="bonus" value="0.00" onchange="calculateNetPay(document.getElementById('employee_id').value)">

                        <label for="deductions" data-key="report.deductionsLabel">Deductions</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="deductions" name="deductions" onchange="calculateNetPay(document.getElementById('employee_id').value)">

                        <label for="loan_repayment" data-key="report.loanRepaymentLabel">Loan Repayment</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="loan_repayment" name="loan_repayment" value="0.00" onchange="calculateNetPay(document.getElementById('employee_id').value)">

                        <label for="net_pay" data-key="report.netPayLabel">Net Pay</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="net_pay" name="net_pay" required readonly>

                        <label for="payment_date" data-key="report.paymentDateLabel">Payment Date</label>
                        <div class="d-flex mb-2 align-items-center">
                            <div class="flex-grow-1">
                                <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                            </div>
                            <div class="ms-2">
                                <select class="form-select form-select-sm payment-date-helper" data-target="#payment_date">
                                    <option value="">Set date to...</option>
                                    <option value="today">Today (Default)</option>
                                    <option value="end-of-month">End of Current Month</option>
                                    <option value="previous-month-end">End of Previous Month</option>
                                    <option value="next-day">Day After Period End</option>
                                    <option value="pay-period-end">Same as Period End</option>
                                </select>
                            </div>
                        </div>

                        <label for="notes" data-key="report.notes">Notes</label>
                        <textarea class="form-control mb-2" id="notes" name="notes"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="report.addPayrollBtn">Add Payroll</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Payroll Modal -->
    <div class="modal fade" id="editPayrollModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPayrollForm">
                    <input type="hidden" id="edit_payroll_id" name="payroll_id">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalEditPayrollTitle">Edit Payroll</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="edit_employee_id" data-key="report.employeeLabel">Employee</label>
                        <select class="form-control mb-2" id="edit_employee_id" name="employee_id" required></select>

                        <label for="edit_pay_period_start" data-key="report.payPeriodStartLabel">Pay Period Start</label>
                        <input type="date" class="form-control mb-2" id="edit_pay_period_start" name="pay_period_start" required>

                        <label for="edit_pay_period_end" data-key="report.payPeriodEndLabel">Pay Period End</label>
                        <input type="date" class="form-control mb-2" id="edit_pay_period_end" name="pay_period_end" required>

                        <label for="edit_gross_pay" data-key="report.grossPayLabel">Gross Pay</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="edit_gross_pay" name="gross_pay" required readonly>

                        <label for="edit_bonus" data-key="report.bonusLabel">Bonus</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="edit_bonus" name="bonus" value="0.00">

                        <label for="edit_deductions" data-key="report.deductionsLabel">Deductions</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="edit_deductions" name="deductions">

                        <label for="edit_net_pay" data-key="report.netPayLabel">Net Pay</label>
                        <input type="number" step="0.01" class="form-control mb-2" id="edit_net_pay" name="net_pay" required readonly>

                        <label for="edit_payment_date" data-key="report.paymentDateLabel">Payment Date</label>
                        <div class="d-flex mb-2 align-items-center">
                            <div class="flex-grow-1">
                                <input type="date" class="form-control" id="edit_payment_date" name="payment_date" required>
                            </div>
                            <div class="ms-2">
                                <select class="form-select form-select-sm payment-date-helper" data-target="#edit_payment_date">
                                    <option value="">Set date to...</option>
                                    <option value="today">Today (Default)</option>
                                    <option value="end-of-month">End of Current Month</option>
                                    <option value="previous-month-end">End of Previous Month</option>
                                    <option value="next-day">Day After Period End</option>
                                    <option value="pay-period-end">Same as Period End</option>
                                </select>
                            </div>
                        </div>

                        <label for="edit_notes" data-key="report.notes">Notes</label>
                        <textarea class="form-control mb-2" id="edit_notes" name="notes"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="report.saveChanges">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include_once './footer.php'; ?>



    <script>
    $(document).ready(function () {
        // Track form state
        let isNewRecord = true;
        let formChanged = false;
        let isDuplicateCheckPending = false;
        
        // Helper function to get translation or fallback
        function getTranslation(key, fallback) {
            const translations = JSON.parse(localStorage.getItem('translations') || '{}');
            return translations[key] || fallback;
        }
        
        // Initialize DataTable
        let table = $('#payrollTable').DataTable({
            ajax: 'fetch_payroll.php',
            columns: [
                { data: 'employee_name' },
                { data: 'pay_period_start' },
                { data: 'pay_period_end' },
                { data: 'gross_pay' },
                { data: 'deductions' },
                { data: 'net_pay' },
                { data: 'payment_date' },
                { data: 'notes' },
                {
                    data: null,
                    render: function (data, type, row) {
                        // Get translations from localStorage
                        const translations = JSON.parse(localStorage.getItem('translations') || '{}');
                        
                        return `
                            <div class="d-flex">
                                <button class="btn btn-primary btn-sm me-1 edit-btn" data-id="${row.payroll_id}" title="${translations['report.edit'] || 'Edit'}">
                                    <i class="fa fa-pencil-alt"></i>
                                </button>
                                <button class="btn btn-danger btn-sm me-1 delete-btn" data-id="${row.payroll_id}" title="${translations['report.delete'] || 'Delete'}">
                                    <i class="fa fa-trash-alt"></i>
                                </button>
                                <a href="generate_payroll_pdf.php?id=${row.payroll_id}" class="btn btn-info btn-sm" target="_blank" title="${translations['report.pdf'] || 'PDF'}">
                                    <i class="fa fa-file-pdf"></i>
                                </a>
                            </div>
                        `;
                    }
                }
            ]
        });

        // Re-run translation on every table draw (for newly created buttons)
        $('#payrollTable').on('draw.dt', function () {
          const translations = JSON.parse(localStorage.getItem('translations') || '{}');
          translatePage(translations);
        });

        // When bonus or deductions change, recalc net_pay (Add Form)
        $('#bonus, #deductions, #loan_repayment').on('input', function () {
            calculateNetPay('#bonus', '#deductions', '#loan_repayment', '#net_pay', '#gross_pay');
        });
        
        // For Edit Form
        $('#edit_bonus, #edit_deductions').on('input', function () {
            calculateNetPay('#edit_bonus', '#edit_deductions', '', '#edit_net_pay', '#edit_gross_pay');
        });
        
        // Helper function to calculate net pay
        function calculateNetPay(bonusField, deductionsField, loanRepaymentField, netField, grossField) {
            const bonus = parseFloat($(bonusField).val()) || 0;
            const ded = parseFloat($(deductionsField).val()) || 0;
            const loanRepayment = loanRepaymentField ? parseFloat($(loanRepaymentField).val()) || 0 : 0;
            const gross = parseFloat($(grossField).val()) || 0;
            const net = gross + bonus - ded - loanRepayment;
            $(netField).val(net.toFixed(2));
        }
        
        // Set payment date to end of pay period
        $('#pay_period_end').on('change', function() {
            if ($(this).val()) {
                $('#payment_date').val($(this).val());
            }
        });
        
        // Same for edit form
        $('#edit_pay_period_end').on('change', function() {
            if ($(this).val()) {
                $('#edit_payment_date').val($(this).val());
            }
        });
        
        // Validate pay period (end date should be after start date)
        $('#pay_period_end').on('change', function() {
            const startDate = $('#pay_period_start').val();
            const endDate = $(this).val();
            
            if (startDate && endDate && startDate > endDate) {
                Swal.fire('Warning', 'Pay period end date must be after start date', 'warning');
                $(this).val('');
                $('#payment_date').val('');
            }
        });
        
        // Same for edit form
        $('#edit_pay_period_end').on('change', function() {
            const startDate = $('#edit_pay_period_start').val();
            const endDate = $(this).val();
            
            if (startDate && endDate && startDate > endDate) {
                Swal.fire('Warning', 'Pay period end date must be after start date', 'warning');
                $(this).val('');
                $('#edit_payment_date').val('');
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

        // ============= ADD PAYROLL =============
        $('#addPayrollForm, #editPayrollForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const employeeId = form.find('[name="employee_id"]').val();
            
            // Get employee's active loan balance
            $.get('get_employee_loans.php?id=' + employeeId, function(response) {
                if (response.status === 'success' && response.data.length > 0) {
                    // Sum all outstanding balances
                    const totalBalance = response.data
                        .filter(loan => loan.status === 'active')
                        .reduce((sum, loan) => sum + parseFloat(loan.outstanding_balance), 0);
                    
                    // Populate loan repayment field
                    form.find('[name="loan_repayment"]').val(totalBalance.toFixed(2));
                    
                    // Calculate net pay
                    const grossPay = parseFloat(form.find('[name="gross_pay"]').val()) || 0;
                    const deductions = parseFloat(form.find('[name="deductions"]').val()) || 0;
                    const bonus = parseFloat(form.find('[name="bonus"]').val()) || 0;
                    const netPay = grossPay + bonus - deductions - totalBalance;
                    form.find('[name="net_pay"]').val(netPay.toFixed(2));
                }
                
                // Submit the form
                form.off('submit').submit();
            });
        });
        
        // ============= EDIT PAYROLL =============
        $(document).ready(function() {
            // Initialize modal
            $('#editPayrollModal').modal({show: false, backdrop: 'static'});
            
            // Clear form when modal hides
            $('#editPayrollModal').on('hidden.bs.modal', function() {
                $('#editPayrollForm')[0].reset();
            });
        });

        $('#payrollTable').on('click', '.edit-btn', function() {
            const payrollId = $(this).data('id');
            const editModal = $('#editPayrollModal');
            
            // Show loading state
            editModal.find('.modal-body').addClass('loading');
            
            // Load payroll data
            $.get('fetch_payroll_by_id.php?id=' + payrollId, function(data) {
                if (data.status === 'success') {
                    const dataObj = data.data; // Access the data object
                    $('#edit_payroll_id').val(dataObj.payroll_id);
                    
                    $.get('get_employee_name.php?id=' + dataObj.employee_id, function(response) {
                        if (response.status === 'success') {
                            const name = response.data.firstname + ' ' + response.data.lastname;
                            $('#edit_employee_id').empty().append(
                                $('<option>', {
                                    value: dataObj.employee_id,
                                    text: name
                                })
                            );
                        } else {
                            $('#edit_employee_id').empty().append(
                                $('<option>', {
                                    value: dataObj.employee_id,
                                    text: 'Employee #' + dataObj.employee_id
                                })
                            );
                        }
                    }).fail(function() {
                        $('#edit_employee_id').empty().append(
                            $('<option>', {
                                value: dataObj.employee_id,
                                text: 'Employee #' + dataObj.employee_id
                            })
                        );
                    });

                    $('#edit_pay_period_start').val(dataObj.pay_period_start);
                    $('#edit_pay_period_end').val(dataObj.pay_period_end);
                    $('#edit_gross_pay').val(dataObj.gross_pay);
                    $('#edit_bonus').val(dataObj.bonus || '0.00');
                    $('#edit_deductions').val(dataObj.deductions || '0.00');
                    $('#edit_net_pay').val(dataObj.net_pay);
                    $('#edit_payment_date').val(dataObj.payment_date);
                    $('#edit_notes').val(dataObj.notes || '');
                    
                    // Show modal
                    editModal.modal('show');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            }).always(function() {
                editModal.find('.modal-body').removeClass('loading');
            });
        });

        $('#editPayrollForm').on('submit', function(e) {
            e.preventDefault();
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'update_payroll.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    submitBtn.prop('disabled', false);
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                        table.ajax.reload();
                        $('#editPayrollForm')[0].reset();
                        
                        // Close modal after slight delay
                        setTimeout(() => {
                            $('#editPayrollModal').modal('hide');
                        }, 500);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    submitBtn.prop('disabled', false);
                    Swal.fire('Error', 'Update failed: ' + error, 'error');
                }
            });
        });

        // ============= DELETE PAYROLL =============
        $('#payrollTable').on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirm Operation',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_payroll.php', { id: id }, function (response) {
                        // Customize the success message
                        if (response.status === 'success') {
                            Swal.fire(
                                'Operation Completed',
                                'The payroll record has been successfully deleted.',
                                'success'
                            );
                        } else {
                        Swal.fire(response.status, response.message, response.status);
                        }
                        table.ajax.reload();
                    }, 'json');
                }
            });
        });

        // ============= LOAD EMPLOYEES (For Add Modal) =============
        function loadEmployees() {
            $.get('fetch_employee.php', function (response) {
                const employeeDropdown = $('#employee_id');
                
                // Detach existing change event handlers to avoid duplicates
                employeeDropdown.off('change');
                
                employeeDropdown.empty(); // Clear existing options
                
                // Add a default/placeholder option
                employeeDropdown.append(new Option('Select Employee', ''));
                
                if (response.status === 'success') {
                    response.data.forEach(employee => {
                        employeeDropdown.append(new Option(employee.employee_name, employee.employee_id));
                    });
                    
                    // Re-add change event handler to populate gross_pay
                    employeeDropdown.on('change', function() {
                        const selectedEmployeeId = $(this).val();
                        if (selectedEmployeeId) {
                            fetchEmployeeSalary(selectedEmployeeId, '#net_pay', '#gross_pay');
                        } else {
                            $('#net_pay').val('');
                            $('#gross_pay').val('');
                        }
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        }
        
        // Function to fetch employee salary and populate gross_pay field
        function fetchEmployeeSalary(employeeId, targetFieldNet, targetFieldGross) {
            // Show loading indicator
            const formGroup = $(targetFieldGross).closest('.form-group');
            formGroup.append('<div class="spinner-border spinner-border-sm text-primary ml-2 salary-loader" role="status"><span class="visually-hidden">Loading...</span></div>');
            
            $.ajax({
                url: 'fetch_employee_salary.php',
                type: 'GET',
                data: { id: employeeId },
                success: function(response) {
                    // Remove loading indicator
                    formGroup.find('.salary-loader').remove();
                    
                    if (response.status === 'success') {
                        const salary = parseFloat(response.data.salary) || 0;
                        
                        // Check if salary is 0 - might be null in database
                        if (salary === 0) {
                            const feedbackMsg = $('<small class="text-warning ml-2 salary-feedback">No salary defined for this employee</small>');
                            formGroup.append(feedbackMsg);
                            
                            // Fade out feedback after 3 seconds
                            setTimeout(() => {
                                feedbackMsg.fadeOut(500, function() { $(this).remove(); });
                            }, 3000);
                            
                            // Still set value to 0 but don't overwrite if already populated
                            if (!$(targetFieldGross).val()) {
                                $(targetFieldGross).val('0.00');
                                $(targetFieldGross).trigger('input'); // Trigger for net pay
                            }
                        } else {
                            $(targetFieldGross).val(salary.toFixed(2));
                            $(targetFieldGross).trigger('input'); // Trigger for net pay
                            
                            // Add success feedback message
                            const feedbackMsg = $('<small class="text-success ml-2 salary-feedback">Salary loaded</small>');
                            formGroup.append(feedbackMsg);
                            
                            // Fade out feedback after 3 seconds
                            setTimeout(() => {
                                feedbackMsg.fadeOut(500, function() { $(this).remove(); });
                            }, 3000);
                        }
                    } else {
                        console.error('Error fetching salary:', response.message);
                        Swal.fire('Warning', 'Could not fetch employee salary. Please enter manually.', 'warning');
                    }
                    
                    // Now fetch loan balance
                    $.get('get_employee_loans.php', { employee_id: employeeId }, function(loanResponse) {
                        console.log('Loan Response:', loanResponse); // Debug log
                        
                        if (loanResponse.success === true) {  
                            const outstandingBalance = parseFloat(loanResponse.outstanding_balance) || 0;
                            console.log('Parsed Balance:', outstandingBalance); // Debug log
                            
                            const modal = $(targetFieldGross).closest('.modal');
                            const loanRepaymentField = modal.find('input[name="loan_repayment"]');
                            
                            // Clear any previous balance messages
                            loanRepaymentField.next('.text-info').remove();
                            
                            if (outstandingBalance > 0) {
                                // Auto-populate with full balance but allow manual adjustment
                                loanRepaymentField.val(outstandingBalance.toFixed(2));
                                
                               
                                // Add validation to prevent exceeding gross pay
                                loanRepaymentField.on('input', function() {
                                    const grossPay = parseFloat($(targetFieldGross).val());
                                    if (parseFloat(this.value) > grossPay) {
                                        Swal.fire('Warning', 'Repayment cannot exceed gross pay', 'warning');
                                        this.value = grossPay.toFixed(2);
                                    }
                                    // Ensure net pay is recalculated after loan repayment change
                                    const employeeId = $('#employee_id').val();
                                    calculateNetPay(employeeId);
                                });
                            } else {
                                loanRepaymentField.val('0.00');
                            }
                            
                            // Initial calculation
                            const employeeId = $('#employee_id').val();
                            calculateNetPay(employeeId);
                        } else {
                            console.error('Loan Error:', loanResponse.message || 'Unknown error');
                            Swal.fire('Info', loanResponse.message || 'Could not fetch loan details', 'info');
                        }
                    }, 'json');
                },
                error: function(xhr, status, error) {
                    // Remove loading indicator
                    formGroup.find('.salary-loader').remove();
                    
                    console.error('AJAX Error:', status, error);
                    Swal.fire('Warning', 'Could not fetch employee salary. Please enter manually.', 'warning');
                }
            });
        }

        // Function to initialize payroll form with default values
        function initializePayrollForm() {
            // Auto-populate dates
            const today = new Date();
            const currentDateFormatted = today.toISOString().split('T')[0]; // YYYY-MM-DD format
            
            // Set pay period start to first day of PREVIOUS month
            const firstDayOfPreviousMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const firstDayFormatted = firstDayOfPreviousMonth.toISOString().split('T')[0];
            
            // Set pay period end to last day of PREVIOUS month
            const lastDayOfPreviousMonth = new Date(today.getFullYear(), today.getMonth(), 0);
            const lastDayFormatted = lastDayOfPreviousMonth.toISOString().split('T')[0];
            
            // Set the dates
            $('#pay_period_start').val(firstDayFormatted);
            $('#pay_period_end').val(lastDayFormatted);
            $('#payment_date').val(currentDateFormatted); // Payment date defaults to TODAY
            
            // Set deductions to zero by default
            $('#deductions').val('0.00');
            $('#loan_repayment').val('0.00');
            
            // If gross pay already has a value, calculate net pay
            if ($('#gross_pay').val()) {
                calculateNetPay('#bonus', '#deductions', '#loan_repayment', '#net_pay', '#gross_pay');
            } else {
                // Clear net pay field if no gross pay
                $('#net_pay').val('');
            }
            
            // Load employees if the dropdown is empty
            if ($('#employee_id option').length <= 1) {
                loadEmployees();
            }
        }

        // Populate employees when Add Payroll modal opens
        $('#addPayrollModal').modal({
            backdrop: 'static',
            keyboard: false,
            focus: true
        }).on('show.bs.modal', function() {
            loadEmployees();
            initializePayrollForm();
        });
        
        // Also update the edit form to fetch salary when employee is changed
        $(document).on('change', '#edit_employee_id', function() {
            const selectedEmployeeId = $(this).val();
            if (selectedEmployeeId) {
                fetchEmployeeSalary(selectedEmployeeId, '#edit_net_pay', '#edit_gross_pay');
            }
        });

        // Update the edit modal initialization to match add modal
        $('#editPayrollModal').on('show.bs.modal', function() {
            const employeeId = $('#edit_employee_id').val();
        });

        // Handle payment date helper dropdown
        $(document).on('change', '.payment-date-helper', function() {
            const option = $(this).val();
            const targetField = $(this).data('target');
            
            if (!option) return; // No option selected
            
            const today = new Date();
            let dateToSet;
            
            switch(option) {
                case 'today':
                    dateToSet = today.toISOString().split('T')[0];
                    break;
                    
                case 'end-of-month':
                    // Last day of current month
                    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    dateToSet = lastDay.toISOString().split('T')[0];
                    break;
                
                case 'previous-month-end':
                    // Last day of previous month
                    const lastDayPrevMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                    dateToSet = lastDayPrevMonth.toISOString().split('T')[0];
                    break;
                    
                case 'next-day':
                    // One day after period end
                    const periodEndField = targetField === '#payment_date' ? '#pay_period_end' : '#edit_pay_period_end';
                    const periodEndValue = $(periodEndField).val();
                    
                    if (periodEndValue) {
                        const periodEndDate = new Date(periodEndValue);
                        const nextDay = new Date(periodEndDate);
                        nextDay.setDate(periodEndDate.getDate() + 1);
                        dateToSet = nextDay.toISOString().split('T')[0];
                    } else {
                        Swal.fire('Warning', 'Please set a Pay Period End date first', 'warning');
                        $(this).val(''); // Reset dropdown
                        return;
                    }
                    break;
                    
                case 'pay-period-end':
                    // Same as period end
                    const periodField = targetField === '#payment_date' ? '#pay_period_end' : '#edit_pay_period_end';
                    dateToSet = $(periodField).val();
                    
                    if (!dateToSet) {
                        Swal.fire('Warning', 'Please set a Pay Period End date first', 'warning');
                        $(this).val(''); // Reset dropdown
                        return;
                    }
                    break;
            }
            
            $(targetField).val(dateToSet);
            $(this).val(''); // Reset dropdown after use
        });

        // Helper function to format date if needed
        function formatDateIfNeeded(dateString) {
            if (!dateString) return '';
            
            // Check if the date is already in YYYY-MM-DD format
            if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                return dateString;
            }
            
            // Try to parse and format the date
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString; // Invalid date, return as is
                
                return date.toISOString().split('T')[0]; // YYYY-MM-DD
            } catch (e) {
                console.error('Error formatting date:', e);
                return dateString; // Return original on error
            }
        }

        // Reset form state on modal show/hide
        $('#addPayrollModal').on('show.bs.modal', function() {
            isNewRecord = true;
            formChanged = false;
        });
        
        $('#addPayrollModal').on('hide.bs.modal', function(e) {
            // Remove the unsaved changes confirmation, as requested
            formChanged = false; // Reset flag to prevent confirmation
        });
        
        // Track form changes
        $('#addPayrollForm').on('input change', 'input, select, textarea', function() {
            formChanged = true;
        });
        
        // Reset button should re-initialize form
        $('#addPayrollModal .btn-secondary').on('click', function() {
            setTimeout(() => {
                $('#addPayrollForm')[0].reset();
                initializePayrollForm();
                formChanged = false;
            }, 100);
        });
        
        // Prevent double submits
        $('#addPayrollForm').on('submit', function() {
            $(this).find('button[type="submit"]').prop('disabled', true);
            setTimeout(() => {
                $(this).find('button[type="submit"]').prop('disabled', false);
            }, 3000);
        });

        // Global event handler for PDF downloads
        $(document).on('click', '.pdf-download-link, .pdf-auto-close', function() {
            // Close SweetAlert if open
            if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                Swal.close();
            }
            
            // Close any notification
            setTimeout(() => {
                $('.pdf-download-notification').alert('close');
                $('.alert').alert('close');
            }, 800);
            
            // Close modals
            $('#addPayrollModal').modal('hide');
            $('#editPayrollModal').modal('hide');
        });
    });
    </script>

    <script>
    function calculateNetPay(employeeId) {
        // Get all input elements
        const employeeSelect = document.getElementById('employee_id');
        const bonusInput = document.getElementById('bonus');
        const deductionsInput = document.getElementById('deductions');
        const loanRepaymentInput = document.getElementById('loan_repayment');
        const netPayInput = document.getElementById('net_pay');
        
        // Set default values if empty/undefined
        const bonus = parseFloat(bonusInput.value) || 0;
        const deductions = parseFloat(deductionsInput.value) || 0;
        const loanRepayment = parseFloat(loanRepaymentInput.value) || 0;
        
        // Fetch employee's basic salary and loans when employee is selected
        if (employeeId) {
            // First fetch salary
            fetch('get_employee_salary.php?employee_id=' + employeeId)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    
                    const basicSalary = parseFloat(data.salary) || 0;
                    
                    // Then fetch any outstanding loans
                    fetch('get_employee_loans.php?employee_id=' + employeeId)
                        .then(response => response.json())
                        .then(loanData => {
                            let calculatedLoanRepayment = 0;
                            
                            if (loanData.success && loanData.outstanding_balance > 0) {
                                calculatedLoanRepayment = parseFloat(loanData.outstanding_balance) || 0;
                                loanRepaymentInput.value = calculatedLoanRepayment.toFixed(2);
                            }
                            
                            // Calculate final net pay (salary + bonus - deductions - loan repayment)
                            const netPay = basicSalary + bonus - deductions - calculatedLoanRepayment;
                            netPayInput.value = Math.max(0, netPay).toFixed(2);
                        });
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Calculate with just bonus/deductions/loan if salary fetch fails
                    const calculatedNet = bonus - deductions - loanRepayment;
                    netPayInput.value = calculatedNet.toFixed(2);
                });
        } else {
            // Calculate with just bonus/deductions/loan if no employee selected
            const calculatedNet = bonus - deductions - loanRepayment;
            netPayInput.value = calculatedNet.toFixed(2);
        }
    }
    
    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const employeeSelect = document.getElementById('employee_id');
        if (employeeSelect) {
            employeeSelect.addEventListener('change', function() {
                calculateNetPay(this.value);
            });
        }
        
        ['bonus', 'deductions', 'loan_repayment', 'pay_period_start', 'pay_period_end'].forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('change', function() {
                    const employeeId = document.getElementById('employee_id').value;
                    if (employeeId) calculateNetPay(employeeId);
                });
            }
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Make sure SweetAlert buttons are always translated properly
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    const confirmBtn = document.querySelector('.swal2-confirm');
                    const cancelBtn = document.querySelector('.swal2-cancel');
                    
                    if (confirmBtn) {
                        const translations = JSON.parse(localStorage.getItem('translations') || '{}');
                        confirmBtn.textContent = translations['common.confirmDelete'] || 'Confirm Operation';
                    }
                    
                    if (cancelBtn) {
                        const translations = JSON.parse(localStorage.getItem('translations') || '{}');
                        cancelBtn.textContent = translations['common.cancel'] || 'Cancel';
                    }
                }
            });
        });
      
        observer.observe(document.body, { childList: true, subtree: true });
    });
    </script>
</body>
</html>

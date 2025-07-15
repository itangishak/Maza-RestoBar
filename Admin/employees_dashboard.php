<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Title will be translated -->
    <title data-key="report.employeesManagement"></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS Libraries -->
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
    </style>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebar.php'; ?>

    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <!-- Header translated -->
                <h4 data-key="report.employeesManagement"></h4>
                <!-- Button translated (using textContent via translation script) -->
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal" data-key="report.addEmployee"></button>
            </div>
            <div class="card-body">
                <table id="employeeTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th data-key="report.name"></th>
                            <th data-key="report.email"></th>
                            <th data-key="report.position"></th>
                            <th data-key="report.salary"></th>
                            <th data-key="report.hireDate"></th>
                            <th data-key="report.action"></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addEmployeeForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalAddEmployeeTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="user_id" data-key="report.user"></label>
                        <select class="form-control mb-2" id="user_id" name="user_id" required></select>

                        <label for="position" data-key="report.positionLabel"></label>
                        <input type="text" class="form-control mb-2" id="position" name="position" required>

                        <label for="salary" data-key="report.salaryLabel"></label>
                        <input type="number" class="form-control mb-2" id="salary" name="salary" required>

                        <label for="hire_date" data-key="report.hireDateLabel"></label>
                        <input type="date" class="form-control mb-2" id="hire_date" name="hire_date" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.close"></button>
                        <button type="submit" class="btn btn-primary" data-key="report.addEmployeeBtn"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editEmployeeForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalEditEmployeeTitle"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_employee_id" name="employee_id">
                        <label for="edit_position" data-key="report.positionLabel"></label>
                        <input type="text" class="form-control mb-2" id="edit_position" name="position" required>

                        <label for="edit_salary" data-key="report.salaryLabel"></label>
                        <input type="number" class="form-control mb-2" id="edit_salary" name="salary" required>

                        <label for="edit_hire_date" data-key="report.hireDateLabel"></label>
                        <input type="date" class="form-control mb-2" id="edit_hire_date" name="hire_date" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.close"></button>
                        <button type="submit" class="btn btn-primary" data-key="report.saveChanges"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once './footer.php'; ?>

    <!-- JS Libraries -->

    <script>
    $(document).ready(function () {
        // Initialize DataTable
        let table = $('#employeeTable').DataTable({
            ajax: 'fetch_employees.php',
            columns: [
                { data: 'name' },
                { data: 'email' },
                { data: 'position' },
                { data: 'salary', render: $.fn.dataTable.render.number(',', '.', 2, 'BIF') },
                { data: 'hire_date' },
                { 
                    data: null,
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary edit-btn" data-id="${row.employee_id}" data-key="report.edit"></button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${row.employee_id}" data-key="report.delete"></button>
                        `;
                    }
                }
            ],
            dom: 'Bfrtip',
            buttons: ['copy', 'pdf', 'print']
        });

        // Re-translate new rows when table is drawn
        $('#employeeTable').on('draw.dt', function () {
          const translations = JSON.parse(localStorage.getItem('translations') || '{}');
          translatePage(translations);
        });

        // Load users into the dropdown dynamically
        function loadUsers() {
            $.get('fetch_users.php', function (response) {
                const userDropdown = $('#user_id');
                userDropdown.empty();
                if (response.status === 'success') {
                    response.data.forEach(user => {
                        userDropdown.append(new Option(user.name, user.UserId));
                    });
                    userDropdown.append(new Option('Create New User', 'create_new'));
                } else {
                    Swal.fire('Error', 'Failed to load users.', 'error');
                }
            }, 'json');
        }

        $('#addEmployeeModal').on('show.bs.modal', loadUsers);

        // Redirect if "Create New User" selected
        $('#user_id').on('change', function () {
            if ($(this).val() === 'create_new') {
                window.location.href = 'users_dashboard.php';
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

        // Add Employee
        $('#addEmployeeForm').on('submit', function (e) {
            e.preventDefault();
            $.post('add_employee.php', $(this).serialize(), function (response) {
                if (response.status === 'success') {
                    Swal.fire('Success', response.message, 'success');
                    $('#addEmployeeForm')[0].reset();
                    table.ajax.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        });

        // Edit Employee (open modal)
        $('#employeeTable').on('click', '.edit-btn', function () {
            const employeeId = $(this).data('id');
            $.get('get_employee.php', { employee_id: employeeId }, function (response) {
                if (response.status === 'success') {
                    const employee = response.data;
                    $('#edit_employee_id').val(employee.employee_id);
                    $('#edit_position').val(employee.position);
                    $('#edit_salary').val(employee.salary);
                    $('#edit_hire_date').val(employee.hire_date);
                    $('#editEmployeeModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        });

        $('#editEmployeeForm').on('submit', function (e) {
            e.preventDefault();
            $.post('update_employee.php', $(this).serialize(), function (response) {
                if (response.status === 'success') {
                    Swal.fire('Success', response.message, 'success');
                    $('#editEmployeeModal').modal('hide');
                    table.ajax.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        });

        // Delete Employee
        $('#employeeTable').on('click', '.delete-btn', function () {
            const employeeId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_employee.php', { employee_id: employeeId }, function (response) {
                        if (response.status === 'success') {
                            Swal.fire('Deleted!', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }, 'json');
                }
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

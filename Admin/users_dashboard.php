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
    <title data-key="users.title">Users Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS Libraries -->
    
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
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>
    <!-- End Sidebar -->

    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 data-key="users.title">Users Management</h4>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal" data-key="users.addUserBtn">
                    + Add User
                </button>
            </div>
            <div class="card-body">
                <table id="userTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th data-key="users.table.name">Name</th>
                            <th data-key="users.table.email">Email</th>
                            <th data-key="users.table.privilege">Privilege</th>
                            <th data-key="users.table.image">Image</th>
                            <th data-key="users.table.regDate">Registration Date</th>
                            <th data-key="users.table.action">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addUserForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="users.addNewUser">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="firstname" data-key="users.firstName">First Name</label>
                        <input type="text" class="form-control mb-2" id="firstname" name="firstname" required>
                        
                        <label for="lastname" data-key="users.lastName">Last Name</label>
                        <input type="text" class="form-control mb-2" id="lastname" name="lastname" required>
                        
                        <label for="email" data-key="users.email">Email</label>
                        <input type="email" class="form-control mb-2" id="email" name="email" required>
                        
                        <label for="password" data-key="users.password">Password</label>
                        <input type="password" class="form-control mb-2" id="password" name="password" required>
                        
                        <label for="privilege" data-key="users.privilege">Privilege</label>
                        <select class="form-control mb-2" id="privilege" name="privilege" required>
                            <option value="Admin" data-key="users.privilegeAdmin">Admin</option>
                            <option value="User" data-key="users.privilegeUser">User</option>
                            <option value="Manager" data-key="users.privilegeManager">Manager</option>
                            <option value="Storekeeper" data-key="users.privilegeStorekeeper">Storekeeper</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="users.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="users.addUser">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel" data-key="users.editUser">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_firstname" class="form-label" data-key="users.firstName">First Name</label>
                            <input type="text" class="form-control" id="edit_firstname" name="firstname" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_lastname" class="form-label" data-key="users.lastName">Last Name</label>
                            <input type="text" class="form-control" id="edit_lastname" name="lastname" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label" data-key="users.email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_privilege" class="form-label" data-key="users.privilege">Privilege</label>
                            <select class="form-select" id="edit_privilege" name="privilege" required>
                                <option value="" disabled data-key="users.privilegeSelect">Select Privilege</option>
                                <option value="Boss" data-key="users.privilegeBoss">Boss</option>
                                <option value="User" data-key="users.privilegeUser">User</option>
                                <option value="Manager" data-key="users.privilegeManager">Manager</option>
                                <option value="Admin" data-key="users.privilegeAdmin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="users.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="users.saveChanges">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once './footer.php'; ?>

    <!-- ======================================
         JS Libraries: jQuery, DataTables, Buttons, Bootstrap
        (Ensure jQuery and DataTables load before your initialization code)
        ====================================== -->
    <!-- Include the print button extension -->

    <script>
    $(document).ready(function () {
        // Initialize DataTable
        let table = $('#userTable').DataTable({
            ajax: {
                url: 'fetch_users_list.php', // Replace with your actual endpoint
                type: 'POST',
                dataSrc: 'data'
            },
            columns: [
                { data: 'name', title: getTranslation('users.table.name') },
                { data: 'email', title: getTranslation('users.table.email') },
                { data: 'privilege', title: getTranslation('users.table.privilege') },
                {
                    data: 'image',
                    title: getTranslation('users.table.image'),
                    render: function (data) {
                        return data
                            ? `<img src="${data}" alt="Profile Image" style="width:50px;height:50px;border-radius:50%;">`
                            : 'No Image';
                    }
                },
                { data: 'reg_date', title: getTranslation('users.table.regDate') },
                {
                    data: null,
                    title: getTranslation('users.table.action'),
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary edit-btn" data-id="${row.UserId}" data-key="users.edit">
                                ${getTranslation('users.edit')}
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${row.UserId}" data-key="users.delete">
                                ${getTranslation('users.delete')}
                            </button>
                        `;
                    }
                },
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: getTranslation('users.copy')
                },
                {
                    extend: 'pdf',
                    text: getTranslation('users.exportPdf')
                },
                {
                    extend: 'print',
                    text: getTranslation('users.print')
                }
            ]
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

        // Add User
        $('#addUserForm').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: 'add_user.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire(getTranslation('users.success'), response.message, 'success');
                        $('#addUserForm')[0].reset();
                        table.ajax.reload();
                    } else {
                        Swal.fire(getTranslation('users.error'), response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('users.error'), 'Failed to add user.', 'error');
                }
            });
        });

        // Edit Button
        $('#userTable').on('click', '.edit-btn', function () {
            const userId = $(this).data('id');
            $.post('fetch_user.php', { user_id: userId }, function (res) {
                if (res.status === 'success') {
                    const user = res.data;
                    $('#edit_user_id').val(user.UserId);
                    $('#edit_firstname').val(user.firstname);
                    $('#edit_lastname').val(user.lastname);
                    $('#edit_email').val(user.email);
                    $('#edit_privilege').val(user.privilege);
                    $('#editUserModal').modal('show');
                } else {
                    Swal.fire(getTranslation('users.error'), res.message, 'error');
                }
            }, 'json').fail(function () {
                Swal.fire(getTranslation('users.error'), 'Failed to fetch user details.', 'error');
            });
        });

        // Update User
        $('#editUserForm').on('submit', function (e) {
            e.preventDefault();
            $.post('edit_user.php', $(this).serialize(), function (res) {
                if (res.status === 'success') {
                    Swal.fire(getTranslation('users.success'), res.message, 'success');
                    $('#editUserModal').modal('hide');
                    table.ajax.reload();
                } else {
                    Swal.fire(getTranslation('users.error'), res.message, 'error');
                }
            }, 'json').fail(function () {
                Swal.fire(getTranslation('users.error'), 'Failed to update user.', 'error');
            });
        });

        // Delete Button
        $('#userTable').on('click', '.delete-btn', function () {
            const userId = $(this).data('id');
            Swal.fire({
                title: getTranslation('users.confirmDelete'),
                text: getTranslation('users.unrevertable'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: getTranslation('users.deleteConfirm'),
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_user.php', { user_id: userId }, function (res) {
                        if (res.status === 'success') {
                            Swal.fire(getTranslation('users.success'), res.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire(getTranslation('users.error'), res.message, 'error');
                        }
                    }, 'json')
                    .fail(function () {
                        Swal.fire(getTranslation('users.error'), 'Failed to delete user.', 'error');
                    });
                }
            });
        });

        // Utility function for DataTables column headers & action buttons
        function getTranslation(key) {
            const translations = JSON.parse(localStorage.getItem('translations')) || {};
            return translations[key] || key; // fallback if missing
        }
    });
    </script>

    <!-- Sidebar toggling logic -->
    <script>
    $(document).ready(function () {
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
    });
    </script>

    <!-- Translation Script (with button fix) -->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const lang = localStorage.getItem('userLang') || 'en';
        fetch(`../languages/${lang}.json`)
          .then(response => {
              if (!response.ok) {
                  console.error("Translation file not found:", response.statusText);
              }
              return response.json();
          })
          .then(data => {
              console.log("Translations loaded:", data);
              translatePage(data);
          })
          .catch(error => console.error("Error fetching translations:", error));
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

<?php
session_start();
require_once 'connection.php'; // or the path to your DB connection

// Optional debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in (example)
if (!isset($_SESSION['UserId'])) {
    $_SESSION['UserId'] = 1; 
    $_SESSION['privilege'] = 'Administrateur';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="menu.dashboard">Menu Dashboard</title>

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

      <!-- Navbar (if desired) -->
      <?php  include_once './navbar.php'; ?>

 <!-- ======= Sidebar ======= -->
 <?php include_once 'sidebar.php'; ?>
<!-- End Sidebar -->

    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 data-key="menu.dashboard">Menu Dashboard</h4>
                <!-- Instead of an Add Modal, we have a link to menu.php -->
                <a href="menu.php" class="btn btn-primary btn-sm" data-key="menu.createMenu">Create Menu</a>
            </div>
            <div class="card-body">
                <table id="menuTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th data-key="menu.menuId">Menu ID</th>
                            <th data-key="menu.name">Name</th>
                            <th data-key="menu.category">Category</th>
                            <th data-key="menu.price">Price</th>
                            <th data-key="menu.availability">Availability</th>
                            <th data-key="menu.action">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

     <!-- Include Footer -->
  <?php include_once './footer.php'; ?>

    <script>
    $(document).ready(function () {
        // Initialize DataTable
        let table = $('#menuTable').DataTable({
            ajax: 'fetch_menus.php', // This script returns { "data": [ ... ] } in JSON
            columns: [
                { data: 'menu_id',      "data-key": "menu.menuId" },
                { data: 'name',         "data-key": "menu.name" },
                { data: 'category_name',"data-key": "menu.category" }, 
                { data: 'price',        "data-key": "menu.price" },
                { data: 'availability', "data-key": "menu.availability" },
                {
                    data: null,
                    "data-key": "menu.action",
                    render: function (data, type, row) {
                        // Example: link to an edit page (no modal) + a delete button
                        return `
                            <a href="edit_menu.php?id=${row.menu_id}" 
                                class="btn btn-sm btn-primary me-2" data-key="category.edit">
                                Edit
                            </a>
                            <button class="btn btn-sm btn-danger delete-btn"
                                data-id="${row.menu_id}" data-key="category.delete">
                                Delete
                            </button>
                        `;
                    }
                }
            ]
        });

            // Sidebar toggling logic
            const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
            const sidebar = document.querySelector('.sidebar');
            const mainContainer = document.querySelector('.main-container');
            const card = document.querySelector('.card');

            // Adjust card size on page load
            if (sidebar && card) {
                if (!sidebar.classList.contains('collapsed')) {
                    card.style.width = '87%';
                    card.style.marginLeft = '20%';
                } else {
                    card.style.width = '100%';
                    card.style.marginLeft = '5%';
                }
            }

            // Toggle sidebar functionality
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

        // Handle delete
        $('#menuTable').on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_menu.php', { id: id }, function (response) {
                        // Show success or error
                        Swal.fire(response.status, response.message, response.status);
                        table.ajax.reload();
                    }, 'json')
                    .fail(function (xhr) {
                        console.error('Delete error:', xhr.responseText);
                        Swal.fire('Error', 'Failed to delete menu.', 'error');
                    });
                }
            });
        });

        // Basic Translation Logic (only if not in header/footer)
        const systemLang = navigator.language || navigator.userLanguage;
        let defaultLang = 'en';
        if (systemLang.startsWith('fr')) {
            defaultLang = 'fr';
        }
        let currentLang = localStorage.getItem('userLang') || defaultLang;
        loadTranslations(currentLang);
        
        function loadTranslations(lang) {
            fetch('../languages/' + lang + '.json')
                .then(response => response.json())
                .then(translations => {
                    localStorage.setItem('translations', JSON.stringify(translations));
                    translatePage(translations);
                    updateDataTableHeaders(translations);
                })
                .catch(error => console.error('Error loading translations:', error));
        }
        
        function translatePage(translations) {
            document.querySelectorAll('[data-key]').forEach(element => {
                const key = element.getAttribute('data-key');
                if (translations[key]) {
                    element.textContent = translations[key];
                }
            });
        }
        
        function updateDataTableHeaders(translations) {
            const columns = table.settings()[0].aoColumns;
            columns.forEach((column, index) => {
                const key = column.sTitle.match(/data-key="([^"]+)"/)?.[1];
                if (key && translations[key]) {
                    table.column(index).header().textContent = translations[key];
                }
            });
            table.draw();
        }
    });
    </script>
</body>
</html>
<?php
session_start();
require_once 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-key="category.title">Category Dashboard</title>

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
<?php include_once './navbar.php'; ?>
<!-- ======= Sidebar ======= -->
<?php include_once 'sidebar.php'; ?>
<!-- End Sidebar -->
<div class="container main-container">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 data-key="category.dashboard">Category Dashboard</h4>
            <button class="btn btn-primary" id="addCategoryBtn" data-key="category.addCategoryBtn">Add Category</button>
        </div>
        <div class="card-body">
            <table id="categoryTable" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th data-key="category.id">ID</th>
                    <th data-key="category.name">Name</th>
                    <th data-key="category.description">Description</th>
                    <th data-key="category.createdAt">Created At</th>
                    <th data-key="category.actions">Actions</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addCategoryForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryLabel" data-key="category.addCategory">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addName" class="form-label" data-key="category.name">Name</label>
                        <input type="text" class="form-control" id="addName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="addDescription" class="form-label" data-key="category.description">Description</label>
                        <textarea class="form-control" id="addDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="category.close">Close</button>
                    <button type="submit" class="btn btn-primary" data-key="category.save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel" data-key="category.editCategory">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_id">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label" data-key="category.name">Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategoryDescription" class="form-label" data-key="category.description">Description</label>
                        <textarea class="form-control" id="editCategoryDescription" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="category.close">Close</button>
                    <button type="submit" class="btn btn-primary" data-key="category.saveChanges">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Footer -->
<?php include_once './footer.php'; ?>

<script>
$(document).ready(function () {
    const categoryTable = $('#categoryTable').DataTable({
        ajax: 'fetch_category.php',
        columns: [
            { data: 'category_id' },
            { data: 'name' },
            { data: 'description' },
            { data: 'created_at' },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${row.category_id}">${getTranslation('category.edit')}</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.category_id}">${getTranslation('category.delete')}</button>
                    `;
                },
            },
        ],
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

    $('#addCategoryBtn').on('click', function () {
        $('#addCategoryModal').modal('show');
    });

    $('#addCategoryForm').on('submit', function (e) {
        e.preventDefault();
        const lang = localStorage.getItem('userLang') || 'en';
        const formData = $(this).serialize() + '&lang=' + encodeURIComponent(lang); // Add language to form data

        $.ajax({
            url: 'add_categories.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire(getTranslation('category.success'), response.message, 'success');
                    $('#addCategoryModal').modal('hide');
                    categoryTable.ajax.reload();
                } else {
                    Swal.fire(getTranslation('category.error'), response.message, 'error');
                }
            },
            error: function () {
                Swal.fire(getTranslation('category.error'), getTranslation('category.failedAdd'), 'error');
            },
        });
    });

    $(document).on('click', '.edit-btn', function () {
        const categoryId = $(this).data('id'); // Get the category ID from the button

        if (!categoryId) {
            Swal.fire(getTranslation('category.error'), getTranslation('category.invalidId'), 'error');
            return;
        }

        // Make an AJAX call to fetch the category details
        $.ajax({
            url: 'get_category_details.php',
            type: 'GET',
            data: { category_id: categoryId, lang: localStorage.getItem('userLang') || 'en' }, // Pass language
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    // Populate the modal fields with the category data
                    $('#editCategoryForm [name="category_id"]').val(response.data.category_id);
                    $('#editCategoryForm [name="name"]').val(response.data.name);
                    $('#editCategoryForm [name="description"]').val(response.data.description);
                    $('#editCategoryModal').modal('show'); // Show the modal
                } else {
                    Swal.fire(getTranslation('category.error'), response.message, 'error');
                }
            },
            error: function () {
                Swal.fire(getTranslation('category.error'), getTranslation('category.failedFetch'), 'error');
            }
        });
    });

    $('#editCategoryForm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission
        const lang = localStorage.getItem('userLang') || 'en';
        const formData = $(this).serialize() + '&lang=' + encodeURIComponent(lang); // Add language to form data

        $.ajax({
            url: 'update_category.php', // Update the category
            type: 'POST',
            data: formData, // Serialize form data
            dataType: 'json', // Expect JSON response
            success: function (response) {
                if (response.status === 'success') {
                    // Show success message
                    Swal.fire(getTranslation('category.success'), response.message, 'success');

                    // Reload the DataTable
                    if (typeof categoryTable !== 'undefined') {
                        categoryTable.ajax.reload();
                    }

                    // Close the modal
                    $('#editCategoryModal').modal('hide');
                } else {
                    // Show error message
                    Swal.fire(getTranslation('category.error'), response.message, 'error');
                }
            },
            error: function () {
                // Show error message for AJAX failure
                Swal.fire(getTranslation('category.error'), getTranslation('category.failedUpdate'), 'error');
            },
        });
    });

    $(document).on('click', '.delete-btn', function () {
        const category_id = $(this).data('id'); // Ensure this matches the button's data-id attribute
        if (category_id) {
            const lang = localStorage.getItem('userLang') || 'en';
            Swal.fire({
                title: getTranslation('category.confirmDelete'),
                text: getTranslation('category.undoneWarning'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: getTranslation('category.deleteConfirm'),
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_category.php',
                        type: 'POST',
                        data: { category_id: category_id, lang: lang },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire(getTranslation('category.success'), response.message, 'success');
                                $('#categoryTable').DataTable().ajax.reload(); // Reload the table
                            } else {
                                Swal.fire(getTranslation('category.error'), response.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire(getTranslation('category.error'), getTranslation('category.failedDelete'), 'error');
                        },
                    });
                }
            });
        } else {
            Swal.fire(getTranslation('category.error'), getTranslation('category.invalidId'), 'error');
        }
    });

    // Language Switching Logic
    const systemLang = navigator.language || navigator.userLanguage;
    let defaultLang = 'en';
    if (systemLang.startsWith('fr')) {
        defaultLang = 'fr';
    }

    let currentLang = localStorage.getItem('userLang') || defaultLang;

    loadTranslations(currentLang);

    function loadTranslations(lang) {
        fetch('../languages/' + lang + '.json')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load language file: ' + response.statusText);
                }
                return response.json();
            })
            .then(translations => {
                localStorage.setItem('translations', JSON.stringify(translations));
                translatePage(translations);
            })
            .catch(error => {
                console.error('Error loading translations:', error);
                // Fallback to English
                fetch('../languages/en.json')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Fallback to English failed: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(translations => {
                        localStorage.setItem('translations', JSON.stringify(translations));
                        translatePage(translations);
                    })
                    .catch(fallbackError => console.error('Fallback error:', fallbackError));
            });
    }

    function translatePage(translations) {
        document.querySelectorAll('[data-key]').forEach(element => {
            const key = element.getAttribute('data-key');
            if (translations[key]) {
                if (element.tagName === 'INPUT') {
                    element.value = translations[key]; // Use value for input fields
                } else if (element.tagName === 'BUTTON') {
                    element.textContent = translations[key]; // Use textContent for button text
                } else if (element.tagName === 'SELECT' || element.tagName === 'OPTION') {
                    element.textContent = translations[key]; // Use textContent for select/options
                } else {
                    element.textContent = translations[key]; // Use textContent for other elements
                }
            }
        });
    }

    function getTranslation(key) {
        const translations = JSON.parse(localStorage.getItem('translations')) || {};
        return translations[key] || (currentLang === 'en' ? 'Default English Text' : 'Texte par défaut en français');
    }

    function switchLanguage(lang) {
        localStorage.setItem('userLang', lang);
        loadTranslations(lang);
    }
});
</script>
</body>
</html>
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-key="inventory.title">Inventory Management</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

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
        .category-filter {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <main class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 data-key="inventory.management">Inventory Management</h4>
                <div>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal" data-key="inventory.addCategoryBtn">+ Add Category</button>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addUnitModal" data-key="inventory.addUnitBtn">+ Add Unit</button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInventoryModal" data-key="inventory.addInventoryBtn">+ Add Inventory</button>
                </div>
            </div>
            <div class="card-body">
                <!-- Category Filter -->
                <div class="category-filter">
                    <label for="categoryFilter" class="form-label">Filter by Category:</label>
                    <select id="categoryFilter" class="form-select" style="width: auto; display: inline-block;">
                        <option value="">All Categories</option>
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <table id="inventoryTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th data-key="inventory.itemName">Item Name</th>
                            <th data-key="inventory.category">Category</th> <!-- Added Category Column -->
                            <th data-key="inventory.unit">Unit</th>
                            <th data-key="inventory.quantity">Quantity</th>
                            <th data-key="inventory.unitCost">Unit Cost</th>
                            <th data-key="inventory.supplier">Supplier</th>
                            <th data-key="inventory.reorderLevel">Reorder Level</th>
                            <th data-key="inventory.createdDate">Created Date</th>
                            <th data-key="inventory.action">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Inventory Modal -->
    <div class="modal fade" id="addInventoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addInventoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="inventory.addNewInventory">Add New Inventory</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="item_name" class="form-label" data-key="inventory.itemName">Item Name</label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label" data-key="inventory.category">Category</label>
                            <select class="form-control" id="category_id" name="category_id" required></select>
                        </div>
                        <div class="mb-3">
                            <label for="unit_id" class="form-label" data-key="inventory.unit">Unit</label>
                            <select class="form-control" id="unit_id" name="unit_id" required></select>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label" data-key="inventory.quantity">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit_cost" class="form-label" data-key="inventory.unitCost">Unit Cost</label>
                            <input type="number" step="0.01" class="form-control" id="unit_cost" name="unit_cost" required>
                        </div>
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label" data-key="inventory.supplier">Supplier</label>
                            <select class="form-control" id="supplier_id" name="supplier_id" required></select>
                        </div>
                        <div class="mb-3">
                            <label for="reorder_level" class="form-label" data-key="inventory.reorderLevel">Reorder Level</label>
                            <input type="number" class="form-control" id="reorder_level" name="reorder_level">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="inventory.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="inventory.addInventory">Add Inventory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addCategoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="inventory.addNewCategory">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_name" class="form-label" data-key="inventory.categoryName">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" data-key="inventory.saveCategory">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Unit Modal -->
    <div class="modal fade" id="addUnitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addUnitForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="inventory.addNewUnit">Add New Unit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="unit_name" class="form-label" data-key="inventory.unitName">Unit Name</label>
                            <input type="text" class="form-control" id="unit_name" name="unit_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning" data-key="inventory.saveUnit">Add Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Inventory Modal -->
    <div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editInventoryForm">
                    <input type="hidden" id="edit_inventory_id" name="inventory_id">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="inventory.editInventory">Edit Inventory</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" data-key="inventory.itemName">Item Name</label>
                            <input type="text" id="edit_item_name" name="item_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-key="inventory.category">Category</label>
                            <select id="edit_category_id" name="category_id" class="form-control"></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-key="inventory.unit">Unit</label>
                            <select id="edit_unit_id" name="unit_id" class="form-control"></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-key="inventory.unitCost">Unit Cost</label>
                            <input type="number" step="0.01" id="edit_unit_cost" name="unit_cost" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-key="inventory.reorderLevel">Reorder Level</label>
                            <input type="number" id="edit_reorder_level" name="reorder_level" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-key="inventory.supplier">Supplier</label>
                            <select id="edit_supplier_id" name="supplier_id" class="form-control"></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" data-key="inventory.description">Description</label>
                            <textarea id="edit_description" name="description" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="inventory.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="inventory.saveChanges">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include_once './footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function () {
        // Initialize DataTable
        let table = $('#inventoryTable').DataTable({
            ajax: {
                url: 'fetch_inventory.php',
                data: function (d) {
                    d.category = $('#categoryFilter').val(); // Pass category filter to server
                }
            },
            columns: [
                { data: 'item_name', title: getTranslation('inventory.itemName') },
                { data: 'category', title: getTranslation('inventory.category') }, // Added Category Column
                { data: 'unit', title: getTranslation('inventory.unit') },
                { data: 'quantity_in_stock', title: getTranslation('inventory.quantity') },
                { data: 'unit_cost', title: getTranslation('inventory.unitCost') },
                { data: 'supplier_name', title: getTranslation('inventory.supplier') },
                { data: 'reorder_level', title: getTranslation('inventory.reorderLevel') },
                { data: 'created_date', title: getTranslation('inventory.createdDate') },
                {
                    data: null,
                    title: getTranslation('inventory.action'),
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary edit-btn" data-id="${row.inventory_id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${row.inventory_id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: getTranslation('inventory.copy'), exportOptions: { columns: ':not(:last-child)' } },
                { extend: 'pdf', text: getTranslation('inventory.exportPdf'), exportOptions: { columns: ':not(:last-child)' } },
                { extend: 'print', text: getTranslation('inventory.print'), exportOptions: { columns: ':not(:last-child)' } }
            ]
        });

        // Populate Category Filter Dropdown
        $.get('fetch_categories.php', function (data) {
            let options = '<option value="">All Categories</option>';
            if (data.status === 'success' && Array.isArray(data.data)) {
                data.data.forEach(category => {
                    options += `<option value="${category.category_id}">${category.category_name}</option>`;
                });
            }
            $('#categoryFilter').html(options);
        }, 'json');

        // Filter Table by Category
        $('#categoryFilter').on('change', function () {
            table.ajax.reload();
        });

        // Reload category/unit/supplier dropdowns
        function reloadDropdowns() {
            $('#category_id, #edit_category_id').load('fetch_categories.php');
            $('#unit_id, #edit_unit_id').load('fetch_units.php');
            $('#supplier_id, #edit_supplier_id').load('fetch_suppliers_list.php');
        }

        // On modal show => reload dropdowns
        $('#addInventoryModal, #editInventoryModal').on('show.bs.modal', function () {
            reloadDropdowns();
        });

        // Add Inventory
        $('#addInventoryForm').on('submit', function (e) {
            e.preventDefault();
            $.post('add_inventory.php', $(this).serialize(), function (res) {
                if (res.status === 'success') {
                    Swal.fire(getTranslation('inventory.success'), res.message, 'success');
                    $('#addInventoryForm')[0].reset();
                    table.ajax.reload();
                } else {
                    Swal.fire(getTranslation('inventory.error'), res.message, 'error');
                }
            }, 'json').fail(() => Swal.fire(getTranslation('inventory.error'), getTranslation('inventory.failedServer'), 'error'));
        });

        // Add Category
        $('#addCategoryForm').on('submit', function (e) {
            e.preventDefault();
            $.post('add_category.php', $(this).serialize(), function (res) {
                if (res.status === 'success') {
                    Swal.fire(getTranslation('inventory.success'), res.message, 'success');
                    $('#addCategoryForm')[0].reset();
                  
                    reloadDropdowns();
                    // Refresh category filter
                    $.get('fetch_categories.php', function (data) {
                        let options = '<option value="">All Categories</option>';
                        if (data.status === 'success' && Array.isArray(data.data)) {
                            data.data.forEach(category => {
                                options += `<option value="${category.category_id}">${category.category_name}</option>`;
                            });
                        }
                        $('#categoryFilter').html(options);
                    }, 'json');
                } else {
                    Swal.fire(getTranslation('inventory.error'), res.message, 'error');
                }
            }, 'json').fail(() => Swal.fire(getTranslation('inventory.error'), getTranslation('inventory.failedCategory'), 'error'));
        });

        // Add Unit
        $('#addUnitForm').on('submit', function (e) {
            e.preventDefault();
            $.post('add_unit.php', $(this).serialize(), function (res) {
                if (res.status === 'success') {
                    Swal.fire(getTranslation('inventory.success'), res.message, 'success');
                    $('#addUnitForm')[0].reset();
                  
                    reloadDropdowns();
                } else {
                    Swal.fire(getTranslation('inventory.error'), res.message, 'error');
                }
            }, 'json').fail(() => Swal.fire(getTranslation('inventory.error'), getTranslation('inventory.failedUnit'), 'error'));
        });

        // Edit Inventory (populate modal)
        $('#inventoryTable').on('click', '.edit-btn', function () {
            const inventoryId = $(this).data('id');
            $.post('get_inventory.php', { inventory_id: inventoryId }, function (res) {
                if (res.status === 'success') {
                    const item = res.data;
                    $('#edit_inventory_id').val(item.inventory_id);
                    $('#edit_item_name').val(item.item_name);
                    $('#edit_category_id').val(item.category_id); // Use category_id instead of category
                    $('#edit_unit_id').val(item.unit);
                    $('#edit_unit_cost').val(item.unit_cost);
                    $('#edit_reorder_level').val(item.reorder_level);
                    $('#edit_supplier_id').val(item.supplier_id);
                    $('#edit_description').val(item.description);
                    $('#editInventoryModal').modal('show');
                } else {
                    Swal.fire(getTranslation('inventory.error'), res.message, 'error');
                }
            }, 'json').fail(() => Swal.fire(getTranslation('inventory.error'), getTranslation('inventory.failedFetchItem'), 'error'));
        });

        // Save Edited Inventory
        $('#editInventoryForm').on('submit', function (e) {
            e.preventDefault();
            $.post('update_inventory.php', $(this).serialize(), function (res) {
                if (res.status === 'success') {
                    Swal.fire(getTranslation('inventory.success'), res.message, 'success');
                  
                    table.ajax.reload();
                } else {
                    Swal.fire(getTranslation('inventory.error'), res.message, 'error');
                }
            }, 'json').fail(() => Swal.fire(getTranslation('inventory.error'), getTranslation('inventory.failedUpdate'), 'error'));
        });

        // Delete Inventory
        $('#inventoryTable').on('click', '.delete-btn', function () {
            const inventoryId = $(this).data('id');
            Swal.fire({
                title: getTranslation('inventory.confirmDelete'),
                text: getTranslation('inventory.unrevertable'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: getTranslation('inventory.deleteConfirm'),
                cancelButtonText: getTranslation('inventory.cancel')
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_inventory.php',
                        type: 'POST',
                        data: { inventory_id: inventoryId },
                        dataType: 'json',
                        success: function (res) {
                            if (res.status === 'success') {
                                Swal.fire(getTranslation('inventory.success'), res.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire(getTranslation('inventory.error'), res.message || 'Failed to delete item', 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            Swal.fire(getTranslation('inventory.error'), `Server error: ${error}`, 'error');
                            console.error('Delete AJAX error:', xhr.responseText);
                        }
                    });
                }
            });
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
                card.style.marginTop = '5%';
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

        // Translation Logic
        let currentLang = 'en';
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
                });
        }

        function translatePage(translations) {
            document.querySelectorAll('[data-key]').forEach(element => {
                const key = element.getAttribute('data-key');
                if (translations[key]) {
                    if (element.tagName === 'INPUT') {
                        element.value = translations[key];
                    } else {
                        element.textContent = translations[key];
                    }
                }
            });
            // Update DataTable titles
            table.columns().every(function () {
                const column = this;
                const title = $(column.header()).attr('data-key');
                if (title && translations[title]) {
                    $(column.header()).text(translations[title]);
                }
            });
        }

        function getTranslation(key) {
            const translations = JSON.parse(localStorage.getItem('translations')) || {};
            return translations[key] || key;
        }
    });
    </script>
</body>
</html>
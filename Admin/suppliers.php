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
    <title data-key="suppliers.title">Suppliers Management</title>

    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
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
                <h4 data-key="suppliers.management">Suppliers Management</h4>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addSupplierModal" data-key="suppliers.addSupplierBtn">+ Add Supplier</button>
                    <button class="btn btn-secondary btn-sm me-2" id="exportBtn" data-key="suppliers.exportBtn">Export</button>
                    <button class="btn btn-info btn-sm" id="importBtn" data-key="suppliers.importBtn">Import</button>
                </div>
            </div>
            <div class="card-body">
                <table id="supplierTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th data-key="suppliers.supplierName">Supplier Name</th>
                            <th data-key="suppliers.contactPerson">Contact Person</th>
                            <th data-key="suppliers.phone">Phone</th>
                            <th data-key="suppliers.email">Email</th>
                            <th data-key="suppliers.address">Address</th>
                            <th data-key="suppliers.createdDate">Created Date</th>
                            <th data-key="suppliers.action">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addSupplierForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSupplierModalLabel" data-key="suppliers.addNewSupplier">Add New Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="supplier_name" class="form-label" data-key="suppliers.supplierNameRequired">Supplier Name *</label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact_person" class="form-label" data-key="suppliers.contactPerson">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label" data-key="suppliers.phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label" data-key="suppliers.email">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label" data-key="suppliers.address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="suppliers.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="suppliers.addSupplier">Add Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editSupplierForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSupplierModalLabel" data-key="suppliers.editSupplier">Edit Supplier</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_supplier_id" name="supplier_id">
                        <div class="mb-3">
                            <label for="edit_supplier_name" class="form-label" data-key="suppliers.supplierNameRequired">Supplier Name *</label>
                            <input type="text" class="form-control" id="edit_supplier_name" name="supplier_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_contact_person" class="form-label" data-key="suppliers.contactPerson">Contact Person</label>
                            <input type="text" class="form-control" id="edit_contact_person" name="contact_person">
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label" data-key="suppliers.phone">Phone</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label" data-key="suppliers.email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label" data-key="suppliers.address">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="suppliers.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="suppliers.updateSupplier">Update Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Suppliers Modal -->
    <div class="modal fade" id="importSuppliersModal" tabindex="-1" aria-labelledby="importSuppliersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="importSuppliersForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importSuppliersModalLabel" data-key="suppliers.importSuppliers">Import Suppliers from CSV</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="csv_file" class="form-label" data-key="suppliers.chooseCsv">Choose CSV File</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <small class="text-muted" data-key="suppliers.csvFormat">Expected columns: supplier_name, contact_person, phone, email, address</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="suppliers.cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-key="suppliers.importSuppliers">Import Suppliers</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once './footer.php'; ?>

    <!-- JS Libraries are assumed to be loaded via header.php or footer.php -->
    <script>
    $(document).ready(function () {
        let table = $('#supplierTable').DataTable({
            ajax: 'fetch_suppliers.php',
            columns: [
                { title: getTranslation('suppliers.supplierName') },
                { title: getTranslation('suppliers.contactPerson') },
                { title: getTranslation('suppliers.phone') },
                { title: getTranslation('suppliers.email') },
                { title: getTranslation('suppliers.address') },
                { title: getTranslation('suppliers.createdDate') },
                { title: getTranslation('suppliers.action') }
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: getTranslation('suppliers.copy'),
                    className: 'btn btn-secondary btn-sm',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdf',
                    text: getTranslation('suppliers.exportPdf'),
                    className: 'btn btn-danger btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    text: getTranslation('suppliers.print'),
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ]
        });

        // Handle Export button
        $('#exportBtn').on('click', function () {
            window.location.href = 'export_suppliers.php';
        });

        // Handle Import button
        $('#importBtn').on('click', function () {
            $('#importSuppliersModal').modal('show');
        });

        // Submit Add Supplier Form
        $('#addSupplierForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: 'add_supplier.php',
                type: 'POST',
                data: formData,
                success: function (response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire(getTranslation('suppliers.success'), res.message, 'success');
                            $('#addSupplierForm')[0].reset();
                            table.ajax.reload();
                        } else {
                            Swal.fire(getTranslation('suppliers.error'), res.message, 'error');
                        }
                    } catch (e) {
                        Swal.fire(getTranslation('suppliers.error'), 'Invalid response from server.', 'error');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('suppliers.error'), 'Something went wrong. Please try again later.', 'error');
                }
            });
        });

        // Handle Edit button click
        $('#supplierTable').on('click', '.edit-btn', function () {
            const supplierId = $(this).data('id');
            $.ajax({
                url: 'get_supplier.php',
                type: 'POST',
                data: { supplier_id: supplierId },
                success: function (response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            const supplier = res.data;
                            $('#edit_supplier_id').val(supplier.supplier_id);
                            $('#edit_supplier_name').val(supplier.supplier_name);
                            $('#edit_contact_person').val(supplier.contact_person);
                            $('#edit_phone').val(supplier.phone);
                            $('#edit_email').val(supplier.email);
                            $('#edit_address').val(supplier.address);
                            $('#editSupplierModal').modal('show');
                        } else {
                            Swal.fire(getTranslation('suppliers.error'), res.message, 'error');
                        }
                    } catch (e) {
                        Swal.fire(getTranslation('suppliers.error'), 'Invalid response from server.', 'error');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('suppliers.error'), 'Something went wrong. Please try again later.', 'error');
                }
            });
        });

        // Submit Edit Supplier Form
        $('#editSupplierForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: 'update_supplier.php',
                type: 'POST',
                data: formData,
                success: function (response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire(getTranslation('suppliers.success'), res.message, 'success');
                            $('#editSupplierForm')[0].reset();
                            table.ajax.reload();
                        } else {
                            Swal.fire(getTranslation('suppliers.error'), res.message, 'error');
                        }
                    } catch (e) {
                        Swal.fire(getTranslation('suppliers.error'), 'Invalid response from server.', 'error');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('suppliers.error'), 'Something went wrong. Please try again later.', 'error');
                }
            });
        });

      // Handle Delete button click
    $('#supplierTable').on('click', '.delete-btn', function () {
        const supplierId = $(this).data('id');
        Swal.fire({
            title: getTranslation('suppliers.confirmDelete'),
            text: getTranslation('suppliers.unrevertable'),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: getTranslation('suppliers.deleteConfirm'),
            cancelButtonText: getTranslation('suppliers.cancel')
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_supplier.php', // Ensure this path is correct!
                    type: 'POST',
                    dataType: 'json', // Automatically parse JSON
                    data: { supplier_id: supplierId },
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire(getTranslation('suppliers.success'), res.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire(getTranslation('suppliers.error'), res.message, 'error');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Delete AJAX error:', textStatus, errorThrown);
                        Swal.fire(getTranslation('suppliers.error'), 'Something went wrong. Please try again later.', 'error');
                    }
                });
            }
        });
    });
        
        // Submit Import Suppliers Form
        $('#importSuppliersForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: 'import_suppliers.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire(getTranslation('suppliers.success'), res.message, 'success');
                            $('#importSuppliersForm')[0].reset();
                            table.ajax.reload();
                        } else {
                            Swal.fire(getTranslation('suppliers.error'), res.message, 'error');
                        }
                    } catch (e) {
                        Swal.fire(getTranslation('suppliers.error'), 'Invalid response from server.', 'error');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('suppliers.error'), 'Something went wrong. Please try again later.', 'error');
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
    
    //===========================
    // Translation Logic
    //===========================
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
                    element.value = translations[key];
                } else if (element.tagName === 'BUTTON') {
                    element.textContent = translations[key];
                } else if (element.tagName === 'SELECT') {
                    element.textContent = translations[key];
                } else {
                    element.textContent = translations[key];
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
    </script>
</body>
</html>

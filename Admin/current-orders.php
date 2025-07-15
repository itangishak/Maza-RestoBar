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
    <title data-key="order.title">Current Orders</title>
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
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <div class="container main-container">
        <div class="card shadow">
            <div class="card-header">
                <h1 data-key="order.currentOrders">Current Orders</h1>
            </div>
            <div class="card-body">
                <button id="addOrderBtn" type="button" class="btn btn-primary mb-3 float-end" data-key="order.addOrderBtn">
                    Add Order
                </button>
                <table id="orderTable" class="table table-bordered display" style="width:100%">
                    <thead>
                        <tr>
                            <th data-key="order.orderId">Order ID</th>
                            <th data-key="order.customerName">Customer Name</th>
                            <th data-key="order.totalPrice">Total Price</th>
                            <th data-key="order.orderDate">Order Date</th>
                            <th data-key="order.status">Status</th>
                            <th data-key="order.action">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Adding Orders -->
    <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addOrderForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addOrderModalLabel" data-key="order.addNewOrder">Add New Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label" data-key="order.customer">Customer</label>
                            <select id="customer_id" name="customer_id" class="form-select" required>
                                <option value="" data-key="order.selectCustomer">Select Customer</option>
                            </select>
                        </div>

                        <div id="menuItemsContainer" class="mb-3">
                            <label class="form-label" data-key="order.menuItems">Menu Items</label>
                            <div class="input-group mb-2 menu-item-row">
                                <select name="menu_items[]" class="form-select menu-item" required disabled>
                                    <option value="" data-key="order.selectMenuItem">Select Menu Item</option>
                                </select>
                                <input type="number" name="quantities[]" class="form-control" placeholder="Quantity" required min="1" disabled>
                                <button type="button" class="btn btn-danger removeMenuItemBtn" disabled data-key="order.removeItem">-</button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-secondary" id="addMenuItemBtn" disabled data-key="order.addItem">+ Add Item</button>

                        <div class="d-flex justify-content-end mt-3">
                            <h5 class="text-end">
                                <span data-key="order.total">Total:</span> <span id="orderTotalDisplay">0.00</span>
                            </h5>
                        </div>
                        <input type="hidden" id="hiddenOrderTotal" name="order_total" value="0.00" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="saveOrderBtn" class="btn btn-success" data-key="order.saveOrder">Save Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include_once './footer.php'; ?>

    <!-- =================================
         JS Libraries in correct order
         ================================= -->
    <!-- jQuery first -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <!-- DataTables Buttons and Print extension -->
    <!-- Bootstrap Bundle -->

    <script>
    $(document).ready(function () {
        // Initialize DataTable for Current Orders
        let orderTable = $('#orderTable').DataTable({
            ajax: {
                url: 'fetch_orders.php', // This should return a JSON array of orders
                type: 'GET',
                dataSrc: ''
            },
            columns: [
                { data: 'order_id' },
                { data: 'customer_name' },
                { data: 'total_price' },
                { data: 'order_date' },
                { data: 'status' },
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary editOrderBtn" data-id="${row.order_id}" data-key="order.edit">
                                ${getTranslation('order.edit')}
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

        // Order Modal Logic
        let itemPrices = {}; // {menu_id: price}

        $('#addOrderBtn').on('click', function () {
            $('#addOrderForm')[0].reset();
            $('#menuItemsContainer').html(`
                <div class="input-group mb-2 menu-item-row">
                    <select name="menu_items[]" class="form-select menu-item" required disabled>
                        <option value="" data-key="order.selectMenuItem">${getTranslation('order.selectMenuItem')}</option>
                    </select>
                    <input type="number" name="quantities[]" class="form-control" placeholder="Quantity" required min="1" disabled>
                    <button type="button" class="btn btn-danger removeMenuItemBtn" disabled data-key="order.removeItem">-</button>
                </div>
            `);
            $('#orderTotalDisplay').text('0.00');
            $('#hiddenOrderTotal').val('0.00');
            $('#addMenuItemBtn').prop('disabled', true);
            $('#addOrderModal').modal('show');
            loadCustomers();
            loadMenuItems();
        });

        function loadCustomers() {
            $.ajax({
                url: 'fetch_customers_order.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    let options = `<option value="" data-key="order.selectCustomer">${getTranslation('order.selectCustomer')}</option>`;
                    data.forEach(customer => {
                        options += `<option value="${customer.customer_id}" data-key="order.customerOption">${customer.first_name} ${customer.last_name}</option>`;
                    });
                    $('#customer_id').html(options);
                },
                error: function () {
                    Swal.fire(getTranslation('order.error'), getTranslation('order.failedLoadCustomers'), 'error');
                }
            });
        }

        function loadMenuItems() {
            $.ajax({
                url: 'fetch_menu_items_order.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    itemPrices = {};
                    data.forEach(item => {
                        itemPrices[item.menu_id] = parseFloat(item.price) || 0;
                    });
                    const options = data.map(item =>
                        `<option value="${item.menu_id}" data-key="order.menuItemOption">${item.name}</option>`
                    ).join('');
                    $('.menu-item').each(function() {
                        const oldVal = $(this).val() || '';
                        $(this).html(`<option value="" data-key="order.selectMenuItem">${getTranslation('order.selectMenuItem')}</option>` + options)
                               .val(oldVal);
                    });
                },
                error: function () {
                    Swal.fire(getTranslation('order.error'), getTranslation('order.failedLoadMenuItems'), 'error');
                }
            });
        }

        $('#customer_id').on('change', function() {
            const selected = $(this).val() !== '';
            $('#addMenuItemBtn, .menu-item, input[name="quantities[]"], .removeMenuItemBtn')
                .prop('disabled', !selected);
            recalcOrderTotal();
        });

        $('#addMenuItemBtn').on('click', function() {
            let incomplete = false;
            $('.menu-item-row').each(function() {
                const val = $(this).find('.menu-item').val();
                const qty = $(this).find('input[name="quantities[]"]').val();
                if (!val || qty < 1) {
                    incomplete = true;
                }
            });
            if (incomplete) {
                Swal.fire(getTranslation('order.error'), getTranslation('order.fillItemsFirst'), 'error');
                return;
            }
            $('#menuItemsContainer').append(`
                <div class="input-group mb-2 menu-item-row">
                    <select name="menu_items[]" class="form-select menu-item" required>
                        <option value="" data-key="order.selectMenuItem">${getTranslation('order.selectMenuItem')}</option>
                    </select>
                    <input type="number" name="quantities[]" class="form-control" placeholder="Quantity" required min="1">
                    <button type="button" class="btn btn-danger removeMenuItemBtn" data-key="order.removeItem">-</button>
                </div>
            `);
            loadMenuItems();
        });

        $('#menuItemsContainer').on('click', '.removeMenuItemBtn', function() {
            $(this).closest('.menu-item-row').remove();
            recalcOrderTotal();
        });

        $('#menuItemsContainer')
            .on('change', '.menu-item', function() {
                recalcOrderTotal();
            })
            .on('input', 'input[name="quantities[]"]', function() {
                recalcOrderTotal();
            });

        function recalcOrderTotal() {
            let total = 0;
            $('.menu-item-row').each(function() {
                const itemId = $(this).find('.menu-item').val();
                const qty = parseFloat($(this).find('input[name="quantities[]"]').val()) || 0;
                if (itemId && qty > 0) {
                    const price = itemPrices[itemId] || 0;
                    total += price * qty;
                }
            });
            $('#orderTotalDisplay').text(total.toFixed(2));
            $('#hiddenOrderTotal').val(total.toFixed(2));
        }

        $('#addOrderForm').on('submit', function(e) {
            e.preventDefault();
            if (parseFloat($('#hiddenOrderTotal').val()) <= 0) {
                Swal.fire(getTranslation('order.error'), getTranslation('order.zeroTotal'), 'error');
                return;
            }
            $.ajax({
                url: 'add_order.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire(getTranslation('order.success'), response.message, 'success');
                   
                        $('#orderTable').DataTable().ajax.reload();
                    } else {
                        Swal.fire(getTranslation('order.error'), response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('order.error'), getTranslation('order.failedSave'), 'error');
                }
            });
        });

        // Edit Order: Using SweetAlert for updating status
        $('#orderTable').on('click', '.editOrderBtn', function () {
            const orderId = $(this).data('id');
            const currentStatus = $(this).data('status') || 'pending';
            Swal.fire({
                title: getTranslation('order.updateStatusTitle'),
                input: 'select',
                inputOptions: {
                    'pending': getTranslation('order.pending'),
                    'confirmed': getTranslation('order.confirmed'),
                    'canceled': getTranslation('order.canceled')
                },
                inputValue: currentStatus,
                inputPlaceholder: getTranslation('order.selectStatus'),
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const newStatus = result.value;
                    if (!newStatus) return;
                    if (newStatus === 'confirmed' || newStatus === 'canceled') {
                        Swal.fire({
                            title: getTranslation('order.confirmUpdate'),
                            text: getTranslation('order.irrevocableWarning', { status: newStatus }),
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: getTranslation('order.proceed'),
                            cancelButtonText: getTranslation('order.cancel')
                        }).then((confirmResult) => {
                            if (confirmResult.isConfirmed) {
                                updateOrderStatus(orderId, newStatus);
                            }
                        });
                    } else {
                        updateOrderStatus(orderId, newStatus);
                    }
                }
            });
        });

        function updateOrderStatus(orderId, newStatus) {
            $.ajax({
                url: 'update_order.php',
                type: 'POST',
                dataType: 'json',
                data: { order_id: orderId, status: newStatus },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire(getTranslation('order.success'), response.message, 'success');
                        $('#orderTable').DataTable().ajax.reload();
                    } else {
                        Swal.fire(getTranslation('order.error'), response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('order.error'), getTranslation('order.failedUpdate'), 'error');
                }
            });
        }

        // Utility: getTranslation function for DataTables columns & other texts
        function getTranslation(key, params = {}) {
            const translations = JSON.parse(localStorage.getItem('translations')) || {};
            let translation = translations[key] || key;
            Object.keys(params).forEach(param => {
                translation = translation.replace(`{${param}}`, params[param]);
            });
            return translation;
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
        fetch('../languages/' + lang + '.json')
          .then(response => {
              if (!response.ok) {
                  console.error("Translation file not found:", response.statusText);
              }
              return response.json();
          })
          .then(data => {
              console.log("Translations loaded:", data);
              localStorage.setItem('translations', JSON.stringify(data));
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
        loadTranslations(lang);
      }
      
      function loadTranslations(lang) {
        fetch('../languages/' + lang + '.json')
          .then(response => response.json())
          .then(data => {
              localStorage.setItem('translations', JSON.stringify(data));
              translatePage(data);
          })
          .catch(error => console.error('Error loading translations:', error));
      }
    </script>
</body>
</html>

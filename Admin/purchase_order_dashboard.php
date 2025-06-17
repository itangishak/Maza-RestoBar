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
  <!-- The page title can also be translated -->
  <title data-key="report.purchaseOrderDashboard"></title>

  <!-- Include your custom header for translations, etc. -->
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

  <div class="container main-container">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <!-- Using data-key for the dashboard title -->
        <h4 data-key="report.purchaseOrderDashboard"></h4>
        <!-- Using data-key for the create button text -->
        <a href="create_purchase_order.php" class="btn btn-primary btn-sm" data-key="report.createPurchaseOrder">
          + <span data-key="report.createPurchaseOrder"></span>
        </a>
      </div>
      <div class="card-body">
        <table id="purchaseOrderTable" class="table table-striped table-bordered" style="width:100%">
          <thead>
            <tr>
              <th><span data-key="report.poNumber"></span></th>
              <th><span data-key="report.orderDate"></span></th>
              <th><span data-key="report.paymentMethod"></span></th>
              <th><span data-key="report.createdBy"></span></th>
              <th><span data-key="report.total"></span></th>
              <th><span data-key="report.action"></span></th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>

  <?php include_once './footer.php'; ?>

  <script>
    // We'll store translations from localStorage
    let translations = {};

    function getTranslation(key) {
      // Helper function to retrieve a single translation
      if (translations[key]) {
        return translations[key];
      }
      return key; // fallback if missing
    }

    $(document).ready(function () {
      // Load the translations from localStorage
      const stored = localStorage.getItem('translations');
      if (stored) {
        translations = JSON.parse(stored);
      }

      // Initialize DataTable
      const table = $('#purchaseOrderTable').DataTable({
        ajax: 'fetch_purchase_orders.php',
        columns: [
          { data: 'po_number' },
          { data: 'order_date'    },
          { data: 'payment_method'},
          { data: 'created_by'    },
          {
            data: 'total',
            render: $.fn.dataTable.render.number(',', '.', 2, 'BIF ')
          },
          {
            data: null,
            orderable: false,
            render: (data, type, row) => `
              <button class="btn btn-sm btn-primary edit-btn"  data-id="${row.purchase_id}">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-danger delete-btn" data-id="${row.purchase_id}">
                <i class="bi bi-trash"></i>
              </button>
            `
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

      // Calculate order total function - moved to global scope
      function calculateOrderTotal() {
        let orderTotal = 0;
        $('#purchaseItemsTable tr').each(function() {
          const totalCell = $(this).find('.total-cell');
          if (totalCell.length > 0) {
            const rowTotal = parseFloat(totalCell.text()) || 0;
            orderTotal += rowTotal;
          } else {
            // For existing items (not in edit mode)
            const totalText = $(this).find('td:eq(4)').text();
            orderTotal += parseFloat(totalText) || 0;
          }
        });
        $('#edit_total').val(orderTotal.toFixed(2));
        return orderTotal;
      }

      // Calculate row total function - moved to global scope
      function calculateRowTotal($row) {
        const qty = parseFloat($row.find('.quantity-input').val()) || 0;
        const price = parseFloat($row.find('.unit-price-input').val()) || 0;
        const total = (qty * price).toFixed(2);
        $row.find('.total-cell').text(total);
        calculateOrderTotal();
      }

      // Edit functionality
      $(document).on('click', '.edit-btn', function() {
        const purchaseId = $(this).data('id');
        
        // Disable total field during loading
        $('#edit_total').prop('disabled', true).val('Loading...');
        
        // First load and display the order
        $.get(`get_purchase_order.php?id=${purchaseId}`)
        .then(function(order) {
            // Format and display order data
            const formattedOrder = {
                id: order.purchase_id,
                po_number: order.po_number,
                date: new Date(order.order_date).toISOString().split('T')[0],
                payment_method: order.payment_method,
                total: parseFloat(order.total || 0).toFixed(2)
            };
            
            $('#edit_purchase_id').val(formattedOrder.id);
            $('#edit_po_number').val(formattedOrder.po_number);
            $('#edit_order_date').val(formattedOrder.date);
            $('#edit_payment_method').val(formattedOrder.payment_method);
            
            // Show modal
            $('#editPurchaseOrderModal').modal('show');
            
            // Load items and calculate total
            return $.get(`fetch_purchase_order_items.php?purchase_id=${purchaseId}`);
        })
        .then(function(items) {
            const $tableBody = $('#purchaseItemsTable');
            $tableBody.empty();
            
            let calculatedTotal = 0;
            
            if (items && items.length > 0) {
                items.forEach(item => {
                    const quantity = parseFloat(item.quantity || 0);
                    const unitPrice = parseFloat(item.unit_price || 0);
                    const itemTotal = quantity * unitPrice;
                    calculatedTotal += itemTotal;
                    
                    $tableBody.append(`
                        <tr data-id="${item.po_item_id}">
                            <td>${item.item_name || ''}</td>
                            <td>${item.category || ''}</td>
                            <td>${quantity}</td>
                            <td>${unitPrice.toFixed(2)}</td>
                            <td>${itemTotal.toFixed(2)}</td>
                            <td>
                                <button class="btn btn-sm btn-danger remove-item-btn" type="button">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
            }
            
            // Enable total field and set calculated value
            $('#edit_total').prop('disabled', false).val(calculatedTotal.toFixed(2));
        })
        .catch(function(error) {
            console.error('Error loading order:', error);
            $('#edit_total').val('0.00').prop('disabled', false);
            alert('Error loading purchase order details');
        });
      });

      // Handle modal form submission
      $('#editPurchaseOrderForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
          url: 'update_purchase_order.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.status === 'success') {
              $('#editPurchaseOrderModal').modal('hide');
              Swal.fire('Success', response.message, 'success');
              table.ajax.reload();
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          },
          error: function() {
            Swal.fire('Error', 'An error occurred while updating', 'error');
          }
        });
      });

      // Delete functionality
      $('#purchaseOrderTable').on('click', '.delete-btn', function () {
        const orderId = $(this).data('id');
        Swal.fire({
          title: getTranslation('report.areYouSure'),
          text: getTranslation('report.cannotRevert'),
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: getTranslation('report.yesDeleteIt')
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'delete_purchase_order.php',
              type: 'POST',
              dataType: 'json',
              data: { purchase_order_id: orderId },
              success: function (response) {
                if (response.status === 'success') {
                  Swal.fire(
                    getTranslation('report.deleted'),
                    response.message,
                    'success'
                  );
                  table.ajax.reload();
                } else {
                  Swal.fire(
                    getTranslation('report.error'),
                    response.message,
                    'error'
                  );
                }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                console.error('Delete AJAX error:', textStatus, errorThrown);
                Swal.fire(
                  getTranslation('report.error'),
                  getTranslation('report.somethingWentWrong'),
                  'error'
                );
              }
            });
          }
        });
      });

      // Remove item functionality
      $(document).on('click', '.remove-item-btn', function() {
        const $row = $(this).closest('tr');
        const itemId = $row.data('id');
        
        if (itemId) {
          // Existing item - confirm deletion
          if (confirm('Are you sure you want to remove this item?')) {
            $row.remove();
            calculateOrderTotal();
          }
        } else {
          // New item - just remove
          $row.remove();
          calculateOrderTotal();
        }
      });

      // Add item functionality
      $('#addItemBtn').click(function() {
        const newRow = `
          <tr>
            <td>
              <select class="form-control form-control-sm item-select" required>
                <option value="">Select Item</option>
              </select>
            </td>
            <td>
              <input type="text" class="form-control form-control-sm category-input" readonly>
            </td>
            <td>
              <input type="number" step="0.01" min="0.01" class="form-control form-control-sm quantity-input" value="1" required>
            </td>
            <td>
              <input type="number" step="0.01" min="0" class="form-control form-control-sm unit-price-input" required>
            </td>
            <td class="total-cell">0.00</td>
            <td>
              <button class="btn btn-sm btn-danger remove-item-btn" type="button">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        `;
        $('#purchaseItemsTable').append(newRow);
        
        // Load items for the new dropdown
        loadItemsForDropdown($('#purchaseItemsTable tr:last .item-select'));
      });

      // Load items for dropdown
      function loadItemsForDropdown($select) {
        if (!$select) {
          $select = $('.item-select:last');
        }
        
        $.get('fetch_inventory_items.php')
        .done(function(items) {
          $select.empty().append('<option value="">Select Item</option>');
          
          if (items && Array.isArray(items)) {
            items.forEach(item => {
              $select.append(`
                <option value="${item.inventory_id}" 
                    data-category="${item.category || ''}"
                    data-unit-price="${item.unit_cost || 0}">
                    ${item.item_name || 'Unknown Item'}
                </option>
              `);
            });
          }
        })
        .fail(function() {
          console.error('Failed to load inventory items');
        });
      }

      // Handle item selection change
      $(document).on('change', '.item-select', function() {
        const $row = $(this).closest('tr');
        const selectedOption = $(this).find('option:selected');
        
        const category = selectedOption.data('category') || '';
        const unitPrice = parseFloat(selectedOption.data('unit-price')) || 0;
        
        $row.find('.category-input').val(category);
        $row.find('.unit-price-input').val(unitPrice.toFixed(2));
        
        calculateRowTotal($row);
      });

      // Handle quantity/price changes
      $(document).on('input change', '.quantity-input, .unit-price-input', function() {
        const $row = $(this).closest('tr');
        calculateRowTotal($row);
      });

      // Calculate total when modal opens
      $('#editPurchaseOrderModal').on('shown.bs.modal', function() {
        // Small delay to ensure DOM is ready
        setTimeout(function() {
          calculateOrderTotal();
        }, 100);
      });

      // Recalculate total when modal is about to close
      $('#editPurchaseOrderModal').on('hide.bs.modal', function() {
        calculateOrderTotal();
      });
    });
  </script>

  <!-- Translation script with fallback -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const systemLang = navigator.language || 'en';
      const userLang = localStorage.getItem('userLang') || systemLang;
      loadTranslations(userLang);
    });

    function loadTranslations(lang) {
      // Adjust the path to your .json file as needed
      fetch(`../languages/${lang}.json`)
        .then(resp => resp.json())
        .then(data => {
          localStorage.setItem('translations', JSON.stringify(data));
          translatePage(data);
        })
        .catch(() => {
          // fallback to english if not found
          fetch('../languages/en.json')
            .then(r => r.json())
            .then(d => {
              localStorage.setItem('translations', JSON.stringify(d));
              translatePage(d);
            });
        });
    }

    function translatePage(translations) {
      document.querySelectorAll('[data-key]').forEach(el => {
        const key = el.getAttribute('data-key');
        if (translations[key]) {
          if (el.tagName === 'INPUT' && el.type === 'submit') {
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

  <!-- Edit Purchase Order Modal -->
  <div class="modal fade" id="editPurchaseOrderModal" tabindex="-1" aria-labelledby="editPurchaseOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form id="editPurchaseOrderForm">
          <div class="modal-header">
            <h5 class="modal-title" id="editPurchaseOrderModalLabel">Edit Purchase Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="edit_purchase_id" name="purchase_id">
            
            <div class="row mb-3">
              <div class="col-md-3">
                <label class="form-label">PO Number</label>
                <input type="text" class="form-control" id="edit_po_number" name="po_number" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Order Date</label>
                <input type="date" class="form-control" id="edit_order_date" name="order_date" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select class="form-control" id="edit_payment_method" name="payment_method" required>
                  <option value="">Select Payment Method</option>
                  <option value="cash">Cash</option>
                  <option value="credit">Credit</option>
                  <option value="bank">Bank Transfer</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Total Amount</label>
                <input type="number" step="0.01" min="0" class="form-control" id="edit_total" name="total" readonly>
              </div>
            </div>
            
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6>Purchased Items</h6>
                <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                  <i class="bi bi-plus"></i> Add Item
                </button>
              </div>
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>Item</th>
                      <th>Category</th>
                      <th>Quantity</th>
                      <th>Unit Price</th>
                      <th>Total</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="purchaseItemsTable">
                    <!-- Items will be inserted here by JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
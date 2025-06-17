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
    <!-- Title replaced with data-key -->
    <title data-key="report.createPurchaseOrder"></title>
   
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
      /* Preview table styling */
      #previewContent table {
        width: 100%;
        border-collapse: collapse;
      }
      #previewContent th, #previewContent td {
        border: 1px solid #dee2e6;
        padding: 8px;
        text-align: left;
      }
      #previewContent th {
        background-color: #f8f9fa;
      }
      #previewContent .text-end {
        text-align: right;
      }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>
    <main class="container main-container">
        <div class="card shadow">
            <div class="card-header">
                <!-- Heading with data-key -->
                <h4 data-key="report.createPurchaseOrder"></h4>
            </div>
            <div class="card-body">
                <form id="purchaseOrderForm" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <!-- Label and select for supplier -->
                            <label for="supplier_id" class="form-label" data-key="report.supplier"></label>
                            <select name="supplier_id" id="supplier_id" class="form-control" required>
                                <option value="" data-key="report.selectSupplier"></option>
                            </select>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th data-key="report.product"></th>
                                <th data-key="report.quantityInStock"></th>
                                <th data-key="report.quantity"></th>
                                <th data-key="report.unitCost"></th>
                                <th data-key="report.action"></th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <!-- Rows will be added dynamically -->
                        </tbody>
                    </table>

                    <!-- Create button translated with data-key -->
                    <button type="submit" class="btn btn-success" data-key="report.createPurchaseInvoice">Create</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Modal for Preview -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" data-key="report.purchaseOrderPreview"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="previewContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.cancel">Cancel</button>
                    <button type="button" id="confirmPurchase" class="btn btn-primary" data-key="report.confirmPurchase">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once './footer.php'; ?>
    <script>
    // Ensure Select2 CSS is loaded
    (function() {
        if (!document.querySelector('link[href*="select2.min.css"]')) {
            console.log('Select2 CSS not found, adding it');
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.href = 'assets/css/select2.min.css';
            document.head.appendChild(link);
        } else {
            console.log('Select2 CSS already loaded');
        }
    })();
    
    // Ensure Select2 is available before proceeding with initialization
    function ensureSelect2Loaded(callback) {
        if (typeof $.fn.select2 === 'function') {
            console.log('Select2 already loaded, proceeding with initialization');
            callback();
        } else {
            console.log('Select2 not loaded, loading it now...');
            
            // Try to load Select2 from CDN again
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
            script.onload = function() {
                console.log('Select2 loaded from CDN');
                setTimeout(callback, 100); // Short delay to ensure it's fully initialized
            };
            script.onerror = function() {
                console.error('Failed to load Select2 from CDN, trying local file');
                
                // Try local file as fallback
                var localScript = document.createElement('script');
                localScript.src = 'assets/js/select2.min.js';
                localScript.onload = function() {
                    console.log('Select2 loaded from local file');
                    setTimeout(callback, 100);
                };
                localScript.onerror = function() {
                    console.error('Failed to load Select2 from local file as well');
                    // Continue anyway as we have a safety check in the code
                    callback();
                };
                document.body.appendChild(localScript);
            };
            document.body.appendChild(script);
        }
    }
    
    $(document).ready(function () {
        // Ensure Select2 is loaded before proceeding
        ensureSelect2Loaded(function() {
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

        // Dynamic supplier & product loading
        let selectedProducts = new Set();
        let productOptionsHtml = '';

        // Load suppliers dynamically
        $.getJSON('fetch_suppliers.php', function (response) {
            if (response.data && Array.isArray(response.data)) {
                let options = response.data.map(item => {
                    const supplierId = item[0];
                    const supplierName = item[1];
                    return `<option value="${supplierId}">${supplierName}</option>`;
                });
                $("#supplier_id").append(options.join(""));
                // Initialize Select2 for supplier_id
                if ($.fn.select2) {
                    $("#supplier_id").select2({
                        placeholder: getTranslation("report.selectSupplier", "Select Supplier"), // Fallback text
                        allowClear: true,
                        width: "100%"
                    });
                }
            } else {
                Swal.fire('Error', 'Failed to load suppliers.', 'error');
            }
        }).fail(function () {
            Swal.fire('Error', 'Unable to fetch suppliers.', 'error');
        });

        // Load products dynamically
        $.getJSON('fetch_products.php', function (response) {
            if (Array.isArray(response)) {
                productOptionsHtml = response.map(item => 
                    `<option value="${item.inventory_id}" data-name="${item.item_name}" data-unit="${item.unit_name}">${item.item_name}</option>`
                ).join('');
                addNewRow();
            } else {
                Swal.fire('Error', 'Failed to load products.', 'error');
            }
        }).fail(function () {
            Swal.fire('Error', 'Unable to fetch products.', 'error');
        });

        // Add a new row to the table
        function addNewRow() {
                try {
            const rowCount = $('#productsTableBody tr').length;
            const newRow = $(`
                <tr>
                    <td>
                        <select name="items[${rowCount}][product]" class="form-control productSelect" required>
                            <option value="">Select Product</option>
                            ${productOptionsHtml}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="items[${rowCount}][quantity_in_stock]" class="form-control quantityInStockField" readonly>
                    </td>
                    <td>
                        <input type="number" name="items[${rowCount}][quantity]" class="form-control quantityField" required min="1" step="any">
                    </td>
                    <td>
                        <input type="number" name="items[${rowCount}][unit_cost]" class="form-control unitCostField" required min="0" step="0.01">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary addRowBtn">+</button>
                    </td>
                </tr>
            `);

            $('#productsTableBody').append(newRow);

                    // Wait a short moment to ensure DOM is updated
                    setTimeout(function() {
                        // Try to initialize Select2
                        try {
                            if (typeof $.fn.select2 === 'function') {
                                newRow.find('.productSelect').select2({
                                    placeholder: "Search for a product",
                                    allowClear: true,
                                    width: "100%"
                                });
                                console.log('Select2 initialized successfully for row');
                            } else {
                                console.warn('Select2 not available when trying to initialize in row ' + rowCount);
                                // Let's try to load it on demand
                                ensureSelect2Loaded(function() {
                                    if (typeof $.fn.select2 === 'function') {
            newRow.find('.productSelect').select2({
                placeholder: "Search for a product",
                allowClear: true,
                width: "100%"
            });
                                        console.log('Select2 initialized after loading for row ' + rowCount);
                                    }
                                });
                            }
                        } catch(e) {
                            console.error('Error initializing Select2:', e);
                        }

            // Disable product select if no supplier is chosen
            const supplierSelected = $('#supplier_id').val() !== '';
            newRow.find('.productSelect').prop('disabled', !supplierSelected);
                    }, 10);
                } catch(e) {
                    console.error('Error in addNewRow:', e);
                }
        }

        // Add row button handler
        $('#productsTableBody').on('click', '.addRowBtn', function () {
            const currentRow = $(this).closest('tr');
            const product = currentRow.find('.productSelect').val();
            const quantity = currentRow.find('.quantityField').val();

            if (!product) {
                Swal.fire('Warning', 'Please select a product before adding a new row.', 'warning');
                return;
            }
            if (!quantity || quantity <= 0) {
                Swal.fire('Warning', 'Please enter a valid quantity before adding a new row.', 'warning');
                return;
            }

            $(this)
                .removeClass('btn-primary addRowBtn')
                .addClass('btn-danger removeRowBtn')
                .text('−');

            addNewRow();
        });

        // Remove row handler
        $('#productsTableBody').on('click', '.removeRowBtn', function () {
            const row = $(this).closest('tr');
            const productId = row.find('.productSelect').val();
            if (productId) {
                selectedProducts.delete(productId);
            }
            row.remove();
        });

        // Handle product selection with duplicate check
        $('#productsTableBody').on('change', '.productSelect', function () {
            const $this = $(this);
            const selectedProductId = $this.val();
            const row = $this.closest('tr');

            if (selectedProductId) {
                if (selectedProducts.has(selectedProductId)) {
                    Swal.fire('Warning', 'This product is already selected. Please choose a different product.', 'warning');
                    $this.val('').trigger('change');
                    return;
                }
                selectedProducts.add(selectedProductId);
                $.getJSON(`fetch_product_details.php?inventory_id=${selectedProductId}`, function (data) {
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        row.find('.quantityInStockField').val('');
                        row.find('.unitCostField').val('');
                    } else {
                        row.find('.quantityInStockField').val(data.quantity_in_stock);
                        row.find('.unitCostField').val(data.unit_cost);
                    }
                }).fail(function () {
                    Swal.fire('Error', 'Unable to fetch product details. Please try again.', 'error');
                });
            } else {
                // When cleared, remove previous selection from set
                const previousVal = $this.data('previous-value');
                if (previousVal) {
                    selectedProducts.delete(previousVal);
                }
                row.find('.quantityInStockField').val('');
                row.find('.unitCostField').val('');
            }
            $this.data('previous-value', selectedProductId);
        });

        // Handle supplier selection enabling/disabling product selects
        $('#supplier_id').on('change', function () {
            const supplierSelected = $(this).val() !== '';
            $('.productSelect').prop('disabled', !supplierSelected);
            if (!supplierSelected) {
                selectedProducts.clear();
                $('#productsTableBody').empty();
                addNewRow();
                Swal.fire('Warning', 'Please select a supplier to add products.', 'warning');
            }
        });

        // Form submission to show preview modal
        $('#purchaseOrderForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            let supplierId = $('#supplier_id').val();
            let supplierName = $('#supplier_id option:selected').text();
            let totalAmount = 0.0;

            // Generate preview content
            let previewContent = `
                <div class="text-center mb-4">
                    <h3>Maza Resto-Bar</h3>
                    <p>Address: Boulevard Melchior Ndadaye, Peace Corner, Bujumbura</p>
                    <p>Phone: +257 69 80 58 98 | Email: barmazaresto@gmail.com</p>
                </div>
                <p><strong data-key="report.supplier"></strong>: ${supplierName}</p>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th data-key="report.product"></th>
                            <th data-key="report.measure"></th>
                            <th data-key="report.quantity"></th>
                            <th data-key="report.unitCost"></th>
                            <th data-key="report.totalCost"></th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            $('#productsTableBody tr').each(function () {
                const productOption = $(this).find('.productSelect option:selected');
                const productId = productOption.val();
                const productName = productOption.data('name') || 'Unknown';
                const measurement = productOption.data('unit') || 'N/A';
                const quantity = parseFloat($(this).find('.quantityField').val()) || 0;
                const unitCost = parseFloat($(this).find('.unitCostField').val()) || 0;
                const totalCost = quantity * unitCost;

                if (productId && quantity > 0) {
                    totalAmount += totalCost;
                    previewContent += `
                        <tr>
                            <td>${productName}</td>
                            <td>${measurement}</td>
                            <td>${quantity}</td>
                            <td>${unitCost.toFixed(2)} BIF</td>
                            <td>${totalCost.toFixed(2)} BIF</td>
                        </tr>
                    `;
                }
            });

            previewContent += `
                    </tbody>
                </table>
                <p class="text-end"><strong data-key="report.total"></strong>: ${totalAmount.toFixed(2)} BIF</p>
            `;

            // Show preview in modal
            $('#previewContent').html(previewContent);
            $('#previewModal').modal('show');

            // Store form data for confirmation
            $('#confirmPurchase').data('formData', formData);
        });

        // Confirm purchase handler
        $('#confirmPurchase').on('click', function () {
            const formData = $(this).data('formData');

            // Submit to purchase_invoice.php via AJAX
            $.ajax({
                url: 'purchase_invoice.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        // Print the receipt
                        printReceipt(response.receiptContent);
                    } else {
                        Swal.fire('Erreur', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    // Try to parse JSON from the error response
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse && errorResponse.status === 'success') {
                            // If the server indicates success despite the AJAX error
                            printReceipt(errorResponse.receiptContent);
                        } else {
                            Swal.fire('Erreur', errorResponse.message || 'Échec de la communication avec le serveur.', 'error');
                        }
                    } catch (e) {
                        Swal.fire('Erreur', 'Échec de la communication avec le serveur.', 'error');
                    }
                }
            });
        });

        // Function to print receipt
        async function printReceipt(receiptContent) {
            // Set receipt content in DOM
            const receiptDiv = document.createElement('div');
            receiptDiv.id = 'receiptContent';
            receiptDiv.style.display = 'none';
            receiptDiv.innerHTML = receiptContent;
            document.body.appendChild(receiptDiv);

            // Load logo
            try {
                const logoData = await window.printer.getLogoBase64();
                if (logoData && logoData.startsWith("data:image/")) {
                    document.getElementById('receiptLogo').src = logoData;
                } else {
                    console.warn("Invalid or empty logo data, removing logo.");
                    document.getElementById('receiptLogo').remove();
                }
            } catch (logoErr) {
                console.error("Failed to load logo:", logoErr);
                document.getElementById('receiptLogo').remove();
            }

            // Capture final content
            const finalContent = receiptDiv.innerHTML;

            // Print HTML
            const html = `
                <html>
                <head>
                    <style>
                        @page { margin: 0; }
                        body { margin: 0; padding: 0; }
                        #receiptContent {
                            margin: 0;
                            padding: 0;
                            width: 220px;
                            font-family: Arial, sans-serif;
                            text-align: center;
                            font-size: 10px;
                            line-height: 1.2;
                        }
                        #receiptContent img,
                        #receiptContent h2,
                        #receiptContent p,
                        #receiptContent hr {
                            margin: 0;
                            padding: 0;
                        }
                        #receiptContent img {
                            width: 100px;
                            margin: 0 auto 5px auto;
                            display: block;
                        }
                        #receiptContent h2 {
                            font-size: 14px;
                            font-weight: bold;
                        }
                        #receiptContent p {
                            margin: 5px 0;
                            font-size: 10px;
                        }
                        #receiptContent hr {
                            border: none;
                            border-top: 1px dashed #000;
                            margin: 6px 0;
                            width: 100%;
                        }
                        #receiptContent table {
                            width: 100%;
                            margin: 5px 0;
                            border-collapse: collapse;
                            font-size: 9px;
                        }
                        #receiptContent th,
                        #receiptContent td {
                            border: 1px solid #000;
                            padding: 3px;
                            text-align: center;
                        }
                        #receiptContent .text-end {
                            text-align: right;
                        }
                    </style>
                </head>
                <body>
                    <div id="receiptContent">${finalContent}</div>
                </body>
                </html>
            `;

            try {
                await window.printer.printHTML(html);
                console.log("Print succeeded");
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: 'Bon de commande créé et imprimé avec succès.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#previewModal').modal('hide');
                    window.location.href = 'create_purchase_order.php';
                });
            } catch (printErr) {
                console.error("Print failed:", printErr);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Bon de commande créé, mais échec de l\'impression.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    $('#previewModal').modal('hide');
                    window.location.href = 'create_purchase_order.php';
                });
            } finally {
                // Clean up
                document.body.removeChild(receiptDiv);
            }
        }
        });
    });

    // Translation Script with Button Fix
    document.addEventListener('DOMContentLoaded', function () {
        const currentLang = localStorage.getItem('userLang') || 'en';
        fetch(`../languages/${currentLang}.json`)
            .then(response => response.json())
            .then(data => translatePage(data))
            .catch(error => console.error('Error loading translations:', error));
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

<!-- Fix for Select2 initialization -->
<script>
// Ensure Select2 is properly loaded
console.log('Ensuring Select2 is properly loaded...');

// Check if jQuery is loaded
if (typeof jQuery === 'undefined') {
  console.error('jQuery not available!');
} else {
  console.log('jQuery is available.');
  
  // Check if Select2 is already loaded
  if (typeof $.fn.select2 !== 'function') {
    console.log('Select2 not loaded, loading it now...');
    
    // Force load Select2 directly
    var selectScript = document.createElement('script');
    selectScript.src = 'assets/js/select2.min.js';
    selectScript.onload = function() {
      console.log('Select2 script loaded successfully');
      
      // Add a small delay before initializing any existing select
      setTimeout(function() {
        console.log('Initializing Select2...', typeof $.fn.select2);
        
        if (typeof $.fn.select2 === 'function') {
          try {
            $('.productSelect').select2({
              width: 'resolve',
              dropdownAutoWidth: true,
              minimumResultsForSearch: 0,
              placeholder: 'Search products...',
              allowClear: true
            });
            console.log('Search capability added successfully');
          } catch(e) {
            console.error('Error applying Select2:', e);
          }
        } else {
          console.error('Select2 function not available after loading!');
        }
      }, 500);
    };
    
    document.body.appendChild(selectScript);
  } else {
    console.log('Select2 already loaded.');
  }
}

// Patch the addNewRow function to handle Select2 not being available
const originalAddNewRow = window.addNewRow;
window.addNewRow = function() {
  const result = originalAddNewRow.apply(this, arguments);
  
  // Ensure Select2 is applied only if available
  if (typeof $.fn.select2 === 'function') {
    try {
      $('.productSelect').not('.select2-hidden-accessible').select2({
        placeholder: "Search for a product",
        allowClear: true,
        width: "100%"
      });
    } catch(e) {
      console.error('Error applying Select2 to new row:', e);
    }
  } else {
    console.warn('Select2 not available for new row - will retry soon');
    // Try again after a short delay
    setTimeout(function() {
      if (typeof $.fn.select2 === 'function') {
        $('.productSelect').not('.select2-hidden-accessible').select2({
          placeholder: "Search for a product",
          allowClear: true,
          width: "100%"
        });
      }
    }, 1000);
  }
  
  return result;
};
    </script>
</body>
</html>
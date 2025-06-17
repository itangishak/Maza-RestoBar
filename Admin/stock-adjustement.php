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
    <title data-key="stockAdjustment.title">Stock Adjustment</title>

  
  
    <?php include_once './header.php'; ?>
    
    <style>
        .main-container {
            margin-top: 50px;
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

    <main class="container main-container">
        <div class="card shadow">
            <div class="card-header">
                <h4 data-key="stockAdjustment.header">Stock Adjustment</h4>
            </div>
            <div class="card-body">
                <form id="stockAdjustmentForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="adjustmentType" data-key="stockAdjustment.type">Type</label>
                            <select name="type" id="adjustmentType" class="form-control" required>
                                <option value="" data-key="stockAdjustment.selectOne">Select One</option>
                                <option value="Add" data-key="stockAdjustment.add">Add</option>
                                <option value="Reduce" data-key="stockAdjustment.reduce">Reduce</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="adjustmentReason" data-key="stockAdjustment.reason">Reason</label>
                            <select name="reason" id="adjustmentReason" class="form-control" required>
                                <option value="" data-key="stockAdjustment.selectReason">Select Reason</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label data-key="stockAdjustment.supplier">Supplier</label>
                            <select name="supplier_id" id="supplier_id" class="form-control" required>
                                <option value="" data-key="stockAdjustment.selectSupplier">Select Supplier</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label data-key="stockAdjustment.notes">Notes</label>
                            <textarea name="notes" class="form-control"></textarea>
                        </div>
                    </div>

                    
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th data-key="stockAdjustment.table.product">Product</th>
                                <th data-key="stockAdjustment.table.measurement">Measurement</th>
                                <th data-key="stockAdjustment.table.currentStock">Current Stock</th>
                                <th data-key="stockAdjustment.table.quantity">Quantity</th>
                                <th data-key="stockAdjustment.table.unitCost">Unit Cost</th>
                                <th data-key="stockAdjustment.table.stockAfter">Stock After</th>
                                <th data-key="stockAdjustment.table.action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <!-- Rows inserted dynamically -->
                        </tbody>
                    </table>

                    <button type="submit" class="btn btn-success" data-key="stockAdjustment.adjustStock">Adjust Stock</button>
                </form>
            </div>
        </div>
    </main>

    <?php include_once './footer.php'; ?>


   
    <script>
    $(document).ready(function () {
        let productOptionsHtml = '';
        let isUpdatingProduct = false; // to prevent recursion on product selection reset

        // ============================
        // Load Supplier & Product Data
        // ============================
        function loadDropdowns() {
            // Load suppliers
            $.getJSON('fetch_suppliers_stock.php', function (data) {
                let options = data.map(item => `<option value="${item.supplier_id}">${item.supplier_name}</option>`);
                $('#supplier_id').html(`<option value="">${getTranslation('stockAdjustment.selectSupplier')}</option>` + options.join(''));
                if ($.fn.select2) { // Check if select2 is loaded
                    $("#supplier_id").select2({
                        placeholder: getTranslation("stockAdjustment.selectSupplier"),
                        allowClear: true,
                        width: "100%"
                    });
                }
            });

            // Load products
            $.getJSON('fetch_products.php', function (data) {
                productOptionsHtml = data.map(item =>
                    `<option value="${item.inventory_id}">${item.item_name}</option>`
                ).join('');

                // Add initial row once product data is loaded
                addNewRow();
                toggleProductSelects();
            });
        }
        loadDropdowns();

        // ============================
        // "Type" & "Reason" Behavior
        // ============================
        $('#adjustmentType').on('change', function () {
            const type = $(this).val();
            const adjustmentReason = $('#adjustmentReason');
            adjustmentReason.empty();

            if (type === 'Add') {
                adjustmentReason.append(`<option value="Received" data-key="stockAdjustment.received">${getTranslation('stockAdjustment.received')}</option>`);
                adjustmentReason.append(`<option value="Correction" data-key="stockAdjustment.correction">${getTranslation('stockAdjustment.correction')}</option>`);
            } else if (type === 'Reduce') {
                adjustmentReason.append(`<option value="Used" data-key="stockAdjustment.used">${getTranslation('stockAdjustment.used')}</option>`);
                adjustmentReason.append(`<option value="Expired" data-key="stockAdjustment.expired">${getTranslation('stockAdjustment.expired')}</option>`);
                adjustmentReason.append(`<option value="Damaged" data-key="stockAdjustment.damaged">${getTranslation('stockAdjustment.damaged')}</option>`);
                adjustmentReason.append(`<option value="Lost" data-key="stockAdjustment.lost">${getTranslation('stockAdjustment.lost')}</option>`);
            } else {
                adjustmentReason.append(`<option value="" data-key="stockAdjustment.selectReason">${getTranslation('stockAdjustment.selectReason')}</option>`);
            }
            // Initialize or update Select2 for adjustmentReason - with safe check
            if ($.fn.select2) {
                try {
                    // Check if select2 is already initialized on this element
                    if ($('#adjustmentReason').hasClass('select2-hidden-accessible')) {
                        $('#adjustmentReason').select2('destroy');
                    }
                    
                $("#adjustmentReason").select2({
                    placeholder: getTranslation("stockAdjustment.selectReason"),
                    allowClear: true,
                    width: "100%"
                });
                } catch (e) {
                    console.error("Error reinitializing select2:", e);
                }
            }
            toggleProductSelects();
        });

        // ============================
        // Toggle product selects
        // (disable if "Type" not chosen)
        // ============================
        function toggleProductSelects() {
            const hasType = $('#adjustmentType').val() !== '';
            $('.productSelect').prop('disabled', !hasType);
        }

        // ============================
        // Check Duplicate Product
        // ============================
        function isDuplicateProduct(productId, currentSelect) {
            let duplicate = false;
            $('.productSelect').not(currentSelect).each(function () {
                if ($(this).val() === productId) duplicate = true;
            });
            return duplicate;
        }

        // ============================
        // Add a new blank row
        // ============================
        function addNewRow() {
            const rowCount = $('#productsTableBody tr').length;
            const newRow = $(`
                <tr>
                    <td>
                        <select name="items[${rowCount}][product]" class="form-control productSelect" required>
                            <option value="">${getTranslation('stockAdjustment.selectProduct')}</option>
                            ${productOptionsHtml}
                        </select>
                    </td>
                    <td><input type="text" name="items[${rowCount}][measurement]" class="form-control measurementField" readonly></td>
                    <td><input type="number" name="items[${rowCount}][current_stock]" class="form-control currentStockField" readonly></td>
                    <td><input type="number" name="items[${rowCount}][quantity]" class="form-control quantityField" required></td>
                    <td><input type="number" step="0.01" name="items[${rowCount}][unit_cost]" class="form-control unitCostField" readonly></td>
                    <td><input type="number" step="0.01" name="items[${rowCount}][stock_after]" class="form-control stockAfterField" readonly></td>
                    <td><button type="button" class="btn btn-sm btn-primary addRowBtn" data-key="stockAdjustment.addRow">+</button></td>
                </tr>
            `);
            $('#productsTableBody').append(newRow);

            // Initialize select2 on productSelect
            if (typeof $.fn.select2 === 'function') {
                newRow.find('.productSelect').select2({
                    placeholder: getTranslation('stockAdjustment.searchProduct', 'Search for a product'),
                    allowClear: true,
                    width: "100%",
                    minimumResultsForSearch: 0, // Always show search box
                    dropdownCssClass: 'select2-dropdown-large', // Add custom class for styling
                    matcher: function(params, data) {
                        // Custom matcher to improve search functionality
                        if (!params.term || params.term.trim() === '') {
                            return data;
                        }
                        
                        var term = params.term.toLowerCase();
                        var text = data.text.toLowerCase();
                        
                        if (text.indexOf(term) > -1) {
                            return data;
                        }
                        
                        return null;
                    }
                });
                
                // Add CSS for better search appearance
                if (!document.getElementById('select2-custom-styles')) {
                    $('head').append(`
                        <style id="select2-custom-styles">
                            .select2-dropdown-large {
                                min-width: 300px !important;
                            }
                            .select2-container--default .select2-search--dropdown .select2-search__field {
                                padding: 8px !important;
                                height: 38px !important;
                            }
                            .select2-results__option {
                                padding: 8px !important;
                            }
                        </style>
                    `);
                }
            } else {
                console.error("Select2 is not loaded or not a function when trying to initialize on .productSelect in stock-adjustement.php");
            };

            // Ensure productSelect is disabled if no "type" selected yet
            toggleProductSelects();
        }

        // ============================
        // Add Row Button
        // ============================
        $('#productsTableBody').on('click', '.addRowBtn', function () {
            const row = $(this).closest('tr');
            const product = row.find('.productSelect').val();
            const quantity= row.find('.quantityField').val();
            const hasType= $('#adjustmentType').val() !== '';

            if (!hasType) {
                Swal.fire(getTranslation('stockAdjustment.warning'), getTranslation('stockAdjustment.selectTypeWarning'), 'warning');
                return;
            }
            if (!product || !quantity || quantity <= 0) {
                Swal.fire(getTranslation('stockAdjustment.warning'), getTranslation('stockAdjustment.completeRowWarning'), 'warning');
                return;
            }

            // Convert + button to − button
            $(this)
                .removeClass('btn-primary addRowBtn')
                .addClass('btn-danger removeRowBtn')
                .text('-')
                .removeAttr('data-key');

            addNewRow();
        });

        // ============================
        // Remove Row Button
        // ============================
        $('#productsTableBody').on('click', '.removeRowBtn', function () {
            $(this).closest('tr').remove();
        });

        // ============================
        // On Product Change
        // ============================
        $('#productsTableBody').on('change', '.productSelect', function () {
            if (isUpdatingProduct) return;
            const productId = $(this).val();
            const row = $(this).closest('tr');

            // Check duplicates
            if (productId && isDuplicateProduct(productId, this)) {
                Swal.fire(getTranslation('stockAdjustment.error'), getTranslation('stockAdjustment.duplicateProductError'), 'error');
                isUpdatingProduct = true;
                $(this).val('').trigger('change');
                isUpdatingProduct = false;
                return;
            }

            // If product is selected, fetch details
            if (productId) {
                $.getJSON(`fetch_product_details.php?inventory_id=${productId}`, function (data) {
                    row.find('.measurementField').val(data.unit);
                    row.find('.currentStockField').val(data.quantity_in_stock);
                    row.find('.unitCostField').val(data.unit_cost);
                    row.find('.quantityField, .stockAfterField').val('');
                });
            } else {
                // Clear fields if no product
                row.find('.measurementField').val('');
                row.find('.currentStockField').val('');
                row.find('.unitCostField').val('');
                row.find('.quantityField, .stockAfterField').val('');
            }
        });

        // ============================
        // Calculate Stock After
        // ============================
        $('#productsTableBody').on('input', '.quantityField', function () {
            const row       = $(this).closest('tr');
            const currentSt = parseFloat(row.find('.currentStockField').val()) || 0;
            const qty       = parseFloat($(this).val()) || 0;
            const type      = $('#adjustmentType').val();

            let stockAfter = currentSt;
            if (type === 'Add') {
                stockAfter = currentSt + qty;
            } else if (type === 'Reduce') {
                stockAfter = currentSt - qty;
            }
            if (stockAfter < 0) {
                Swal.fire(getTranslation('stockAdjustment.error'), getTranslation('stockAdjustment.reduceBelowZeroError'), 'error');
                $(this).val('');
                row.find('.stockAfterField').val(currentSt);
            } else {
                row.find('.stockAfterField').val(stockAfter);
            }
        });

        // ============================
        // Submit => Post JSON to Server
        // ============================
        $('#stockAdjustmentForm').on('submit', function(e) {
            e.preventDefault();
            let adjustmentData = {
                type:       $('#adjustmentType').val(),
                reason:     $('#adjustmentReason').val(),
                supplier_id:$('#supplier_id').val(),
                notes:      $('textarea[name="notes"]').val(),
                items:      []
            };

            // Gather items
            $('#productsTableBody tr').each(function() {
                const productId = $(this).find('.productSelect').val();
                if (!productId) return;  // skip blank row

                const qty      = parseFloat($(this).find('.quantityField').val()) || 0;
                const unitCost = parseFloat($(this).find('.unitCostField').val()) || 0;

                adjustmentData.items.push({
                    product_id: productId,
                    quantity:   qty,
                    unit_cost:  unitCost
                });
            });

            // Must have at least one product
            if (adjustmentData.items.length === 0) {
                Swal.fire(getTranslation('stockAdjustment.warning'), getTranslation('stockAdjustment.noProductsWarning'), 'warning');
                return;
            }

            $.ajax({
                url: 'submit_stock_adjustment.php',
                type: 'POST',
                data: JSON.stringify(adjustmentData),
                contentType: 'application/json',
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire(getTranslation('stockAdjustment.success'), response.message, 'success');
                        $('#stockAdjustmentForm')[0].reset();
                        $('#productsTableBody').empty();
                        addNewRow(); // optionally add a fresh blank row
                    } else {
                        Swal.fire(getTranslation('stockAdjustment.error'), response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('stockAdjustment.error'), getTranslation('stockAdjustment.failedServer'), 'error');
                }
            });
        });

        // ============================
        // Sidebar toggling logic
        // ============================
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
                card.style.marginLeft = '1%';
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
                    card.style.marginLeft = '1%';
                }
            });
        }

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
                    // fallback to English
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
                    } else if (element.tagName === 'SELECT' || element.tagName === 'OPTION') {
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
    });
    </script>

<!-- Force load Select2 CSS -->
<link href="assets/css/select2.min.css" rel="stylesheet" />

<!-- Fix Select2 initialization -->
<script>
// Debug and force load Select2
console.log('Attempting to add search capability to select dropdowns');

// Check if jQuery is loaded
if (typeof jQuery === 'undefined') {
  console.error('jQuery not available!');
} else {
  console.log('jQuery is available.');
  
  // Load DataTables first (if needed)
  loadDataTablesScripts();
  
  // Force load Select2 directly
  var selectScript = document.createElement('script');
  selectScript.src = 'assets/js/select2.min.js';
  selectScript.onload = function() {
    console.log('Select2 script loaded successfully');
    
    // Add a small delay before initializing
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
}
</script>

<!-- Complete solution for searchable dropdowns -->
<script>
// Ensure search box is always visible
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded, ensuring search box visibility');
  
  // Add custom CSS to ensure search box is visible
  var style = document.createElement('style');
  style.textContent = `
    .select2-search { 
      display: block !important;
      padding: 8px !important;
    }
    .select2-search input {
      height: 30px !important;
      width: 100% !important;
      padding: 6px 10px !important;
      font-size: 14px !important;
    }
    .select2-dropdown {
      min-width: 200px !important;
    }
    .select2-results__option {
      padding: 8px !important;
    }
  `;
  document.head.appendChild(style);
});
</script>

<!-- Force load Select2 CSS -->
<link href="assets/css/select2.min.css" rel="stylesheet" />

<!-- Fix DataTables and Select2 initialization -->
<script>
// Properly load DataTables and dependencies in the correct order
function loadDataTablesScripts() {
  console.log('jQuery is available, loading DataTables scripts...');
  
  const scripts = [
    'assets/vendor/datatables/jquery.dataTables.min.js',
    'assets/vendor/datatables/dataTables.bootstrap5.min.js',
    'assets/vendor/datatables/dataTables.buttons.min.js',
    'assets/vendor/datatables/buttons.print.min.js'
  ];
  
  function loadScriptSequentially(index) {
    if (index >= scripts.length) {
      console.log('All DataTables scripts loaded successfully');
      return;
    }
    
    const script = document.createElement('script');
    script.src = scripts[index];
    script.onload = function() {
      console.log('Loaded:', scripts[index]);
      // Load the next script only after this one is loaded
      loadScriptSequentially(index + 1);
    };
    script.onerror = function() {
      console.error('Failed to load:', scripts[index]);
      // Continue with next script even if this one fails
      loadScriptSequentially(index + 1);
    };
    document.body.appendChild(script);
  }
  
  // Start loading scripts sequentially
  loadScriptSequentially(0);
}

// Debug and force load Select2
console.log('Attempting to add search capability to select dropdowns');
    </script>
</body>
</html>


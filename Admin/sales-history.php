<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check if user has Boss privilege for additional functionality
$isBoss = false;
if ((isset($_SESSION['privilege']) && $_SESSION['privilege'] === 'Boss') || 
    (isset($_SESSION['Privilege']) && $_SESSION['Privilege'] === 'Boss')) {
    $isBoss = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Title using data-key for translation -->
    <title data-key="report.salesHistory"></title>
    
    <!-- Include header which contains all necessary CSS and JS -->
    <?php include_once './header.php'; ?>
    
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
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
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <div class="container main-container">
        <div class="card">
            <div class="card-header">
                <!-- Card header translated -->
                <h4 data-key="report.salesHistory"></h4>
            </div>
            <div class="card-body">
                <!-- Sale type selection buttons -->
                <div class="mb-4">
                    <div class="btn-group" role="group" aria-label="Sale type filter">
                        <button type="button" class="btn btn-outline-primary active" data-sale-type="all" data-key="report.allSales">All Sales</button>
                        <button type="button" class="btn btn-outline-primary" data-sale-type="menu" data-key="report.menuSales">Menu</button>
                        <button type="button" class="btn btn-outline-primary" data-sale-type="drink" data-key="report.drinkSales">Drinks</button>
                        <button type="button" class="btn btn-outline-primary" data-sale-type="buffet" data-key="report.buffetSales">Buffet</button>
                    </div>
                </div>
                
                <table id="salesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th data-key="report.saleID">ID</th>
                            <th data-key="report.itemName">Item</th>
                            <th data-key="report.quantitySold">Quantity</th>
                            <th data-key="report.totalSalePrice">Price</th>
                            <th data-key="report.saleDate">Date</th>
                            <?php if ($isBoss): ?>
                            <th data-key="report.status">Status</th>
                            <th data-key="report.actions">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data populated dynamically via server-side processing -->
                    </tbody>
                </table>
                
                <!-- Cancellation Reason Modal (Only visible for Boss) -->
                <?php if ($isBoss): ?>
                <!-- Use a higher tabindex and remove aria-hidden to fix accessibility issues -->
                <div class="modal fade" id="cancellationModal" tabindex="1" aria-labelledby="cancellationModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="cancellationModalLabel" data-key="report.cancelSale">Cancel Sale</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="saleIdToCancel" value="">
                                <input type="hidden" id="saleTypeToCancel" value="">
                                <div class="mb-3">
                                    <label for="cancellationReason" class="form-label" data-key="report.cancelReason">Reason for cancellation</label>
                                    <textarea class="form-control" id="cancellationReason" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="general.close">Close</button>
                                <button type="button" class="btn btn-danger" id="confirmCancellation" data-key="report.confirmCancel">Confirm Cancellation</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Include Footer -->
    <?php include_once './footer.php'; ?>
    
    <script>
        // Define variables in a scope accessible to all functions
        let salesTable; // Global variable to hold the DataTable instance
        let currentSaleType = 'all'; // Store the current sale type filter
        
        // Make the initialization function globally available
        window.initializeDataTables = function() {
            console.log('Initializing DataTable...');
            try {
                // Check if DataTable object is available
                if (typeof $.fn.DataTable !== 'function') {
                    console.error('DataTable is not loaded');
                    return;
                }
                
                // Create a simpler DataTable with fixed column structure
                salesTable = $('#salesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "fetch_sales_history.php",
                        type: "GET",
                        data: function(d) {
                            d.saleType = currentSaleType;
                            return d;
                        },
                        error: function(xhr, error, thrown) {
                            console.error('DataTables Ajax error:', error, thrown);
                        }
                    },
                    order: [[4, "desc"]], // Sort by date column
                    columns: [
                        { data: "sale_id" },
                        { data: "item_name" },
                        { data: "quantity" },
                        { 
                            data: "price",
                            render: function(data) {
                                return `BIF ${parseFloat(data).toFixed(2)}`;
                            }
                        },
                        { data: "sale_date" }
                        <?php if ($isBoss): ?>
                        ,
                        { 
                            data: "status", 
                            render: function(data) {
                                if (data === 'active') {
                                    return '<span class="badge bg-success">Active</span>';
                                } else {
                                    return '<span class="badge bg-danger">Canceled</span>';
                                }
                            }
                        },
                        { 
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                if (row.status === 'active') {
                                    return `<button class="btn btn-sm btn-danger cancel-sale" data-id="${row.sale_id}" data-type="${row.sale_type}">Cancel</button>`;
                                } else {
                                    return `<small class="text-muted">${row.cancellation_reason || 'No reason provided'}</small>`;
                                }
                            }
                        }
                        <?php endif; ?>
                    ],
                    pageLength: 10,
                    lengthMenu: [10, 25, 50, 100],
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'print',
                            text: 'Print',
                            className: 'btn btn-primary',
                            exportOptions: {
                                columns: ':visible'
                            },
                            title: 'Sales History Report'
                        }
                    ],
                    language: {
                        processing: "Processing...",
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        infoPostFix: "",
                        loadingRecords: "Loading...",
                        zeroRecords: "No matching records found",
                        emptyTable: "No data available in table",
                        paginate: {
                            first: "First",
                            previous: "Previous",
                            next: "Next",
                            last: "Last"
                        }
                    }
                });
                
                console.log('DataTable initialized successfully');
            } catch (error) {
                console.error('Error initializing DataTable:', error);
            }
        };

        // Initialize sidebar
        function initializeSidebar() {
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
        }

        // Initialize sidebar when document is ready
        $(document).ready(function() {
            console.log('Document ready, initializing sidebar...');
            initializeSidebar();
            
            // Sale type filter button handling
            $('.btn-group [data-sale-type]').on('click', function() {
                // Update UI
                $('.btn-group [data-sale-type]').removeClass('active');
                $(this).addClass('active');
                
                // Update filter and reload table
                currentSaleType = $(this).data('sale-type');
                
                // Log the filter change for debugging
                console.log('Changing sale type filter to: ' + currentSaleType);
                
                // Clear any existing search and reload with the new filter
                salesTable.search('').draw();
                salesTable.ajax.reload();
            });
            
            <?php if ($isBoss): ?>
            // Global variable for the modal instance
            let cancellationModalInstance = null;
            
            // Sale cancellation handling (single event handler)
            $(document).on('click', '.cancel-sale', function() {
                const saleId = $(this).data('id');
                const saleType = $(this).data('type');
                
                // Set values in the modal
                $('#saleIdToCancel').val(saleId);
                $('#saleTypeToCancel').val(saleType);
                $('#cancellationReason').val(''); // Clear previous reason
                
                // Clean up any existing modal instance
                if (cancellationModalInstance) {
                    cancellationModalInstance.dispose();
                }
                
                // Create a new modal instance
                cancellationModalInstance = new bootstrap.Modal(document.getElementById('cancellationModal'), {
                    backdrop: true,
                    keyboard: true
                });
                
                // Store the trigger button for focus return
                const triggerButton = this;
                
                // Clear any existing event handlers
                $('#cancellationModal').off('shown.bs.modal hidden.bs.modal');
                
                // Set up new event handlers
                $('#cancellationModal').on('shown.bs.modal', function() {
                    // Focus the textarea when modal opens
                    $('#cancellationReason').trigger('focus');
                });
                
                $('#cancellationModal').on('hidden.bs.modal', function() {
                    // Return focus to the triggering element when modal closes
                    $(triggerButton).trigger('focus');
                });
                
                // Show the modal
                cancellationModalInstance.show();
                
                // Log for debugging
                console.log('Cancel button clicked for sale ID:', saleId, 'of type:', saleType);
            });
            
            // Confirm cancellation button handler
            $('#confirmCancellation').on('click', function() {
                const saleId = $('#saleIdToCancel').val();
                const saleType = $('#saleTypeToCancel').val();
                const reason = $('#cancellationReason').val();
                
                if (!reason) {
                    alert('Please provide a reason for cancellation');
                    return;
                }
                
                // Close the modal first to prevent UI issues
                if (cancellationModalInstance) {
                    cancellationModalInstance.hide();
                }
                
                // Show loading message
                Swal.fire({
                    title: 'Processing...',
                    text: 'Cancelling the sale',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send cancellation request to server with jQuery AJAX
                console.log('Preparing to send cancellation request with data:', {
                    saleId: saleId,
                    saleType: saleType,
                    reason: reason
                });
                
                $.ajax({
                    url: 'cancel_sale.php',
                    type: 'POST',
                    data: {
                        saleId: saleId,
                        saleType: saleType,
                        reason: reason
                    },
                    dataType: 'json',
                    cache: false,
                    beforeSend: function() {
                        console.log('AJAX request being sent to cancel_sale.php');
                    },
                    success: function(response) {
                        console.log('AJAX success callback triggered');
                        console.log('Raw server response:', response);
                        console.log('Response type:', typeof response);
                        console.log('Response JSON:', JSON.stringify(response));
                        
                        // Close the loading indicator
                        Swal.close();
                        
                        // Log before checking response
                        console.log('About to check response.success value:', response.success);
                        
                        // Check response status
                        if (response && response.success === true) {
                            console.log('Success condition met, showing success message');
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'The sale has been successfully canceled.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                console.log('Success dialog closed with result:', result);
                                // Reload table data after user clicks OK
                                salesTable.ajax.reload();
                            });
                        } else {
                            console.log('Error condition met, showing error message');
                            // Show error message with detailed response
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: (response && response.message) ? response.message : 'Failed to cancel sale',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX error callback triggered');
                        console.error('AJAX error status:', status);
                        console.error('AJAX error:', error);
                        console.log('XMLHttpRequest object:', xhr);
                        console.log('Response text:', xhr.responseText);
                        console.log('Response headers:', xhr.getAllResponseHeaders());
                        
                        // Close the loading indicator
                        Swal.close();
                        
                        // Try to parse the response in case it's JSON
                        let errorMessage = 'A server error occurred. Please try again.';
                        try {
                            if (xhr.responseText && xhr.responseText.trim()) {
                                console.log('Attempting to parse JSON from:', xhr.responseText);
                                const jsonResponse = JSON.parse(xhr.responseText);
                                console.log('Parsed JSON response:', jsonResponse);
                                if (jsonResponse && jsonResponse.message) {
                                    errorMessage = jsonResponse.message;
                                    console.log('Using message from JSON response:', errorMessage);
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing JSON response:', e);
                        }
                        
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
            <?php endif; ?>
        });
    </script>

    <!-- Translation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const systemLang = navigator.language || navigator.userLanguage;
            let defaultLang = 'en';
            if (systemLang.startsWith('fr')) {
                defaultLang = 'fr';
            }
            const currentLang = localStorage.getItem('userLang') || defaultLang;
            loadTranslations(currentLang);
        });

        function loadTranslations(lang) {
            fetch(`../languages/${lang}.json`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Cannot fetch translations: ${response.statusText}`);
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
                                throw new Error(`Fallback error: ${response.statusText}`);
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
                    } else {
                        element.textContent = translations[key];
                    }
                }
            });
        }

        function switchLanguage(lang) {
            localStorage.setItem('userLang', lang);
            loadTranslations(lang);
        }
    </script>
</body>
</html>

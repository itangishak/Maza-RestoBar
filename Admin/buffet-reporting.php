<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check if user has Boss privilege
if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'Boss') {
    // Redirect to dashboard with access denied message
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Initialize variables
$success_message = $error_message = '';
$time_periods = ['Morning', 'Noon', 'Evening'];
$current_date = date('Y-m-d');
$selected_period = '';
$selected_date = $current_date;

// Get buffet price based on time of day
function getBuffetPrice($conn, $time_of_day) {
    $stmt = $conn->prepare("SELECT base_price, fixed_discount, percentage_discount FROM buffet_preferences WHERE period_name = ?");
    $stmt->bind_param("s", $time_of_day);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $base_price = $row['base_price'];
        $fixed_discount = $row['fixed_discount'] ?? 0;
        $percentage_discount = $row['percentage_discount'] ?? 0;
        
        // Calculate price after discounts
        $price_after_fixed = $base_price - $fixed_discount;
        $final_price = $price_after_fixed - ($price_after_fixed * ($percentage_discount / 100));
        
        return $final_price;
    }
    
    return 0; // Default if no price found
}

// Handle form submission for new buffet sale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_buffet_sale') {
    // Retrieve and sanitize form data
    $sale_date = $_POST['sale_date'] ?? $current_date;
    $time_of_day = $_POST['time_of_day'] ?? '';
    $dishes_sold = (int)($_POST['dishes_sold'] ?? 0);
    
    // Basic validation
    if (empty($sale_date) || empty($time_of_day) || $dishes_sold <= 0) {
        $error_message = "Please fill in all required fields with valid values.";
    } else {
        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // Get buffet price
            $price = getBuffetPrice($conn, $time_of_day);
            
            // Insert into buffet_sale_items
            $stmt = $conn->prepare("INSERT INTO buffet_sale_items (sale_date, time_of_day, price) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $sale_date, $time_of_day, $price);
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting buffet sale: " . $stmt->error);
            }
            
            $buffet_item_id = $conn->insert_id;
            
            // Process components if any
            if (isset($_POST['component']) && is_array($_POST['component'])) {
                $component_ids = $_POST['component'] ?? [];
                $quantities = $_POST['quantity'] ?? [];
                
                for ($i = 0; $i < count($component_ids); $i++) {
                    if (empty($component_ids[$i]) || empty($quantities[$i])) {
                        continue; // Skip empty entries
                    }
                    
                    $component_id = (int)$component_ids[$i];
                    $quantity_used = (float)$quantities[$i];
                    
                    // Insert component usage into buffet_sale_item table
                    $stmt = $conn->prepare("INSERT INTO buffet_sale_item (buffet_item_id, item_id, quantity_used, sale_date, time_of_day) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("iidss", $buffet_item_id, $component_id, $quantity_used, $sale_date, $time_of_day);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Error inserting component usage: " . $stmt->error);
                    }
                    
                    // Update inventory by reducing stock
                    $stmt = $conn->prepare("UPDATE inventory_items SET quantity_in_stock = quantity_in_stock - ? WHERE inventory_id = ?");
                    $stmt->bind_param("di", $quantity_used, $component_id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Error updating inventory: " . $stmt->error);
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            $success_message = "Buffet sale recorded successfully! Components deducted from inventory.";
            
            // Reset form data after successful submission
            $selected_period = '';
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Get inventory items for component selection
try {
    $inventory_query = "SELECT inventory_id, item_name, category, quantity_in_stock, unit FROM inventory_items ORDER BY category, item_name";
    $inventory_result = $conn->query($inventory_query);
    $inventory_items = [];
    
    if ($inventory_result && $inventory_result->num_rows > 0) {
        while ($row = $inventory_result->fetch_assoc()) {
            $inventory_items[] = $row;
        }
    }
} catch (Exception $e) {
    $error_message = "Error fetching inventory items: " . $e->getMessage();
}

// Recent buffet sales table has been removed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-key="sidebar.buffetReporting">Buffet Reporting</title>
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
        }
        .sidebar.collapsed {
            width: 70px;
            transition: width 0.3s;
        }
        .main-container.full {
            margin-left: 0 !important;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .component-row {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-canceled {
            color: red;
            font-weight: bold;
        }
        .component-container {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .text-danger {
            color: #dc3545;
        }
        .select2-container {
            width: 100% !important;
        }
        /* Form field spacing and alignment */
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 500;
        }
        .form-control, .form-select {
            height: 45px;
            border-radius: 4px;
        }
        /* Table styling */
        .table-responsive {
            border-radius: 5px;
            overflow: hidden;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        /* Button styling */
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 8px 16px;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 8px 16px;
        }
        /* Card header styling */
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 1rem 1.25rem;
        }
        .card-header h4 {
            margin-bottom: 0;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebarboss.php'; ?>

    <div class="container main-container">
        <!-- Form Card for New Buffet Sale -->
        <div class="card">
            <div class="card-header">
                <h4 data-key="sidebar.buffetReporting">Add New Buffet Sale</h4>
            </div>
            <div class="card-body">
                <!-- SweetAlert will be triggered by JavaScript -->

                        <form method="POST" action="" id="buffetSaleForm">
                            <input type="hidden" name="action" value="add_buffet_sale">
                            
                            <div class="row mb-4">
                                <!-- Sale Date -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sale_date" class="form-label">Sale Date</label>
                                        <input type="date" class="form-control" id="sale_date" name="sale_date" value="<?php echo $selected_date; ?>" required>
                                    </div>
                                </div>
                                
                                <!-- Time of Day -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="time_of_day" class="form-label">Time of Day</label>
                                        <select class="form-select" id="time_of_day" name="time_of_day" required>
                                            <option value="">Select Time Period</option>
                                            <?php foreach ($time_periods as $period): ?>
                                                <option value="<?php echo $period; ?>" <?php echo ($selected_period === $period) ? 'selected' : ''; ?>>
                                                    <?php echo $period; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Number of Dishes Sold -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="dishes_sold" class="form-label">Number of Dishes Sold</label>
                                        <input type="number" class="form-control" id="dishes_sold" name="dishes_sold" min="0" value="0" readonly required>
                                        <small class="text-muted">Auto-populated based on sales data. Zero means no sales to report.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Price Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="buffet_price" class="form-label">Buffet Price (per dish)</label>
                                        <input type="text" class="form-control" id="buffet_price" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="total_price" class="form-label">Total Price</label>
                                        <input type="text" class="form-control" id="total_price" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- Components Used Section -->
                            <div class="mb-4">
                                <h5 class="mb-3">Components Used</h5>
                                <p class="text-muted">Add the ingredients/components used for this buffet. Quantities will be deducted from inventory.</p>
                                
                                <div class="component-container border rounded p-3 bg-light" id="componentContainer">
                                        <!-- Component rows will be added here -->
                                        <div class="component-row" id="componentRow0">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="component0" class="form-label">Component</label>
                                                        <select class="form-select component-select" id="component0" name="component[]">
                                                            <option value="">Select Component</option>
                                                            <?php foreach ($inventory_items as $item): ?>
                                                                <option value="<?php echo $item['inventory_id']; ?>" 
                                                                        data-stock="<?php echo $item['quantity_in_stock']; ?>"
                                                                        data-unit="<?php echo $item['unit']; ?>">
                                                                    <?php echo $item['item_name']; ?> (<?php echo $item['category']; ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="quantity0" class="form-label">Quantity Used</label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" id="quantity0" name="quantity[]" min="0.01" step="0.01">
                                                            <span class="input-group-text unit-text" id="unit0"></span>
                                                        </div>
                                                        <small class="text-muted stock-info" id="stock0"></small>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="button" class="btn btn-danger form-control" onclick="removeComponentRow(this)">
                                                            <i class="bi bi-trash"></i> Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" class="btn btn-secondary" id="addComponentBtn">
                                    <i class="bi bi-plus-circle"></i> Add Another Component
                                </button>
                            </div>
                            
                            <!-- Alert for zero dishes - hidden by default -->
                            <div class="alert alert-warning mt-3 d-none" id="zeroDishesAlert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                No dishes sold detected for this date and time period. No need to report.
                            </div>
                            
                            <div class="text-center mt-4 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="saveBuffetBtn">
                                    <i class="bi bi-save"></i> Save Buffet Sale
                                </button>
                                <a href="buffet-reporting.php" class="btn btn-secondary btn-lg ms-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
            </div>
        </div>
    </div>

    <?php include_once './footer.php'; ?>
    
    <script>
        // SweetAlert Notifications
        document.addEventListener('DOMContentLoaded', function() {
            // Check for URL parameters for success/error messages
            const urlParams = new URLSearchParams(window.location.search);
            
            // Process any stored offline operations that were pending
            checkPendingOfflineOperations();
            
            // Handle success messages
            if (urlParams.has('success')) {
                let successMessage = 'Operation completed successfully';
                
                switch(urlParams.get('success')) {
                    case 'sale_added':
                        successMessage = 'Buffet sale has been successfully added';
                        break;
                    case 'sale_canceled':
                        successMessage = 'Buffet sale has been successfully canceled';
                        break;
                    default:
                        successMessage = 'Operation completed successfully';
                }
                
                Swal.fire({
                    title: 'Success!',
                    text: successMessage,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
            
            // Handle error messages
            if (urlParams.has('error')) {
                let errorMessage = 'An error occurred';
                
                switch(urlParams.get('error')) {
                    case 'invalid_sale':
                        errorMessage = 'Invalid sale information';
                        break;
                    case 'access_denied':
                        errorMessage = 'You do not have permission to perform this action';
                        break;
                    case 'sale_not_found':
                        errorMessage = 'The requested sale was not found';
                        break;
                    default:
                        errorMessage = 'An error occurred: ' + urlParams.get('error');
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
            
            <?php if (!empty($success_message)): ?>
            // Display PHP-generated success message
            Swal.fire({
                title: 'Success!',
                text: '<?php echo addslashes($success_message); ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            // Display PHP-generated error message
            Swal.fire({
                title: 'Error!',
                text: '<?php echo addslashes($error_message); ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>
        });
        
        // Function to check for pending offline operations stored in localStorage or cookies
        function checkPendingOfflineOperations() {
            // Check for pending buffet sale cancellations
            const pendingCancelCookie = getCookie('pending_buffet_cancel');
            if (pendingCancelCookie) {
                try {
                    const pendingData = JSON.parse(pendingCancelCookie);
                    // If we're online now, attempt to synchronize
                    if (navigator.onLine) {
                        Swal.fire({
                            title: 'Pending Action',
                            text: 'There is a pending buffet sale cancellation. Would you like to retry it now?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, retry now',
                            cancelButtonText: 'No, keep pending'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Clear cookie and redirect to the details page
                                document.cookie = 'pending_buffet_cancel=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
                                window.location.href = 'buffet-sale-details.php?id=' + pendingData.buffet_item_id;
                            }
                        });
                    } else {
                        // Still offline, just notify
                        Swal.fire({
                            title: 'Offline Mode',
                            text: 'There are pending operations that will be synchronized when you are back online.',
                            icon: 'info',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (e) {
                    console.error('Error parsing pending cancel data:', e);
                }
            }
            
            // Helper function to get cookie by name
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
                return null;
            }
        }
    </script>
    
    <script>
        // Sidebar toggling logic
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');
        const mainContainer = document.querySelector('.main-container');
        const cards = document.querySelectorAll('.card');
        
        function applyCardStyles() {
            if (cards && cards.length > 0) {
                cards.forEach(card => {
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
        
        // Apply styles on page load
        if (sidebar && cards) {
            applyCardStyles();
        }
        
        // Toggle sidebar and update styles on click
        if (toggleSidebarBtn && sidebar && mainContainer) {
            toggleSidebarBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                mainContainer.classList.toggle('full');
                applyCardStyles();
            });
        }
        
        // Initialize variables
        let componentCount = 1;
        
        // Function to fetch buffet price based on time of day and sale date from buffet_sale_items
        function getBuffetPrice() {
            const timeOfDay = document.getElementById('time_of_day').value;
            const saleDate = document.getElementById('sale_date').value;
            
            if (timeOfDay) {
                // Show loading indicator
                const buffetPriceField = document.getElementById('buffet_price');
                buffetPriceField.value = 'Loading...';
                
                // Format the query parameters
                const formattedTimeOfDay = timeOfDay.charAt(0).toUpperCase() + timeOfDay.slice(1).toLowerCase();
                let queryUrl = `get_buffet_item_price.php?time_of_day=${encodeURIComponent(formattedTimeOfDay)}`;
                
                // Add sale date if available
                if (saleDate) {
                    queryUrl += `&sale_date=${encodeURIComponent(saleDate)}`;
                }
                
                console.log('Fetching buffet price from URL:', queryUrl);
                
                // Fetch buffet price from server (using buffet_sale_items table)
                fetch(queryUrl)
                .then(response => {
                    console.log('Price response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Received price data:', data);
                    if (data.status === 'success') {
                        // Set buffet price
                        buffetPriceField.value = data.price + ' ' + data.currency;
                        console.log('Set buffet price to:', data.price);
                        // Update total price
                        updateTotalPrice();
                        // Now that we have the time of day and potentially the date, get dishes sold
                        if (saleDate) {
                            getDishesSoldCount();
                        } else {
                            // If we don't have a date yet, default dishes to 0
                            const dishesSoldField = document.getElementById('dishes_sold');
                            if (dishesSoldField) {
                                dishesSoldField.value = 0;
                                handleZeroDishesCase();
                            }
                        }
                    } else {
                        buffetPriceField.value = '0.00 BIF';
                        console.error('Error fetching buffet price:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    buffetPriceField.value = '0.00 BIF';
                });
            }
        }
        
        // Function to update the total price based on buffet price and dishes sold
        function updateTotalPrice() {
            const buffetPriceField = document.getElementById('buffet_price');
            const dishesSoldField = document.getElementById('dishes_sold');
            const totalPriceField = document.getElementById('total_price');
            
            if (buffetPriceField && dishesSoldField && totalPriceField) {
                const buffetPrice = parseFloat(buffetPriceField.value.replace(/[^\d.]/g, '')) || 0;
                const dishesSold = parseInt(dishesSoldField.value) || 1;
                
                // Calculate total price
                const totalPrice = buffetPrice * dishesSold;
                
                // Format with 2 decimal places and add currency
                totalPriceField.value = totalPrice.toFixed(2) + ' BIF';
            }
        }
        
        // Function to check if dishes_sold is zero and handle form accordingly
        function handleZeroDishesCase() {
            const dishesSoldField = document.getElementById('dishes_sold');
            const zeroDishesAlert = document.getElementById('zeroDishesAlert');
            const saveBuffetBtn = document.getElementById('saveBuffetBtn');
            
            if (dishesSoldField && zeroDishesAlert && saveBuffetBtn) {
                const dishesSoldValue = parseInt(dishesSoldField.value) || 0;
                
                if (dishesSoldValue === 0) {
                    // Show alert and disable submit button for zero dishes
                    zeroDishesAlert.classList.remove('d-none');
                    saveBuffetBtn.disabled = true;
                    saveBuffetBtn.classList.add('disabled');
                } else {
                    // Hide alert and enable submit button for non-zero dishes
                    zeroDishesAlert.classList.add('d-none');
                    saveBuffetBtn.disabled = false;
                    saveBuffetBtn.classList.remove('disabled');
                }
            }
        }
        
        // Function to get and set dishes sold count based on date and time of day
        function getDishesSoldCount() {
            const saleDate = document.getElementById('sale_date').value;
            const timeOfDay = document.getElementById('time_of_day').value;
            
            console.log('Getting dishes sold for date:', saleDate, 'and time:', timeOfDay);
            
            if (saleDate && timeOfDay) {
                // Show loading indicator
                const dishesSoldField = document.getElementById('dishes_sold');
                dishesSoldField.value = 'Loading...';
                
                // Force the time_of_day to exactly match the case stored in database
                // The database uses 'Morning', 'Noon', 'Evening' (first letter capitalized)
                const formattedTimeOfDay = timeOfDay.charAt(0).toUpperCase() + timeOfDay.slice(1).toLowerCase();
                
                // Create the query URL with properly formatted parameters
                const queryUrl = `get_dishes_sold.php?sale_date=${encodeURIComponent(saleDate)}&time_of_day=${encodeURIComponent(formattedTimeOfDay)}`;
                console.log('Fetching from URL:', queryUrl);
                
                // Fetch dishes sold count from server
                fetch(queryUrl)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Received data:', data);
                        if (data.status === 'success') {
                            // Set dishes sold count
                            dishesSoldField.value = data.dishes_sold;
                            console.log('Set dishes_sold to:', data.dishes_sold);
                            // Update the total price
                            updateTotalPrice();
                            // Handle zero dishes case
                            handleZeroDishesCase();
                        } else {
                            // Default to 0 if there's an error
                            dishesSoldField.value = 0;
                            console.error('Error fetching dishes sold:', data.message);
                            handleZeroDishesCase();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        dishesSoldField.value = 0; // Default to 0 on error
                        handleZeroDishesCase();
                    });
            }
        }
        
        // Initialize on document ready
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for date and time of day fields
            const saleDateField = document.getElementById('sale_date');
            const timeOfDayField = document.getElementById('time_of_day');
            
            if (saleDateField && timeOfDayField) {
                // When time of day changes, get buffet price
                timeOfDayField.addEventListener('change', function() {
                    getBuffetPrice();
                    // getDishesSoldCount will be called after price is fetched if date exists
                });
                
                // When date changes and time of day is already selected, get dishes sold
                saleDateField.addEventListener('change', function() {
                    if (timeOfDayField.value) {
                        getDishesSoldCount();
                    }
                });
                
                // Add event listener for dishes_sold to update total price when manually changed
                const dishesSoldField = document.getElementById('dishes_sold');
                if (dishesSoldField) {
                    dishesSoldField.addEventListener('change', function() {
                        updateTotalPrice();
                    });
                    dishesSoldField.addEventListener('input', function() {
                        updateTotalPrice();
                    });
                }
            }
            
            // Initialize Select2 for better dropdowns
            if ($.fn.select2) {
                $('.component-select').select2({
                    placeholder: "Select a component",
                    allowClear: true
                });
            }
            
            // Set up event listeners
            setupEventListeners();
            
            // Initialize DataTable
            if ($.fn.DataTable) {
                $('#salesTable').DataTable({
                    order: [[1, 'desc'], [2, 'desc']],
                    pageLength: 10,
                    responsive: true
                });
            }
            
            // Show SweetAlert messages if needed
            <?php if (!empty($success_message)): ?>
            Swal.fire({
                title: 'Success',
                text: '<?php echo $success_message; ?>',
                icon: 'success'
            });
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            Swal.fire({
                title: 'Error',
                text: '<?php echo $error_message; ?>',
                icon: 'error'
            });
            <?php endif; ?>
        });
        
        // Set up event listeners
        function setupEventListeners() {
            // Time of day change event
            $('#time_of_day').on('change', function() {
                fetchBuffetPrice();
            });
            
            // Dishes sold change event
            $('#dishes_sold').on('input', function() {
                updateTotalPrice();
            });
            
            // Add component button click
            $('#addComponentBtn').on('click', function() {
                addComponentRow();
            });
            
            // Initial component selection change event
            $('#component0').on('change', function() {
                updateComponentInfo(0);
            });
            
            // Form submission validation
            $('#buffetSaleForm').on('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
                
                // Confirm submission
                if (!confirm('Are you sure you want to save this buffet sale? This will deduct components from inventory.')) {
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        }
        
        // Fetch buffet price based on time of day
        function fetchBuffetPrice() {
            const timeOfDay = $('#time_of_day').val();
            if (!timeOfDay) {
                $('#buffet_price').val('');
                updateTotalPrice();
                return;
            }
            
            // Ajax call to get buffet price
            $.ajax({
                url: 'get_buffet_price.php',
                type: 'POST',
                data: { time_of_day: timeOfDay },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#buffet_price').val(response.price.toFixed(2) + ' BIF');
                        updateTotalPrice();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to fetch buffet price', 'error');
                }
            });
        }
        
        // Update total price based on price per dish and number of dishes
        function updateTotalPrice() {
            const priceText = $('#buffet_price').val();
            const dishes = parseInt($('#dishes_sold').val()) || 0;
            
            if (priceText) {
                const price = parseFloat(priceText.replace(' BIF', '')) || 0;
                const total = price * dishes;
                $('#total_price').val(total.toFixed(2) + ' BIF');
            } else {
                $('#total_price').val('');
            }
        }
        
        // Add a new component row
        function addComponentRow() {
            const newRow = $('#componentRow0').clone();
            
            // Update IDs and names
            newRow.attr('id', 'componentRow' + componentCount);
            newRow.find('.component-select').attr('id', 'component' + componentCount).val('');
            newRow.find('input[name="quantity[]"]').attr('id', 'quantity' + componentCount).val('');
            newRow.find('.unit-text').attr('id', 'unit' + componentCount).text('');
            newRow.find('.stock-info').attr('id', 'stock' + componentCount).text('');
            
            // Append to container
            $('#componentContainer').append(newRow);
            
            // Initialize Select2 for the new dropdown
            if ($.fn.select2) {
                newRow.find('.component-select').select2({
                    placeholder: "Select a component",
                    allowClear: true
                });
            }
            
            // Set up event listener for the new component dropdown
            newRow.find('.component-select').on('change', function() {
                updateComponentInfo(componentCount);
            });
            
            componentCount++;
        }
        
        // Remove a component row
        function removeComponentRow(button) {
            // Don't remove if it's the only row
            if ($('.component-row').length > 1) {
                $(button).closest('.component-row').remove();
            } else {
                Swal.fire('Info', 'You need at least one component row', 'info');
            }
        }
        
        // Update component information (unit and stock)
        function updateComponentInfo(index) {
            const select = $('#component' + index);
            const selectedOption = select.find('option:selected');
            
            if (selectedOption.val()) {
                const unit = selectedOption.data('unit');
                const stock = selectedOption.data('stock');
                
                $('#unit' + index).text(unit);
                $('#stock' + index).text('Available: ' + stock + ' ' + unit);
                
                // Check if stock is low
                if (stock < 10) {
                    $('#stock' + index).addClass('text-danger');
                } else {
                    $('#stock' + index).removeClass('text-danger');
                }
            } else {
                $('#unit' + index).text('');
                $('#stock' + index).text('');
            }
        }
        
        // Validate form before submission
        function validateForm() {
            let isValid = true;
            const saleDate = $('#sale_date').val();
            const timeOfDay = $('#time_of_day').val();
            const dishesSold = $('#dishes_sold').val();
            
            // Check required fields
            if (!saleDate || !timeOfDay || !dishesSold) {
                Swal.fire('Error', 'Please fill in all required fields', 'error');
                return false;
            }
            
            // Validate components
            let hasComponents = false;
            $('.component-select').each(function() {
                if ($(this).val()) {
                    hasComponents = true;
                    const index = $(this).attr('id').replace('component', '');
                    const quantity = $('#quantity' + index).val();
                    
                    if (!quantity || parseFloat(quantity) <= 0) {
                        Swal.fire('Error', 'Please enter a valid quantity for each selected component', 'error');
                        isValid = false;
                        return false;
                    }
                    
                    // Check if quantity exceeds available stock
                    const availableStock = parseFloat($(this).find('option:selected').data('stock'));
                    if (parseFloat(quantity) > availableStock) {
                        Swal.fire('Error', 'Quantity exceeds available stock for one or more components', 'error');
                        isValid = false;
                        return false;
                    }
                }
            });
            
            if (!hasComponents) {
                Swal.fire('Error', 'Please select at least one component for the buffet', 'error');
                return false;
            }
            
            return isValid;
        }
    </script>
</body>
</html>
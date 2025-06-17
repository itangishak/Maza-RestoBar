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
    // Redirect to dashboard or show access denied message
    header("Location: dashboard.php?error=access_denied");
    exit();
}

// Initialize variables
$success_message = $error_message = '';
$editing = false;
$period_id = 0;

// Default values for form
$period = [
    'period_name' => 'Noon',
    'start_time' => '12:00',
    'end_time' => '14:00',
    'base_price' => '10000.00',
    'fixed_discount' => null,
    'percentage_discount' => null,
    'valid_from' => null,
    'valid_to' => null,
    'is_active' => 1
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Handle different actions
        switch ($_POST['action']) {
            case 'add':
            case 'update':
                // Validate form data
                $period_name = $_POST['period_name'] ?? '';
                $start_time = $_POST['start_time'] ?? '';
                $end_time = $_POST['end_time'] ?? '';
                $base_price = $_POST['base_price'] ?? 0;
                $fixed_discount = !empty($_POST['fixed_discount']) ? $_POST['fixed_discount'] : null;
                $percentage_discount = !empty($_POST['percentage_discount']) ? $_POST['percentage_discount'] : null;
                $valid_from = !empty($_POST['valid_from']) ? $_POST['valid_from'] : null;
                $valid_to = !empty($_POST['valid_to']) ? $_POST['valid_to'] : null;
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Basic validation
                if (empty($period_name) || empty($start_time) || empty($end_time) || empty($base_price)) {
                    $error_message = "Please fill in all required fields.";
                } else {
                    // Prepare query based on action
                    if ($_POST['action'] === 'add') {
                        $query = "INSERT INTO buffet_preferences 
                                (period_name, start_time, end_time, base_price, fixed_discount, 
                                percentage_discount, valid_from, valid_to, is_active) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    } else { // update
                        $query = "UPDATE buffet_preferences SET 
                                period_name = ?, start_time = ?, end_time = ?, base_price = ?, 
                                fixed_discount = ?, percentage_discount = ?, valid_from = ?, 
                                valid_to = ?, is_active = ? 
                                WHERE period_id = ?";
                    }
                    
                    try {
                        $stmt = $conn->prepare($query);
                        
                        if (!$stmt) {
                            throw new Exception("Error preparing statement: " . $conn->error);
                        }
                        
                        // Bind parameters
                        if ($_POST['action'] === 'add') {
                            // For mysqli, we need to bind each parameter individually
                            $stmt->bind_param("sssdddssi", 
                                $period_name, $start_time, $end_time, $base_price, 
                                $fixed_discount, $percentage_discount, $valid_from, $valid_to, $is_active
                            );
                            
                            if ($stmt->execute()) {
                                $success_message = "Buffet period added successfully!";
                            } else {
                                throw new Exception("Error executing statement: " . $stmt->error);
                            }
                        } else { // update
                            $period_id = $_POST['period_id'] ?? 0;
                            $stmt->bind_param("sssdddssii", 
                                $period_name, $start_time, $end_time, $base_price, 
                                $fixed_discount, $percentage_discount, $valid_from, $valid_to, 
                                $is_active, $period_id
                            );
                            
                            if ($stmt->execute()) {
                                $success_message = "Buffet period updated successfully!";
                            } else {
                                throw new Exception("Error executing statement: " . $stmt->error);
                            }
                        }
                    } catch (Exception $e) {
                        $error_message = "Database error: " . $e->getMessage();
                    }
                }
                break;
                
            case 'delete':
                $period_id = $_POST['period_id'] ?? 0;
                if ($period_id) {
                    try {
                        $stmt = $conn->prepare("DELETE FROM buffet_preferences WHERE period_id = ?");
                        if (!$stmt) {
                            throw new Exception("Error preparing statement: " . $conn->error);
                        }
                        
                        $stmt->bind_param("i", $period_id);
                        
                        if ($stmt->execute()) {
                            $success_message = "Buffet period deleted successfully!";
                        } else {
                            throw new Exception("Error executing statement: " . $stmt->error);
                        }
                    } catch (Exception $e) {
                        $error_message = "Database error: " . $e->getMessage();
                    }
                }
                break;
                
            case 'edit':
                $period_id = $_POST['period_id'] ?? 0;
                if ($period_id) {
                    try {
                        $stmt = $conn->prepare("SELECT * FROM buffet_preferences WHERE period_id = ?");
                        if (!$stmt) {
                            throw new Exception("Error preparing statement: " . $conn->error);
                        }
                        
                        $stmt->bind_param("i", $period_id);
                        
                        if ($stmt->execute()) {
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                $period = $result->fetch_assoc();
                                $editing = true;
                            }
                        } else {
                            throw new Exception("Error executing statement: " . $stmt->error);
                        }
                    } catch (Exception $e) {
                        $error_message = "Database error: " . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Fetch all buffet periods
try {
    $result = $conn->query("SELECT * FROM buffet_preferences ORDER BY 
                         CASE period_name 
                            WHEN 'Morning' THEN 1 
                            WHEN 'Noon' THEN 2 
                            WHEN 'Evening' THEN 3 
                         END");
    
    $periods = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $periods[] = $row;
        }
    }
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    $periods = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="sidebar.buffetSettings">Buffet Settings</title>
    <?php include_once './header.php'; ?>
    <style>
        .form-container {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: red;
        }
        .discount-info {
            font-size: 0.9em;
            color: #6c757d;
        }
        
        /* Time visualization styling */
        .time-visualization-container {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        
        #time-visualization {
            position: relative;
            height: 60px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 5px;
        }
        
        .time-period {
            position: absolute;
            height: 40px;
            top: 10px;
            border-radius: 4px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 0 8px;
        }
        
        .morning-period {
            background-color: #ffc107;
        }
        
        .noon-period {
            background-color: #fd7e14;
        }
        
        .evening-period {
            background-color: #6f42c1;
        }
        
        /* Price preview styling */
        .price-preview {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .preview-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .preview-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .preview-total {
            margin-top: 10px;
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 0 10px;
        }
        .time-label {
            position: absolute;
            font-size: 0.7rem;
            color: #495057;
            transform: translateX(-50%);
        }
        .price-preview {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .preview-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .preview-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .preview-total {
            font-weight: bold;
            border-top: 1px solid #ced4da;
            padding-top: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebarboss.php'; ?>

    <div class="container main-container">
        <div class="card">
            <div class="card-header">
                <h4 data-key="sidebar.buffetSettings">Buffet Settings</h4>
            </div>
            <div class="card-body">
                <!-- Success and Error messages will be shown via SweetAlert -->

                <!-- Buffet Period Form -->
                <div class="form-container">
                    <h5><?php echo $editing ? 'Edit' : 'Add New'; ?> Buffet Period</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'add'; ?>">
                        <?php if ($editing): ?>
                            <input type="hidden" name="period_id" value="<?php echo htmlspecialchars($period['period_id']); ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="period_name" class="form-label">Period Name</label>
                                <select class="form-select" id="period_name" name="period_name" required>
                                    <option value="Morning" <?php echo $period['period_name'] === 'Morning' ? 'selected' : ''; ?>>Morning</option>
                                    <option value="Noon" <?php echo $period['period_name'] === 'Noon' ? 'selected' : ''; ?>>Noon</option>
                                    <option value="Evening" <?php echo $period['period_name'] === 'Evening' ? 'selected' : ''; ?>>Evening</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo htmlspecialchars($period['start_time']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo htmlspecialchars($period['end_time']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="time-visualization-container">
                            <h6>Time Period Visualization</h6>
                            <div class="time-visualization" id="time-visualization">
                                <!-- Time periods will be visualized here via JavaScript -->
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>00:00</span>
                                <span>06:00</span>
                                <span>12:00</span>
                                <span>18:00</span>
                                <span>23:59</span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="base_price" class="form-label">Base Price (BIF)</label>
                                <input type="number" step="100" class="form-control" id="base_price" name="base_price" value="<?php echo htmlspecialchars($period['base_price']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="fixed_discount" class="form-label">Fixed Discount (BIF)</label>
                                <input type="number" step="100" class="form-control" id="fixed_discount" name="fixed_discount" value="<?php echo htmlspecialchars($period['fixed_discount'] ?? ''); ?>">
                                <small class="discount-info">Leave empty if not applicable</small>
                            </div>
                            <div class="col-md-4">
                                <label for="percentage_discount" class="form-label">Percentage Discount (%)</label>
                                <input type="number" step="1" max="100" class="form-control" id="percentage_discount" name="percentage_discount" value="<?php echo htmlspecialchars($period['percentage_discount'] ?? ''); ?>">
                                <small class="discount-info">Leave empty if not applicable</small>
                            </div>
                        </div>
                        
                        <!-- Price Preview Calculation -->
                        <div class="price-preview" id="price-preview">
                            <div class="preview-title">Price Preview</div>
                            <div class="preview-row">
                                <span>Base Price:</span>
                                <span id="preview-base">0 BIF</span>
                            </div>
                            <div class="preview-row" id="preview-fixed-row">
                                <span>Fixed Discount:</span>
                                <span id="preview-fixed" class="text-danger">0 BIF</span>
                            </div>
                            <div class="preview-row" id="preview-percentage-row">
                                <span>Percentage Discount:</span>
                                <span id="preview-percentage" class="text-danger">0 BIF</span>
                            </div>
                            <div class="preview-row preview-total">
                                <span>Final Price:</span>
                                <span id="preview-final">0 BIF</span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="valid_from" class="form-label">Valid From</label>
                                <input type="date" class="form-control" id="valid_from" name="valid_from" value="<?php echo htmlspecialchars($period['valid_from'] ?? ''); ?>">
                                <small class="discount-info">Leave empty for no start date</small>
                            </div>
                            <div class="col-md-4">
                                <label for="valid_to" class="form-label">Valid To</label>
                                <input type="date" class="form-control" id="valid_to" name="valid_to" value="<?php echo htmlspecialchars($period['valid_to'] ?? ''); ?>">
                                <small class="discount-info">Leave empty for no end date</small>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $period['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Add'; ?> Buffet Period</button>
                            <?php if ($editing): ?>
                                <a href="buffet_settings.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Buffet Periods Table -->
                <div class="table-responsive">
                    <h5>Existing Buffet Periods</h5>
                    <?php if (empty($periods)): ?>
                        <p>No buffet periods defined yet.</p>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Time Range</th>
                                    <th>Base Price</th>
                                    <th>Discounts</th>
                                    <th>Valid Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($periods as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['period_name']); ?></td>
                                        <td><?php echo htmlspecialchars($p['start_time']) . ' - ' . htmlspecialchars($p['end_time']); ?></td>
                                        <td><?php echo number_format($p['base_price'], 2) . ' BIF'; ?></td>
                                        <td>
                                            <?php if (!empty($p['fixed_discount'])): ?>
                                                <div>Fixed: <?php echo number_format($p['fixed_discount'], 2) . ' BIF'; ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($p['percentage_discount'])): ?>
                                                <div>Percentage: <?php echo $p['percentage_discount'] . '%'; ?></div>
                                            <?php endif; ?>
                                            <?php if (empty($p['fixed_discount']) && empty($p['percentage_discount'])): ?>
                                                None
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['valid_from']) || !empty($p['valid_to'])): ?>
                                                <?php echo !empty($p['valid_from']) ? htmlspecialchars($p['valid_from']) : 'Any'; ?>
                                                to
                                                <?php echo !empty($p['valid_to']) ? htmlspecialchars($p['valid_to']) : 'Any'; ?>
                                            <?php else: ?>
                                                Always
                                            <?php endif; ?>
                                        </td>
                                        <td class="<?php echo $p['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="period_id" value="<?php echo $p['period_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary">Edit</button>
                                            </form>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this period?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="period_id" value="<?php echo $p['period_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include_once './footer.php'; ?>
    
    <script>
        // Client-side validation and interactive functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Show SweetAlert messages for PHP success/error messages
            <?php if (!empty($success_message)): ?>
            Swal.fire({
                title: 'Success',
                text: '<?php echo addslashes($success_message); ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            Swal.fire({
                title: 'Error',
                text: '<?php echo addslashes($error_message); ?>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            <?php endif; ?>
            
            // Get form elements
            const form = document.querySelector('form');
            const periodNameSelect = document.getElementById('period_name');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            const basePriceInput = document.getElementById('base_price');
            const fixedDiscountInput = document.getElementById('fixed_discount');
            const percentageDiscountInput = document.getElementById('percentage_discount');
            
            // Price preview elements
            const previewBase = document.getElementById('preview-base');
            const previewFixed = document.getElementById('preview-fixed');
            const previewPercentage = document.getElementById('preview-percentage');
            const previewFinal = document.getElementById('preview-final');
            const previewFixedRow = document.getElementById('preview-fixed-row');
            const previewPercentageRow = document.getElementById('preview-percentage-row');
            
            // Time visualization element
            const timeVisualization = document.getElementById('time-visualization');
            
            // Function to convert time string to minutes
            function timeToMinutes(timeStr) {
                if (!timeStr) return 0;
                const [hours, minutes] = timeStr.split(':').map(Number);
                return hours * 60 + minutes;
            }
            
            // Function to calculate position and width percentages for time visualization
            function calculateTimePosition(startTime, endTime) {
                const totalMinutes = 24 * 60; // Total minutes in a day
                const startMinutes = timeToMinutes(startTime);
                const endMinutes = timeToMinutes(endTime);
                
                // Calculate percentages
                const startPercent = (startMinutes / totalMinutes) * 100;
                const endPercent = (endMinutes / totalMinutes) * 100;
                const widthPercent = endPercent - startPercent;
                
                return {
                    start: startPercent,
                    width: widthPercent > 0 ? widthPercent : 0
                };
            }
            
            // Function to update time visualization
            function updateTimeVisualization() {
                // Clear existing visualization
                timeVisualization.innerHTML = '';
                
                // Get current values
                const periodName = periodNameSelect.value;
                const startTime = startTimeInput.value;
                const endTime = endTimeInput.value;
                
                if (!startTime || !endTime) return;
                
                // Calculate position
                const position = calculateTimePosition(startTime, endTime);
                
                // Create time period element
                const timePeriod = document.createElement('div');
                timePeriod.className = `time-period ${periodName.toLowerCase()}-period`;
                timePeriod.style.left = `${position.start}%`;
                timePeriod.style.width = `${position.width}%`;
                timePeriod.textContent = `${periodName}: ${startTime} - ${endTime}`;
                
                // Add to visualization
                timeVisualization.appendChild(timePeriod);
            }
            
            // Function to update price preview
            function updatePricePreview() {
                // Get values
                const basePrice = parseFloat(basePriceInput.value) || 0;
                const fixedDiscount = parseFloat(fixedDiscountInput.value) || 0;
                const percentageDiscount = parseFloat(percentageDiscountInput.value) || 0;
                
                // Calculate discounts
                const percentageAmount = (basePrice * percentageDiscount) / 100;
                const totalDiscount = fixedDiscount + percentageAmount;
                const finalPrice = Math.max(0, basePrice - totalDiscount);
                
                // Update display
                previewBase.textContent = `${basePrice.toLocaleString()} BIF`;
                previewFixed.textContent = `${fixedDiscount.toLocaleString()} BIF`;
                previewPercentage.textContent = `${percentageAmount.toLocaleString()} BIF (${percentageDiscount}%)`;
                previewFinal.textContent = `${finalPrice.toLocaleString()} BIF`;
                
                // Show/hide discount rows based on values
                previewFixedRow.style.display = fixedDiscount > 0 ? 'flex' : 'none';
                previewPercentageRow.style.display = percentageDiscount > 0 ? 'flex' : 'none';
            }
            
            // Add event listeners for time inputs
            startTimeInput.addEventListener('change', updateTimeVisualization);
            endTimeInput.addEventListener('change', updateTimeVisualization);
            periodNameSelect.addEventListener('change', updateTimeVisualization);
            
            // Add event listeners for price inputs
            basePriceInput.addEventListener('input', updatePricePreview);
            fixedDiscountInput.addEventListener('input', updatePricePreview);
            percentageDiscountInput.addEventListener('input', updatePricePreview);
            
            // Initialize visualizations
            updateTimeVisualization();
            updatePricePreview();
            
            // Form validation
            form.addEventListener('submit', function(event) {
                const startTime = startTimeInput.value;
                const endTime = endTimeInput.value;
                
                if (startTime >= endTime) {
                    Swal.fire({
                        title: 'Time Error',
                        text: 'End time must be after start time',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    event.preventDefault();
                    return;
                }
                
                const fixedDiscount = parseFloat(fixedDiscountInput.value || 0);
                const percentageDiscount = parseFloat(percentageDiscountInput.value || 0);
                const basePrice = parseFloat(basePriceInput.value);
                
                if (fixedDiscount >= basePrice) {
                    Swal.fire({
                        title: 'Discount Error',
                        text: 'Fixed discount cannot be greater than or equal to the base price',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    event.preventDefault();
                    return;
                }
                
                if (percentageDiscount >= 100) {
                    Swal.fire({
                        title: 'Discount Error',
                        text: 'Percentage discount cannot be 100% or more',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    event.preventDefault();
                    return;
                }
                
                // Calculate final price after discounts
                const percentageAmount = (basePrice * percentageDiscount) / 100;
                const finalPrice = basePrice - fixedDiscount - percentageAmount;
                
                if (finalPrice <= 0) {
                    Swal.fire({
                        title: 'Pricing Error',
                        text: 'Total discounts cannot exceed the base price',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    event.preventDefault();
                    return;
                }
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
        });
    </script>
</body>
</html>

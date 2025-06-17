<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
    exit();
}

// Output headers
header('Content-Type: application/json');

try {
    // Check if time_of_day parameter is provided (for buffet reporting)
    if (isset($_POST['time_of_day']) && !empty($_POST['time_of_day'])) {
        $time_of_day = $_POST['time_of_day'];
        
        // Get price for specific time period
        $query = "SELECT period_name, base_price, fixed_discount, percentage_discount 
                FROM buffet_preferences 
                WHERE period_name = ? 
                AND is_active = 1
                LIMIT 1";
                
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
            exit();
        }
        
        $stmt->bind_param('s', $time_of_day);
    } else {
        // Use current time and date logic
        $current_time = date('H:i:s');
        $current_date = date('Y-m-d');
        
        // Query to get the buffet price for the current time
        // Find active buffet period where current time falls between start_time and end_time
        // and current date falls within valid_from and valid_to (if specified)
        $query = "SELECT period_name, base_price, fixed_discount, percentage_discount 
                FROM buffet_preferences 
                WHERE ? BETWEEN start_time AND end_time 
                AND (valid_from IS NULL OR ? >= valid_from)
                AND (valid_to IS NULL OR ? <= valid_to)
                AND is_active = 1
                LIMIT 1";
                
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
            exit();
        }
        
        $stmt->bind_param('sss', $current_time, $current_date, $current_date);
    }
    
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        exit();
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $period_name = $row['period_name'];
        $base_price = (float)$row['base_price'];
        $fixed_discount = (float)($row['fixed_discount'] ?? 0);
        $percentage_discount = (float)($row['percentage_discount'] ?? 0);
        
        // Calculate final price with discounts
        $price_after_fixed = $base_price - $fixed_discount;
        $final_price = $price_after_fixed - ($price_after_fixed * ($percentage_discount / 100));
        
        echo json_encode([
            'status' => 'success',
            'price' => $final_price,
            'base_price' => $base_price,
            'fixed_discount' => $fixed_discount,
            'percentage_discount' => $percentage_discount,
            'period_name' => $period_name
        ]);
    } else {
        // No applicable buffet period found
        // Determine default price based on time period or current hour
        $period_name = isset($_POST['time_of_day']) ? $_POST['time_of_day'] : '';
        
        if (empty($period_name)) {
            // Use current hour to determine period
            $hour = (int)date('H');
            
            if ($hour >= 6 && $hour < 10) {
                $period_name = 'Morning';
            } else if ($hour >= 11 && $hour < 15) {
                $period_name = 'Noon';
            } else {
                $period_name = 'Evening';
            }
        }
        
        // Set default prices for each period
        switch ($period_name) {
            case 'Morning':
                $base_price = 8000;
                break;
            case 'Noon':
                $base_price = 10000;
                break;
            case 'Evening':
                $base_price = 12000;
                break;
            default:
                $base_price = 10000; // Default fallback
                break;
        }
        
        echo json_encode([
            'status' => 'success',
            'price' => $base_price,
            'base_price' => $base_price,
            'fixed_discount' => 0,
            'percentage_discount' => 0,
            'period_name' => $period_name,
            'notice' => 'Using default price. No active buffet period configured for this time period.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

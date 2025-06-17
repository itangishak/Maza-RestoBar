<?php
require_once 'connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    die(json_encode(['success' => false, 'message' => 'User not logged in']));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    die(json_encode(['success' => false, 'message' => 'Invalid data received']));
}

// Validate required fields
if (empty($data['sale_date']) || empty($data['time_of_day']) || !isset($data['dishes_sold']) || !isset($data['buffet_price'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Start transaction
$conn->begin_transaction();

try {
    // Calculate total price for buffet dishes
    $buffetPrice = floatval($data['buffet_price']);
    $dishesSold = intval($data['dishes_sold']);
    $buffetSubtotal = $buffetPrice * $dishesSold;
    
    // Insert into buffet_sale_items table
    $stmt = $conn->prepare("INSERT INTO buffet_sale_items 
        (sale_date, time_of_day, price) 
        VALUES (?, ?, ?)");
    
    // Using PDO-style parameter binding since your connection seems to be PDO
    $stmt->execute([
        $data['sale_date'],
        $data['time_of_day'],
        $buffetPrice
    ]);
    
    // Get the buffet_item_id for reference in other tables
    $buffet_item_id = $conn->lastInsertId();
    
    // Process accompaniments if any
    if (!empty($data['accompaniment_id']) && is_array($data['accompaniment_id'])) {
        $stmt = $conn->prepare("INSERT INTO buffet_accompaniments 
            (buffet_item_id, accompaniment_name, accompaniment_price) 
            VALUES (?, ?, ?)");
            
        foreach ($data['accompaniment_id'] as $index => $accompaniment_id) {
            if (empty($accompaniment_id)) continue; // Skip empty selections
            
            $accompaniment_name = $data['accompaniment_name'][$index] ?? "Accompaniment $index";
            $accompaniment_price = floatval($data['accompaniment_price'][$index] ?? 0);
            
            $stmt->execute([
                $buffet_item_id,
                $accompaniment_name,
                $accompaniment_price
            ]);
        }
    }
    
    // Process discount if any
    if (!empty($data['discount_amount']) && floatval($data['discount_amount']) > 0) {
        $discountAmount = floatval($data['discount_amount']);
        $discountReason = $data['discount_reason'] ?? 'No reason provided';
        
        $stmt = $conn->prepare("INSERT INTO buffet_sale_adjustments 
            (buffet_item_id, adjustment_type, adjustment_amount, adjustment_reason) 
            VALUES (?, 'Discount', ?, ?)");
            
        $stmt->execute([
            $buffet_item_id,
            $discountAmount,
            $discountReason
        ]);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Calculate total price after discounts
    $accompanimentTotal = 0;
    if (!empty($data['accompaniment_price']) && is_array($data['accompaniment_price'])) {
        foreach ($data['accompaniment_price'] as $price) {
            $accompanimentTotal += floatval($price);
        }
    }
    
    $discountAmount = floatval($data['discount_amount'] ?? 0);
    $grandTotal = $buffetSubtotal + $accompanimentTotal - $discountAmount;
    
    // Return success response with buffet item ID and totals
    echo json_encode([
        'success' => true,
        'buffet_item_id' => $buffet_item_id,
        'subtotal' => $buffetSubtotal,
        'accompaniment_total' => $accompanimentTotal,
        'discount' => $discountAmount,
        'grand_total' => $grandTotal,
        'message' => 'Buffet sale recorded successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error recording buffet sale: ' . $e->getMessage()
    ]);
}

$conn->close(); 
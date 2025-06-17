<?php
session_start();
require_once 'connection.php';

// Set header to return JSON
header('Content-Type: application/json');

try {
    // First check if the menu table exists
    $checkTableSql = "SHOW TABLES LIKE 'menu'";
    $tableResult = $conn->query($checkTableSql);
    
    if (!$tableResult || $tableResult->num_rows === 0) {
        // Return empty results if table doesn't exist
        echo json_encode([
            'status' => 'success',
            'accompaniments' => [],
            'message' => 'No menu items available'
        ]);
        exit;
    }
    
    // Query to get menu items that can be used as accompaniments
    $sql = "SELECT menu_id, menu_name, menu_price 
           FROM menu 
           WHERE menu_status = 'available' 
           ORDER BY menu_name ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $accompaniments = [];
    
    // Process results
    while ($row = $result->fetch_assoc()) {
        $accompaniments[] = [
            'id' => $row['menu_id'],
            'name' => $row['menu_name'],
            'price' => (float)$row['menu_price']
        ];
    }
    
    // Return JSON response
    echo json_encode(['status' => 'success', 'accompaniments' => $accompaniments]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

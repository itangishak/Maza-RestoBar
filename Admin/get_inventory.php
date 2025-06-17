<?php
require_once 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inventory_id = (int)$_POST['inventory_id'] ?? 0;

    if ($inventory_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Inventory ID.']);
        exit();
    }

    // Fetch the inventory item details with unit name
    $stmt = $conn->prepare("
        SELECT 
            i.inventory_id, 
            i.item_name, 
            i.category, 
            u.unit_name AS unit,  -- Fetch actual unit name
            i.description, 
            i.reorder_level, 
            i.supplier_id,
            i.unit_cost  -- Added unit cost
        FROM 
            inventory_items i
        LEFT JOIN 
            units u ON i.unit = u.unit_id  -- Join with units table
        WHERE 
            i.inventory_id = ?
    ");
    $stmt->bind_param("i", $inventory_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

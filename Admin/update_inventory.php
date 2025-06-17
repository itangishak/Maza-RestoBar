<?php
require_once 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize inputs
    $inventory_id = isset($_POST['inventory_id']) ? (int) $_POST['inventory_id'] : 0;
    $item_name = isset($_POST['item_name']) ? trim(htmlspecialchars($_POST['item_name'])) : '';
    $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
    $unit_id = isset($_POST['unit_id']) ? (int) $_POST['unit_id'] : 0;
    $reorder_level = isset($_POST['reorder_level']) ? (float) $_POST['reorder_level'] : 0;
    $supplier_id = isset($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : 0;
    $unit_cost = isset($_POST['unit_cost']) ? (float) $_POST['unit_cost'] : 0.00;
    $description = isset($_POST['description']) ? trim(htmlspecialchars($_POST['description'])) : '';

    // Validate required fields
    if (
        $inventory_id <= 0 || empty($item_name) || 
        $category_id <= 0 || $unit_id <= 0 || 
        $supplier_id <= 0 || $unit_cost <= 0
    ) {
        echo json_encode(['status' => 'error', 'message' => 'All fields except description are required.']);
        exit();
    }

    // Update the inventory item, ensuring unit is properly updated
    $stmt = $conn->prepare("
        UPDATE inventory_items 
        SET item_name = ?, category = ?, unit = ?, reorder_level = ?, supplier_id = ?, unit_cost = ?, description = ?
        WHERE inventory_id = ?
    ");
    $stmt->bind_param("siididis", $item_name, $category_id, $unit_id, $reorder_level, $supplier_id, $unit_cost, $description, $inventory_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Inventory updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update inventory.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

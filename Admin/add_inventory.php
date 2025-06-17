<?php
require_once 'connection.php';

ob_clean(); // Clean the output buffer to remove any unwanted content
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize inputs
    $item_name = trim(htmlspecialchars($_POST['item_name'] ?? ''));
    $category_id = (int)$_POST['category_id'] ?? 0;
    $unit_id = (int)$_POST['unit_id'] ?? 0;
    $quantity = (float)$_POST['quantity'] ?? 0;
    $reorder_level = (float)$_POST['reorder_level'] ?? 0;
    $supplier_id = (int)$_POST['supplier_id'] ?? 0;
    $unit_cost = isset($_POST['unit_cost']) ? (float) $_POST['unit_cost'] : 0.00;

    // Validate required fields
    if (empty($item_name) || $category_id <= 0 || $unit_id <= 0 || $supplier_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Check for duplicate item name (case-insensitive)
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM inventory_items WHERE LOWER(item_name) = LOWER(?)");
    if (!$check_stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare duplicate check statement.']);
        exit();
    }
    $check_stmt->bind_param("s", $item_name);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Item with the same name already exists.']);
        exit();
    }

    // Insert data into the database
    $insert_stmt = $conn->prepare("
        INSERT INTO inventory_items (item_name, category, unit, quantity_in_stock, reorder_level, supplier_id, created_at,unit_cost) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(),?)
    ");
    if (!$insert_stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare insertion statement.']);
        exit();
    }

    $insert_stmt->bind_param("siiidid", $item_name, $category_id, $unit_id, $quantity, $reorder_level, $supplier_id,$unit_cost );

    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Inventory added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add inventory.']);
    }

    $insert_stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

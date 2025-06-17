<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit();
}

// Get POST data
$purchase_id = isset($_POST['purchase_id']) ? (int)$_POST['purchase_id'] : 0;
$inventory_id = isset($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : 0;
$quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 0;
$unit_price = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;

// Validate input
if (!$purchase_id || !$inventory_id || $quantity <= 0 || $unit_price <= 0) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
  exit();
}

// Check if item already exists in order
$checkQuery = "SELECT po_item_id FROM purchase_order_items 
               WHERE purchase_order_id = ? AND inventory_id = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("ii", $purchase_id, $inventory_id);
$checkStmt->execute();
$exists = $checkStmt->get_result()->fetch_assoc();

if ($exists) {
  // Update existing item
  $query = "UPDATE purchase_order_items SET 
            quantity = ?, 
            unit_price = ?
            WHERE po_item_id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ddi", $quantity, $unit_price, $exists['po_item_id']);
} else {
  // Add new item
  $query = "INSERT INTO purchase_order_items 
            (purchase_order_id, inventory_id, quantity, unit_price)
            VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("iidd", $purchase_id, $inventory_id, $quantity, $unit_price);
}

if ($stmt->execute()) {
  echo json_encode(['status' => 'success', 'message' => 'Item updated']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Failed to update item']);
}
?>

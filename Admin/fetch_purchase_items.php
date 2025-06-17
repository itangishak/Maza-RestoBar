<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

// Get purchase order ID
$purchase_id = isset($_GET['purchase_id']) ? (int)$_GET['purchase_id'] : 0;
if (!$purchase_id) {
  echo json_encode(['error' => 'Invalid purchase ID']);
  exit();
}

// Fetch order items
$query = "SELECT 
            i.po_item_id,
            i.inventory_id,
            inv.item_name,
            i.quantity,
            i.unit_price,
            (i.quantity * i.unit_price) AS total
          FROM purchase_order_items i
          JOIN inventory_items inv ON i.inventory_id = inv.inventory_id
          WHERE i.purchase_order_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
  echo json_encode(['error' => 'Database error: ' . $conn->error]);
  exit();
}

$stmt->bind_param("i", $purchase_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($items);
?>

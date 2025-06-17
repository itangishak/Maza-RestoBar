<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  header('HTTP/1.1 401 Unauthorized');
  exit();
}

// Get purchase order ID
$purchase_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$purchase_id) {
  header('HTTP/1.1 400 Bad Request');
  exit();
}

// Fetch order details
$query = "SELECT 
            p.purchase_id, 
            p.po_number, 
            p.order_date, 
            p.payment_method, 
            p.created_by, 
            p.total
          FROM purchase_records p
          WHERE p.purchase_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
  header('HTTP/1.1 500 Internal Server Error');
  exit(json_encode(['error' => 'Database error: ' . $conn->error]));
}

$stmt->bind_param("i", $purchase_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
  header('HTTP/1.1 404 Not Found');
  exit();
}

// Fetch order items
$itemsQuery = "SELECT 
                i.po_item_id,
                i.inventory_id,
                inv.item_name,
                i.quantity,
                i.unit_price
              FROM purchase_order_items i
              JOIN inventory_items inv ON i.inventory_id = inv.inventory_id
              WHERE i.purchase_order_id = ?";
$itemsStmt = $conn->prepare($itemsQuery);
$itemsStmt->bind_param("i", $purchase_id);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
$order['items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($order);
?>

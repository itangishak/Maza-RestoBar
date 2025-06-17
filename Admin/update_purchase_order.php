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
$po_number = isset($_POST['po_number']) ? trim($_POST['po_number']) : '';
$order_date = isset($_POST['order_date']) ? trim($_POST['order_date']) : '';
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
$total = isset($_POST['total']) ? (float)$_POST['total'] : 0;

// Validate input
if (!$purchase_id || !$po_number || !$order_date || !$payment_method || $total <= 0) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
  exit();
}

// Update order
$query = "UPDATE purchase_records SET 
          po_number = ?, 
          order_date = ?, 
          payment_method = ?, 
          total = ?, 
          order_date = NOW() 
          WHERE purchase_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
  echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
  exit();
}

$stmt->bind_param("sssdi", $po_number, $order_date, $payment_method, $total, $purchase_id);

if ($stmt->execute()) {
  echo json_encode([
    'status' => 'success', 
    'message' => 'Purchase order updated successfully',
    'purchase_id' => $purchase_id,
    'po_number' => $po_number,
    'total' => number_format($total, 2, '.', '')
  ]);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Failed to update order']);
}
?>

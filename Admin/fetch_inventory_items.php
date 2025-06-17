<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

// Fetch inventory items
$query = "SELECT 
            inventory_id, 
            item_name, 
            category,
            unit,
            unit_cost
          FROM inventory_items
          ORDER BY item_name ASC";
$result = $conn->query($query);

if (!$result) {
  echo json_encode(['error' => 'Database error: ' . $conn->error]);
  exit();
}

$items = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($items);
?>

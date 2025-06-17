<?php
// fetch_menu_items_order.php
require_once 'connection.php';

// Example: SELECT menu_id, name, price from your table
$query = "SELECT menu_id, name, price FROM menu_items";
$result = $conn->query($query);

$menuItems = array();
while ($row = $result->fetch_assoc()) {
    // Make sure $row['price'] is numeric
    // If it's stored in your DB as DECIMAL or FLOAT, this should be fine
    // but it's good practice to cast it:
    $row['price'] = (float) $row['price'];
    $menuItems[] = $row;
}

header('Content-Type: application/json');
echo json_encode($menuItems);
?>

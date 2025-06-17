<?php
require_once 'connection.php';

header('Content-Type: application/json; charset=utf-8');
$conn->set_charset('utf8mb4');

if (!isset($_GET['purchase_id'])) {
    echo json_encode(['error' => 'Missing purchase_id parameter']);
    exit;
}

$purchase_id = $conn->real_escape_string($_GET['purchase_id']);

$sql = "
    SELECT 
        poi.po_item_id,
        poi.inventory_id,
        ii.item_name,
        ii.category,
        poi.quantity,
        poi.unit_price,
        (poi.quantity * poi.unit_price) AS total
    FROM purchase_order_items poi
    JOIN inventory_items ii ON poi.inventory_id = ii.inventory_id
    WHERE poi.purchase_order_id = '$purchase_id'
";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
$conn->close();

<?php
require_once 'connection.php';

$sql = "SELECT m.menu_id, m.name, m.price, mc.name AS category_name 
        FROM menu_items m
        LEFT JOIN menu_categories mc ON m.category_id = mc.category_id
        WHERE m.availability = 'available'";

$result = $conn->query($sql);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
header('Content-Type: application/json');
echo json_encode($data);
?>
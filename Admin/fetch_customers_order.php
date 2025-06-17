<?php
require_once 'connection.php';

header('Content-Type: application/json');

$query = "SELECT customer_id, first_name, last_name FROM customers";
$result = $conn->query($query);

$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

echo json_encode($customers);
?>

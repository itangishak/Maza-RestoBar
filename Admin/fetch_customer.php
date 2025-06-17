<?php
require_once 'connection.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

$query = "
    SELECT 
      customer_id,
      CONCAT(first_name, ' ', last_name) AS full_name
    FROM customers
    ORDER BY customer_id DESC
";
$result = $conn->query($query);

$customers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $result->free();
    echo json_encode(['status' => 'success', 'data' => $customers]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $conn->error]);
}

$conn->close();

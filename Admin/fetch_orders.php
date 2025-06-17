<?php
require_once 'connection.php';

// Modified query to join "orders" and "customers" 
// and only display orders with a pending status:
$sql = "
    SELECT 
        o.order_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        o.total_price,
        o.order_date,
        o.status
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.status = 'pending'
    ORDER BY o.order_id DESC
";

$result = $conn->query($sql);
$data = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    // If the query fails, you can set an error response
    // or just return an empty array.
}

// Output as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>

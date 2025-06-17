<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the 'orders' table exists
$checkOrdersTable = $conn->query("SHOW TABLES LIKE 'orders'");
if ($checkOrdersTable->num_rows == 0) {
    echo json_encode(["error" => "Orders table not found."]);
    exit;
}

// Fetch customers and aggregate order data
$query = "
    SELECT 
        c.customer_id,
        c.first_name,
        c.last_name,
        COALESCE(c.email, 'N/A') AS email, 
        COALESCE(c.phone, 'N/A') AS phone, 
        COALESCE(c.address, 'N/A') AS address,
        DATE_FORMAT(c.created_at, '%Y/%m/%d') AS joined_date,
        COUNT(o.order_id) AS total_orders,
        COALESCE(SUM(o.total_price), 0) AS total_revenue
    FROM customers c
    LEFT JOIN orders o ON c.customer_id = o.customer_id
    GROUP BY c.customer_id
    ORDER BY c.created_at DESC
";

$result = $conn->query($query);

if (!$result) {
    error_log("Database query failed: " . $conn->error);
    echo json_encode(["error" => "Database query failed: " . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "customer_name" => htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8'),
        "email" => htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'),
        "phone" => htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'),
        "address" => htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'),
        "joined_date" => $row['joined_date'],
        "total_orders" => (int)$row['total_orders'],
        "total_revenue" => "BIF" . number_format($row['total_revenue'], 2),
        "actions" => '<button class="btn btn-primary btn-sm edit-btn" data-id="' . (int)$row['customer_id'] . '"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-danger btn-sm delete-btn" data-id="' . (int)$row['customer_id'] . '"><i class="bi bi-trash"></i></button>'
    ];
}

// Encode JSON safely
echo json_encode(["data" => $data], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>

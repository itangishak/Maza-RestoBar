<?php
require_once 'connection.php';

header('Content-Type: application/json; charset=utf-8');
$conn->set_charset('utf8mb4');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Missing id parameter']);
    exit;
}

$purchase_id = $conn->real_escape_string($_GET['id']);

$sql = "
    SELECT 
        purchase_id,
        po_number,
        DATE_FORMAT(order_date, '%Y-%m-%d') AS order_date,
        payment_method,
        created_by,
        total
    FROM purchase_records
    WHERE purchase_id = '$purchase_id'
";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Purchase order not found']);
    exit;
}

echo json_encode($result->fetch_assoc(), JSON_UNESCAPED_UNICODE);
$conn->close();

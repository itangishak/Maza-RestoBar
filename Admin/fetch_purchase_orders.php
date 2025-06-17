<?php
require_once 'connection.php';

// Tell the browser it’s JSON and force the right charset
header('Content-Type: application/json; charset=utf-8');
$conn->set_charset('utf8mb4');

// Run your query
$sql = "
    SELECT
        pr.purchase_id,
        pr.po_number,
        DATE_FORMAT(pr.order_date, '%Y-%m-%d')     AS order_date,
        pr.payment_method,
        pr.created_by,
        pr.total
    FROM purchase_records pr
    ORDER BY pr.order_date DESC
";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
$conn->close();

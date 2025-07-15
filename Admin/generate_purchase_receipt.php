<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json; charset=utf-8');

// Ensure user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$purchase_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($purchase_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid purchase ID']);
    exit();
}

// Fetch purchase order details
$stmt = $conn->prepare("SELECT po_number, order_date, payment_method, created_by, total FROM purchase_records WHERE purchase_id = ?");
$stmt->bind_param('i', $purchase_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Purchase order not found']);
    exit();
}

// Fetch order items
$stmt = $conn->prepare("SELECT ii.item_name, poi.quantity, poi.unit_price, (poi.quantity * poi.unit_price) AS total FROM purchase_order_items poi JOIN inventory_items ii ON poi.inventory_id = ii.inventory_id WHERE poi.purchase_order_id = ?");
$stmt->bind_param('i', $purchase_id);
$stmt->execute();
$result = $stmt->get_result();

$itemsHtml = '';
while ($row = $result->fetch_assoc()) {
    $itemsHtml .= "<tr>"
        . "<td>" . htmlspecialchars($row['item_name']) . "</td>"
        . "<td>" . $row['quantity'] . "</td>"
        . "<td>" . number_format($row['unit_price'], 2) . "</td>"
        . "<td>" . number_format($row['total'], 2) . "</td>"
        . "</tr>";
}
$stmt->close();

$receiptContent = '
    <img id="receiptLogo" alt="Logo" style="width:100px; margin-bottom:0px;" />
    <hr>
    <h2>Maza Resto-Bar</h2>
    <p>Address: Boulevard Melchior Ndadaye, Peace Corner, Bujumbura</p>
    <p>Phone: +257 69 80 58 98 | Email: barmazaresto@gmail.com</p>
    <hr>
    <p>Purchase Order #: ' . htmlspecialchars($order['po_number']) . '</p>
    <p>Date: ' . date('Y-m-d', strtotime($order['order_date'])) . '</p>
    <p>Payment: ' . htmlspecialchars($order['payment_method']) . '</p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>' . $itemsHtml . '</tbody>
    </table>
    <p class="text-end">Total: ' . number_format($order['total'], 2) . ' BIF</p>
    <hr>
    <p>Updated by: ' . htmlspecialchars($order['created_by']) . '</p>
    <p>Thank you!</p>';

echo json_encode([
    'status' => 'success',
    'receiptContent' => $receiptContent
]);
?>

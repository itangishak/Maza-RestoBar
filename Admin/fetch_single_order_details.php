<?php
require_once 'connection.php';

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo "No Order ID provided.";
    exit;
}

// Query the order details
$sql = "
    SELECT 
       od.menu_id,
       m.name AS menu_name,
       od.quantity,
       od.price
    FROM order_details od
    LEFT JOIN menu_items m ON od.menu_id = m.menu_id
    WHERE od.order_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result->num_rows) {
    echo "No details found for order #$order_id.";
    exit;
}

// Build some HTML
$html = "<h5>Order #$order_id Details</h5><table class='table table-bordered'>";
$html .= "<thead><tr><th>Menu Item</th><th>Quantity</th><th>Price</th></tr></thead><tbody>";

while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
        <td>{$row['menu_name']}</td>
        <td>{$row['quantity']}</td>
        <td>{$row['price']}</td>
    </tr>";
}
$html .= "</tbody></table>";

echo $html;

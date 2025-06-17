<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Initialize response
$response = [
    'total' => 0,
    'pendingOrders' => [],
    'lowStockItems' => []
];

try {
    // Fetch Pending Orders
    $pendingOrders = [];
    $sqlOrders = "
        SELECT o.order_id, CONCAT(c.first_name, ' ', c.last_name) AS customer_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.customer_id
        WHERE o.status = 'pending'
        ORDER BY o.order_date DESC
        LIMIT 5"; // Limit to 5 for performance
    $resultOrders = $conn->query($sqlOrders);
    if ($resultOrders) {
        while ($row = $resultOrders->fetch_assoc()) {
            $pendingOrders[] = [
                'order_id' => $row['order_id'],
                'customer_name' => $row['customer_name'] ?? 'Unknown Customer' // Fallback if customer not found
            ];
        }
        $response['pendingOrders'] = $pendingOrders;
    } else {
        throw new Exception("Error fetching pending orders: " . $conn->error);
    }

    // Fetch Low Stock Items
    $lowStockItems = [];
    $sqlStock = "
        SELECT item_name, quantity_in_stock
        FROM inventory_items
        WHERE quantity_in_stock <= reorder_level
        ORDER BY quantity_in_stock ASC
        LIMIT 5"; // Limit to 5 for performance
    $resultStock = $conn->query($sqlStock);
    if ($resultStock) {
        while ($row = $resultStock->fetch_assoc()) {
            $lowStockItems[] = [
                'item_name' => $row['item_name'],
                'quantity_in_stock' => number_format($row['quantity_in_stock'], 2)
            ];
        }
        $response['lowStockItems'] = $lowStockItems;
    } else {
        throw new Exception("Error fetching low stock items: " . $conn->error);
    }

    // Calculate total notifications
    $response['total'] = count($pendingOrders) + count($lowStockItems);

} catch (Exception $e) {
    // Return error response
    $response = [
        'total' => 0,
        'pendingOrders' => [],
        'lowStockItems' => [],
        'error' => $e->getMessage()
    ];
}

// Output JSON
echo json_encode($response);
$conn->close();
?>
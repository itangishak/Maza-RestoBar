<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Query to fetch inventory data (join with categories, units, suppliers)
$query = "
    SELECT 
        i.inventory_id,
        i.item_name,
        c.category_name AS category,
        u.unit_name AS unit,
        i.description,
        i.quantity_in_stock,
        i.unit_cost,
        i.reorder_level,
        s.supplier_name,
        DATE_FORMAT(i.created_at, '%Y-%m-%d') AS created_date
    FROM inventory_items i
    LEFT JOIN categories c ON i.category = c.category_id
    LEFT JOIN units u ON i.unit = u.unit_id
    LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
    ORDER BY i.created_at DESC
";

$result = $conn->query($query);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            // IMPORTANT: Return inventory_id explicitly
            'inventory_id'      => $row['inventory_id'],

            'item_name'         => htmlspecialchars($row['item_name']),
            'category'          => htmlspecialchars($row['category'] ?: 'N/A'),
            'unit'              => htmlspecialchars($row['unit'] ?: 'N/A'),
            'description'       => htmlspecialchars($row['description'] ?: ''),
            'quantity_in_stock' => $row['quantity_in_stock'],
            'unit_cost'         => number_format($row['unit_cost'], 2), // format as currency
            'reorder_level'     => $row['reorder_level'],
            'supplier_name'     => htmlspecialchars($row['supplier_name'] ?: 'N/A'),
            'created_date'      => $row['created_date'],
        ];
    }
    echo json_encode(['data' => $data]);
} else {
    // If query fails, return an error in JSON
    echo json_encode([
        'data'  => [],
        'error' => $conn->error
    ]);
}

$conn->close();

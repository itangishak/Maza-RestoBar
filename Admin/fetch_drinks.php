<?php
// fetch_drinks.php
require_once 'connection.php';

// We assume:
//  - `inventory_items.category` is an INT referencing categories.category_id
//  - categories.category_name = 'Drink' for drink items

$sql = "
    SELECT 
        i.inventory_id,
        i.item_name,
        i.quantity_in_stock,
        REPLACE(ROUND(i.unit_cost, 2), ',', '.') AS price,
        i.unit,
        c.category_name
    FROM inventory_items i
    JOIN categories c 
       ON i.category = c.category_id
    WHERE c.category_name = 'Drink'
";

$data = [];
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>

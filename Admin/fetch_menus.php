<?php
require_once 'connection.php'; // Adjust path if needed

header('Content-Type: application/json');

$query = "
    SELECT 
        m.menu_id, 
        m.name, 
        c.name AS category_name, 
        m.price, 
        m.availability 
    FROM menu_items m
    LEFT JOIN menu_categories c ON m.category_id = c.category_id
";

$result = $conn->query($query);

if ($result) {
    $menus = [];
    while ($row = $result->fetch_assoc()) {
        $menus[] = $row;
    }
    echo json_encode(['data' => $menus]);
} else {
    echo json_encode(['data' => [], 'error' => $conn->error]);
}
$conn->close();

<?php
require_once 'connection.php';
header('Content-Type: application/json');

$query = "SELECT category_id, name, description, created_at FROM menu_categories";
$result = $conn->query($query);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch data: ' . $conn->error]);
    exit;
}

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = [
        'category_id' => $row['category_id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'created_at' => $row['created_at'],
        'actions' => '
            <button class="btn btn-sm btn-primary editCategoryBtn" data-id="' . $row['category_id'] . '">Edit</button>
            <button class="btn btn-sm btn-danger deleteCategoryBtn" data-id="' . $row['category_id'] . '">Delete</button>
        ',
    ];
}

echo json_encode([
    'data' => $categories
]);

$conn->close();
?>

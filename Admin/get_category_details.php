<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['category_id']) || empty($_GET['category_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid category ID.'
        ]);
        exit;
    }

    $category_id = intval($_GET['category_id']);

    // Fetch category details
    $query = "SELECT category_id, name, description FROM menu_categories WHERE category_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Category not found.'
        ]);
        exit;
    }

    $category = $result->fetch_assoc();

    echo json_encode([
        'status' => 'success',
        'data' => $category
    ]);
    exit;
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}
$conn->close();

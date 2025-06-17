<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if category_id is provided
    if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid category ID'
        ]);
        exit;
    }

    // Sanitize the category_id
    $category_id = intval($_POST['category_id']);

    // Prepare and execute the delete query
    $query = "DELETE FROM menu_categories WHERE category_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to prepare the query: ' . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param('i', $category_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Category deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No category found with the provided ID.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete the category: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method. Please use POST.'
    ]);
}

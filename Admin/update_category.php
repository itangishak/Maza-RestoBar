<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    if (
        !isset($_POST['category_id']) || empty($_POST['category_id']) ||
        !isset($_POST['name']) || empty(trim($_POST['name']))
    ) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Category ID and Name are required.'
        ]);
        exit;
    }

    $category_id = intval($_POST['category_id']);
    $name = trim($_POST['name']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    try {
        // Prepare the SQL query
        $query = "UPDATE menu_categories SET name = ?, description = ?, created_at = CURRENT_TIMESTAMP WHERE category_id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception('Failed to prepare the statement: ' . $conn->error);
        }

        $stmt->bind_param("ssi", $name, $description, $category_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Category updated successfully.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'No changes were made.'
                ]);
            }
        } else {
            throw new Exception('Failed to execute the query: ' . $stmt->error);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>

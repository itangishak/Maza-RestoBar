<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (!isset($_POST['name']) || empty(trim($_POST['name']))) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Category name is required.'
        ]);
        exit;
    }

    $name = trim($_POST['name']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    // Check if the category already exists
    $checkQuery = "SELECT * FROM menu_categories WHERE name = ?";
    $stmtCheck = $conn->prepare($checkQuery);
    $stmtCheck->bind_param("s", $name);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Category with this name already exists.'
        ]);
        $stmtCheck->close();
        exit;
    }

    // Insert the new category
    $insertQuery = "INSERT INTO menu_categories (name, description) VALUES (?, ?)";
    $stmtInsert = $conn->prepare($insertQuery);
    $stmtInsert->bind_param("ss", $name, $description);

    if ($stmtInsert->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Category added successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add category. Please try again.'
        ]);
    }

    $stmtInsert->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
$conn->close();

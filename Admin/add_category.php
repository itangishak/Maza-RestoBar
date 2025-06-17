<?php
require_once 'connection.php';

ob_clean(); // Clean output buffer to ensure only JSON is returned
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim(htmlspecialchars($_POST['category_name'] ?? ''));

    if (empty($category_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
        exit();
    }

    // Check for duplicate category
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(category_name) = LOWER(?)");
    $check_stmt->bind_param("s", $category_name);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Category already exists.']);
        exit();
    }

    // Insert new category
    $insert_stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $insert_stmt->bind_param("s", $category_name);

    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Category added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add category.']);
    }

    $insert_stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

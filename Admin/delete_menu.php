<?php
require_once 'connection.php'; // Adjust path if needed
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_id = intval($_POST['id']);

    if (!$menu_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid menu ID.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM menu_items WHERE menu_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $menu_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Menu item deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete menu item.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare delete statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
$conn->close();

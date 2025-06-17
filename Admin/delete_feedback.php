<?php
require_once 'connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback_id = $_POST['feedback_id'] ?? null;

    if (!$feedback_id) {
        echo json_encode(["status" => "error", "message" => "Invalid feedback ID."]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM feedback WHERE feedback_id = ?");
    $stmt->bind_param("i", $feedback_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Feedback deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete feedback."]);
    }

    $stmt->close();
}
?>

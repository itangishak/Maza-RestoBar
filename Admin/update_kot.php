<?php
require_once 'connection.php';

header('Content-Type: application/json');

$kot_id = $_POST['kot_id'] ?? null;
$new_status = $_POST['kot_status'] ?? null;

if (!$kot_id || !$new_status) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
    exit;
}

$sql = "UPDATE kitchen_orders SET kot_status = ? WHERE kot_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_status, $kot_id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'KOT status updated successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update KOT status.']);
}
?>

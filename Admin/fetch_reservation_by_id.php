<?php
require_once 'connection.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

// Prepare statement
$stmt = $conn->prepare("
    SELECT reservation_id, customer_name, email, phone, 
           reservation_date, reservation_time, guests, status
    FROM reservations
    WHERE reservation_id = ?
");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Reservation not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $conn->error]);
}

$stmt->close();
$conn->close();

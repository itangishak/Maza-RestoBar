<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
        exit;
    }

    $customer_name     = $_POST['customer_name'] ?? '';
    $email            = $_POST['email'] ?? '';
    $phone            = $_POST['phone'] ?? '';
    $reservation_date = $_POST['reservation_date'] ?? '';
    $reservation_time = $_POST['reservation_time'] ?? '';
    $guests           = $_POST['guests'] ?? 1;
    $status           = $_POST['status'] ?? 'Pending';

    $stmt = $conn->prepare("
        INSERT INTO reservations 
            (customer_name, email, phone, reservation_date, reservation_time, guests, status)
        VALUES
            (?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    // s, s, s, s, s, i, s
    $stmt->bind_param("sssssis", 
        $customer_name, 
        $email, 
        $phone, 
        $reservation_date, 
        $reservation_time, 
        $guests,
        $status
    );

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Reservation added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Execution failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

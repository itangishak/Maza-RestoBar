<?php
// fetch_reservations.php
require_once 'connection.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode([
        'data'  => [],
        'error' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

// Simple SELECT from the table
$sql = "SELECT reservation_id, customer_name, email, phone, 
               reservation_date, reservation_time, guests, status
        FROM reservations
        ORDER BY reservation_id DESC";

$result = $conn->query($sql);
$reservations = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    $result->free();
} else {
    echo json_encode([
        'data'  => [],
        'error' => 'SQL error: ' . $conn->error
    ]);
    exit;
}

// Return in DataTables format: { "data": [...] }
echo json_encode([ 'data' => $reservations ]);
$conn->close();

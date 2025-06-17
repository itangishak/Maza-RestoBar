<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sale_date = isset($_POST['sale_date']) ? $_POST['sale_date'] : '';
    $time_of_day = isset($_POST['time_of_day']) ? $_POST['time_of_day'] : '';

    if (empty($sale_date) || empty($time_of_day)) {
        echo json_encode(['status' => 'error', 'message' => 'Date and Time of Day are required.']);
        exit;
    }

    // Insert into buffet_sale_items table
    $stmt = $conn->prepare("INSERT INTO buffet_sale_items (sale_date, time_of_day) VALUES (?, ?)");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('ss', $sale_date, $time_of_day);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data inserted into database successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

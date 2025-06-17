<?php
require_once 'connection.php'; // Include your database connection file

header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// Read the raw input
$rawInput = file_get_contents('php://input');
// Decode the JSON input into an associative array
$data = json_decode($rawInput, true);

// Extract variables from the decoded JSON
$po_number = $data['po_number'] ?? null;
$order_date = $data['order_date'] ?? null;
$payment_method = $data['payment_method'] ?? null;
$created_by = $data['created_by'] ?? null;
$total = $data['total'] ?? null;

// Validate required fields
if (!$po_number || !$order_date || !$payment_method || !$created_by || $total === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit();
}

// Prepare and execute the SQL statement
$stmt = $conn->prepare("
    INSERT INTO purchase_records (po_number, order_date, payment_method, created_by, total)
    VALUES (?, ?, ?, ?, ?)
");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
    exit();
}

// Bind parameters: 
// po_number (string), order_date (string), payment_method (string), created_by (string), total (double)
$total = (float)$total; // ensure it's a numeric type
$stmt->bind_param("ssssd", $po_number, $order_date, $payment_method, $created_by, $total);

// Execute the statement
if ($stmt->execute()) {
    // Success: return the inserted ID
    $insert_id = $stmt->insert_id;
    echo json_encode([
        'status' => 'success',
        'message' => 'Purchase record saved successfully.',
        'purchase_id' => $insert_id
    ]);
} else {
    // On failure, respond with an error
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save the purchase record: ' . $stmt->error
    ]);
}

// Close statement and connection
$stmt->close();
$conn->close();

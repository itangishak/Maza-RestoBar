<?php
session_start();
require_once 'connection.php';

// Set the proper content type header
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Clear any existing output if output buffering is active
if (ob_get_length()) {
    ob_clean();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';

    if (empty($customer_id)) {
        echo json_encode(['status' => 'error', 'message' => 'No customer ID provided.']);
        exit;
    }

    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $customer_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Customer deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete customer.']);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

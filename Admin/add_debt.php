<?php
session_start(); // Start session to get user ID
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
        exit;
    }

    // Check if user is logged in
    if (!isset($_SESSION['UserId'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
        exit;
    }
    
    // Remove boss-only permission check - all authenticated users can add debts
    
    $customer_id = $_POST['customer_id'] ?? null;
    $amount      = $_POST['amount'] ?? 0;
    $due_date    = $_POST['due_date'] ?? null;
    $status      = $_POST['status'] ?? 'pending'; // Default to pending with lowercase
    $notes       = $_POST['notes'] ?? '';
    $created_by  = $_SESSION['UserId']; // Set created_by from session

    // Validate status to ensure it matches the ENUM values
    $validStatuses = ['pending', 'partial', 'paid', 'overdue'];
    if (!in_array($status, $validStatuses)) {
        $status = 'pending'; // Default to pending if invalid status
    }

    // Prepare
    $stmt = $conn->prepare("
        INSERT INTO debts 
            (customer_id, amount, due_date, status, notes, created_by)
        VALUES 
            (?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    // Match the parameter types:
    //   customer_id (int), amount (double), due_date (string), status (string), notes (string), created_by (int)
    $stmt->bind_param("idsssi", $customer_id, $amount, $due_date, $status, $notes, $created_by);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Debt added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Execution failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
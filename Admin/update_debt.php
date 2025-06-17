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

    $debt_id     = $_POST['debt_id'] ?? 0;
    $customer_id = $_POST['customer_id'] ?? null;
    $amount      = $_POST['amount'] ?? 0;
    $due_date    = $_POST['due_date'] ?? null;
    $status      = $_POST['status'] ?? 'pending'; // Default to pending with lowercase
    $notes       = $_POST['notes'] ?? '';
    
    // Check if the user has 'Boss' role
    $isBoss = false;
    if (
        (isset($_SESSION['Privilege']) && (strtolower($_SESSION['Privilege']) === 'boss' || $_SESSION['Privilege'] === 'Boss')) || 
        (isset($_SESSION['privilege']) && (strtolower($_SESSION['privilege']) === 'boss' || $_SESSION['privilege'] === 'Boss'))
    ) {
        $isBoss = true;
    }
    
    // For non-boss users, get the original customer_id and amount values
    // to prevent unauthorized changes
    if (!$isBoss) {
        $checkStmt = $conn->prepare("SELECT customer_id, amount FROM debts WHERE debt_id = ?");
        $checkStmt->bind_param("i", $debt_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $customer_id = $row['customer_id']; // Keep original customer_id
            $amount = $row['amount']; // Keep original amount
        }
        
        $checkStmt->close();
    }
    
    // Debug line to check privileges and session data
    error_log("Debt Update - Full Session Data: " . print_r($_SESSION, true));
    error_log("Debt Update - User ID: {$_SESSION['UserId']}, Privilege: " . 
        ($_SESSION['Privilege'] ?? $_SESSION['privilege'] ?? 'none') . 
        ", isBoss: " . ($isBoss ? 'true' : 'false'));

    // Validate status to ensure it matches the ENUM values
    $validStatuses = ['pending', 'partial', 'paid', 'overdue'];
    if (!in_array($status, $validStatuses)) {
        $status = 'pending'; // Default to pending if invalid status
    }

    $stmt = $conn->prepare("
        UPDATE debts
        SET 
          customer_id = ?, 
          amount      = ?, 
          due_date    = ?, 
          status      = ?, 
          notes       = ?
        WHERE debt_id = ?
    ");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    // (int, double, string, string, string, int)
    $stmt->bind_param("idsssi", 
        $customer_id, 
        $amount, 
        $due_date, 
        $status, 
        $notes, 
        $debt_id
    );

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Debt updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Execution failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
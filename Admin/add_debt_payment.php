<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Extract data from request
$debt_id = $_POST['debt_id'] ?? 0;
$paid_amount = floatval($_POST['paid_amount'] ?? 0);
$method = $_POST['method'] ?? 'cash';
$notes = $_POST['notes'] ?? '';
$created_by = $_SESSION['UserId'];

// Validate required data
if (empty($debt_id) || $paid_amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment data']);
    exit;
}

// Validate payment method
$valid_methods = ['cash', 'bank', 'electronic'];
if (!in_array($method, $valid_methods)) {
    $method = 'cash'; // Default to cash if invalid
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get current debt info
    $stmt = $conn->prepare("SELECT amount, status FROM debts WHERE debt_id = ?");
    $stmt->bind_param("i", $debt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception("Debt not found");
    }
    
    $debt = $result->fetch_assoc();
    $total_debt = floatval($debt['amount']);
    $stmt->close();
    
    // 2. Make sure payment amount doesn't exceed debt
    if ($paid_amount > $total_debt) {
        throw new Exception("Payment amount cannot exceed the debt amount");
    }
    
    // 3. Get sum of existing payments
    $stmt = $conn->prepare("SELECT COALESCE(SUM(paid_amount), 0) as total_paid FROM debt_payments WHERE debt_id = ?");
    $stmt->bind_param("i", $debt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment_data = $result->fetch_assoc();
    $previous_paid = floatval($payment_data['total_paid']);
    $stmt->close();
    
    // 4. Calculate total paid (previous + current)
    $total_paid = $previous_paid + $paid_amount;
    
    // 5. Determine new status
    $new_status = 'pending';
    if ($total_paid >= $total_debt) {
        $new_status = 'paid';
    } else if ($total_paid > 0) {
        $new_status = 'partial';
    }
    
    // 6. Insert payment record
    $stmt = $conn->prepare("INSERT INTO debt_payments (debt_id, paid_amount, method, notes, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idssi", $debt_id, $paid_amount, $method, $notes, $created_by);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to record payment: " . $stmt->error);
    }
    $stmt->close();
    
    // 7. Update debt status
    $stmt = $conn->prepare("UPDATE debts SET status = ? WHERE debt_id = ?");
    $stmt->bind_param("si", $new_status, $debt_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update debt status: " . $stmt->error);
    }
    $stmt->close();
    
    // Commit the transaction
    $conn->commit();
    
    // Success response
    echo json_encode([
        'status' => 'success', 
        'message' => 'Payment recorded successfully',
        'paid_amount' => $paid_amount,
        'total_paid' => $total_paid,
        'new_status' => $new_status
    ]);
    
} catch (Exception $e) {
    // Roll back the transaction on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?> 
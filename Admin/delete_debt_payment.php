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

// Check if user has Boss privilege
$isBoss = (
    (isset($_SESSION['Privilege']) && (strtolower($_SESSION['Privilege']) === 'boss' || $_SESSION['Privilege'] === 'Boss')) || 
    (isset($_SESSION['privilege']) && (strtolower($_SESSION['privilege']) === 'boss' || $_SESSION['privilege'] === 'Boss'))
);
error_log("Delete payment - User ID: {$_SESSION['UserId']}, Privilege check: " . 
    ($_SESSION['Privilege'] ?? $_SESSION['privilege'] ?? 'none') . ", isBoss: " . ($isBoss ? 'true' : 'false'));
if (!$isBoss) {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied. Only Boss users can delete payments.']);
    exit;
}

// Extract data from request
$payment_id = intval($_POST['payment_id'] ?? 0);
$debt_id = intval($_POST['debt_id'] ?? 0);

// Validate required data
if (empty($payment_id) || empty($debt_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payment ID or debt ID']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get the payment amount before deleting it
    $stmt = $conn->prepare("SELECT paid_amount FROM debt_payments WHERE payment_id = ? AND debt_id = ?");
    $stmt->bind_param("ii", $payment_id, $debt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception("Payment not found");
    }
    
    $payment = $result->fetch_assoc();
    $deleted_amount = floatval($payment['paid_amount']);
    $stmt->close();
    
    // 2. Delete the payment
    $stmt = $conn->prepare("DELETE FROM debt_payments WHERE payment_id = ? AND debt_id = ?");
    $stmt->bind_param("ii", $payment_id, $debt_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete payment: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Payment not found or already deleted");
    }
    $stmt->close();
    
    // 3. Get sum of remaining payments
    $stmt = $conn->prepare("SELECT COALESCE(SUM(paid_amount), 0) as total_paid FROM debt_payments WHERE debt_id = ?");
    $stmt->bind_param("i", $debt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment_data = $result->fetch_assoc();
    $total_paid = floatval($payment_data['total_paid']);
    $stmt->close();
    
    // 4. Determine new debt status
    $new_status = 'pending';
    
    // Get the debt amount
    $stmt = $conn->prepare("SELECT amount FROM debts WHERE debt_id = ?");
    $stmt->bind_param("i", $debt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception("Debt not found");
    }
    
    $debt = $result->fetch_assoc();
    $total_debt = floatval($debt['amount']);
    $stmt->close();
    
    if ($total_paid >= $total_debt) {
        $new_status = 'paid';
    } else if ($total_paid > 0) {
        $new_status = 'partial';
    }
    
    // 5. Update debt status
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
        'message' => 'Payment deleted successfully',
        'deleted_amount' => $deleted_amount,
        'remaining_paid' => $total_paid,
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
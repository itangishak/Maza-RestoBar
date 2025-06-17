<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

// Check if user has Boss privilege
if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'Boss') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit();
}

// Check if expense_id is provided
if (!isset($_POST['expense_id']) || empty($_POST['expense_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No expense ID provided']);
    exit();
}

$expense_id = intval($_POST['expense_id']);

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if expense exists
    $check_stmt = $conn->prepare("SELECT expense_id FROM expenses WHERE expense_id = ?");
    $check_stmt->bind_param("i", $expense_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception("Expense not found");
    }
    
    // Delete the expense
    $delete_stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id = ?");
    $delete_stmt->bind_param("i", $expense_id);
    $delete_stmt->execute();
    
    // Check if delete was successful
    if ($delete_stmt->affected_rows === 0) {
        throw new Exception("Failed to delete expense");
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'Expense deleted successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    error_log("Error deleting expense: " . $e->getMessage());
}
?>

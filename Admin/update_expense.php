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

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Get and sanitize form data
$expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$expense_date = isset($_POST['expense_date']) ? trim($_POST['expense_date']) : date('Y-m-d');
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Validate data
if (empty($description)) {
    echo json_encode(['status' => 'error', 'message' => 'Description is required']);
    exit();
}

if ($amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than zero']);
    exit();
}

if (empty($category)) {
    echo json_encode(['status' => 'error', 'message' => 'Category is required']);
    exit();
}

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
    
    // Update the expense
    $update_stmt = $conn->prepare("UPDATE expenses SET description = ?, amount = ?, expense_date = ?, category = ?, notes = ? WHERE expense_id = ?");
    $update_stmt->bind_param("sdsssi", $description, $amount, $expense_date, $category, $notes, $expense_id);
    $update_stmt->execute();
    
    // Check if update was successful
    if ($update_stmt->affected_rows === 0) {
        throw new Exception("No changes made to the expense");
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'Expense updated successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    error_log("Error updating expense: " . $e->getMessage());
}
?>

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
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE expense_id = ?");
    $stmt->bind_param("i", $expense_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if expense exists
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Expense not found']);
        exit();
    }
    
    // Get the expense data
    $expense = $result->fetch_assoc();
    
    // Return the expense data as JSON
    echo json_encode([
        'status' => 'success',
        'expense_id' => $expense['expense_id'],
        'description' => $expense['description'],
        'amount' => $expense['amount'],
        'expense_date' => $expense['expense_date'],
        'category' => $expense['category'],
        'notes' => $expense['notes']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    error_log("Error fetching expense: " . $e->getMessage());
}
?>

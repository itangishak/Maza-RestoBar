<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Check if requesting outstanding balance for payroll
if (isset($_GET['employee_id'])) {
    $employeeId = intval($_GET['employee_id']);
    
    $query = "SELECT 
        COALESCE(SUM(outstanding_balance), 0) as outstanding_balance
    FROM employee_loans 
    WHERE employee_id = ? AND status = 'active'";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit();
    }
    
    $stmt->bind_param('i', $employeeId);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to execute query']);
        exit();
    }
    
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'outstanding_balance' => number_format($data['outstanding_balance'], 2, '.', '')
    ]);
    exit();
}

// Check required parameters
if (!isset($_GET['status'])) {
    echo json_encode(['success' => false, 'message' => 'Status parameter is required']);
    exit();
}

$status = $_GET['status'];
$validStatuses = ['active', 'repaid', 'defaulted'];

if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status parameter']);
    exit();
}

// Build query based on status
$query = "SELECT 
    el.loan_id, 
    CONCAT(u.firstname, ' ', u.lastname) as employee_name,
    el.loan_amount, 
    DATE_FORMAT(el.loan_date, '%Y-%m-%d') as loan_date,
    el.purpose,
    el.outstanding_balance,
    el.status,
    DATE_FORMAT((SELECT MAX(repayment_date) FROM loan_repayments WHERE loan_id = el.loan_id), '%Y-%m-%d') as repaid_date
FROM employee_loans el
JOIN employees e ON el.employee_id = e.employee_id
JOIN user u ON e.user_id = u.UserId
WHERE el.status = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $status);
$stmt->execute();
$result = $stmt->get_result();

$loans = [];
while ($row = $result->fetch_assoc()) {
    $loans[] = $row;
}

echo json_encode($loans);
?>

<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Validate input
$required = ['loan_id', 'repayment_amount', 'repayment_date', 'repayment_method'];
$missing = array_diff($required, array_keys($_POST));

if (!empty($missing)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
    exit();
}

// Prepare data
$loan_id = $_POST['loan_id'];
$repayment_amount = (float)$_POST['repayment_amount'];
$repayment_date = $_POST['repayment_date'];
$repayment_method = $_POST['repayment_method'];
$payroll_id = $_POST['payroll_id'] ?? null;

// Start transaction
$conn->begin_transaction();

try {
    // Insert repayment
    $query = "INSERT INTO loan_repayments (loan_id, payroll_id, repayment_amount, repayment_date, repayment_method) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iidss', $loan_id, $payroll_id, $repayment_amount, $repayment_date, $repayment_method);
    $stmt->execute();
    
    // Update loan balance
    $query = "UPDATE employee_loans 
              SET outstanding_balance = outstanding_balance - ? 
              WHERE loan_id = ? AND outstanding_balance >= ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('did', $repayment_amount, $loan_id, $repayment_amount);
    $stmt->execute();
    
    // Check if fully repaid
    if ($stmt->affected_rows > 0) {
        $query = "SELECT outstanding_balance FROM employee_loans WHERE loan_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $loan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['outstanding_balance'] <= 0) {
            $query = "UPDATE employee_loans SET status = 'repaid' WHERE loan_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $loan_id);
            $stmt->execute();
        }
    } else {
        throw new Exception("Repayment amount exceeds outstanding balance");
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Repayment added successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to add repayment: ' . $e->getMessage()]);
}
?>

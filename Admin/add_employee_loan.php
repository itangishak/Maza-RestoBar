<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Validate input
$required = ['employee_id', 'loan_amount', 'loan_date'];
$missing = array_diff($required, array_keys($_POST));

if (!empty($missing)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
    exit();
}

// Prepare data
$employee_id = $_POST['employee_id'];
$loan_amount = (float)$_POST['loan_amount'];
$loan_date = $_POST['loan_date'];
$purpose = $_POST['purpose'] ?? null;
$status = 'active';
$outstanding_balance = $loan_amount;

// Insert loan
$query = "INSERT INTO employee_loans (employee_id, loan_amount, loan_date, purpose, outstanding_balance, status) 
          VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('idssds', $employee_id, $loan_amount, $loan_date, $purpose, $outstanding_balance, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Loan added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add loan: ' . $conn->error]);
}
?>

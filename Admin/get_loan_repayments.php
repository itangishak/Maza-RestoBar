<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Validate input
if (!isset($_GET['loan_id'])) {
    echo json_encode(['success' => false, 'message' => 'Loan ID is required']);
    exit();
}

$loan_id = (int)$_GET['loan_id'];

// Get repayments
$query = "SELECT 
    repayment_id,
    repayment_amount,
    DATE_FORMAT(repayment_date, '%Y-%m-%d') as repayment_date,
    repayment_method
FROM loan_repayments
WHERE loan_id = ?
ORDER BY repayment_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $loan_id);
$stmt->execute();
$result = $stmt->get_result();

$repayments = [];
while ($row = $result->fetch_assoc()) {
    $repayments[] = $row;
}

echo json_encode(['success' => true, 'data' => $repayments]);
?>

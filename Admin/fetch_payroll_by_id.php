<?php
require_once 'connection.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payroll ID']);
    exit;
}

// Fetch the record
$stmt = $conn->prepare("
    SELECT 
        payroll_id, 
        employee_id, 
        pay_period_start, 
        pay_period_end, 
        gross_pay, 
        deductions, 
        net_pay, 
        payment_date, 
        notes
    FROM payroll_records
    WHERE payroll_id = ?
");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Payroll record not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch payroll']);
}

$stmt->close();
$conn->close();
?>
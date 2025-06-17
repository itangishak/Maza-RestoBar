<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Get query parameters
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$payroll_id = isset($_GET['payroll_id']) ? $_GET['payroll_id'] : null;

// Validate input
if (!$employee_id || !$start_date || !$end_date) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters',
        'duplicate' => false
    ]);
    exit;
}

// Check for duplicate payroll records (excluding current record if editing)
$duplicate_check_sql = "SELECT payroll_id FROM payroll_records 
                        WHERE employee_id = ? 
                        " . ($payroll_id ? "AND payroll_id != ?" : "") . "
                        AND (
                            (pay_period_start BETWEEN ? AND ?) OR
                            (pay_period_end BETWEEN ? AND ?) OR
                            (? BETWEEN pay_period_start AND pay_period_end) OR
                            (? BETWEEN pay_period_start AND pay_period_end)
                        )";

$check_stmt = $conn->prepare($duplicate_check_sql);

if (!$check_stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $conn->error,
        'duplicate' => false
    ]);
    exit;
}

// Bind parameters differently based on whether we're checking for an edit
if ($payroll_id) {
    $check_stmt->bind_param("iissssss", 
        $employee_id,
        $payroll_id,
        $start_date, $end_date, 
        $start_date, $end_date,
        $start_date, $end_date
    );
} else {
    $check_stmt->bind_param("issssss", 
        $employee_id,
        $start_date, $end_date, 
        $start_date, $end_date,
        $start_date, $end_date
    );
}

$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Found overlapping record
    echo json_encode([
        'duplicate' => true,
        'message' => 'This employee already has a payroll record for the same period or an overlapping period.'
    ]);
} else {
    // No duplicate found
    echo json_encode([
        'duplicate' => false,
        'message' => 'No duplicate found'
    ]);
}

$check_stmt->close();
$conn->close();
?> 
<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Optional debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!$conn) {
    echo json_encode([
        'data' => [],
        'error' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

/*
  We assume:
  - payroll_records.employee_id -> references employees.employee_id
  - employees.user_id -> references user.UserId
  - user has firstname, lastname
*/
$query = "
    SELECT 
        p.payroll_id,
        p.employee_id,
        CONCAT(u.firstname, ' ', u.lastname) AS employee_name,
        p.pay_period_start,
        p.pay_period_end,
        p.gross_pay,
        p.deductions,
        p.net_pay,
        p.payment_date,
        p.notes
    FROM payroll_records AS p
    JOIN employees AS e ON p.employee_id = e.employee_id
    JOIN user AS u      ON e.user_id      = u.UserId
    ORDER BY p.payroll_id DESC
";

$result = $conn->query($query);
$payrollData = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $payrollData[] = $row;
    }
    $result->free();
} else {
    // If query fails, let's see the error
    echo json_encode([
        'error' => 'SQL Error: ' . $conn->error,
        'data'  => []
    ]);
    exit;
}

echo json_encode(['data' => $payrollData]);
$conn->close();

?>
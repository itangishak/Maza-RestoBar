<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Employee ID is required']);
    exit;
}

$employee_id = intval($_GET['id']);

// Query to get employee salary information - removed salary_type which doesn't exist
$query = "
    SELECT 
        e.employee_id,
        e.salary
    FROM employees e
    WHERE e.employee_id = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to prepare statement: ' . $conn->error,
        'sql_error' => $conn->error,
        'query' => $query
    ]);
    exit;
}

$stmt->bind_param('i', $employee_id);
if (!$stmt->execute()) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to execute query: ' . $stmt->error,
        'sql_error' => $stmt->error
    ]);
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
    exit;
}

$employee = $result->fetch_assoc();

// Return the salary information - removed salary_type field
echo json_encode([
    'status' => 'success',
    'data' => [
        'employee_id' => $employee['employee_id'],
        'salary' => $employee['salary'] ?? 0
    ]
]);

$stmt->close();
$conn->close();
?> 
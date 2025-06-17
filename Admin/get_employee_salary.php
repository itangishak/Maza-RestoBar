<?php
header('Content-Type: application/json');
require_once 'connection.php';

if (!isset($_GET['employee_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Employee ID is required', 'debug' => 'No employee_id parameter provided']);
    exit();
}

$employeeId = $_GET['employee_id'];

// Sanitize input
$employeeId = filter_var($employeeId, FILTER_SANITIZE_NUMBER_INT);

// Log the request for debugging
error_log("Fetching salary for employee ID: " . $employeeId);

// Get employee salary from database
$query = "SELECT employee_id, salary FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $conn->error]);
    exit();
}

$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    error_log("Found employee: " . print_r($employee, true));
    echo json_encode(['salary' => $employee['salary'], 'debug' => $employee]);
} else {
    error_log("Employee not found for ID: " . $employeeId);
    http_response_code(404);
    echo json_encode(['error' => 'Employee not found', 'employee_id' => $employeeId]);
}

$stmt->close();
$conn->close();
?>

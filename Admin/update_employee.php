<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = (int)$_POST['employee_id'];
    $position = htmlspecialchars($_POST['position']);
    $salary = (float)$_POST['salary'];
    $hire_date = $_POST['hire_date'];

    if (empty($position) || empty($salary) || empty($hire_date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    $query = "
        UPDATE employees 
        SET position = ?, salary = ?, hire_date = ? 
        WHERE employee_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('sdsi', $position, $salary, $hire_date, $employee_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Employee updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update employee.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$conn->close();
?>

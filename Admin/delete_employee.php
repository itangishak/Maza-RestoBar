<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = (int)$_POST['employee_id'];

    $query = "DELETE FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $employee_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Employee deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete employee.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$conn->close();
?>

<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['employee_id'])) {
    $employee_id = (int)$_GET['employee_id'];

    $query = "
        SELECT 
            e.employee_id, 
            e.position, 
            e.salary, 
            e.hire_date
        FROM employees e
        WHERE e.employee_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $employee_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $data = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Employee not found.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch employee details.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$conn->close();
?>

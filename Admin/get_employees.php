<?php
require_once 'connection.php';
header('Content-Type: application/json');

$query = "SELECT employee_id, CONCAT(u.firstname, ' ', u.lastname) AS name 
          FROM employees e 
          JOIN user u ON e.user_id = u.UserId";
$result = $conn->query($query);

$employees = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = [
            'employee_id' => $row['employee_id'],
            'name' => $row['name']
        ];
    }
    echo json_encode(['status' => 'success', 'employees' => $employees]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch employees']);
}
$conn->close();
?>
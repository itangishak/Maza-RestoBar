<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Query to fetch employee names and IDs
$query = "SELECT employee_id, CONCAT(firstname, ' ', lastname) AS employee_name FROM employees e
          LEFT JOIN user u ON e.user_id = u.UserId";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $employees]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No employees found.']);
}

$conn->close();
?>

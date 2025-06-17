<?php
header('Content-Type: application/json');
require_once 'connection.php';
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Employee ID required']);
    exit;
}

$employeeId = (int)$_GET['id'];
$query = "SELECT u.firstname, u.lastname 
          FROM employees e 
          JOIN user u ON e.user_id = u.UserId 
          WHERE e.employee_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'data' => [
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
}

$stmt->close();
$conn->close();
?>

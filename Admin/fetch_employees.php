<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required',
        'data' => []
    ]);
    exit;
}

// Fetch all employees
$query = "
    SELECT 
        e.employee_id,
        CONCAT(u.firstname, ' ', u.lastname) AS name,
        u.email,
        e.position,
        e.salary,
        e.hire_date
    FROM 
        employees e
        JOIN user u ON e.user_id = u.UserId
    ORDER BY 
        u.firstname, u.lastname
";

try {
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = [
            'employee_id' => $row['employee_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'position' => $row['position'],
            'salary' => $row['salary'],
            'hire_date' => $row['hire_date']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $employees
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'data' => []
    ]);
}

$conn->close();

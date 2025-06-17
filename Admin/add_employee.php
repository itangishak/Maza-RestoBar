<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $position = isset($_POST['position']) ? htmlspecialchars($_POST['position']) : '';
    $salary = isset($_POST['salary']) ? (float)$_POST['salary'] : 0;
    $hire_date = isset($_POST['hire_date']) ? $_POST['hire_date'] : null;

    // Validate required fields
    if (empty($user_id) || empty($position) || empty($salary) || empty($hire_date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Check if the user is already an employee
    $checkQuery = "SELECT employee_id FROM employees WHERE user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This user is already an employee.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Insert the new employee record
    $query = "
        INSERT INTO employees (user_id, position, salary, hire_date, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isds", $user_id, $position, $salary, $hire_date);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Employee added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add employee.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>

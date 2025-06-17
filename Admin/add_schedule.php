<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check if user is Boss (has necessary privileges)
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$privStmt = $conn->prepare($privilegeQuery);
$privStmt->bind_param("i", $userId);
$privStmt->execute();
$privResult = $privStmt->get_result();

$isAuthorized = false;
if ($privResult && $row = $privResult->fetch_assoc()) {
    $isAuthorized = ($row['privilege'] === 'Boss');
}
$privStmt->close();

if (!$isAuthorized) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You do not have permission to add schedules'
    ]);
    exit;
}

// Process the form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $employeeId = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    $shiftId = isset($_POST['shift_id']) ? intval($_POST['shift_id']) : 0;
    $workDate = isset($_POST['work_date']) ? trim($conn->real_escape_string($_POST['work_date'])) : '';
    
    // Validate required fields
    if ($employeeId <= 0 || $shiftId <= 0 || empty($workDate)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]);
        exit;
    }
    
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $workDate)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid date format. Please use YYYY-MM-DD'
        ]);
        exit;
    }
    
    // Check if employee exists
    $checkEmployeeQuery = "SELECT COUNT(*) as count FROM employees WHERE employee_id = ?";
    $checkEmployeeStmt = $conn->prepare($checkEmployeeQuery);
    $checkEmployeeStmt->bind_param("i", $employeeId);
    $checkEmployeeStmt->execute();
    $checkEmployeeResult = $checkEmployeeStmt->get_result();
    $employeeRow = $checkEmployeeResult->fetch_assoc();
    
    if ($employeeRow['count'] === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Employee not found'
        ]);
        exit;
    }
    $checkEmployeeStmt->close();
    
    // Check if shift exists
    $checkShiftQuery = "SELECT COUNT(*) as count FROM shift_templates WHERE shift_id = ?";
    $checkShiftStmt = $conn->prepare($checkShiftQuery);
    $checkShiftStmt->bind_param("i", $shiftId);
    $checkShiftStmt->execute();
    $checkShiftResult = $checkShiftStmt->get_result();
    $shiftRow = $checkShiftResult->fetch_assoc();
    
    if ($shiftRow['count'] === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Shift template not found'
        ]);
        exit;
    }
    $checkShiftStmt->close();
    
    // Check if schedule already exists for this employee on this date with this shift
    $checkScheduleQuery = "SELECT COUNT(*) as count FROM schedules WHERE employee_id = ? AND work_date = ? AND shift_id = ?";
    $checkScheduleStmt = $conn->prepare($checkScheduleQuery);
    $checkScheduleStmt->bind_param("isi", $employeeId, $workDate, $shiftId);
    $checkScheduleStmt->execute();
    $checkScheduleResult = $checkScheduleStmt->get_result();
    $scheduleRow = $checkScheduleResult->fetch_assoc();
    
    if ($scheduleRow['count'] > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'This employee is already scheduled for this shift on this date'
        ]);
        exit;
    }
    $checkScheduleStmt->close();
    
    // Insert the schedule
    $query = "INSERT INTO schedules (employee_id, shift_id, work_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $employeeId, $shiftId, $workDate);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Schedule added successfully',
            'schedule_id' => $conn->insert_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add schedule: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 
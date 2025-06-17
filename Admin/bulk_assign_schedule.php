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
        'message' => 'You do not have permission to bulk assign schedules'
    ]);
    exit;
}

// Process the form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $employeeId = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    $shiftId = isset($_POST['shift_id']) ? intval($_POST['shift_id']) : 0;
    $startDate = isset($_POST['start_date']) ? trim($conn->real_escape_string($_POST['start_date'])) : '';
    $endDate = isset($_POST['end_date']) ? trim($conn->real_escape_string($_POST['end_date'])) : '';
    $days = isset($_POST['days']) && is_array($_POST['days']) ? $_POST['days'] : [];
    
    // Validate required fields
    if ($employeeId <= 0 || $shiftId <= 0 || empty($startDate) || empty($endDate) || empty($days)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required and at least one day of the week must be selected'
        ]);
        exit;
    }
    
    // Validate date formats
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid date format. Please use YYYY-MM-DD'
        ]);
        exit;
    }
    
    // Validate date range
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    
    if ($startDateObj > $endDateObj) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Start date must be before or equal to end date'
        ]);
        exit;
    }
    
    // Validate days of week
    $validDays = ['0', '1', '2', '3', '4', '5', '6']; // 0 = Sunday, 1 = Monday, etc.
    foreach ($days as $day) {
        if (!in_array($day, $validDays)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid day of week'
            ]);
            exit;
        }
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
    
    // Generate dates that match the selected days of the week
    $currentDate = clone $startDateObj;
    $scheduleDates = [];
    
    while ($currentDate <= $endDateObj) {
        $dayOfWeek = $currentDate->format('w'); // 0 (Sunday) to 6 (Saturday)
        
        if (in_array($dayOfWeek, $days)) {
            $scheduleDates[] = $currentDate->format('Y-m-d');
        }
        
        $currentDate->modify('+1 day');
    }
    
    if (empty($scheduleDates)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No dates match the selected days of the week within the date range'
        ]);
        exit;
    }
    
    // Prepare for batch insert
    $insertQuery = "INSERT INTO schedules (employee_id, shift_id, work_date) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iis", $employeeId, $shiftId, $workDate);
    
    $conn->begin_transaction();
    
    try {
        $insertedCount = 0;
        $skippedCount = 0;
        
        foreach ($scheduleDates as $workDate) {
            // Check if schedule already exists
            $checkExistingQuery = "SELECT COUNT(*) as count FROM schedules WHERE employee_id = ? AND work_date = ? AND shift_id = ?";
            $checkExistingStmt = $conn->prepare($checkExistingQuery);
            $checkExistingStmt->bind_param("isi", $employeeId, $workDate, $shiftId);
            $checkExistingStmt->execute();
            $checkExistingResult = $checkExistingStmt->get_result();
            $existingRow = $checkExistingResult->fetch_assoc();
            
            if ($existingRow['count'] === 0) {
                $insertStmt->execute();
                $insertedCount++;
            } else {
                $skippedCount++;
            }
            
            $checkExistingStmt->close();
        }
        
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => "Bulk assignment completed: $insertedCount shifts assigned, $skippedCount skipped (already assigned)"
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to assign shifts: ' . $e->getMessage()
        ]);
    }
    
    $insertStmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 
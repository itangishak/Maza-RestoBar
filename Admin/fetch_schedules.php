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

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('monday this week'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('sunday this week'));
$employeeId = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

// Validate dates
if (!strtotime($startDate)) $startDate = date('Y-m-d', strtotime('monday this week'));
if (!strtotime($endDate)) $endDate = date('Y-m-d', strtotime('sunday this week'));

// Sanitize input
$startDate = $conn->real_escape_string($startDate);
$endDate = $conn->real_escape_string($endDate);

try {
    // Generate an array of all dates in the range
    $dateRange = [];
    $currentDate = new DateTime($startDate);
    $lastDate = new DateTime($endDate);
    
    while ($currentDate <= $lastDate) {
        $dateRange[] = $currentDate->format('Y-m-d');
        $currentDate->modify('+1 day');
    }
    
    // Fetch employees based on filter
    $employeeQuery = "
        SELECT 
            e.employee_id,
            CONCAT(u.firstname, ' ', u.lastname) AS name,
            e.position
        FROM 
            employees e
            JOIN user u ON e.user_id = u.UserId
    ";
    
    if ($employeeId > 0) {
        $employeeQuery .= " WHERE e.employee_id = $employeeId";
    }
    
    $employeeQuery .= " ORDER BY u.firstname, u.lastname";
    
    $employeeResult = $conn->query($employeeQuery);
    if (!$employeeResult) {
        throw new Exception("Failed to fetch employees: " . $conn->error);
    }
    
    $employees = [];
    while ($row = $employeeResult->fetch_assoc()) {
        $employees[] = [
            'employee_id' => $row['employee_id'],
            'name' => $row['name'],
            'position' => $row['position']
        ];
    }
    
    // Fetch schedules for the date range
    $scheduleQuery = "
        SELECT 
            s.schedule_id,
            s.employee_id,
            s.shift_id,
            s.work_date,
            st.name AS shift_name,
            st.start_time,
            st.end_time,
            st.grace_period,
            CONCAT(u.firstname, ' ', u.lastname) AS employee_name
        FROM 
            schedules s
            JOIN shift_templates st ON s.shift_id = st.shift_id
            JOIN employees e ON s.employee_id = e.employee_id
            JOIN user u ON e.user_id = u.UserId
        WHERE 
            s.work_date BETWEEN '$startDate' AND '$endDate'
    ";
    
    if ($employeeId > 0) {
        $scheduleQuery .= " AND s.employee_id = $employeeId";
    }
    
    $scheduleQuery .= " ORDER BY s.work_date, st.start_time";
    
    $scheduleResult = $conn->query($scheduleQuery);
    if (!$scheduleResult) {
        throw new Exception("Failed to fetch schedules: " . $conn->error);
    }
    
    $schedules = [];
    while ($row = $scheduleResult->fetch_assoc()) {
        $schedules[] = [
            'schedule_id' => $row['schedule_id'],
            'employee_id' => $row['employee_id'],
            'employee_name' => $row['employee_name'],
            'shift_id' => $row['shift_id'],
            'work_date' => $row['work_date'],
            'shift_name' => $row['shift_name'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'grace_period' => $row['grace_period']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $schedules,
        'employees' => $employees,
        'dates' => $dateRange
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'data' => []
    ]);
}

$conn->close();
?> 
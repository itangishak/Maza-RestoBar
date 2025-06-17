<?php
session_start();
require_once 'connection.php';
require_once '../includes/holiday_functions.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get user information and privilege
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$stmtPrivilege = $conn->prepare($privilegeQuery);
$stmtPrivilege->bind_param("i", $userId);
$stmtPrivilege->execute();
$privilegeResult = $stmtPrivilege->get_result();

$userPrivilege = '';
if ($privilegeResult && $row = $privilegeResult->fetch_assoc()) {
    $userPrivilege = $row['privilege'];
}
$stmtPrivilege->close();

// Get employee ID if the user is an employee
$employeeQuery = "SELECT employee_id FROM employees WHERE user_id = ?";
$stmtEmployee = $conn->prepare($employeeQuery);
$stmtEmployee->bind_param("i", $userId);
$stmtEmployee->execute();
$employeeResult = $stmtEmployee->get_result();

$userEmployeeId = null;
if ($employeeResult && $row = $employeeResult->fetch_assoc()) {
    $userEmployeeId = $row['employee_id'];
}
$stmtEmployee->close();

// Get parameters
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    switch ($action) {
        case 'request':
            requestTimeOff($conn, $userEmployeeId);
            break;
        case 'approve':
            approveTimeOff($conn, $userPrivilege);
            break;
        case 'reject':
            rejectTimeOff($conn, $userPrivilege);
            break;
        case 'list':
            listTimeOff($conn, $userPrivilege, $userEmployeeId);
            break;
        case 'delete':
            deleteTimeOff($conn, $userPrivilege, $userEmployeeId);
            break;
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action specified'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Request time off (any employee can do this)
 */
function requestTimeOff($conn, $employeeId) {
    // If overriding for another employee (managers/boss only)
    if (isset($_POST['employee_id']) && isAuthorized()) {
        $employeeId = (int)$_POST['employee_id'];
    }
    
    // Validate employee ID
    if (!$employeeId) {
        throw new Exception("Employee ID is required");
    }
    
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $timeOffType = $_POST['time_off_type'] ?? 'Vacation';
    $notes = $_POST['notes'] ?? '';
    
    // Validate dates
    if (empty($startDate) || empty($endDate)) {
        throw new Exception("Start date and end date are required");
    }
    
    if (strtotime($endDate) < strtotime($startDate)) {
        throw new Exception("End date cannot be before start date");
    }
    
    // Check for conflicts with existing approved time off
    $conflictQuery = "SELECT COUNT(*) AS conflict_count FROM employee_time_off 
                      WHERE employee_id = ? AND approval_status = 'Approved'
                      AND ((start_date <= ? AND end_date >= ?) OR 
                           (start_date <= ? AND end_date >= ?) OR
                           (start_date >= ? AND end_date <= ?))";
    $conflictStmt = $conn->prepare($conflictQuery);
    $conflictStmt->bind_param("issssss", $employeeId, $endDate, $startDate, $startDate, $endDate, $startDate, $endDate);
    $conflictStmt->execute();
    $conflictResult = $conflictStmt->get_result();
    $conflictRow = $conflictResult->fetch_assoc();
    
    if ($conflictRow['conflict_count'] > 0) {
        throw new Exception("This time off request conflicts with already approved time off");
    }
    
    // Insert the time off request
    $insertQuery = "INSERT INTO employee_time_off 
                   (employee_id, start_date, end_date, time_off_type, approval_status, notes) 
                   VALUES (?, ?, ?, ?, 'Pending', ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("issss", $employeeId, $startDate, $endDate, $timeOffType, $notes);
    
    if ($insertStmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Time off request submitted successfully',
            'time_off_id' => $conn->insert_id
        ]);
    } else {
        throw new Exception("Failed to submit time off request: " . $conn->error);
    }
}

/**
 * Approve a time off request (managers/boss only)
 */
function approveTimeOff($conn, $userPrivilege) {
    if (!isAuthorized($userPrivilege)) {
        throw new Exception("Insufficient privileges");
    }
    
    $timeOffId = isset($_POST['time_off_id']) ? (int)$_POST['time_off_id'] : 0;
    
    if ($timeOffId <= 0) {
        throw new Exception("Invalid time off ID");
    }
    
    // Update the status to Approved
    $updateQuery = "UPDATE employee_time_off SET approval_status = 'Approved' WHERE time_off_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $timeOffId);
    
    if ($updateStmt->execute()) {
        // Get the employee and date range for this time off
        $infoQuery = "SELECT employee_id, start_date, end_date FROM employee_time_off WHERE time_off_id = ?";
        $infoStmt = $conn->prepare($infoQuery);
        $infoStmt->bind_param("i", $timeOffId);
        $infoStmt->execute();
        $infoResult = $infoStmt->get_result();
        
        if ($row = $infoResult->fetch_assoc()) {
            $employeeId = $row['employee_id'];
            $startDate = $row['start_date'];
            $endDate = $row['end_date'];
            
            // Update any existing attendance records for this date range
            $updateAttQuery = "UPDATE attendance_records 
                              SET status = 'Holiday' 
                              WHERE employee_id = ? 
                              AND attendance_date BETWEEN ? AND ? 
                              AND status = 'Absent'";
            $updateAttStmt = $conn->prepare($updateAttQuery);
            $updateAttStmt->bind_param("iss", $employeeId, $startDate, $endDate);
            $updateAttStmt->execute();
            $updatedRecords = $updateAttStmt->affected_rows;
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Time off request approved',
                'attendance_records_updated' => $updatedRecords
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Time off request approved'
            ]);
        }
    } else {
        throw new Exception("Failed to approve time off request: " . $conn->error);
    }
}

/**
 * Reject a time off request (managers/boss only)
 */
function rejectTimeOff($conn, $userPrivilege) {
    if (!isAuthorized($userPrivilege)) {
        throw new Exception("Insufficient privileges");
    }
    
    $timeOffId = isset($_POST['time_off_id']) ? (int)$_POST['time_off_id'] : 0;
    
    if ($timeOffId <= 0) {
        throw new Exception("Invalid time off ID");
    }
    
    // Update the status to Rejected
    $updateQuery = "UPDATE employee_time_off SET approval_status = 'Rejected' WHERE time_off_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $timeOffId);
    
    if ($updateStmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Time off request rejected'
        ]);
    } else {
        throw new Exception("Failed to reject time off request: " . $conn->error);
    }
}

/**
 * List time off requests
 * - Employees can see only their own requests
 * - Managers/boss can see all requests
 */
function listTimeOff($conn, $userPrivilege, $userEmployeeId) {
    $whereClause = "";
    $params = [];
    $types = "";
    
    // Filter by employee ID if not manager/boss
    if (!isAuthorized($userPrivilege)) {
        if (!$userEmployeeId) {
            throw new Exception("Not authorized to view time off requests");
        }
        $whereClause = "WHERE eto.employee_id = ?";
        $params[] = $userEmployeeId;
        $types .= "i";
    } 
    // Filter by specific employee if requested (for managers/boss)
    else if (isset($_POST['employee_id'])) {
        $employeeId = (int)$_POST['employee_id'];
        $whereClause = "WHERE eto.employee_id = ?";
        $params[] = $employeeId;
        $types .= "i";
    }
    
    // Additional filters
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    if (!empty($status)) {
        $whereClause .= ($whereClause ? " AND " : "WHERE ") . "eto.approval_status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $fromDate = isset($_POST['from_date']) ? $_POST['from_date'] : '';
    if (!empty($fromDate)) {
        $whereClause .= ($whereClause ? " AND " : "WHERE ") . "eto.start_date >= ?";
        $params[] = $fromDate;
        $types .= "s";
    }
    
    $toDate = isset($_POST['to_date']) ? $_POST['to_date'] : '';
    if (!empty($toDate)) {
        $whereClause .= ($whereClause ? " AND " : "WHERE ") . "eto.end_date <= ?";
        $params[] = $toDate;
        $types .= "s";
    }
    
    // Query for time off requests with employee name
    $query = "SELECT eto.*, 
             CONCAT(u.firstname, ' ', u.lastname) as employee_name,
             DATEDIFF(eto.end_date, eto.start_date) + 1 as days_count
             FROM employee_time_off eto
             JOIN employees e ON eto.employee_id = e.employee_id
             JOIN user u ON e.user_id = u.UserId
             $whereClause
             ORDER BY eto.start_date DESC";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $timeOffRequests = [];
    while ($row = $result->fetch_assoc()) {
        $timeOffRequests[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'time_off_requests' => $timeOffRequests
    ]);
}

/**
 * Delete a time off request
 * - Employees can delete only their pending requests
 * - Managers/boss can delete any request
 */
function deleteTimeOff($conn, $userPrivilege, $userEmployeeId) {
    $timeOffId = isset($_POST['time_off_id']) ? (int)$_POST['time_off_id'] : 0;
    
    if ($timeOffId <= 0) {
        throw new Exception("Invalid time off ID");
    }
    
    // Check authorization
    if (!isAuthorized($userPrivilege)) {
        // For regular employees, check if this is their request and it's pending
        $checkQuery = "SELECT employee_id, approval_status FROM employee_time_off WHERE time_off_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $timeOffId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        $isAuthorized = false;
        if ($row = $checkResult->fetch_assoc()) {
            $isAuthorized = ($row['employee_id'] == $userEmployeeId && $row['approval_status'] == 'Pending');
        }
        
        if (!$isAuthorized) {
            throw new Exception("You can only delete your own pending time off requests");
        }
    }
    
    // Delete the time off request
    $deleteQuery = "DELETE FROM employee_time_off WHERE time_off_id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $timeOffId);
    
    if ($deleteStmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Time off request deleted'
        ]);
    } else {
        throw new Exception("Failed to delete time off request: " . $conn->error);
    }
}

/**
 * Check if user is authorized (manager or boss)
 */
function isAuthorized($userPrivilege = '') {
    return ($userPrivilege === 'Boss' || $userPrivilege === 'Manager');
}

$conn->close();
?> 
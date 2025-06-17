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

// Get parameters
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    switch ($action) {
        case 'mark_attendance':
            markAttendance($conn);
            break;
        case 'update_attendance':
            updateAttendance($conn);
            break;
        case 'bulk_process':
            bulkProcessAttendance($conn);
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
 * Mark attendance for a specific employee
 */
function markAttendance($conn) {
    $employeeId = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
    $attendanceDate = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d');
    $status = isset($_POST['status']) ? $_POST['status'] : 'Present';
    $clockInTime = isset($_POST['clock_in_time']) ? $_POST['clock_in_time'] : date('H:i:s');
    $clockOutTime = isset($_POST['clock_out_time']) ? $_POST['clock_out_time'] : null;
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $attendanceType = isset($_POST['attendance_type']) ? $_POST['attendance_type'] : 'Manual';
    
    // Validate inputs
    if ($employeeId <= 0) {
        throw new Exception("Employee ID is required");
    }
    
    // Check if employee has no shift scheduled for this day
    if ($status === 'Absent' && isEmployeeDayOff($conn, $employeeId, $attendanceDate)) {
        $status = 'Holiday';
    }
    
    // Check if record already exists
    $checkQuery = "SELECT attendance_id FROM attendance_records 
                   WHERE employee_id = ? AND attendance_date = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("is", $employeeId, $attendanceDate);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update existing record
        $row = $checkResult->fetch_assoc();
        $attendanceId = $row['attendance_id'];
        
        $updateQuery = "UPDATE attendance_records 
                       SET status = ?, clock_in_time = ?, clock_out_time = ?, 
                           notes = ?, attendance_type = ?
                       WHERE attendance_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssssi", $status, $clockInTime, $clockOutTime, 
                               $notes, $attendanceType, $attendanceId);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update attendance record: " . $conn->error);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Attendance record updated successfully'
        ]);
        
        $updateStmt->close();
    } else {
        // Insert new record
        $insertQuery = "INSERT INTO attendance_records 
                       (employee_id, attendance_date, status, clock_in_time, 
                        clock_out_time, notes, attendance_type) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("issssss", $employeeId, $attendanceDate, 
                               $status, $clockInTime, $clockOutTime, 
                               $notes, $attendanceType);
        
        if (!$insertStmt->execute()) {
            throw new Exception("Failed to insert attendance record: " . $conn->error);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Attendance record created successfully',
            'attendance_id' => $conn->insert_id
        ]);
        
        $insertStmt->close();
    }
    
    $checkStmt->close();
}

/**
 * Update existing attendance record
 */
function updateAttendance($conn) {
    $attendanceId = isset($_POST['attendance_id']) ? (int)$_POST['attendance_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : null;
    $clockInTime = isset($_POST['clock_in_time']) ? $_POST['clock_in_time'] : null;
    $clockOutTime = isset($_POST['clock_out_time']) ? $_POST['clock_out_time'] : null;
    $notes = isset($_POST['notes']) ? $_POST['notes'] : null;
    
    // Validate inputs
    if ($attendanceId <= 0) {
        throw new Exception("Attendance ID is required");
    }
    
    // Get the current record to check if employee has a shift
    if ($status === 'Absent') {
        $dateQuery = "SELECT employee_id, attendance_date FROM attendance_records WHERE attendance_id = ?";
        $dateStmt = $conn->prepare($dateQuery);
        $dateStmt->bind_param("i", $attendanceId);
        $dateStmt->execute();
        $dateResult = $dateStmt->get_result();
        
        if ($row = $dateResult->fetch_assoc()) {
            $employeeId = $row['employee_id'];
            $attendanceDate = $row['attendance_date'];
            if (isEmployeeDayOff($conn, $employeeId, $attendanceDate)) {
                $status = 'Holiday';
            }
        }
        
        $dateStmt->close();
    }
    
    // Build the update query dynamically based on provided fields
    $updateFields = [];
    $params = [];
    $types = "";
    
    if ($status !== null) {
        $updateFields[] = "status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if ($clockInTime !== null) {
        $updateFields[] = "clock_in_time = ?";
        $params[] = $clockInTime;
        $types .= "s";
    }
    
    if ($clockOutTime !== null) {
        $updateFields[] = "clock_out_time = ?";
        $params[] = $clockOutTime;
        $types .= "s";
    }
    
    if ($notes !== null) {
        $updateFields[] = "notes = ?";
        $params[] = $notes;
        $types .= "s";
    }
    
    if (empty($updateFields)) {
        throw new Exception("No fields to update");
    }
    
    $updateQuery = "UPDATE attendance_records SET " . implode(", ", $updateFields) . 
                  " WHERE attendance_id = ?";
    $params[] = $attendanceId;
    $types .= "i";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param($types, ...$params);
    
    if (!$updateStmt->execute()) {
        throw new Exception("Failed to update attendance record: " . $conn->error);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Attendance record updated successfully'
    ]);
    
    $updateStmt->close();
}

/**
 * Process attendance in bulk for a specific date
 */
function bulkProcessAttendance($conn) {
    $attendanceDate = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d');
    $defaultStatus = isset($_POST['default_status']) ? $_POST['default_status'] : 'Absent';
    
    // Get all active employees
    $employeeQuery = "SELECT employee_id FROM employees WHERE status = 'Active'";
    $employeeResult = $conn->query($employeeQuery);
    
    if (!$employeeResult) {
        throw new Exception("Failed to fetch employees: " . $conn->error);
    }
    
    $processedCount = 0;
    
    // Start a transaction
    $conn->begin_transaction();
    
    try {
        // Check for existing records for this date
        $existingQuery = "SELECT employee_id FROM attendance_records WHERE attendance_date = ?";
        $existingStmt = $conn->prepare($existingQuery);
        $existingStmt->bind_param("s", $attendanceDate);
        $existingStmt->execute();
        $existingResult = $existingStmt->get_result();
        
        $existingEmployees = [];
        while ($row = $existingResult->fetch_assoc()) {
            $existingEmployees[] = $row['employee_id'];
        }
        
        // Process each employee
        while ($employee = $employeeResult->fetch_assoc()) {
            $employeeId = $employee['employee_id'];
            
            // Skip if record already exists
            if (in_array($employeeId, $existingEmployees)) {
                continue;
            }
            
            // Check if scheduled for this date
            $scheduleQuery = "SELECT shift_id FROM schedules 
                             WHERE employee_id = ? AND work_date = ?";
            $scheduleStmt = $conn->prepare($scheduleQuery);
            $scheduleStmt->bind_param("is", $employeeId, $attendanceDate);
            $scheduleStmt->execute();
            $scheduleResult = $scheduleStmt->get_result();
            
            // If employee has a shift scheduled and didn't show up: Absent
            // If employee has no shift scheduled: Holiday (day off)
            $status = 'Absent'; // Default if scheduled but not present
            $hasShift = false;
            
            if ($scheduleRow = $scheduleResult->fetch_assoc()) {
                $hasShift = true;
            } else {
                $status = 'Holiday'; // No shift scheduled = day off
            }
            
            $scheduleStmt->close();
            
            // Insert attendance record
            $insertQuery = "INSERT INTO attendance_records 
                          (employee_id, attendance_date, status, clock_in_time, 
                           clock_out_time, notes, attendance_type) 
                          VALUES (?, ?, ?, ?, ?, ?, 'Manual')";
            $insertStmt = $conn->prepare($insertQuery);
            
            // Default clock times
            $clockInTime = "09:00:00";
            $clockOutTime = "17:00:00";
            $notes = $hasShift ? "Auto-generated (absent from scheduled shift)" : "Auto-generated (day off - no scheduled shift)";
            
            $insertStmt->bind_param("isssss", $employeeId, $attendanceDate, 
                                  $status, $clockInTime, $clockOutTime, $notes);
            
            if (!$insertStmt->execute()) {
                throw new Exception("Failed to insert attendance record: " . $conn->error);
            }
            
            $insertStmt->close();
            $processedCount++;
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => "Processed attendance for $processedCount employees"
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        throw $e;
    }
}

$conn->close();
?> 
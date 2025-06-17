<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in with Boss privileges
if (!isset($_SESSION['UserId'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check user privilege
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$stmtPrivilege = $conn->prepare($privilegeQuery);
$stmtPrivilege->bind_param("i", $userId);
$stmtPrivilege->execute();
$privilegeResult = $stmtPrivilege->get_result();

$isAuthorized = false;
if ($privilegeResult && $row = $privilegeResult->fetch_assoc()) {
    $isAuthorized = ($row['privilege'] === 'Boss' || $row['privilege'] === 'Manager');
}
$stmtPrivilege->close();

if (!$isAuthorized) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Insufficient privileges'
    ]);
    exit;
}

try {
    // Start a transaction
    $conn->begin_transaction();
    
    // Get attendance records without schedule_id
    $query = "SELECT ar.attendance_id, ar.employee_id, ar.attendance_date, ar.status 
              FROM attendance_records ar 
              WHERE ar.schedule_id IS NULL";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Failed to fetch attendance records: " . $conn->error);
    }
    
    $updatedCount = 0;
    $errors = [];
    
    // Process each record
    while ($record = $result->fetch_assoc()) {
        $attendanceId = $record['attendance_id'];
        $employeeId = $record['employee_id'];
        $attendanceDate = $record['attendance_date'];
        
        // Find matching schedule
        $scheduleQuery = "SELECT schedule_id FROM schedules 
                         WHERE employee_id = ? AND work_date = ?";
        
        $stmtSchedule = $conn->prepare($scheduleQuery);
        $stmtSchedule->bind_param("is", $employeeId, $attendanceDate);
        $stmtSchedule->execute();
        $scheduleResult = $stmtSchedule->get_result();
        
        if ($scheduleResult && $scheduleResult->num_rows > 0) {
            $scheduleRow = $scheduleResult->fetch_assoc();
            $scheduleId = $scheduleRow['schedule_id'];
            
            // Update attendance record with schedule_id
            $updateQuery = "UPDATE attendance_records 
                           SET schedule_id = ? 
                           WHERE attendance_id = ?";
            
            $stmtUpdate = $conn->prepare($updateQuery);
            $stmtUpdate->bind_param("ii", $scheduleId, $attendanceId);
            $stmtUpdate->execute();
            
            if ($stmtUpdate->affected_rows > 0) {
                $updatedCount++;
            }
            
            $stmtUpdate->close();
        } else {
            // No matching schedule found - create one
            $status = 'Absent';
            
            // Determine if this should be a day off instead
            $hasOtherShifts = false;
            $otherShiftsQuery = "SELECT COUNT(*) as shift_count FROM schedules 
                               WHERE employee_id = ? AND work_date = ?";
            $stmtOtherShifts = $conn->prepare($otherShiftsQuery);
            $stmtOtherShifts->bind_param("is", $employeeId, $attendanceDate);
            $stmtOtherShifts->execute();
            $otherShiftsResult = $stmtOtherShifts->get_result();
            
            if ($otherShiftsResult && $row = $otherShiftsResult->fetch_assoc()) {
                $hasOtherShifts = ($row['shift_count'] > 0);
            }
            $stmtOtherShifts->close();
            
            if (!$hasOtherShifts) {
                $status = 'DayOff';
            }
            
            // Get default shift
            $defaultShiftQuery = "SELECT shift_id FROM shift_templates LIMIT 1";
            $defaultShiftResult = $conn->query($defaultShiftQuery);
            
            if (!$defaultShiftResult || $defaultShiftResult->num_rows === 0) {
                $errors[] = "No shift templates found for attendance ID $attendanceId";
                continue;
            }
            
            $defaultShiftRow = $defaultShiftResult->fetch_assoc();
            $defaultShiftId = $defaultShiftRow['shift_id'];
            
            // Insert new schedule entry
            $insertQuery = "INSERT INTO schedules (employee_id, shift_id, status, work_date) 
                          VALUES (?, ?, ?, ?)";
            
            $stmtInsert = $conn->prepare($insertQuery);
            $stmtInsert->bind_param("iiss", $employeeId, $defaultShiftId, $status, $attendanceDate);
            $stmtInsert->execute();
            
            if ($stmtInsert->affected_rows > 0) {
                $newScheduleId = $stmtInsert->insert_id;
                
                // Update attendance record with the new schedule_id
                $updateQuery = "UPDATE attendance_records 
                              SET schedule_id = ? 
                              WHERE attendance_id = ?";
                
                $stmtUpdate = $conn->prepare($updateQuery);
                $stmtUpdate->bind_param("ii", $newScheduleId, $attendanceId);
                $stmtUpdate->execute();
                
                if ($stmtUpdate->affected_rows > 0) {
                    $updatedCount++;
                }
                
                $stmtUpdate->close();
            }
            
            $stmtInsert->close();
        }
        
        $stmtSchedule->close();
    }
    
    // Commit changes
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => "Updated $updatedCount attendance records with schedule links",
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 
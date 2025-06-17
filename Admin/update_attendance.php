<?php
session_start(); // Start session to access user data
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

// Check user's privilege
$userId = $_SESSION['UserId'];
$userIsBoss = false;

$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$privStmt = $conn->prepare($privilegeQuery);
$privStmt->bind_param("i", $userId);
$privStmt->execute();
$privResult = $privStmt->get_result();

if ($privResult && $row = $privResult->fetch_assoc()) {
    $userIsBoss = ($row['privilege'] === 'Boss');
}
$privStmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure database connection is successful
    if (!$conn) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed: ' . mysqli_connect_error()
        ]);
        exit;
    }

    // Collect attendance ID
    $attendance_id = $_POST['attendance_id'];
    
    // Get the current schedule_id before making any changes
    $scheduleIdQuery = "SELECT schedule_id FROM attendance_records WHERE attendance_id = ?";
    $scheduleIdStmt = $conn->prepare($scheduleIdQuery);
    
    if (!$scheduleIdStmt) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error fetching schedule ID: ' . $conn->error
        ]);
        exit;
    }
    
    $scheduleIdStmt->bind_param("i", $attendance_id);
    $scheduleIdStmt->execute();
    $scheduleResult = $scheduleIdStmt->get_result();
    $scheduleId = null;
    
    if ($scheduleResult && $scheduleRow = $scheduleResult->fetch_assoc()) {
        $scheduleId = $scheduleRow['schedule_id'];
    }
    $scheduleIdStmt->close();
    
    // For non-Boss users, get original values from the database for restricted fields
    if (!$userIsBoss) {
        $fetchQuery = "
            SELECT ar.employee_id, ar.attendance_type, ar.attendance_date, s.status, ar.clock_in_time
            FROM attendance_records ar
            LEFT JOIN schedules s ON ar.schedule_id = s.schedule_id
            WHERE ar.attendance_id = ?
        ";
        
        $fetchStmt = $conn->prepare($fetchQuery);
        $fetchStmt->bind_param("i", $attendance_id);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        
        if ($result && $originalData = $result->fetch_assoc()) {
            // Use original values for fields that non-Boss users can't modify
            $employee_id = $originalData['employee_id'];
            $attendance_type = $originalData['attendance_type'];
            $attendance_date = $originalData['attendance_date'];
            $status = $originalData['status'];
            $clock_in_time = $originalData['clock_in_time'];
            
            // Only allow non-Boss users to update these fields
            $clock_out_time = $_POST['clock_out_time'];
            $notes = $_POST['notes'];
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve original record'
            ]);
            exit;
        }
        
        $fetchStmt->close();
    } else {
        // Boss user: Fetch original data for locked fields, use POST for updatable ones.
        $fetchQueryBoss = "
            SELECT ar.employee_id, ar.attendance_type, ar.attendance_date, s.status, ar.clock_in_time, ar.notes
            FROM attendance_records ar
            LEFT JOIN schedules s ON ar.schedule_id = s.schedule_id
            WHERE ar.attendance_id = ?
        ";
        
        $fetchStmtBoss = $conn->prepare($fetchQueryBoss);
        if (!$fetchStmtBoss) {
            echo json_encode(['status' => 'error', 'message' => 'DB error (boss fetch): ' . $conn->error]);
            exit;
        }
        
        $fetchStmtBoss->bind_param("i", $attendance_id);
        $fetchStmtBoss->execute();
        $resultBoss = $fetchStmtBoss->get_result();
        
        if ($resultBoss && $originalDataBoss = $resultBoss->fetch_assoc()) {
            $employee_id     = $originalDataBoss['employee_id'];
            $attendance_type = $originalDataBoss['attendance_type'];
            $attendance_date = $originalDataBoss['attendance_date'];
            $status          = $originalDataBoss['status']; // Default to DB status
            $clock_in_time   = $originalDataBoss['clock_in_time'];
            $notes_original  = $originalDataBoss['notes'];

            // Clock out time from POST (auto-populated by server time on frontend)
            if (isset($_POST['clock_out_time'])) {
                $clock_out_time = $_POST['clock_out_time'];
            } else {
                $clock_out_time = null; // Should ideally always be set by frontend AJAX
            }

            // Notes from POST (UI field is locked, so this should be original value or manipulated POST)
            if (isset($_POST['notes'])) {
                $notes = $_POST['notes'];
            } else {
                $notes = $notes_original; // Fallback to original notes from DB
            }

            // Allow Boss to override status if a valid one is POSTed (robustness for direct POST)
            if (isset($_POST['status']) && !empty($_POST['status']) && in_array($_POST['status'], ['Present', 'Absent', 'DayOff', 'Ill', 'Justified'])) {
                $status = $_POST['status'];
            }
            
            // If changing status to Present or Absent, check for conflicts (FOR BOSS)
            if ($status == 'Present' || $status == 'Absent') {
                $conflicting_status = ($status == 'Present') ? 'Absent' : 'Present';
                
                $check_query_boss = "SELECT COUNT(*) AS conflict_count 
                                  FROM schedules 
                                  WHERE employee_id = ? 
                                  AND work_date = ? 
                                  AND status = ?";
                
                $params_boss = array($employee_id, $attendance_date, $conflicting_status);
                $types_boss = "iss";
                
                // $scheduleId is fetched earlier in the script (lines 43-62 of full script)
                if ($scheduleId) { 
                    $check_query_boss .= " AND schedule_id != ?";
                    $params_boss[] = $scheduleId;
                    $types_boss .= "i";
                }
                
                $check_stmt_boss = $conn->prepare($check_query_boss);
                if (!$check_stmt_boss) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Database error during Boss conflict check: ' . $conn->error
                    ]);
                    exit;
                }
                
                $ref_params_boss = array();
                $ref_params_boss[] = &$types_boss;
                foreach ($params_boss as $key => $value) {
                    $ref_params_boss[] = &$params_boss[$key];
                }
                
                call_user_func_array(array($check_stmt_boss, 'bind_param'), $ref_params_boss);
                $check_stmt_boss->execute();
                $check_result_boss = $check_stmt_boss->get_result();
                $row_boss = $check_result_boss->fetch_assoc();
                
                if ($row_boss['conflict_count'] > 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Cannot mark employee as both Present and Absent on the same day (Boss)'
                    ]);
                    $check_stmt_boss->close();
                    exit;
                }
                $check_stmt_boss->close();
            } // End of Boss conflict check

        } else { // else for 'if ($resultBoss && $originalDataBoss = $resultBoss->fetch_assoc())'
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve original record for Boss update.'
            ]);
            // $fetchStmtBoss is defined in the outer 'else' block for Boss users.
            // We should close it here before exiting if the inner fetch failed.
            if (isset($fetchStmtBoss) && $fetchStmtBoss instanceof mysqli_stmt) {
                 $fetchStmtBoss->close();
            }
            exit;
        }
        // This close statement belongs to the $fetchStmtBoss prepared for the Boss user,
        // executed if the $resultBoss && $originalDataBoss was successful.
        if (isset($fetchStmtBoss) && $fetchStmtBoss instanceof mysqli_stmt) {
             $fetchStmtBoss->close();
        }
    } // Correct closing brace for the main 'else' block (Boss user logic)
    
    // First, update the status in the schedules table if we have a schedule ID
    if ($scheduleId) {
        // Make sure the status is one of the allowed values in schedules table
        $allowedStatuses = ['Present', 'Absent', 'DayOff', 'Ill', 'Justified'];
        if (!in_array($status, $allowedStatuses)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid status value provided'
            ]);
            exit;
        }
        
        $updateScheduleQuery = "UPDATE schedules SET status = ? WHERE schedule_id = ?";
        $updateScheduleStmt = $conn->prepare($updateScheduleQuery);
        
        if (!$updateScheduleStmt) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error while updating schedule: ' . $conn->error
            ]);
            exit;
        }
        
        $updateScheduleStmt->bind_param("si", $status, $scheduleId);
        if (!$updateScheduleStmt->execute()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update schedule status: ' . $updateScheduleStmt->error
            ]);
            exit;
        }
        $updateScheduleStmt->close();
    } else {
        // If no schedule exists, try to find or create one
        // First, check if a schedule exists for this employee on this date
        $schedule_query = "SELECT schedule_id, shift_id FROM schedules WHERE employee_id = ? AND work_date = ?";
        $schedule_stmt = $conn->prepare($schedule_query);
        
        if (!$schedule_stmt) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error while checking schedules: ' . $conn->error
            ]);
            exit;
        }
        
        $schedule_stmt->bind_param("is", $employee_id, $attendance_date);
        $schedule_stmt->execute();
        $schedule_result = $schedule_stmt->get_result();
        $scheduleId = null;
        $shift_id = null;
        
        if ($schedule_row = $schedule_result->fetch_assoc()) {
            // Schedule exists, get its ID and update its status
            $scheduleId = $schedule_row['schedule_id'];
            
            // Update the schedule status
            $update_query = "UPDATE schedules SET status = ? WHERE schedule_id = ?";
            $update_stmt = $conn->prepare($update_query);
            
            if (!$update_stmt) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database error while updating schedule: ' . $conn->error
                ]);
                exit;
            }
            
            $update_stmt->bind_param("si", $status, $scheduleId);
            if (!$update_stmt->execute()) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to update schedule status: ' . $update_stmt->error
                ]);
                exit;
            }
            $update_stmt->close();
        } else {
            // No schedule exists, create one
            // First try to find a shift for this employee
            $shift_query = "SELECT shift_id FROM shift_templates LIMIT 1";
            $shift_result = $conn->query($shift_query);
            
            if ($shift_result && $shift_row = $shift_result->fetch_assoc()) {
                $shift_id = $shift_row['shift_id'];
            } else {
                // No shifts exist, create a dummy one with ID 1
                $shift_id = 1;
            }
            
            // Create a new schedule
            $new_schedule_query = "INSERT INTO schedules (employee_id, shift_id, status, work_date) VALUES (?, ?, ?, ?)";
            $new_schedule_stmt = $conn->prepare($new_schedule_query);
            
            if (!$new_schedule_stmt) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Database error while creating schedule: ' . $conn->error
                ]);
                exit;
            }
            
            $new_schedule_stmt->bind_param("iiss", $employee_id, $shift_id, $status, $attendance_date);
            if (!$new_schedule_stmt->execute()) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create schedule: ' . $new_schedule_stmt->error
                ]);
                exit;
            }
            
            $scheduleId = $conn->insert_id;
            $new_schedule_stmt->close();
        }
        
        $schedule_stmt->close();
    }

    // Prepare the Update query for attendance_records to only update clock_out_time and schedule_id
    $query = "
        UPDATE attendance_records 
        SET 
            clock_out_time  = ?, 
            schedule_id     = ? 
        WHERE 
            attendance_id   = ?
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Prepare failed for clock_out update: ' . $conn->error
        ]);
        exit;
    }

    // Force clock_out_time to current server time for the update, fulfilling auto-population requirement
    $clock_out_time = date('H:i:s'); 

    // Format the current server time for DB (strtotime is robust for H:i:s format)
    $clock_out_to_db = date('H:i:s', strtotime($clock_out_time));

    // Make sure the parameter types match the columns' data types
    // s = string (for time), i = integer
    $stmt->bind_param(
        'sii', 
        $clock_out_to_db,  // Use formatted time
        $scheduleId,       // schedule_id might have been newly created
        $attendance_id
    );

    if (!$stmt->execute()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Execution failed: ' . $stmt->error
        ]);
        exit;
    }

    // Simplified response regardless of affected_rows since the update process works as expected
    echo json_encode([
        'status' => 'success',
        'message' => 'Success'
    ]);


    $stmt->close();
    $conn->close();
    exit;
}
?>

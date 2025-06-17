<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form data was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract form data
    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    $attendance_type = isset($_POST['attendance_type']) ? $conn->real_escape_string($_POST['attendance_type']) : '';
    $attendance_date = isset($_POST['attendance_date']) ? $conn->real_escape_string($_POST['attendance_date']) : '';
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : '';

    $clock_in_time = isset($_POST['clock_in_time']) ? $conn->real_escape_string($_POST['clock_in_time']) : '';
    $clock_out_time = isset($_POST['clock_out_time']) && !empty($_POST['clock_out_time']) 
                     ? $conn->real_escape_string($_POST['clock_out_time']) 
                     : null;
    $notes = isset($_POST['notes']) ? $conn->real_escape_string($_POST['notes']) : '';

    // Validate input
    if ($employee_id <= 0 || empty($attendance_type) || empty($attendance_date) || empty($status) || empty($clock_in_time)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields'
        ]);
        exit;
    }

    // Validate the status is one of the allowed values in schedules table
    $allowedStatuses = ['Present', 'Absent', 'DayOff', 'Ill', 'Justified'];
    if (!in_array($status, $allowedStatuses)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid status value provided'
        ]);
        exit;
    }
    
    // Check if there is already a schedule for this employee on this date
    // with a conflicting status (i.e., if adding "Present" check if "Absent" exists or vice versa)
    $conflicting_status = ($status == 'Present') ? 'Absent' : ($status == 'Absent' ? 'Present' : null);
    
    if ($conflicting_status) {
        $check_query = "SELECT COUNT(*) AS conflict_count 
                       FROM schedules 
                       WHERE employee_id = ? 
                       AND work_date = ? 
                       AND status = ?";
        
        $check_stmt = $conn->prepare($check_query);
        if (!$check_stmt) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error during conflict check: ' . $conn->error
            ]);
            exit;
        }
        
        $check_stmt->bind_param("iss", $employee_id, $attendance_date, $conflicting_status);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $row = $check_result->fetch_assoc();
        
        if ($row['conflict_count'] > 0) {
            // Instead of error, update the conflicting schedule's status to the new status
            $get_schedule_query = "SELECT schedule_id FROM schedules WHERE employee_id = ? AND work_date = ? AND status = ? LIMIT 1";
            $get_schedule_stmt = $conn->prepare($get_schedule_query);
            $get_schedule_stmt->bind_param("iss", $employee_id, $attendance_date, $conflicting_status);
            $get_schedule_stmt->execute();
            $schedule_result = $get_schedule_stmt->get_result();
            if ($schedule_row = $schedule_result->fetch_assoc()) {
                $conflict_schedule_id = $schedule_row['schedule_id'];
                // Update the status
                $update_schedule_query = "UPDATE schedules SET status = ? WHERE schedule_id = ?";
                $update_schedule_stmt = $conn->prepare($update_schedule_query);
                $update_schedule_stmt->bind_param("si", $status, $conflict_schedule_id);
                $update_schedule_stmt->execute();
                $update_schedule_stmt->close();
                // Check if attendance record exists for this schedule
                $attendance_check_query = "SELECT attendance_id FROM attendance_records WHERE schedule_id = ? AND employee_id = ? AND attendance_date = ?";
                $attendance_check_stmt = $conn->prepare($attendance_check_query);
                $attendance_check_stmt->bind_param("iis", $conflict_schedule_id, $employee_id, $attendance_date);
                $attendance_check_stmt->execute();
                $attendance_result = $attendance_check_stmt->get_result();
                if ($attendance_row = $attendance_result->fetch_assoc()) {
                    // Update the attendance record
                    $update_attendance_query = "UPDATE attendance_records SET attendance_type = ?, clock_in_time = ?, clock_out_time = ?, notes = ? WHERE attendance_id = ?";
                    $update_attendance_stmt = $conn->prepare($update_attendance_query);
                    $update_attendance_stmt->bind_param("ssssi", $attendance_type, $clock_in_time, $clock_out_time, $notes, $attendance_row['attendance_id']);
                    $update_attendance_stmt->execute();
                    $update_attendance_stmt->close();
                } else {
                    // Insert new attendance record
                    $insert_attendance_query = "INSERT INTO attendance_records (employee_id, schedule_id, attendance_type, attendance_date, clock_in_time, clock_out_time, proofofclockin, notes) VALUES (?, ?, ?, ?, ?, ?, 'off', ?)";
                    $insert_attendance_stmt = $conn->prepare($insert_attendance_query);
                    $insert_attendance_stmt->bind_param("iisssss", $employee_id, $conflict_schedule_id, $attendance_type, $attendance_date, $clock_in_time, $clock_out_time, $notes);
                    $insert_attendance_stmt->execute();
                    $insert_attendance_stmt->close();
                }
                $attendance_check_stmt->close();
                $get_schedule_stmt->close();
                $check_stmt->close();
                // Return success (skip rest of script)
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Attendance and schedule status updated.'
                ]);
                $conn->close();
                exit;
            }
        }
        
        $check_stmt->close();
    }
    
    // Find the schedule for this employee on this date
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
    $schedule_id = null;
    $shift_id = null;
    $schedules = [];
    while ($row = $schedule_result->fetch_assoc()) {
        $schedules[] = $row;
    }
    if (count($schedules) === 1) {
        $schedule_id = $schedules[0]['schedule_id'];
        $shift_id = $schedules[0]['shift_id'];
        // Get shift interval from shift_templates
        $shift_query = "SELECT start_time, end_time FROM shift_templates WHERE shift_id = ?";
        $shift_stmt = $conn->prepare($shift_query);
        if (!$shift_stmt) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error while fetching shift interval: ' . $conn->error
            ]);
            exit;
        }
        $shift_stmt->bind_param("i", $shift_id);
        $shift_stmt->execute();
        $shift_result = $shift_stmt->get_result();
        if ($shift_row = $shift_result->fetch_assoc()) {
            $shift_start = $shift_row['start_time'];
            $shift_end = $shift_row['end_time'];
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Shift interval not found for this schedule.'
            ]);
            exit;
        }
        $shift_stmt->close();
        // Validate clock_in_time and clock_out_time
        if ($clock_in_time < $shift_start || ($clock_out_time !== null && $clock_out_time > $shift_end)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Clock-in/out time must be within the shift interval (' . $shift_start . ' - ' . $shift_end . ')',
                'shift_interval' => [
                    'start_time' => $shift_start,
                    'end_time' => $shift_end
                ]
            ]);
            exit;
        }
        // Schedule exists, get its ID and update its status
        $update_query = "UPDATE schedules SET status = ? WHERE schedule_id = ?";
        $update_stmt = $conn->prepare($update_query);
        if (!$update_stmt) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error while updating schedule: ' . $conn->error
            ]);
            exit;
        }
        $update_stmt->bind_param("si", $status, $schedule_id);
        if (!$update_stmt->execute()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update schedule status: ' . $update_stmt->error
            ]);
            exit;
        }
        $update_stmt->close();
        // Prevent duplicate attendance for this employee/schedule/date combination
        $attendance_check_query = "SELECT COUNT(*) as cnt FROM attendance_records 
                                 WHERE schedule_id = ? AND employee_id = ? AND attendance_date = ? 
                                 AND attendance_type = ? AND DATE_FORMAT(clock_in_time, '%H:%i') = ?";
        $attendance_check_stmt = $conn->prepare($attendance_check_query);
        if (!$attendance_check_stmt) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error while checking attendance: ' . $conn->error
            ]);
            exit;
        }
        // Format clock_in_time to match the format in the database
        $formatted_clock_in = substr($clock_in_time, 0, 5);
        $attendance_check_stmt->bind_param("iisss", $schedule_id, $employee_id, $attendance_date, $attendance_type, $formatted_clock_in);
        $attendance_check_stmt->execute();
        $attendance_check_result = $attendance_check_stmt->get_result();
        $attendance_check_row = $attendance_check_result->fetch_assoc();
        if ($attendance_check_row['cnt'] > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Duplicate attendance record detected. An attendance record with the same details already exists.'
            ]);
            exit;
        }
        $attendance_check_stmt->close();
    } else if (count($schedules) === 0) {
        // No schedule exists for this employee and date
        echo json_encode([
            'status' => 'error',
            'message' => 'No schedule for this employee on this date.'
        ]);
        exit;
    } else {
        // More than one schedule (multiple shifts for this employee on this date)
        echo json_encode([
            'status' => 'error',
            'message' => 'Multiple shifts scheduled for this employee on this date. Please contact admin.'
        ]);
        exit;
    }
    
    $schedule_stmt->close();
    
    // Now build SQL query for attendance_records with schedule_id
    $query = "INSERT INTO attendance_records 
              (employee_id, schedule_id, attendance_type, attendance_date, clock_in_time, clock_out_time, proofofclockin, notes) 
              VALUES (?, ?, ?, ?, ?, ?, 'off', ?)";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $conn->error
        ]);
        exit;
    }

    // Prepare the statement differently based on whether clock_out_time is null
    if ($clock_out_time === null) {
        // Use NULL for clock_out_time in the query
        $clock_out_time_null = null;
        $stmt->bind_param("iisssss", $employee_id, $schedule_id, $attendance_type, $attendance_date, $clock_in_time, $clock_out_time_null, $notes);
    } else {
        $stmt->bind_param("iisssss", $employee_id, $schedule_id, $attendance_type, $attendance_date, $clock_in_time, $clock_out_time, $notes);
    }

    if ($stmt->execute()) {    // Insert successful
        echo json_encode([
            'status' => 'success',
            'message' => 'Attendance recorded successfully',
            'shift_interval' => [
                'start_time' => isset($shift_start) ? $shift_start : null,
                'end_time' => isset($shift_end) ? $shift_end : null
            ]
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add attendance record: ' . $stmt->error
        ]);
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>

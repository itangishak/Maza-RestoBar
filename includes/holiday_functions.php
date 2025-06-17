<?php
/**
 * Check if a given date is an approved time off for an employee
 * 
 * @param mysqli $conn Database connection
 * @param int $employeeId Employee ID to check
 * @param string $date Date in YYYY-MM-DD format
 * @return bool True if date is a time off day for the employee, false otherwise
 */
function isEmployeeTimeOff($conn, $employeeId, $date) {
    $query = "SELECT COUNT(*) as count FROM employee_time_off 
              WHERE employee_id = ? 
              AND ? BETWEEN start_date AND end_date
              AND approval_status = 'Approved'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $employeeId, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return ($row['count'] > 0);
}

/**
 * Update attendance records for employees with approved time off
 * 
 * @param mysqli $conn Database connection
 * @param string $date Optional date in YYYY-MM-DD format. If not provided, processes all records.
 * @return int Number of records updated
 */
function updateTimeOffAttendance($conn, $date = null) {
    $whereClause = "";
    $params = [];
    $types = "";
    
    if ($date !== null) {
        $whereClause = "AND ar.attendance_date = ?";
        $params[] = $date;
        $types = "s";
    }
    
    // Find all attendance records marked as "Absent" that should be "Holiday" (time off)
    $query = "SELECT ar.attendance_id, ar.employee_id, ar.attendance_date 
              FROM attendance_records ar
              WHERE ar.status = 'Absent' $whereClause";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $updatedCount = 0;
    
    // Check each absent date if it's a time off
    while ($row = $result->fetch_assoc()) {
        $recordDate = $row['attendance_date'];
        $employeeId = $row['employee_id'];
        $recordId = $row['attendance_id'];
        
        if (isEmployeeTimeOff($conn, $employeeId, $recordDate)) {
            // Update the record to "Holiday"
            $updateQuery = "UPDATE attendance_records SET status = 'Holiday' WHERE attendance_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $recordId);
            $updateStmt->execute();
            $updateStmt->close();
            
            $updatedCount++;
        }
    }
    
    $stmt->close();
    return $updatedCount;
}

/**
 * Check if a given date should be considered a day off for an employee
 * by checking if they don't have a scheduled shift on that day
 * 
 * @param mysqli $conn Database connection
 * @param int $employeeId Employee ID to check
 * @param string $date Date in YYYY-MM-DD format
 * @return bool True if employee has no shift scheduled (day off), false if they have a shift
 */
function isEmployeeDayOff($conn, $employeeId, $date) {
    // Check if employee has a shift scheduled for this date
    $query = "SELECT COUNT(*) as has_shift FROM schedules 
              WHERE employee_id = ? AND work_date = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $employeeId, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // If count is 0, employee has no shift (day off)
    return ($row['has_shift'] == 0);
}

/**
 * Update attendance records for employees with unscheduled days 
 * by marking them as "Holiday" instead of "Absent"
 * 
 * @param mysqli $conn Database connection
 * @param string $date Optional date in YYYY-MM-DD format. If not provided, processes all records.
 * @return int Number of records updated
 */
function updateDaysOffAttendance($conn, $date = null) {
    $whereClause = "";
    $params = [];
    $types = "";
    
    if ($date !== null) {
        $whereClause = "AND ar.attendance_date = ?";
        $params[] = $date;
        $types = "s";
    }
    
    // Find all attendance records marked as "Absent" that should be "Holiday" (days off)
    $query = "SELECT ar.attendance_id, ar.employee_id, ar.attendance_date 
              FROM attendance_records ar
              LEFT JOIN schedules s ON ar.employee_id = s.employee_id 
                                   AND ar.attendance_date = s.work_date
              WHERE ar.status = 'Absent' 
              AND s.schedule_id IS NULL $whereClause";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $updatedCount = 0;
    
    // Update each absent record to holiday if there's no schedule
    while ($row = $result->fetch_assoc()) {
        $recordId = $row['attendance_id'];
        
        // Update the record to "Holiday"
        $updateQuery = "UPDATE attendance_records SET status = 'Holiday' WHERE attendance_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $recordId);
        $updateStmt->execute();
        $updateStmt->close();
        
        $updatedCount++;
    }
    
    $stmt->close();
    return $updatedCount;
}

/**
 * Check if an employee should be marked absent or on holiday based on their schedule
 * 
 * @param mysqli $conn Database connection
 * @param int $employeeId Employee ID
 * @param string $date Date to check
 * @return string Status to use ('Holiday' if no shift scheduled, 'Absent' otherwise)
 */
function getEmployeeAttendanceStatus($conn, $employeeId, $date) {
    if (isEmployeeDayOff($conn, $employeeId, $date)) {
        return 'Holiday';
    }
    return 'Absent';
}
?> 
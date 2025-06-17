<?php
session_start();
require_once 'connection.php';
require_once '../includes/holiday_functions.php';
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

// Get parameters
$sourceWeekStart = isset($_POST['source_week_start']) ? $_POST['source_week_start'] : null;
$variationLevel = isset($_POST['variation_level']) ? intval($_POST['variation_level']) : 2; // 1=low, 2=medium, 3=high

// If no source week is provided, get last week
if (!$sourceWeekStart) {
    $sourceWeekStart = date('Y-m-d', strtotime('last week monday'));
}

// Calculate end date of source week
$sourceWeekEnd = date('Y-m-d', strtotime($sourceWeekStart . ' +6 days'));

// Calculate current and next week dates
$currentWeekStart = date('Y-m-d', strtotime('monday this week'));
$currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));
$nextWeekStart = date('Y-m-d', strtotime('next week monday'));
$nextWeekEnd = date('Y-m-d', strtotime($nextWeekStart . ' +6 days'));

try {
    // Start a transaction
    $conn->begin_transaction();

    // Get source week schedules
    $sourceQuery = "
        SELECT 
            s.employee_id,
            s.shift_id,
            s.status,
            s.work_date,
            WEEKDAY(s.work_date) AS day_of_week
        FROM 
            schedules s
        WHERE 
            s.work_date BETWEEN ? AND ?
        ORDER BY 
            s.employee_id, s.work_date, s.shift_id
    ";
    
    $stmt = $conn->prepare($sourceQuery);
    $stmt->bind_param("ss", $sourceWeekStart, $sourceWeekEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Failed to fetch source schedules: " . $conn->error);
    }
    
    $sourceSchedules = [];
    while ($row = $result->fetch_assoc()) {
        $sourceSchedules[] = $row;
    }
    
    // Get all active employees
    $employeeQuery = "
        SELECT 
            e.employee_id,
            CONCAT(u.firstname, ' ', u.lastname) AS name
        FROM 
            employees e
            JOIN user u ON e.user_id = u.UserId
        WHERE 
            e.status = 'Active'
        ORDER BY 
            RAND() -- Randomize for rotation
    ";
    
    $employeeResult = $conn->query($employeeQuery);
    if (!$employeeResult) {
        throw new Exception("Failed to fetch employees: " . $conn->error);
    }
    
    $employees = [];
    while ($row = $employeeResult->fetch_assoc()) {
        $employees[] = $row;
    }
    
    // Get default shift (for DayOff entries)
    $defaultShiftQuery = "SELECT shift_id FROM shift_templates LIMIT 1";
    $defaultShiftResult = $conn->query($defaultShiftQuery);
    
    if (!$defaultShiftResult || $defaultShiftResult->num_rows === 0) {
        throw new Exception("No shift templates found in the database");
    }
    
    $defaultShiftRow = $defaultShiftResult->fetch_assoc();
    $defaultShiftId = $defaultShiftRow['shift_id'];
    
    // Clean existing schedules for target weeks (to avoid duplicates)
    $deleteQuery = "DELETE FROM schedules WHERE work_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($deleteQuery);
    
    // Delete current week schedules
    $stmt->bind_param("ss", $currentWeekStart, $currentWeekEnd);
    $stmt->execute();
    
    // Delete next week schedules
    $stmt->bind_param("ss", $nextWeekStart, $nextWeekEnd);
    $stmt->execute();

    // Create full week schedules - current week
    $currentWeekSchedules = createFullWeekSchedules(
        $sourceSchedules, 
        $employees, 
        $defaultShiftId,
        $variationLevel, 
        $currentWeekStart
    );
    
    // Create full week schedules - next week
    $nextWeekSchedules = createFullWeekSchedules(
        $currentWeekSchedules, 
        $employees, 
        $defaultShiftId,
        $variationLevel, 
        $nextWeekStart
    );
    
    // Insert schedules and get inserted IDs
    $currentWeekIds = insertSchedules($conn, $currentWeekSchedules);
    $nextWeekIds = insertSchedules($conn, $nextWeekSchedules);
    
    // Create attendance records based on schedules
    $attendanceInserted = 0;
    $attendanceInserted += createAttendanceRecords($conn, $currentWeekIds);
    $attendanceInserted += createAttendanceRecords($conn, $nextWeekIds);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Schedules for current and next week have been automatically generated',
        'current_week_count' => count($currentWeekSchedules),
        'next_week_count' => count($nextWeekSchedules),
        'attendance_records_created' => $attendanceInserted
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Create schedules for a full week for all employees
 * 
 * @param array $sourceSchedules Source schedules to base the new schedules on
 * @param array $employees List of all active employees
 * @param int $defaultShiftId Default shift ID to use for day off entries
 * @param int $variationLevel Level of variation (1=low, 2=medium, 3=high)
 * @param string $weekStart Start date of the week (YYYY-MM-DD)
 * @return array Complete set of schedules for the week
 */
function createFullWeekSchedules($sourceSchedules, $employees, $defaultShiftId, $variationLevel, $weekStart) {
    $allSchedules = [];
    $employeeCount = count($employees);
    
    // Group source schedules by day of week and employee
    $sourceByDayAndEmployee = [];
    foreach ($sourceSchedules as $schedule) {
        $dayOfWeek = $schedule['day_of_week'];
        $employeeId = $schedule['employee_id'];
        
        if (!isset($sourceByDayAndEmployee[$dayOfWeek])) {
            $sourceByDayAndEmployee[$dayOfWeek] = [];
        }
        if (!isset($sourceByDayAndEmployee[$dayOfWeek][$employeeId])) {
            $sourceByDayAndEmployee[$dayOfWeek][$employeeId] = [];
        }
        
        $sourceByDayAndEmployee[$dayOfWeek][$employeeId][] = $schedule;
    }
    
    // Determine which employees will be assigned shifts each day based on variability
    $assignedEmployeesByDay = [];
    
    // First, identify employees with shifts in source schedule for each day
    for ($day = 0; $day <= 6; $day++) {
        $assignedEmployeesByDay[$day] = [];
        
        if (isset($sourceByDayAndEmployee[$day])) {
            foreach ($sourceByDayAndEmployee[$day] as $employeeId => $shifts) {
                $assignedEmployeesByDay[$day][] = $employeeId;
            }
        }
    }
    
    // Apply variation to assigned employees based on level
    switch ($variationLevel) {
        case 1: // Low variation - 20% chance to swap employees
            for ($day = 0; $day <= 6; $day++) {
                if (empty($assignedEmployeesByDay[$day])) continue;
                
                // Swap a few employees (20% of them)
                $swapCount = max(1, round(count($assignedEmployeesByDay[$day]) * 0.2));
                
                for ($i = 0; $i < $swapCount; $i++) {
                    // Pick a random employee to remove
                    $randomIndex = array_rand($assignedEmployeesByDay[$day]);
                    $removedEmployee = $assignedEmployeesByDay[$day][$randomIndex];
                    unset($assignedEmployeesByDay[$day][$randomIndex]);
                    $assignedEmployeesByDay[$day] = array_values($assignedEmployeesByDay[$day]);
                    
                    // Pick a new employee to add who isn't already assigned
                    $availableEmployees = [];
                    foreach ($employees as $employee) {
                        if (!in_array($employee['employee_id'], $assignedEmployeesByDay[$day])) {
                            $availableEmployees[] = $employee['employee_id'];
                        }
                    }
                    
                    if (!empty($availableEmployees)) {
                        $newEmployeeIndex = array_rand($availableEmployees);
                        $assignedEmployeesByDay[$day][] = $availableEmployees[$newEmployeeIndex];
                    } else {
                        // If no available employees, put back the removed one
                        $assignedEmployeesByDay[$day][] = $removedEmployee;
                    }
                }
            }
            break;
            
        case 2: // Medium variation - 50% chance to rotate assignments
            for ($day = 0; $day <= 6; $day++) {
                if (empty($assignedEmployeesByDay[$day])) continue;
                
                if (rand(0, 1) === 1) {
                    // Rotate assignments - shift all employees by a random amount
                    $rotationOffset = rand(1, count($assignedEmployeesByDay[$day]) - 1);
                    $assignedEmployeesByDay[$day] = array_merge(
                        array_slice($assignedEmployeesByDay[$day], $rotationOffset),
                        array_slice($assignedEmployeesByDay[$day], 0, $rotationOffset)
                    );
                }
            }
            break;
            
        case 3: // High variation - completely randomize assignments
            for ($day = 0; $day <= 6; $day++) {
                if (empty($assignedEmployeesByDay[$day])) continue;
                
                // Keep the same number of assigned employees but randomize who they are
                $assignmentCount = count($assignedEmployeesByDay[$day]);
                
                // Reset assignments for this day
                $assignedEmployeesByDay[$day] = [];
                
                // Create a pool of all employee IDs
                $employeePool = [];
                foreach ($employees as $employee) {
                    $employeePool[] = $employee['employee_id'];
                }
                
                // Randomly select employees
                shuffle($employeePool);
                for ($i = 0; $i < $assignmentCount && $i < count($employeePool); $i++) {
                    $assignedEmployeesByDay[$day][] = $employeePool[$i];
                }
            }
            break;
            
        default:
            // No variation - keep original assignments
            break;
    }
    
    // Create schedules for each day for all employees
    for ($day = 0; $day <= 6; $day++) {
        $currentDate = date('Y-m-d', strtotime($weekStart . " +$day days"));
        
        // Process each employee
        foreach ($employees as $employee) {
            $employeeId = $employee['employee_id'];
            $isAssigned = in_array($employeeId, $assignedEmployeesByDay[$day]);
            
            if ($isAssigned) {
                // Employee has shift this day
                // Get shift details from source schedules if available
                if (isset($sourceByDayAndEmployee[$day][$employeeId])) {
                    foreach ($sourceByDayAndEmployee[$day][$employeeId] as $sourceSchedule) {
                        $shiftId = $sourceSchedule['shift_id'];
                        
                        $allSchedules[] = [
                            'employee_id' => $employeeId,
                            'shift_id' => $shiftId,
                            'status' => 'Absent', // Default status is Absent until they clock in
                            'work_date' => $currentDate
                        ];
                    }
                } else {
                    // No source schedule, assign default shift
                    $allSchedules[] = [
                        'employee_id' => $employeeId,
                        'shift_id' => $defaultShiftId,
                        'status' => 'Absent',
                        'work_date' => $currentDate
                    ];
                }
            } else {
                // Employee has no shift this day - mark as DayOff
                $allSchedules[] = [
                    'employee_id' => $employeeId,
                    'shift_id' => $defaultShiftId,
                    'status' => 'DayOff',
                    'work_date' => $currentDate
                ];
            }
        }
    }
    
    return $allSchedules;
}

/**
 * Insert schedules into the database and return the inserted schedule IDs
 * 
 * @param mysqli $conn Database connection
 * @param array $schedules Array of schedules to insert
 * @return array Array of inserted schedule IDs with original index
 */
function insertSchedules($conn, $schedules) {
    $insertQuery = "
        INSERT INTO schedules (employee_id, shift_id, status, work_date) 
        VALUES (?, ?, ?, ?)
    ";
    
    $stmt = $conn->prepare($insertQuery);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare insert statement: " . $conn->error);
    }
    
    $insertedIds = [];
    
    foreach ($schedules as $index => $schedule) {
        $stmt->bind_param(
            "iiss", 
            $schedule['employee_id'], 
            $schedule['shift_id'], 
            $schedule['status'], 
            $schedule['work_date']
        );
        
        $stmt->execute();
        
        if ($stmt->error) {
            throw new Exception("Failed to insert schedule: " . $stmt->error);
        }
        
        $insertedIds[$index] = [
            'schedule_id' => $stmt->insert_id,
            'employee_id' => $schedule['employee_id'],
            'shift_id' => $schedule['shift_id'],
            'status' => $schedule['status'],
            'work_date' => $schedule['work_date']
        ];
    }
    
    $stmt->close();
    return $insertedIds;
}

/**
 * Create attendance records based on schedules
 * 
 * @param mysqli $conn Database connection
 * @param array $schedules Array of schedule IDs and details
 * @return int Number of attendance records created
 */
function createAttendanceRecords($conn, $schedules) {
    // Get shift templates for default clock times
    $shiftQuery = "SELECT shift_id, start_time, end_time FROM shift_templates";
    $shiftResult = $conn->query($shiftQuery);
    
    $shiftTimes = [];
    while ($row = $shiftResult->fetch_assoc()) {
        $shiftTimes[$row['shift_id']] = [
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time']
        ];
    }
    
    // Default times if shift not found
    $defaultStartTime = '09:00:00';
    $defaultEndTime = '17:00:00';
    
    $insertQuery = "
        INSERT INTO attendance_records 
        (employee_id, schedule_id, attendance_type, attendance_date, 
         clock_in_time, clock_out_time, notes) 
        VALUES (?, ?, 'Manual', ?, ?, ?, ?)
    ";
    
    $stmt = $conn->prepare($insertQuery);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare insert statement: " . $conn->error);
    }
    
    $insertedCount = 0;
    
    foreach ($schedules as $schedule) {
        // Only create attendance records for non-DayOff schedules
        if ($schedule['status'] !== 'DayOff') {
            $employeeId = $schedule['employee_id'];
            $scheduleId = $schedule['schedule_id'];
            $workDate = $schedule['work_date'];
            $shiftId = $schedule['shift_id'];
            
            // Get clock times from shift template or use defaults
            $clockInTime = isset($shiftTimes[$shiftId]) ? $shiftTimes[$shiftId]['start_time'] : $defaultStartTime;
            $clockOutTime = isset($shiftTimes[$shiftId]) ? $shiftTimes[$shiftId]['end_time'] : $defaultEndTime;
            
            $notes = "Auto-generated from schedule";
            
            $stmt->bind_param(
                "iissss", 
                $employeeId, 
                $scheduleId, 
                $workDate, 
                $clockInTime, 
                $clockOutTime, 
                $notes
            );
            
            $stmt->execute();
            
            if ($stmt->error) {
                throw new Exception("Failed to insert attendance record: " . $stmt->error);
            }
            
            $insertedCount++;
        }
    }
    
    $stmt->close();
    return $insertedCount;
}

$conn->close();
?> 
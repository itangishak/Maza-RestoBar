<?php
require_once 'connection.php';
header('Content-Type: application/json');
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get current date from server in YYYY-MM-DD format
$current_date = date('Y-m-d');

// First, check for conflicting records (same employee marked as both present and absent on the same day)
$check_conflicts = "
    SELECT 
        e.employee_id,
        CONCAT(u.firstname, ' ', u.lastname) AS name,
        GROUP_CONCAT(s.status ORDER BY s.status) AS statuses
    FROM schedules s
    JOIN employees e ON s.employee_id = e.employee_id
    JOIN user u ON e.user_id = u.UserId
    WHERE s.work_date = ?
    GROUP BY e.employee_id
    HAVING COUNT(DISTINCT s.status) > 1 
    AND SUM(CASE WHEN s.status = 'Present' THEN 1 ELSE 0 END) > 0
    AND SUM(CASE WHEN s.status = 'Absent' THEN 1 ELSE 0 END) > 0
";

$stmt_check = $conn->prepare($check_conflicts);
if (!$stmt_check) {
    echo json_encode(['error' => 'Prepare conflict check statement failed: ' . $conn->error]);
    exit;
}

$stmt_check->bind_param("s", $current_date);
$stmt_check->execute();
$conflict_result = $stmt_check->get_result();

// If conflicts exist, return an error with the conflicting employees
if ($conflict_result && $conflict_result->num_rows > 0) {
    $conflicts = [];
    while ($row = $conflict_result->fetch_assoc()) {
        $conflicts[] = $row['name'] . " (Status: " . $row['statuses'] . ")";
    }
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Conflicting attendance records found. An employee cannot be both present and absent on the same day.',
        'conflicts' => $conflicts
    ]);
    $stmt_check->close();
    $conn->close();
    exit;
}

$stmt_check->close();

// If no conflicts, proceed with fetching attendance records
$query = "
    SELECT 
        a.attendance_id,
        a.employee_id,
        CONCAT(u.firstname, ' ', u.lastname) AS name,
        a.clock_in_time,
        a.clock_out_time,
        a.attendance_type,
        a.attendance_date,
        s.status,
        a.notes,
        a.schedule_id
    FROM attendance_records a
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN user u ON e.user_id = u.UserId
    LEFT JOIN schedules s ON a.schedule_id = s.schedule_id
    WHERE a.attendance_date = ?
";

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare statement failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // Make sure all values are properly defined
    $data[] = [
        'attendance_id' => $row['attendance_id'] ?? '',
        'employee_id' => $row['employee_id'] ?? '',
        'name' => $row['name'] ?? 'Unknown',
        'clock_in_time' => $row['clock_in_time'] ?? '',
        'clock_out_time' => $row['clock_out_time'] ?? '',
        'attendance_type' => $row['attendance_type'] ?? '',
        'attendance_date' => $row['attendance_date'] ?? '',
        'status' => $row['status'] ?? 'Unknown',
        'notes' => $row['notes'] ?? '',
        'schedule_id' => $row['schedule_id'] ?? null
    ];
}

// Always return a success status with the data array (even if empty)
echo json_encode([
    'status' => 'success',
    'data' => $data,
    'recordsTotal' => count($data),
    'recordsFiltered' => count($data)
]);
$stmt->close();
$conn->close();

?>
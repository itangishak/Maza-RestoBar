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

// Get schedule ID from request
$scheduleId = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : 0;

if ($scheduleId <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid schedule ID'
    ]);
    exit;
}

// Fetch schedule details
$query = "
    SELECT 
        s.schedule_id,
        s.employee_id,
        s.shift_id,
        s.work_date,
        st.name AS shift_name,
        st.start_time,
        st.end_time,
        st.grace_period
    FROM 
        schedules s
        JOIN shift_templates st ON s.shift_id = st.shift_id
    WHERE 
        s.schedule_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $scheduleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Schedule not found'
    ]);
    exit;
}

$schedule = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'data' => [
        'schedule_id' => $schedule['schedule_id'],
        'employee_id' => $schedule['employee_id'],
        'shift_id' => $schedule['shift_id'],
        'work_date' => $schedule['work_date'],
        'shift_name' => $schedule['shift_name'],
        'start_time' => $schedule['start_time'],
        'end_time' => $schedule['end_time'],
        'grace_period' => $schedule['grace_period']
    ]
]);

$stmt->close();
$conn->close();
?> 
<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Check if `id` parameter is provided
if (isset($_GET['id'])) {
    $attendance_id = $_GET['id'];

    // Query to fetch the attendance record by ID, including status from schedules table
    $query = "
        SELECT 
            a.*,
            s.status 
        FROM 
            attendance_records a
        LEFT JOIN 
            schedules s ON a.schedule_id = s.schedule_id
        WHERE 
            a.attendance_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $attendance_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $attendance = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $attendance]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Attendance record not found.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request. No ID provided.']);
}

$conn->close();
?>

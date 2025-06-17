<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance_id = (int)$_POST['attendance_id'];

    $stmt = $conn->prepare("
        SELECT attendance_id, employee_id, attendance_type, attendance_date, status, clock_in_time, clock_out_time, notes 
        FROM attendance_records 
        WHERE attendance_id = ?
    ");
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
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

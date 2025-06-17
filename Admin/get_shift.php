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

// Get shift ID from request
$shift_id = isset($_GET['shift_id']) ? intval($_GET['shift_id']) : 0;

if ($shift_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid shift ID'
    ]);
    exit;
}

// Fetch shift template details
$query = "SELECT * FROM shift_templates WHERE shift_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $shift_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Shift template not found'
    ]);
    exit;
}

$shift = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'data' => [
        'shift_id' => $shift['shift_id'],
        'name' => $shift['name'],
        'start_time' => $shift['start_time'],
        'end_time' => $shift['end_time'],
        'grace_period' => $shift['grace_period']
    ]
]);

$stmt->close();
$conn->close();
?> 
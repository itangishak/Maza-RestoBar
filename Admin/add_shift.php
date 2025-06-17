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

// Check if user is Boss (has necessary privileges)
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$privStmt = $conn->prepare($privilegeQuery);
$privStmt->bind_param("i", $userId);
$privStmt->execute();
$privResult = $privStmt->get_result();

$isAuthorized = false;
if ($privResult && $row = $privResult->fetch_assoc()) {
    $isAuthorized = ($row['privilege'] === 'Boss');
}
$privStmt->close();

if (!$isAuthorized) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You do not have permission to add shift templates'
    ]);
    exit;
}

// Process the form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = isset($_POST['name']) ? trim($conn->real_escape_string($_POST['name'])) : '';
    $start_time = isset($_POST['start_time']) ? trim($conn->real_escape_string($_POST['start_time'])) : '';
    $end_time = isset($_POST['end_time']) ? trim($conn->real_escape_string($_POST['end_time'])) : '';
    $grace_period = isset($_POST['grace_period']) ? intval($_POST['grace_period']) : 0;
    
    // Validate required fields
    if (empty($name) || empty($start_time) || empty($end_time)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required'
        ]);
        exit;
    }
    
    // Validate time format
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $start_time)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid start time format'
        ]);
        exit;
    }
    
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $end_time)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid end time format'
        ]);
        exit;
    }
    
    // If time doesn't include seconds, add them
    if (substr_count($start_time, ':') === 1) {
        $start_time .= ':00';
    }
    
    if (substr_count($end_time, ':') === 1) {
        $end_time .= ':00';
    }
    
    // Validate grace period
    if ($grace_period < 0 || $grace_period > 60) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Grace period must be between 0 and 60 minutes'
        ]);
        exit;
    }
    
    // Check if shift name already exists
    $checkQuery = "SELECT COUNT(*) as count FROM shift_templates WHERE name = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row = $checkResult->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'A shift template with this name already exists'
        ]);
        exit;
    }
    $checkStmt->close();
    
    // Insert the shift template
    $query = "INSERT INTO shift_templates (name, start_time, end_time, grace_period) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $name, $start_time, $end_time, $grace_period);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Shift template added successfully',
            'shift_id' => $conn->insert_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add shift template: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 
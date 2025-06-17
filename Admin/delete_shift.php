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
        'message' => 'You do not have permission to delete shift templates'
    ]);
    exit;
}

// Process the delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $shift_id = isset($_POST['shift_id']) ? intval($_POST['shift_id']) : 0;
    
    if ($shift_id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid shift ID'
        ]);
        exit;
    }
    
    // Check if shift exists
    $checkQuery = "SELECT COUNT(*) as count FROM shift_templates WHERE shift_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $shift_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row = $checkResult->fetch_assoc();
    
    if ($row['count'] === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Shift template not found'
        ]);
        exit;
    }
    $checkStmt->close();
    
    // Check if this shift is used in schedules
    $usageQuery = "SELECT COUNT(*) as count FROM schedules WHERE shift_id = ?";
    $usageStmt = $conn->prepare($usageQuery);
    $usageStmt->bind_param("i", $shift_id);
    $usageStmt->execute();
    $usageResult = $usageStmt->get_result();
    $usageRow = $usageResult->fetch_assoc();
    
    if ($usageRow['count'] > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'This shift template is currently used in ' . $usageRow['count'] . ' schedule(s). Please remove these schedules first or assign them to a different shift.'
        ]);
        exit;
    }
    $usageStmt->close();
    
    // Delete the shift template
    $query = "DELETE FROM shift_templates WHERE shift_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $shift_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Shift template deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete shift template: ' . $stmt->error
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
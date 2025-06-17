<?php
session_start(); // Start session to access user data
require_once 'connection.php';

// Always send JSON headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check user's privilege
$userId = $_SESSION['UserId'];
$userIsBoss = false;

$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$privStmt = $conn->prepare($privilegeQuery);

// Check DB connection first
if (!$conn) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed: ' . mysqli_connect_error(),
    ]);
    exit;
}

// Check user privilege
if ($privStmt) {
    $privStmt->bind_param("i", $userId);
    $privStmt->execute();
    $privResult = $privStmt->get_result();

    if ($privResult && $row = $privResult->fetch_assoc()) {
        $userIsBoss = ($row['privilege'] === 'Boss');
    }
    $privStmt->close();
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Failed to check user privilege: ' . $conn->error,
    ]);
    exit;
}

// Only allow Boss users to delete attendance records
if (!$userIsBoss) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'You do not have permission to delete attendance records.',
    ]);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Convert to integer, or 0 if not present
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Validate
    if ($id <= 0) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'No valid attendance ID provided.'
        ]);
        exit;
    }

    // Prepare the statement
    $stmt = $conn->prepare("DELETE FROM attendance_records WHERE attendance_id = ?");
    if (!$stmt) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Prepare failed: ' . $conn->error
        ]);
        exit;
    }

    // Bind and execute
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode([
            'status'  => 'success',
            'message' => 'Attendance deleted successfully.'
        ]);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Failed to delete attendance: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    // Not a POST request
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request method.'
    ]);
}

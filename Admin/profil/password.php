<?php
session_start();
require_once '../connection.php';

// Check if the user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to change your password.']);
    exit();
}

$userId = $_SESSION['UserId']; // Get the logged-in user's ID

// Get the form data
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$renewPassword = $_POST['renewPassword'] ?? '';

// Validate inputs
if (empty($currentPassword) || empty($newPassword) || empty($renewPassword)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

if ($newPassword !== $renewPassword) {
    echo json_encode(['success' => false, 'message' => 'The new passwords do not match.']);
    exit();
}

// Password validation: At least 8 characters, one uppercase, one digit, and one special character
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
    echo json_encode(['success' => false, 'message' => 'The new password must contain at least 8 characters, one uppercase letter, one digit, and one special character.']);
    exit();
}

// Fetch the current password from the database
$sql = "SELECT password FROM user WHERE UserId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $currentHashedPassword = $user['password'];

    // Verify if the current password is correct
    if (!password_verify($currentPassword, $currentHashedPassword)) {
        echo json_encode(['success' => false, 'message' => 'The current password is incorrect.']);
        exit();
    }

    // Hash the new password
    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the new password in the database
    $updateSql = "UPDATE user SET password = ? WHERE UserId = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('si', $newHashedPassword, $userId);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'The password has been successfully changed.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update the password.']);
    }

    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
}

$stmt->close();
$conn->close();
?>

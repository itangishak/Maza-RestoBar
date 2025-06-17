<?php
session_start();
require_once '../connection.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Check if the user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['UserId']; // Get the logged-in user's ID

// Fetch the current profile image path from the database
$sql = "SELECT image FROM user WHERE UserId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $profileImage = $user['image'];

    // Check if the user has an image (and it's not the default image)
    if (!empty($profileImage) && file_exists($profileImage)) {
        // Remove the image file from the server
        if (!unlink($profileImage)) {
            // If the file could not be deleted, return an error
            header("Location: ../accountSettings.php?error=remove_failed");
            exit();
        }
    }

    // Update the database to remove the image path
    $updateSql = "UPDATE user SET image = NULL WHERE UserId = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('i', $userId);

    if ($updateStmt->execute()) {
        // Redirect to the main profile page with success message
        header("Location: ../accountSettings.php?success=removed");
    } else {
        // Redirect to the main profile page with a database error message
        header("Location: ../accountSettings.php?error=database");
    }

    $updateStmt->close();
} else {
    // Redirect to the main profile page if no user or image is found
    header("Location: ../accountSettings.php?error=no_image");
}

$stmt->close();
$conn->close();
?>

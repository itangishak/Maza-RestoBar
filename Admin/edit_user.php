<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $user_id = (int)$_POST['user_id'];
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $privilege = htmlspecialchars(trim($_POST['privilege']));

    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($email) || empty($privilege)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Check for duplicate email for other users
    $checkEmailQuery = "SELECT * FROM user WHERE email = ? AND UserId != ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("si", $email, $user_id);
    $checkEmailStmt->execute();
    $result = $checkEmailStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists for another user.']);
        $checkEmailStmt->close();
        $conn->close();
        exit();
    }
    $checkEmailStmt->close();

    // Update user details
    $stmt = $conn->prepare("UPDATE user SET firstname = ?, lastname = ?, email = ?, privilege = ? WHERE UserId = ?");
    $stmt->bind_param("ssssi", $firstname, $lastname, $email, $privilege, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update user.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

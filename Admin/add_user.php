<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $privilege = htmlspecialchars(trim($_POST['privilege']));

    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($email) || empty($_POST['password']) || empty($privilege)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Check for duplicate email
    $checkEmailQuery = "SELECT * FROM user WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailQuery);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $result = $checkEmailStmt->get_result();
    $img="./assets/img/default-profile.jpg";

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
        $checkEmailStmt->close();
        $conn->close();
        exit();
    }
    $checkEmailStmt->close();

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO user (firstname, lastname, email, password, privilege,image,reg_date) VALUES (?, ?, ?, ?, ?,?, NOW())");
    $stmt->bind_param("ssssss", $firstname, $lastname, $email, $password, $privilege,$img);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add user.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

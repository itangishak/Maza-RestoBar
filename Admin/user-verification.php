<?php

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the posted form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare the SQL query to check if the user exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the user row from the database
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if the password matches
        if (password_verify($password, $user['password'])) {
            // Password is correct, so create session variables
            $_SESSION['UserId'] = $user['UserId'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['lastname'] = $user['lastname'];
            $_SESSION['privilege'] = $user['privilege'];

            // Redirect to a dashboard or another page
            header("Location: dashboard.php");
            exit();
        } else {
            // Incorrect password, show an error message
            $login_error = "Invalid email or password.";
        }
    } else {
        // Email not found, show an error message
        $login_error = "Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>
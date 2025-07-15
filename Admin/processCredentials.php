<?php
session_start();

require_once "./connection.php";

header('Content-Type: application/json'); // Ensure JSON is returned

$link = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get credentials from form (make sure the input is sanitized)
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Check if connection is successful
        if ($conn->connect_error) {
            die(json_encode(array("error" => "Database connection failed: " . $conn->connect_error)));
        }

        // SQL query using prepared statement
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        
        // Check if preparation is successful
        if (!$stmt) {
            echo json_encode(array("error" => "SQL prepare error: " . $conn->error));
            exit();
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // output data of each row
            $user = $result->fetch_assoc();

            // Check if password matches the stored hash
            if (password_verify($password, $user['password'])) {
                // Store session variables
                $_SESSION['UserId'] = $user['UserId'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['lastname'] = $user['lastname'];
                $_SESSION['privilege'] = $user['privilege'];
                $_SESSION['last_activity'] = time(); 

                // Log the activity
                $activity = "User " . $user['firstname'] . " logged in successfully.";
                $log_stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");

                if ($log_stmt) {
                    $log_stmt->bind_param("is", $user['UserId'], $activity);
                    $log_stmt->execute();
                    $log_stmt->close();
                } else {
                    error_log("Activity log error: " . $conn->error);
                }

                // Check user role to continue
                if ($user['privilege'] === 'Manager') {
                    // Return link to manager dashboard
                    $_SESSION['privilege'] = $user['privilege'];
                    $link = array("link" => "./manager-dashboard.php?link=$user[UserId]");
                } elseif ($user['privilege'] === 'Boss') {
                    $_SESSION['login'] = true;
                    $_SESSION['privilege'] = $user['privilege'];
                    $link = array("link" => "./boss-dashboard.php?link=$user[UserId]");
                } elseif ($user['privilege'] === 'Storekeeper') {
                    $_SESSION['privilege'] = $user['privilege'];
                    $link = array("link" => "./stock-dashboard.php?link=$user[UserId]");
                } else {
                    $_SESSION['privilege'] = $user['privilege'];
                    $link = array("link" => "./employee-dashboard.php?link=$user[UserId]");
                }
                echo json_encode($link);
            } else {
                echo json_encode(array("error" => "password"));
            }
        } else {
            echo json_encode(array("error" => "username"));
        }
    } catch (Exception $e) {
        // Log exception and return a generic error message
        error_log("Exception occurred: " . $e->getMessage());
        echo json_encode(array("error" => "An unexpected error occurred. Please try again later."));
    }

    // Clean up the statement and connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>

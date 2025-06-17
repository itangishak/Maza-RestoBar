<?php
session_start();

// Check if the timeout parameter exists
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    // Log the timeout reason (optional)
    error_log("User logged out due to inactivity: UserID=" . ($_SESSION['UserId'] ?? 'Unknown'));
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear the session cookie if needed
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header("Location: ../login.php?timeout=1");
exit();
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (!isset($_SESSION['UserId'])) {
        header("Location: login.php");
        exit();
    }
}

function require_privilege(array $allowed) {
    require_login();
    $priv = $_SESSION['privilege'] ?? '';
    if (!in_array($priv, $allowed)) {
        header("Location: dashboard.php");
        exit();
    }
}
?>

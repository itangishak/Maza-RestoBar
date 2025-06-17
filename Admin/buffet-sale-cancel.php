<?php
session_start();
require_once 'connection.php';

// Check if user is logged in and has Boss privilege
if (!isset($_SESSION['UserId'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'Boss') {
    header('Location: dashboard.php?error=access_denied');
    exit();
}

// Check if form was submitted with required fields
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['buffet_item_id']) || !isset($_POST['reason']) || empty($_POST['reason'])) {
    header('Location: buffet-reporting.php?error=invalid_request');
    exit();
}

$buffet_item_id = $_POST['buffet_item_id'];
$reason = trim($_POST['reason']);
$user_id = $_SESSION['UserId'];

// Begin transaction
if (!$conn->begin_transaction()) {
    die("Failed to start transaction: " . $conn->error);
}

try {
    // Store offline data in case of connection issues
    $offline_data = [
        'action' => 'cancel_buffet_sale',
        'buffet_item_id' => $buffet_item_id,
        'reason' => $reason,
        'user_id' => $user_id,
        'timestamp' => time()
    ];
    
    // Create a cookie with the offline data (1 hour expiration)
    setcookie('pending_buffet_cancel', json_encode($offline_data), time() + 3600, '/');
    
    // 1. Check if sale exists and is active
    $check_query = "SELECT * FROM buffet_sale_items WHERE buffet_item_id = ? AND status = 'active'";
    $check_stmt = $conn->prepare($check_query);
    if (!$check_stmt) {
        throw new Exception("Failed to prepare check query: " . $conn->error);
    }
    $check_stmt->bind_param('i', $buffet_item_id);
    if (!$check_stmt->execute()) {
        throw new Exception("Failed to execute check query: " . $check_stmt->error);
    }
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Sale not found or already cancelled
        $conn->rollback();
        // Clear offline data cookie
        setcookie('pending_buffet_cancel', '', time() - 3600, '/');
        header('Location: buffet-reporting.php?error=invalid_sale');
        exit();
    }
    
    $sale_data = $result->fetch_assoc();
    
    // 2. Update sale status to canceled
    $update_query = "UPDATE buffet_sale_items SET status = 'canceled', updated_at = NOW() WHERE buffet_item_id = ?";
    $update_stmt = $conn->prepare($update_query);
    if (!$update_stmt) {
        throw new Exception("Failed to prepare update query: " . $conn->error);
    }
    $update_stmt->bind_param('i', $buffet_item_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update sale status: " . $update_stmt->error);
    }
    
    // 3. Create cancellation_reasons table if it doesn't exist
    $create_table_query = "CREATE TABLE IF NOT EXISTS cancellation_reasons (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           buffet_item_id INT NOT NULL,
                           user_id INT NOT NULL,
                           reason TEXT NOT NULL,
                           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )";
    
    if (!$conn->query($create_table_query)) {
        throw new Exception("Failed to create cancellation_reasons table: " . $conn->error);
    }
    
    // 4. Insert cancellation reason
    $reason_query = "INSERT INTO cancellation_reasons 
                    (buffet_item_id, user_id, reason) 
                    VALUES (?, ?, ?)";
    $reason_stmt = $conn->prepare($reason_query);
    if (!$reason_stmt) {
        throw new Exception("Failed to prepare reason query: " . $conn->error);
    }
    $reason_stmt->bind_param('iis', $buffet_item_id, $user_id, $reason);
    
    if (!$reason_stmt->execute()) {
        throw new Exception("Failed to record cancellation reason: " . $reason_stmt->error);
    }
    
    // If we made it here, commit the transaction
    $conn->commit();
    
    // Clear offline data cookie on success
    setcookie('pending_buffet_cancel', '', time() - 3600, '/');
    
    // Log the successful cancellation
    $log_message = "User {$_SESSION['UserId']} (privilege: {$_SESSION['privilege']}) canceled buffet sale #{$buffet_item_id} with reason: {$reason}";
    error_log($log_message);
    
    // Redirect with success message through SweetAlert
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Cancellation</title>
    <?php include_once './header.php'; ?>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebarboss.php'; ?>
    
    <div class="container main-container">
        <div class="text-center mt-5">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Processing your request...</p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we're online
            if (navigator.onLine) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Buffet sale has been successfully canceled',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'buffet-reporting.php';
                });
            } else {
                // We're offline, but the operation succeeded on the server
                Swal.fire({
                    title: 'Success!',
                    text: 'Buffet sale has been canceled. The changes will be synchronized when you are back online.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'buffet-reporting.php';
                });
            }
        });
    </script>
    
    <?php include_once './footer.php'; ?>
</body>
</html><?php
    exit();
    
} catch (Exception $e) {
    // Something went wrong, rollback the transaction
    $conn->rollback();
    
    // Log the error
    error_log("Error cancelling buffet sale: " . $e->getMessage());
    
    // Show error with SweetAlert
    ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Processing Cancellation</title>
    <?php include_once './header.php'; ?>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebarboss.php'; ?>
    
    <div class="container main-container">
        <div class="text-center mt-5">
            <div class="text-danger">
                <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
            </div>
            <p class="mt-3">An error occurred while processing your request.</p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store the error in localStorage for offline handling
            const errorData = {
                action: 'cancel_buffet_sale_error',
                buffet_item_id: <?php echo $buffet_item_id; ?>,
                error: <?php echo json_encode($e->getMessage()); ?>,
                timestamp: <?php echo time(); ?>
            };
            
            // Store in localStorage (will be available offline)
            localStorage.setItem('buffet_cancel_error', JSON.stringify(errorData));
            
            Swal.fire({
                title: 'Error!',
                text: <?php echo json_encode($e->getMessage()); ?>,
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'buffet-reporting.php';
            });
        });
    </script>
    
    <?php include_once './footer.php'; ?>
</body>
</html><?php
    exit();
}

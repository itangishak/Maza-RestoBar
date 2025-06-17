<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

// Check if user has Boss privilege
$isBoss = (
    (isset($_SESSION['Privilege']) && (strtolower($_SESSION['Privilege']) === 'boss' || $_SESSION['Privilege'] === 'Boss')) || 
    (isset($_SESSION['privilege']) && (strtolower($_SESSION['privilege']) === 'boss' || $_SESSION['privilege'] === 'Boss'))
);
if (!$isBoss) {
    error_log("Delete debt - Access denied. User ID: {$_SESSION['UserId']}, Privilege: " . 
        ($_SESSION['Privilege'] ?? $_SESSION['privilege'] ?? 'none'));
    echo json_encode(['status' => 'error', 'message' => 'Permission denied. Only Boss users can delete debts.']);
    exit;
}

// Get debt ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid debt ID']);
    exit;
}

// Delete debt
try {
    $stmt = $conn->prepare("DELETE FROM debts WHERE debt_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Debt deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Debt not found or already deleted']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete debt: ' . $conn->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
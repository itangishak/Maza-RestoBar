<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $purchase_order_id = isset($_POST['purchase_order_id']) ? (int)$_POST['purchase_order_id'] : 0;

        if ($purchase_order_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Purchase Order ID.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM purchase_records WHERE purchase_id = ?");
        $stmt->bind_param("i", $purchase_order_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Purchase Order deleted successfully.']);
        } else {
            $error = $stmt->error;
            if (strpos($error, 'foreign key constraint fails') !== false) {
                $message = 'Cannot delete this purchase order because it is linked to order items. Please remove or update the related records first.';
            } else {
                $message = 'Failed to delete purchase order.';
            }
            echo json_encode(['status' => 'error', 'message' => $message]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    }
} catch (mysqli_sql_exception $e) {
    $error = $e->getMessage();
    if (strpos($error, 'foreign key constraint fails') !== false) {
        $message = 'Cannot delete this purchase order because it is linked to order items. Please remove or update the related records first.';
    } else {
        $message = 'Failed to delete purchase order.';
    }
    echo json_encode(['status' => 'error', 'message' => $message]);
}

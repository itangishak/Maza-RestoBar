<?php
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;

    if (!$order_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No order ID provided.'
        ]);
        exit;
    }

    $conn->begin_transaction();
    try {
        // 1) Remove from order_details
        $sqlDetails = "DELETE FROM order_details WHERE order_id = ?";
        $stmtDetails = $conn->prepare($sqlDetails);
        $stmtDetails->bind_param("i", $order_id);
        if (!$stmtDetails->execute()) {
            throw new Exception("Failed to delete order details: " . $stmtDetails->error);
        }

        // 2) Remove from orders
        $sqlOrder = "DELETE FROM orders WHERE order_id = ?";
        $stmtOrder = $conn->prepare($sqlOrder);
        $stmtOrder->bind_param("i", $order_id);
        if (!$stmtOrder->execute()) {
            throw new Exception("Failed to delete order: " . $stmtOrder->error);
        }

        $conn->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Order deleted successfully.'
        ]);

    } catch (Exception $ex) {
        $conn->rollback();
        echo json_encode([
            'status' => 'error',
            'message' => $ex->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}

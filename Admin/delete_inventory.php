<?php
require_once 'connection.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $inventory_id = isset($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : 0;

        if ($inventory_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Inventory ID.']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM inventory_items WHERE inventory_id = ?");
        $stmt->bind_param("i", $inventory_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Inventory item deleted successfully.']);
        } else {
            // This branch may not run if an exception is thrown.
            $error = $stmt->error;
            if (strpos($error, 'foreign key constraint fails') !== false) {
                $message = 'Cannot delete this inventory item because it is linked to purchase orders. Please remove or update the related purchase order items first.';
            } else {
                $message = 'Failed to delete inventory item.';
            }
            echo json_encode(['status' => 'error', 'message' => $message]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    }
} catch (mysqli_sql_exception $e) {
    // Catch the exception and return a friendly JSON error response.
    $error = $e->getMessage();
    if (strpos($error, 'foreign key constraint fails') !== false) {
        $message = 'Cannot delete this inventory item because it is linked to purchase orders. Please remove or update the related purchase order items first.';
    } else {
        $message = 'Failed to delete inventory item.';
    }
    echo json_encode(['status' => 'error', 'message' => $message]);
}

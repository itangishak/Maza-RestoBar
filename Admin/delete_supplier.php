<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $supplier_id = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;

        if ($supplier_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Supplier ID.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->bind_param("i", $supplier_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Supplier deleted successfully.']);
        } else {
            $error = $stmt->error;
            if (strpos($error, 'foreign key constraint fails') !== false) {
                $message = 'Cannot delete this supplier because it is linked to other records. Please remove or update the related records first.';
            } else {
                $message = 'Failed to delete supplier.';
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
        $message = 'Cannot delete this supplier because it is linked to other records. Please remove or update the related records first.';
    } else {
        $message = 'Failed to delete supplier.';
    }
    echo json_encode(['status' => 'error', 'message' => $message]);
}

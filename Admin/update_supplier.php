<?php
session_start();
require_once 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'] ?? '';
    $supplier_name = htmlspecialchars(trim($_POST['supplier_name']));
    $contact_person = htmlspecialchars(trim($_POST['contact_person']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));

    if (empty($supplier_id) || empty($supplier_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier ID and Supplier Name are required.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, contact_person = ?, phone = ?, email = ?, address = ? WHERE supplier_id = ?");
    $stmt->bind_param("sssssi", $supplier_name, $contact_person, $phone, $email, $address, $supplier_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Supplier updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update supplier.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

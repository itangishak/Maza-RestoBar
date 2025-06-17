<?php
session_start();
require_once 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_name = htmlspecialchars(trim($_POST['supplier_name']));
    $contact_person = htmlspecialchars(trim($_POST['contact_person']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));

    // Validate required fields
    if (empty($supplier_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier name is required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $supplier_name, $contact_person, $phone, $email, $address);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Supplier added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add supplier.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

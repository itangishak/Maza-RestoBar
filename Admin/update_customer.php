<?php
session_start();
require_once 'connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));

    if (empty($customer_id) || empty($first_name) || empty($last_name) || empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Customer ID, First name, Last name, and Phone are required.']);
        exit;
    }

    // Check email uniqueness (if provided)
    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ? AND customer_id != ?");
        $stmt->bind_param("si", $email, $customer_id);
        $stmt->execute();
        $emailResult = $stmt->get_result();
        if ($emailResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'A customer with this email already exists.']);
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
    }

    // Check phone uniqueness
    $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE phone = ? AND customer_id != ?");
    $stmt->bind_param("si", $phone, $customer_id);
    $stmt->execute();
    $phoneResult = $stmt->get_result();
    if ($phoneResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A customer with this phone number already exists.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Update the customer record
    $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE customer_id = ?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $address, $customer_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update customer.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

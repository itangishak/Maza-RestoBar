<?php
session_start();
require_once './connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'First name, Last name, and Phone are required.']);
        exit;
    }

    // Check if email already exists (if provided)
    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'A customer with this email already exists.']);
            $stmt->close();
            $conn->close();
            exit;
        }
        $stmt->close();
    }

    // Check if phone already exists
    $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A customer with this phone number already exists.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // If we reach here, both email (if provided) and phone are unique
    $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, phone, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $address);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Customer added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add customer.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

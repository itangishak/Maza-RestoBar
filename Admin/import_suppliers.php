<?php
session_start();
require_once 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No CSV file uploaded or an upload error occurred.']);
    exit;
}

$file_path = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file_path, 'r');

if (!$handle) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to open the uploaded file.']);
    exit;
}

// Read header
$header = fgetcsv($handle);
if (!$header) {
    echo json_encode(['status' => 'error', 'message' => 'CSV file is empty or invalid.']);
    fclose($handle);
    exit;
}

$expected_headers = ['supplier_name','contact_person','phone','email','address'];
$header = array_map('strtolower', $header);

if ($header !== $expected_headers) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSV headers. Expected: supplier_name, contact_person, phone, email, address']);
    fclose($handle);
    exit;
}

// Insert rows
$inserted = 0;
$stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

while (($row = fgetcsv($handle)) !== false) {
    $supplier_name = htmlspecialchars(trim($row[0]));
    $contact_person = htmlspecialchars(trim($row[1]));
    $phone = htmlspecialchars(trim($row[2]));
    $email = htmlspecialchars(trim($row[3]));
    $address = htmlspecialchars(trim($row[4]));

    if (empty($supplier_name)) {
        // Skip if supplier_name is missing
        continue;
    }

    $stmt->bind_param("sssss", $supplier_name, $contact_person, $phone, $email, $address);
    if ($stmt->execute()) {
        $inserted++;
    }
}

fclose($handle);
$stmt->close();
$conn->close();

if ($inserted > 0) {
    echo json_encode(['status' => 'success', 'message' => "$inserted suppliers imported successfully."]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No suppliers were imported. Check the CSV content and try again.']);
}

<?php
session_start();
require_once 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Check if a file was uploaded
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No CSV file uploaded or an upload error occurred.']);
    exit;
}

// Validate the uploaded file is a CSV
$file_type = mime_content_type($_FILES['csv_file']['tmp_name']);
$allowed_types = ['text/plain', 'text/csv', 'application/vnd.ms-excel'];
if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Please upload a CSV file.']);
    exit;
}

$file_path = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file_path, 'r');

if (!$handle) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to open the uploaded file.']);
    exit;
}

// Assume the first row is header
$header = fgetcsv($handle);
if (!$header) {
    echo json_encode(['status' => 'error', 'message' => 'CSV file is empty or invalid.']);
    fclose($handle);
    exit;
}

// Expected columns: first_name, last_name, email, phone, address
$expected_headers = ['first_name', 'last_name', 'email', 'phone', 'address'];
$header = array_map('strtolower', $header);

if ($header !== $expected_headers) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSV headers. Expected: first_name, last_name, email, phone, address']);
    fclose($handle);
    exit;
}

// Insert rows into DB
$inserted = 0;
$stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, phone, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

while (($row = fgetcsv($handle)) !== false) {
    // Clean data
    $first_name = htmlspecialchars(trim($row[0]));
    $last_name  = htmlspecialchars(trim($row[1]));
    $email      = htmlspecialchars(trim($row[2]));
    $phone      = htmlspecialchars(trim($row[3]));
    $address    = htmlspecialchars(trim($row[4]));

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($phone)) {
        // Skip rows with missing required fields
        continue;
    }

    $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $address);

    if ($stmt->execute()) {
        $inserted++;
    }
}

fclose($handle);
$stmt->close();
$conn->close();

if ($inserted > 0) {
    echo json_encode(['status' => 'success', 'message' => "$inserted customers imported successfully."]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No customers were imported. Check the CSV content and try again.']);
}

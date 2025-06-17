<?php
require_once 'connection.php'; // Include the database connection file

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the connection is valid
if (!isset($conn)) {
    die(json_encode(['error' => 'Database connection not initialized']));
}

// SQL query to fetch supplier data
$sql = "SELECT supplier_id, supplier_name FROM suppliers";
$result = $conn->query($sql);

// Check if the query succeeded
if ($result) {
    $suppliers = [];
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
    echo json_encode($suppliers); // Return the suppliers as JSON
} else {
    echo json_encode(['error' => 'Failed to fetch suppliers: ' . $conn->error]);
}

// Close the database connection
$conn->close();
?>

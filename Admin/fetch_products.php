<?php
require_once 'connection.php'; // Include the connection file

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the connection is valid
if (!isset($conn)) {
    die(json_encode(['error' => 'Database connection not initialized']));
}

// SQL query to fetch inventory items
$sql = "SELECT inventory_id, item_name FROM inventory_items";
$result = $conn->query($sql);

// Check if query succeeded
if ($result) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode($products); // Return products as JSON
} else {
    echo json_encode(['error' => 'Failed to fetch products: ' . $conn->error]);
}

// Close the database connection
$conn->close();
?>

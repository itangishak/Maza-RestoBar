<?php
require_once 'connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Retrieve the inventory_id from the query string
$inventory_id = $_GET['inventory_id'] ?? null;

// Validate the inventory_id
if (!$inventory_id || !is_numeric($inventory_id)) {
    echo json_encode(['error' => 'Invalid product ID.']);
    exit();
}

// Prepare the SQL query to fetch product details with unit name
$sql = "SELECT 
            u.unit_name AS unit, 
            i.quantity_in_stock, 
            i.unit_cost 
        FROM inventory_items i
        LEFT JOIN units u ON i.unit = u.unit_id
        WHERE i.inventory_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Bind the parameter
    $stmt->bind_param("i", $inventory_id);

    // Execute the statement
    $stmt->execute();

    // Fetch the result
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Return product details as JSON
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Product not found.']);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
}

// Close the database connection
$conn->close();
?>

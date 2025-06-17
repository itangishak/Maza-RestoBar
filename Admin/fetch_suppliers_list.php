<?php
require_once 'connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html');

// Query to fetch supplier list
$query = "SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    // Output each supplier as an <option>
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['supplier_id']) . '">' . htmlspecialchars($row['supplier_name']) . '</option>';
    }
}
// Add "Add New Supplier" option
echo '<option value="add_new">Add New Supplier...</option>';

$conn->close();
?>

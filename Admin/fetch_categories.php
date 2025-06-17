<?php
require_once 'connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html');

$query = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['category_id']) . '">' . htmlspecialchars($row['category_name']) . '</option>';
    }
}
// Add "Add New Category" option at the end
echo '<option value="add_new">Add New Category...</option>';

$conn->close();
?>

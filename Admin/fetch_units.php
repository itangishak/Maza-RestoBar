<?php
require_once 'connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html');

$query = "SELECT unit_id, unit_name FROM units ORDER BY unit_name ASC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['unit_id']) . '">' . htmlspecialchars($row['unit_name']) . '</option>';
    }
}
// Add "Add New Unit" option at the end
echo '<option value="add_new">Add New Unit...</option>';

$conn->close();
?>

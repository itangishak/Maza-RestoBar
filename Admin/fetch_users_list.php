<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Query to fetch all users
$query = "SELECT UserId, CONCAT(firstname, ' ', lastname) AS name, email, privilege, image, reg_date FROM user";
$result = $conn->query($query);

if ($result) {
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['data' => $data]); // Wrap the data in a 'data' key
} else {
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
}

$conn->close();
?>

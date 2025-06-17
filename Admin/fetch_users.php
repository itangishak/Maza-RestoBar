<?php
require_once 'connection.php';
header('Content-Type: application/json');

$query = "SELECT UserId, CONCAT(firstname, ' ', lastname) AS name FROM user ORDER BY firstname ASC";
$result = $conn->query($query);

if ($result) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $users]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch users.']);
}

$conn->close();
?>

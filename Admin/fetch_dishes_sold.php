<?php
require_once 'connection.php';

$date = $_POST['date'];
$time_of_day = $_POST['time_of_day'];

$sql = "SELECT COUNT(*) AS dishes_sold FROM buffet_sale_items 
        WHERE sale_date = ? AND time_of_day = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $date, $time_of_day);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['dishes_sold' => $row['dishes_sold'] ?? 0]);

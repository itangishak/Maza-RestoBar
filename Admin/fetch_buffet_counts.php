<?php
require_once 'connection.php';

$date = date('Y-m-d');

// Query to get buffet counts by time of day
$query = "SELECT 
    COUNT(*) AS total,
    SUM(time_of_day = 'Morning') AS morning,
    SUM(time_of_day = 'Noon') AS noon,
    SUM(time_of_day = 'Evening') AS evening
FROM buffet_sale_items
WHERE sale_date = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $counts = $result->fetch_assoc()) {
    echo json_encode([
        'total' => $counts['total'],
        'morning' => $counts['morning'],
        'noon' => $counts['noon'],
        'evening' => $counts['evening'],
    ]);
} else {
    echo json_encode([
        'total' => 0,
        'morning' => 0,
        'noon' => 0,
        'evening' => 0,
    ]);
}

$stmt->close();
$conn->close();
?>

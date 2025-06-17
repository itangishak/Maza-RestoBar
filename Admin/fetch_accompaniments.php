<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

try {
    // Query to get all available accompaniments
    $query = "SELECT accompaniment_id, accompaniment_name, accompaniment_price FROM buffet_accompaniments";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $accompaniments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $accompaniments[] = [
            'accompaniment_id' => $row['accompaniment_id'],
            'accompaniment_name' => $row['accompaniment_name'],
            'accompaniment_price' => $row['accompaniment_price']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($accompaniments);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

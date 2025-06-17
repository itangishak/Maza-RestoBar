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
    // Query to get all available accompaniments from the master table
    $query = "SELECT id, name, price FROM available_accompaniments WHERE active = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $accompaniments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $accompaniments[] = [
            'accompaniment_id' => $row['id'],
            'accompaniment_name' => $row['name'],
            'accompaniment_price' => $row['price']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($accompaniments);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

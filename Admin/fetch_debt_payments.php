<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

if (!isset($_GET['debt_id']) || empty($_GET['debt_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Debt ID is required']);
    exit;
}

$debt_id = intval($_GET['debt_id']);

try {
    $query = "
        SELECT 
            dp.payment_id,
            dp.paid_amount,
            dp.paid_date,
            dp.method,
            dp.notes,
            CONCAT(u.firstname, ' ', u.lastname) AS created_by_name
        FROM 
            debt_payments dp
        JOIN 
            user u ON dp.created_by = u.UserId
        WHERE 
            dp.debt_id = ?
        ORDER BY 
            dp.paid_date DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $debt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $payments = [];
    $total_paid = 0;
    
    while ($row = $result->fetch_assoc()) {
        // Format the date
        $date = new DateTime($row['paid_date']);
        $row['formatted_date'] = $date->format('M d, Y H:i');
        
        // Format the method
        $row['formatted_method'] = ucfirst($row['method']);
        
        // Add to total
        $total_paid += floatval($row['paid_amount']);
        
        $payments[] = $row;
    }
    
    echo json_encode([
        'status' => 'success', 
        'data' => $payments,
        'total_paid' => $total_paid
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 
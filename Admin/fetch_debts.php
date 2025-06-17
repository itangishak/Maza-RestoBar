<?php
require_once 'connection.php';
header('Content-Type: application/json');

// Optional debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!$conn) {
    echo json_encode(['data' => [], 'error' => 'DB connection failed']);
    exit;
}

// Check if a type filter is provided
$type = $_REQUEST['type'] ?? '';
$whereClause = '';

// Filter by type if provided
if ($type === 'historic') {
    // For historic tab, only show fully paid debts
    $whereClause = "WHERE d.status = 'paid'";
} elseif ($type === 'unpaid') {
    // For unpaid/active tab, show all non-paid debts
    $whereClause = "WHERE d.status != 'paid'";
}

/*
  Join debts.customer_id to customers.customer_id
  We create `customer_name` by CONCAT(first_name, ' ', last_name)
  Include all fields from the updated schema
*/
$query = "
    SELECT 
        d.debt_id,
        d.customer_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        d.amount,
        d.due_date,
        d.status,
        d.notes,
        d.created_at,
        d.updated_at,
        CONCAT(u.firstname, ' ', u.lastname) AS created_by_name
    FROM debts d
    JOIN customers c ON d.customer_id = c.customer_id
    JOIN user u ON d.created_by = u.UserId
    $whereClause
    ORDER BY 
        CASE 
            WHEN d.status = 'overdue' THEN 1
            WHEN d.status = 'pending' THEN 2
            WHEN d.status = 'partial' THEN 3
            WHEN d.status = 'paid' THEN 4
            ELSE 5
        END,
        d.due_date ASC,
        d.debt_id DESC
";

$result = $conn->query($query);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Format dates for better display
        if (!empty($row['due_date'])) {
            $dueDate = new DateTime($row['due_date']);
            $row['due_date'] = $dueDate->format('Y-m-d');
            
            // Add due_date_formatted for display
            $row['due_date_formatted'] = $dueDate->format('M d, Y');
        }
        
        // Format timestamps
        if (!empty($row['created_at'])) {
            $createdAt = new DateTime($row['created_at']);
            $row['created_at_formatted'] = $createdAt->format('M d, Y H:i');
        }
        
        if (!empty($row['updated_at'])) {
            $updatedAt = new DateTime($row['updated_at']);
            $row['updated_at_formatted'] = $updatedAt->format('M d, Y H:i');
        }
        
        $data[] = $row;
    }
    $result->free();
} else {
    echo json_encode(['error' => 'SQL Error: ' . $conn->error, 'data' => []]);
    exit;
}

echo json_encode(['data' => $data]);
$conn->close();
?>
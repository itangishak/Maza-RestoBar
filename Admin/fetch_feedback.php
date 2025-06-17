<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Get DataTables request parameters
$draw = $_GET['draw'];
$start = $_GET['start']; // Pagination offset
$length = $_GET['length']; // Number of records per page
$orderColumnIndex = $_GET['order'][0]['column']; 
$orderDirection = $_GET['order'][0]['dir']; 

// Define columns for sorting (must match DataTables order)
$columns = ['f.feedback_id', 'c.first_name', 'f.rating', 'f.comments', 'f.created_at'];
$orderColumn = $columns[$orderColumnIndex] ?? 'f.created_at'; 

// Get total records count
$totalRecordsQuery = "
    SELECT COUNT(*) as total FROM feedback";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// Fetch paginated feedback data
$sql = "
    SELECT 
        f.feedback_id, 
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        f.rating, 
        f.comments, 
        f.created_at
    FROM feedback f
    LEFT JOIN customers c ON f.customer_id = c.customer_id
    ORDER BY $orderColumn $orderDirection
    LIMIT $start, $length
";

$result = $conn->query($sql);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Return JSON response
$response = [
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
];

echo json_encode($response);
?>

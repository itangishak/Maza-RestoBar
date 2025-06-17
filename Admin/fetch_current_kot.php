<?php
require_once 'connection.php';

header('Content-Type: application/json');

// Get DataTables request parameters
$draw = $_GET['draw'];
$start = $_GET['start']; // Pagination offset
$length = $_GET['length']; // Number of records per page
$orderColumnIndex = $_GET['order'][0]['column']; 
$orderDirection = $_GET['order'][0]['dir']; // "asc" or "desc"

// Define columns for sorting (must match DataTables)
$columns = ['ko.kot_id', 'ko.order_id', 'ko.created_at', 'ko.kot_status'];
$orderColumn = $columns[$orderColumnIndex] ?? 'ko.created_at'; // Default sorting by created_at

// **Step 1: Get total records count (before filtering)**
$totalRecordsQuery = "
    SELECT COUNT(*) as total FROM kitchen_orders 
    WHERE kot_status NOT IN ('delivered', 'canceled')
";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// **Step 2: Get paginated KOT records (excluding delivered & canceled)**
$sql = "
    SELECT ko.kot_id, ko.order_id, ko.kot_status, ko.created_at
    FROM kitchen_orders ko
    WHERE ko.kot_status NOT IN ('delivered', 'canceled')
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

// **Step 3: Return JSON response**
$response = [
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords, // Total records before filtering
    "recordsFiltered" => $totalRecords, // Filtered count (excluding delivered & canceled)
    "data" => $data
];

echo json_encode($response);
?>

<?php
require_once 'connection.php';
// Enable error reporting to catch syntax issues 
error_reporting(E_ALL); 
ini_set('display_errors', 1);


// Get DataTables request parameters
$draw = $_GET['draw'];
$start = $_GET['start'];
$length = $_GET['length'];
$search = $_GET['search']['value'];
$orderColumnIndex = $_GET['order'][0]['column']; 
$orderDirection = $_GET['order'][0]['dir']; // "asc" or "desc"

// Define columns for sorting (must match your table order in DataTables)
$columns = ['o.order_id', 'c.first_name', 'o.total_price', 'o.order_date', 'o.status'];
$orderColumn = $columns[$orderColumnIndex] ?? 'o.order_id'; // Default to order_id

// **Step 1: Get total records count (before filtering)**
$totalRecordsQuery = "
    SELECT COUNT(*) as total FROM orders WHERE status IN ('confirmed', 'canceled')
";
$totalRecordsResult = $conn->query($totalRecordsQuery);
$totalRecords = $totalRecordsResult->fetch_assoc()['total'];

// **Step 2: Get filtered records count**
$searchQuery = "";
if (!empty($search)) {
    $searchQuery = " AND (o.order_id LIKE '%$search%' OR c.first_name LIKE '%$search%' OR o.status LIKE '%$search%')";
}

$totalFilteredQuery = "
    SELECT COUNT(*) as total FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.status IN ('confirmed', 'canceled') $searchQuery
";
$totalFilteredResult = $conn->query($totalFilteredQuery);
$totalFiltered = $totalFilteredResult->fetch_assoc()['total'];

// **Step 3: Get paginated records**
$sql = "
    SELECT 
        o.order_id,
        CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
        o.total_price,
        o.order_date,
        o.status
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.status IN ('confirmed', 'canceled') $searchQuery
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

// **Step 4: Return JSON response**
$response = [
    "draw" => intval($draw),
    "recordsTotal" => $totalRecords, // Total records before filtering
    "recordsFiltered" => $totalFiltered, // Total records after filtering
    "data" => $data
];

header('Content-Type: application/json');
echo json_encode($response);
?>

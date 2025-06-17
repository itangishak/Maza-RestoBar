<?php
require_once 'connection.php';
header('Content-Type: application/json');
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session for Boss privilege
session_start();
if (!isset($_SESSION['UserId'])) {
    echo json_encode(['error' => 'Authentication required', 'data' => []]);
    exit;
}

// Verify privilege
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$privStmt = $conn->prepare($privilegeQuery);
$privStmt->bind_param("i", $userId);
$privStmt->execute();
$privResult = $privStmt->get_result();

$isBoss = false;
if ($privResult && $row = $privResult->fetch_assoc()) {
    $isBoss = ($row['privilege'] === 'Boss');
}
$privStmt->close();

// If not a Boss, return empty result
if (!$isBoss) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Insufficient privileges',
        'data' => [],
        'recordsTotal' => 0,
        'recordsFiltered' => 0
    ]);
    exit;
}

// Get DataTables parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 2; // Default to date column
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';

// Get custom date range parameters
$startDate = isset($_POST['start_date']) && !empty($_POST['start_date']) ? $_POST['start_date'] : null;
$endDate = isset($_POST['end_date']) && !empty($_POST['end_date']) ? $_POST['end_date'] : null;

// First, check for conflicting records where an employee is both present and absent on the same day
$conflict_query = "
    SELECT 
        e.employee_id,
        CONCAT(u.firstname, ' ', u.lastname) AS name,
        s.work_date as attendance_date,
        GROUP_CONCAT(s.status ORDER BY s.status) AS statuses
    FROM schedules s
    JOIN employees e ON s.employee_id = e.employee_id
    JOIN user u ON e.user_id = u.UserId
    WHERE 1=1
";

$conflict_params = [];
$conflict_types = "";

if ($startDate) {
    $conflict_query .= " AND s.work_date >= ?";
    $conflict_params[] = $startDate;
    $conflict_types .= "s";
}

if ($endDate) {
    $conflict_query .= " AND s.work_date <= ?";
    $conflict_params[] = $endDate;
    $conflict_types .= "s";
}

$conflict_query .= " 
    GROUP BY e.employee_id, s.work_date
    HAVING COUNT(DISTINCT s.status) > 1 
    AND SUM(CASE WHEN s.status = 'Present' THEN 1 ELSE 0 END) > 0
    AND SUM(CASE WHEN s.status = 'Absent' THEN 1 ELSE 0 END) > 0
    LIMIT 10
";

$conflict_stmt = $conn->prepare($conflict_query);
if (!$conflict_stmt) {
    echo json_encode(['error' => 'Prepare conflict check statement failed: ' . $conn->error]);
    exit;
}

if (!empty($conflict_types)) {
    $conflict_stmt->bind_param($conflict_types, ...$conflict_params);
}

$conflict_stmt->execute();
$conflict_result = $conflict_stmt->get_result();

// If conflicts exist, return an error with the conflicting employees
if ($conflict_result && $conflict_result->num_rows > 0) {
    $conflicts = [];
    while ($row = $conflict_result->fetch_assoc()) {
        $conflicts[] = $row['name'] . " on " . $row['attendance_date'] . " (Status: " . $row['statuses'] . ")";
    }
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Conflicting attendance records found. An employee cannot be both present and absent on the same day.',
        'conflicts' => $conflicts,
        'data' => [],
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'draw' => $draw
    ]);
    $conflict_stmt->close();
    $conn->close();
    exit;
}

$conflict_stmt->close();

// Build query
$query = "
    SELECT
        a.attendance_id,
        a.employee_id,
        CONCAT(u.firstname, ' ', u.lastname) AS name,
        a.clock_in_time,
        a.clock_out_time,
        a.attendance_type,
        a.attendance_date,
        s.status,
        a.notes,
        a.schedule_id
    FROM attendance_records a
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN user u ON e.user_id = u.UserId
    LEFT JOIN schedules s ON a.schedule_id = s.schedule_id
    WHERE 1=1
";

// Add date range filtering if provided
$params = [];
$types = "";

if ($startDate) {
    $query .= " AND a.attendance_date >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if ($endDate) {
    $query .= " AND a.attendance_date <= ?";
    $params[] = $endDate;
    $types .= "s";
}

// Add search functionality
if (!empty($search)) {
    $query .= " AND (CONCAT(u.firstname, ' ', u.lastname) LIKE ? OR s.status LIKE ? OR a.notes LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

// Count total records (without filtering)
$countQuery = "SELECT COUNT(*) as total FROM attendance_records";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute();
$totalRecords = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

// Count filtered records
$countFilteredQuery = "
    SELECT COUNT(*) as total 
    FROM attendance_records a
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    LEFT JOIN user u ON e.user_id = u.UserId
    LEFT JOIN schedules s ON a.schedule_id = s.schedule_id
    WHERE 1=1
";

// Add the same filtering conditions
if ($startDate) {
    $countFilteredQuery .= " AND a.attendance_date >= ?";
}

if ($endDate) {
    $countFilteredQuery .= " AND a.attendance_date <= ?";
}

if (!empty($search)) {
    $countFilteredQuery .= " AND (CONCAT(u.firstname, ' ', u.lastname) LIKE ? OR s.status LIKE ? OR a.notes LIKE ?)";
}

$countFilteredStmt = $conn->prepare($countFilteredQuery);
if (!empty($types)) {
    $countFilteredStmt->bind_param($types, ...$params);
}
$countFilteredStmt->execute();
$filteredRecords = $countFilteredStmt->get_result()->fetch_assoc()['total'];
$countFilteredStmt->close();

// Add ordering
$columns = [
    0 => 'name',
    1 => 'attendance_type',
    2 => 'attendance_date',
    3 => 'status',
    4 => 'clock_in_time',
    5 => 'clock_out_time',
    6 => 'notes'
];

if (isset($columns[$orderColumn])) {
    $query .= " ORDER BY " . $columns[$orderColumn] . " " . $orderDir;
} else {
    // Default sort
    $query .= " ORDER BY a.attendance_date DESC";
}

// Add pagination
$query .= " LIMIT ?, ?";
$params[] = $start;
$params[] = $length;
$types .= "ii";

// Execute the final query with prepared statement
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare statement failed: ' . $conn->error, 'data' => []]);
    exit;
}

if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(['error' => 'Database query failed: ' . $conn->error, 'data' => []]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    // Make sure all values are properly defined
    $data[] = [
        'attendance_id' => $row['attendance_id'] ?? '',
        'employee_id' => $row['employee_id'] ?? '',
        'name' => $row['name'] ?? 'Unknown',
        'clock_in_time' => $row['clock_in_time'] ?? '',
        'clock_out_time' => $row['clock_out_time'] ?? '',
        'attendance_type' => $row['attendance_type'] ?? '',
        'attendance_date' => $row['attendance_date'] ?? '',
        'status' => $row['status'] ?? 'Unknown',
        'notes' => $row['notes'] ?? '',
        'schedule_id' => $row['schedule_id'] ?? null
    ];
}

// Return DataTables expected format
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $totalRecords,
    'recordsFiltered' => $filteredRecords,
    'data' => $data
]);

$stmt->close();
$conn->close();
?> 
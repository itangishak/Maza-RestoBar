<?php
require_once 'connection.php';
header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid debt ID']);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        debt_id,
        customer_id,
        amount,
        due_date,
        status,
        notes
    FROM debts
    WHERE debt_id = ?
");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Debt not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
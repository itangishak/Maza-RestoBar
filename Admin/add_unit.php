<?php
require_once 'connection.php';

ob_clean(); // Clean output buffer to ensure only JSON is returned
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_name = trim(htmlspecialchars($_POST['unit_name'] ?? ''));

    if (empty($unit_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Unit name is required.']);
        exit();
    }

    // Check for duplicate unit
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM units WHERE LOWER(unit_name) = LOWER(?)");
    $check_stmt->bind_param("s", $unit_name);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Unit already exists.']);
        exit();
    }

    // Insert new unit
    $insert_stmt = $conn->prepare("INSERT INTO units (unit_name) VALUES (?)");
    $insert_stmt->bind_param("s", $unit_name);

    if ($insert_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Unit added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add unit.']);
    }

    $insert_stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

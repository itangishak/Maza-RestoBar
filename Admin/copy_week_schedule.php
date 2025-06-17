<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check if user is Boss (has necessary privileges)
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$privStmt = $conn->prepare($privilegeQuery);
$privStmt->bind_param("i", $userId);
$privStmt->execute();
$privResult = $privStmt->get_result();

$isAuthorized = false;
if ($privResult && $row = $privResult->fetch_assoc()) {
    $isAuthorized = ($row['privilege'] === 'Boss');
}
$privStmt->close();

if (!$isAuthorized) {
    echo json_encode([
        'status' => 'error',
        'message' => 'You do not have permission to copy schedules'
    ]);
    exit;
}

// Process the form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $sourceStartDate = isset($_POST['source_start_date']) ? trim($conn->real_escape_string($_POST['source_start_date'])) : '';
    $sourceEndDate = isset($_POST['source_end_date']) ? trim($conn->real_escape_string($_POST['source_end_date'])) : '';
    $targetStartDate = isset($_POST['target_start_date']) ? trim($conn->real_escape_string($_POST['target_start_date'])) : '';
    $employeeId = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    
    // Validate required fields
    if (empty($sourceStartDate) || empty($sourceEndDate) || empty($targetStartDate)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All date fields are required'
        ]);
        exit;
    }
    
    // Validate date formats
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sourceStartDate) || 
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sourceEndDate) || 
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $targetStartDate)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid date format. Please use YYYY-MM-DD'
        ]);
        exit;
    }
    
    // Calculate how many days are in the source range
    $sourceDateDiff = date_diff(date_create($sourceStartDate), date_create($sourceEndDate));
    $daysInRange = $sourceDateDiff->days;
    
    // Calculate the target end date
    $targetEndDate = date('Y-m-d', strtotime($targetStartDate . ' + ' . $daysInRange . ' days'));
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Fetch source schedules
        $sourceQuery = "SELECT employee_id, shift_id, DATE_FORMAT(work_date, '%w') AS day_of_week 
                        FROM schedules 
                        WHERE work_date BETWEEN ? AND ?";
                        
        // Add employee filter if specified
        if ($employeeId > 0) {
            $sourceQuery .= " AND employee_id = ?";
        }
        
        $sourceStmt = $conn->prepare($sourceQuery);
        
        if ($employeeId > 0) {
            $sourceStmt->bind_param("ssi", $sourceStartDate, $sourceEndDate, $employeeId);
        } else {
            $sourceStmt->bind_param("ss", $sourceStartDate, $sourceEndDate);
        }
        
        $sourceStmt->execute();
        $sourceResult = $sourceStmt->get_result();
        $schedules = [];
        
        while ($row = $sourceResult->fetch_assoc()) {
            $schedules[] = $row;
        }
        
        $sourceStmt->close();
        
        if (empty($schedules)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No schedules found in the source week'
            ]);
            $conn->rollback();
            exit;
        }
        
        // Calculate the day of week for target start date (0 = Sunday, 1 = Monday, etc.)
        $targetStartDayOfWeek = date('w', strtotime($targetStartDate));
        
        // For each source schedule, create a corresponding target schedule
        $inserted = 0;
        $skipped = 0;
        $insertQuery = "INSERT INTO schedules (employee_id, shift_id, work_date) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        
        foreach ($schedules as $schedule) {
            // Calculate the day offset from the start of the week
            $dayOfWeek = $schedule['day_of_week'];
            $dayOffset = ($dayOfWeek - $targetStartDayOfWeek + 7) % 7;
            
            // Calculate the target work date
            $targetWorkDate = date('Y-m-d', strtotime($targetStartDate . ' + ' . $dayOffset . ' days'));
            
            // Check if a schedule already exists for this employee on this date and shift
            $checkQuery = "SELECT COUNT(*) as count FROM schedules WHERE employee_id = ? AND work_date = ? AND shift_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("isi", $schedule['employee_id'], $targetWorkDate, $schedule['shift_id']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkRow = $checkResult->fetch_assoc();
            $checkStmt->close();
            
            if ($checkRow['count'] === 0) {
                // Insert the new schedule
                $insertStmt->bind_param("iis", $schedule['employee_id'], $schedule['shift_id'], $targetWorkDate);
                $insertStmt->execute();
                $inserted++;
            } else {
                $skipped++;
            }
        }
        
        $insertStmt->close();
        
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => "Schedule copied successfully: $inserted schedules copied, $skipped skipped (already existed)",
            'data' => [
                'inserted' => $inserted,
                'skipped' => $skipped
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to copy schedule: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

$conn->close();
?> 
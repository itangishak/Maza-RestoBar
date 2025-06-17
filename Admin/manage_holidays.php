<?php
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

// Check if user is logged in with Boss/Manager privileges
if (!isset($_SESSION['UserId'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check user privilege
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$stmtPrivilege = $conn->prepare($privilegeQuery);
$stmtPrivilege->bind_param("i", $userId);
$stmtPrivilege->execute();
$privilegeResult = $stmtPrivilege->get_result();

$isAuthorized = false;
if ($privilegeResult && $row = $privilegeResult->fetch_assoc()) {
    $isAuthorized = ($row['privilege'] === 'Boss' || $row['privilege'] === 'Manager');
}
$stmtPrivilege->close();

if (!$isAuthorized) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Insufficient privileges'
    ]);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    switch ($action) {
        case 'add':
            addHoliday($conn);
            break;
        case 'update':
            updateHoliday($conn);
            break;
        case 'delete':
            deleteHoliday($conn);
            break;
        case 'list':
            listHolidays($conn);
            break;
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action specified'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function addHoliday($conn) {
    $holidayName = $_POST['holiday_name'] ?? '';
    $holidayDate = $_POST['holiday_date'] ?? '';
    $isRecurring = isset($_POST['is_recurring']) ? (int)$_POST['is_recurring'] : 0;
    
    // Validate inputs
    if (empty($holidayName) || empty($holidayDate)) {
        throw new Exception("Holiday name and date are required");
    }
    
    $recurringMonth = null;
    $recurringDay = null;
    
    if ($isRecurring) {
        $dateObj = new DateTime($holidayDate);
        $recurringMonth = $dateObj->format('n'); // 1-12
        $recurringDay = $dateObj->format('j');   // 1-31
    }
    
    $query = "INSERT INTO holidays (holiday_name, holiday_date, is_recurring, recurring_month, recurring_day) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiii", $holidayName, $holidayDate, $isRecurring, $recurringMonth, $recurringDay);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Holiday added successfully',
            'holiday_id' => $conn->insert_id
        ]);
    } else {
        throw new Exception("Failed to add holiday: " . $conn->error);
    }
    
    $stmt->close();
}

function updateHoliday($conn) {
    $holidayId = isset($_POST['holiday_id']) ? (int)$_POST['holiday_id'] : 0;
    $holidayName = $_POST['holiday_name'] ?? '';
    $holidayDate = $_POST['holiday_date'] ?? '';
    $isRecurring = isset($_POST['is_recurring']) ? (int)$_POST['is_recurring'] : 0;
    
    // Validate inputs
    if ($holidayId <= 0 || empty($holidayName) || empty($holidayDate)) {
        throw new Exception("Holiday ID, name and date are required");
    }
    
    $recurringMonth = null;
    $recurringDay = null;
    
    if ($isRecurring) {
        $dateObj = new DateTime($holidayDate);
        $recurringMonth = $dateObj->format('n');
        $recurringDay = $dateObj->format('j');
    }
    
    $query = "UPDATE holidays 
              SET holiday_name = ?, holiday_date = ?, is_recurring = ?, 
                  recurring_month = ?, recurring_day = ? 
              WHERE holiday_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiiii", $holidayName, $holidayDate, $isRecurring, 
                       $recurringMonth, $recurringDay, $holidayId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Holiday updated successfully'
        ]);
    } else {
        throw new Exception("Failed to update holiday: " . $conn->error);
    }
    
    $stmt->close();
}

function deleteHoliday($conn) {
    $holidayId = isset($_POST['holiday_id']) ? (int)$_POST['holiday_id'] : 0;
    
    if ($holidayId <= 0) {
        throw new Exception("Holiday ID is required");
    }
    
    $query = "DELETE FROM holidays WHERE holiday_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $holidayId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Holiday deleted successfully'
        ]);
    } else {
        throw new Exception("Failed to delete holiday: " . $conn->error);
    }
    
    $stmt->close();
}

function listHolidays($conn) {
    $year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
    
    // Get all one-time holidays for this year
    $query = "SELECT * FROM holidays WHERE 
              (is_recurring = 0 AND YEAR(holiday_date) = ?) OR 
              (is_recurring = 1) 
              ORDER BY MONTH(holiday_date), DAY(holiday_date)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $holidays = [];
    while ($row = $result->fetch_assoc()) {
        // For recurring holidays, adjust the date to the current year
        if ($row['is_recurring']) {
            $month = $row['recurring_month'];
            $day = $row['recurring_day'];
            
            // Check if this day exists in the current year (e.g., Feb 29)
            if (checkdate($month, $day, $year)) {
                $row['holiday_date'] = $year . '-' . 
                                       str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . 
                                       str_pad($day, 2, '0', STR_PAD_LEFT);
            } else {
                // Skip invalid dates (e.g., Feb 29 in non-leap years)
                continue;
            }
        }
        
        $holidays[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'holidays' => $holidays
    ]);
    
    $stmt->close();
}

$conn->close();
?> 
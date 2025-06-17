<?php
// Prevent PHP errors from corrupting JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't output errors to browser

// Include database connection
require_once './connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and has the right privilege
if (!isset($_SESSION['UserId']) || !isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'Boss') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Get the sale date and time of day from the request
$sale_date = isset($_GET['sale_date']) ? $_GET['sale_date'] : null;
$time_of_day = isset($_GET['time_of_day']) ? $_GET['time_of_day'] : null;

// Ensure time_of_day is one of the valid enum values with exact casing
$valid_time_periods = ['Morning', 'Noon', 'Evening'];
if ($time_of_day) {
    // Check if time_of_day exactly matches one of our valid values
    if (!in_array($time_of_day, $valid_time_periods)) {
        // Try case-insensitive matching and fix the case
        foreach ($valid_time_periods as $valid_period) {
            if (strtolower($time_of_day) === strtolower($valid_period)) {
                $time_of_day = $valid_period; // Use the correctly cased value
                error_log("Fixed time_of_day casing to: $time_of_day");
                break;
            }
        }
    }
}

// Ensure consistent date format by parsing and reformatting
if ($sale_date) {
    // Parse the input date and reformat to ensure YYYY-MM-DD format
    $date_obj = new DateTime($sale_date);
    $sale_date = $date_obj->format('Y-m-d');
    error_log("Formatted sale date: $sale_date");
}

// Validate inputs
if (!$time_of_day) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

try {
    // Default price if no data exists
    $buffet_price = 0;
    
    // Generate a DEBUG ID to track this specific request
    $debug_id = uniqid('PRICE_DEBUG_');
    error_log("$debug_id - START DEBUG for buffet price");
    error_log("$debug_id - Parameters: date='$sale_date', time_of_day='$time_of_day'");
    
    // Check if we should look for a specific date or just the most recent price
    if ($sale_date) {
        // Query to get the price from buffet_sale_items for this exact date and time of day
        $stmt = $conn->prepare("
            SELECT price
            FROM buffet_sale_items
            WHERE sale_date = ? AND time_of_day = ? AND status = 'active'
            ORDER BY buffet_item_id DESC
            LIMIT 1
        ");
        $stmt->bind_param('ss', $sale_date, $time_of_day);
    } else {
        // If no date provided, get the most recent price for this time of day
        $stmt = $conn->prepare("
            SELECT price
            FROM buffet_sale_items
            WHERE time_of_day = ? AND status = 'active'
            ORDER BY sale_date DESC, buffet_item_id DESC
            LIMIT 1
        ");
        $stmt->bind_param('s', $time_of_day);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $buffet_price = $row['price'];
        error_log("$debug_id - Found price: $buffet_price");
    } else {
        // If no matching record found, get the most recent price for this time of day
        $stmt = $conn->prepare("
            SELECT price
            FROM buffet_sale_items
            WHERE time_of_day = ? AND status = 'active'
            ORDER BY sale_date DESC, buffet_item_id DESC
            LIMIT 1
        ");
        $stmt->bind_param('s', $time_of_day);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $buffet_price = $row['price'];
            error_log("$debug_id - Found fallback price: $buffet_price");
        } else {
            // If still no price found, default to a standard price based on time of day
            switch ($time_of_day) {
                case 'Morning':
                    $buffet_price = 15000.00;
                    break;
                case 'Noon':
                    $buffet_price = 20000.00;
                    break;
                case 'Evening':
                    $buffet_price = 20000.00;
                    break;
                default:
                    $buffet_price = 15000.00;
            }
            error_log("$debug_id - Using default price: $buffet_price");
        }
    }
    
    // Return the result
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'price' => $buffet_price,
        'currency' => 'BIF'
    ]);
    
} catch (Exception $e) {
    error_log("$debug_id - ERROR: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

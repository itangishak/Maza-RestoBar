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
if (!$sale_date || !$time_of_day) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

try {
    // Default dish count should be 0 if no data exists
    $dishes_sold = 0;
    
    // Generate a DEBUG ID to track this specific request
    $debug_id = uniqid('DISH_DEBUG_');
    error_log("$debug_id - START DEBUG for dishes sold calculation");
    error_log("$debug_id - Parameters: date='$sale_date', time_of_day='$time_of_day'");
    
    // Direct query for debugging
    $debug_query = "SELECT * FROM buffet_sale_items WHERE sale_date = '$sale_date' AND time_of_day = '$time_of_day' AND status = 'active'";
    $debug_result = $conn->query($debug_query);
    error_log("$debug_id - Direct query: $debug_query");
    error_log("$debug_id - Direct query found " . $debug_result->num_rows . " rows");
    
    // List all matching records for debugging
    while($row = $debug_result->fetch_assoc()) {
        error_log("$debug_id - Found matching record: ID={$row['buffet_item_id']}, Date={$row['sale_date']}, Time={$row['time_of_day']}, Status={$row['status']}");
    }
    
    // Also check other dates in the table for comparison
    $all_dates_query = "SELECT DISTINCT sale_date, time_of_day, COUNT(*) as count FROM buffet_sale_items WHERE status = 'active' GROUP BY sale_date, time_of_day";
    $all_dates_result = $conn->query($all_dates_query);
    error_log("$debug_id - All dates in the table:");
    while($row = $all_dates_result->fetch_assoc()) {
        error_log("$debug_id - Available: Date={$row['sale_date']}, Time={$row['time_of_day']}, Count={$row['count']}");
    }
    
    // Now do the actual prepared statement query
    $count_stmt = $conn->prepare("
        SELECT COUNT(*) as sale_count
        FROM buffet_sale_items
        WHERE sale_date = ? AND time_of_day = ? AND status = 'active'
    ");
    $count_stmt->bind_param('ss', $sale_date, $time_of_day);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    
    error_log("$debug_id - Prepared statement found count: " . ($count_row ? $count_row['sale_count'] : 'none'));
    
    // Count the ACTUAL number of dishes sold (records in the table)
    if ($count_row && intval($count_row['sale_count']) > 0) {
        $dishes_sold = intval($count_row['sale_count']); // Use the actual count from the database
        error_log("$debug_id - Setting dishes_sold to $dishes_sold based on {$count_row['sale_count']} active sales");
    } else {
        $dishes_sold = 0; // Force it to be 0 when no records exist
        error_log("$debug_id - Keeping dishes_sold as 0 because no active sales found");
    }
    
    // Double-check with a direct query to be absolutely sure
    $direct_check = $conn->query("SELECT COUNT(*) as direct_count FROM buffet_sale_items WHERE sale_date = '$sale_date' AND time_of_day = '$time_of_day' AND status = 'active'");
    $direct_row = $direct_check->fetch_assoc();
    
    error_log("$debug_id - DIRECT CHECK found {$direct_row['direct_count']} records");
    
    // If direct check finds records but our prepared statement didn't, override with the direct result
    if ($direct_row && intval($direct_row['direct_count']) > 0 && $dishes_sold == 0) {
        $dishes_sold = 1;
        error_log("$debug_id - OVERRIDE: Setting dishes_sold to 1 based on direct query results");
    }
    
    error_log("$debug_id - FINAL dishes_sold value: $dishes_sold");
    error_log("$debug_id - END DEBUG");
    
    // Return the count directly - we want 1 if there's already a sale for this date/time
    // We don't need historical data because the current implementation is confusing
    
    // Return the result
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'dishes_sold' => $dishes_sold
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

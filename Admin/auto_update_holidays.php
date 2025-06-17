<?php
// This file can be run via cron job to automatically update attendance for unassigned shifts

require_once '../connection.php';
require_once '../includes/holiday_functions.php';

// Set execution time limit to handle large datasets
set_time_limit(300);

try {
    // Update all attendance records based on schedule
    $updatedRecords = updateDaysOffAttendance($conn);
    
    echo "Success: Updated $updatedRecords attendance records from 'Absent' to 'Holiday'.\n";
    
    // Log the results
    $logMessage = date('Y-m-d H:i:s') . " - Auto update completed. Updated $updatedRecords records.\n";
    file_put_contents('days_off_update_log.txt', $logMessage, FILE_APPEND);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Log the error
    $errorLog = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
    file_put_contents('days_off_update_error_log.txt', $errorLog, FILE_APPEND);
}

$conn->close();
?> 
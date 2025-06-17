<?php
// Set headers to prevent caching and set content type
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the current server date and time
$current_date = date('Y-m-d');  // Format: YYYY-MM-DD
$current_time = date('H:i');    // Format: HH:MM (24-hour format)

// Return as JSON response
echo json_encode([
    'status' => 'success',
    'date' => $current_date,
    'time' => $current_time,
    'timestamp' => time(),
    'timezone' => date_default_timezone_get()
]); 
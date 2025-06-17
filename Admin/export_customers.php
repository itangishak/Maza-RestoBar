<?php
session_start();
require_once 'connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CSV file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customers.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Write column headers
fputcsv($output, ['Name', 'Email', 'Phone', 'Address', 'Date Joined', 'Total Orders', 'Total Revenue']);

// Fetch customer data
$query = "
    SELECT 
        CONCAT(first_name, ' ', last_name) AS name,
        email,
        phone,
        address,
        DATE_FORMAT(created_at, '%Y/%m/%d') AS joined_date,
        COUNT(o.order_id) AS total_orders,
        COALESCE(SUM(o.total_amount), 0) AS total_revenue
    FROM customers c
    LEFT JOIN orders o ON c.customer_id = o.customer_id
    GROUP BY c.customer_id
    ORDER BY c.first_name, c.last_name
";

$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Write data to CSV
        fputcsv($output, [
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['address'],
            $row['joined_date'],
            $row['total_orders'],
            '$' . number_format($row['total_revenue'], 2),
        ]);
    }
} else {
    // If query fails, output an error message
    fputcsv($output, ['Error fetching data.']);
}

fclose($output);
$conn->close();

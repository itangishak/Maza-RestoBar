<?php
session_start();
require_once 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=suppliers.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Supplier Name', 'Contact Person', 'Phone', 'Email', 'Address', 'Created Date']);

$query = "
    SELECT 
        supplier_name,
        contact_person,
        phone,
        email,
        address,
        DATE_FORMAT(created_at, '%Y/%m/%d') AS created_date
    FROM suppliers
    ORDER BY supplier_name
";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['supplier_name'],
        $row['contact_person'],
        $row['phone'],
        $row['email'],
        $row['address'],
        $row['created_date']
    ]);
}

fclose($output);
$conn->close();

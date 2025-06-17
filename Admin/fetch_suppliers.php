<?php
require_once 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$query = "
    SELECT 
        supplier_id,
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

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        htmlspecialchars($row['supplier_name']),
        htmlspecialchars($row['contact_person']),
        htmlspecialchars($row['phone']),
        htmlspecialchars($row['email']),
        htmlspecialchars($row['address']),
        htmlspecialchars($row['created_date']),
        // Action buttons with icons for edit and delete
        '<button class="btn btn-primary btn-sm edit-btn" data-id="' . $row['supplier_id'] . '"><i class="bi bi-pencil"></i></button> 
         <button class="btn btn-danger btn-sm delete-btn" data-id="' . $row['supplier_id'] . '"><i class="bi bi-trash"></i></button>'
    ];
}

echo json_encode(["data" => $data]);

<?php
require_once 'connection.php';

$category_id = $_GET['category_id'];
$query = "SELECT * FROM menu_categories WHERE category_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $category_id);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_assoc());
?>

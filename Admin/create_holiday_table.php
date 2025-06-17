<?php
session_start();
require_once 'connection.php';

// Check if user is logged in with Boss/Manager privileges
if (!isset($_SESSION['UserId'])) {
    echo "Authentication required";
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
    echo "Insufficient privileges";
    exit;
}

// Drop old holidays table if it exists
$dropTableSQL = "DROP TABLE IF EXISTS holidays";
$conn->query($dropTableSQL);

// Create employee_time_off table
$createTableSQL = "
CREATE TABLE IF NOT EXISTS employee_time_off (
  time_off_id INT(11) NOT NULL AUTO_INCREMENT,
  employee_id INT(11) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  time_off_type ENUM('Vacation', 'Sick', 'Personal', 'Other') NOT NULL DEFAULT 'Vacation',
  approval_status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
  notes TEXT NULL,
  PRIMARY KEY (time_off_id),
  INDEX (employee_id),
  INDEX (start_date, end_date),
  CONSTRAINT fk_time_off_employee
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($createTableSQL)) {
    echo "Employee time off table created successfully.";
} else {
    echo "Error creating employee time off table: " . $conn->error;
}

$conn->close();
?> 
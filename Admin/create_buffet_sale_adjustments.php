<?php
require_once 'dbconnect.php';

// Create the buffet_sale_adjustments table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS buffet_sale_adjustments (
    adjustment_id INT(11) NOT NULL AUTO_INCREMENT,
    buffet_item_id INT(11) NOT NULL,
    adjustment_type ENUM('discount', 'surcharge', 'tax') NOT NULL DEFAULT 'discount',
    adjustment_amount DECIMAL(10,2) NOT NULL,
    adjustment_reason TEXT DEFAULT NULL,
    status ENUM('active', 'canceled') DEFAULT 'active',
    cancellation_reason TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (adjustment_id),
    FOREIGN KEY (buffet_item_id) REFERENCES buffet_sale_items(buffet_item_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table buffet_sale_adjustments created successfully or already exists";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>

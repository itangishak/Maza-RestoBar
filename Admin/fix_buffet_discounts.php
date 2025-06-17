<?php
require_once 'dbconnect.php';

// This script fixes the discount handling in the POS system

// 1. Create the buffet_sale_adjustments table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `buffet_sale_adjustments` (
  `adjustment_id` int(11) NOT NULL AUTO_INCREMENT,
  `buffet_item_id` int(11) NOT NULL,
  `adjustment_type` ENUM('discount', 'surcharge', 'tax') NOT NULL DEFAULT 'discount',
  `adjustment_amount` decimal(10,2) NOT NULL,
  `adjustment_reason` text DEFAULT NULL,
  `status` ENUM('active', 'canceled') DEFAULT 'active',
  `cancellation_reason` TEXT DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`adjustment_id`),
  FOREIGN KEY (`buffet_item_id`) REFERENCES `buffet_sale_items`(`buffet_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

$result = $conn->query($sql);
echo "Creating buffet_sale_adjustments table: " . ($result ? "Success" : "Error: " . $conn->error) . "<br>";

// 2. Find if buffet_sale_items has discount_amount and discount_reason columns
// which shouldn't be there according to the schema
$sql = "SHOW COLUMNS FROM `buffet_sale_items` LIKE 'discount%'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    echo "Found incorrect discount columns in buffet_sale_items table. These should be moved to buffet_sale_adjustments.<br>";
    
    // 3. Move any existing discount data from buffet_sale_items to buffet_sale_adjustments
    $sql = "SELECT buffet_item_id, discount_amount, discount_reason FROM buffet_sale_items 
            WHERE discount_amount > 0";
    $discountResults = $conn->query($sql);
    
    if ($discountResults && $discountResults->num_rows > 0) {
        echo "Found " . $discountResults->num_rows . " discount records to migrate.<br>";
        
        // Prepare the insert statement
        $insertSql = "INSERT INTO buffet_sale_adjustments 
                      (buffet_item_id, adjustment_type, adjustment_amount, adjustment_reason, status) 
                      VALUES (?, 'discount', ?, ?, 'active')";
        $stmt = $conn->prepare($insertSql);
        
        if ($stmt) {
            while ($row = $discountResults->fetch_assoc()) {
                $stmt->bind_param("ids", 
                    $row['buffet_item_id'], 
                    $row['discount_amount'], 
                    $row['discount_reason']
                );
                $result = $stmt->execute();
                echo "Migrated discount for buffet_item_id " . $row['buffet_item_id'] . ": " . 
                     ($result ? "Success" : "Error: " . $stmt->error) . "<br>";
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error . "<br>";
        }
    } else {
        echo "No discount data found to migrate.<br>";
    }
    
    // 4. Alter the table to remove the columns
    echo "Note: To complete this fix, you should alter the buffet_sale_items table to remove the discount_amount and discount_reason columns after verifying the data migration.<br>";
    echo "ALTER TABLE buffet_sale_items DROP COLUMN discount_amount, DROP COLUMN discount_reason;<br>";
} else {
    echo "No incorrect discount columns found in buffet_sale_items table. Schema is correct.<br>";
}

echo "<br>Now you need to update your sell_item_action.php file to use the buffet_sale_adjustments table for discounts instead of trying to update buffet_sale_items.";

$conn->close();
?>

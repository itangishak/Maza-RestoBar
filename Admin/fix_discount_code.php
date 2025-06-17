<?php
// This is a utility script to fix the discount handling in sell_item_action.php

// First, create the buffet_sale_adjustments table if it doesn't exist
$createTableSql = "CREATE TABLE IF NOT EXISTS `buffet_sale_adjustments` (
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

// Read the original file content
$original = file_get_contents('sell_item_action.php');

// Replace the incorrect discount handling code with the correct one
$search = "// 2.1 If discount was applied, record it with the sale item\n        if (isset($_POST['allow_discount']) && $_POST['allow_discount'] == 1 && $discountAmount > 0) {\n            // Update the buffet_sale_items record with discount information\n            $sqlDiscount = \"UPDATE buffet_sale_items \n                           SET discount_amount = ?, discount_reason = ? \n                           WHERE buffet_item_id = ?\";\n            $stmtDiscount = $conn->prepare($sqlDiscount);\n            if (!$stmtDiscount) {\n                throw new Exception(\"Failed to prepare discount update statement: \" . $conn->error);\n            }\n            $stmtDiscount->bind_param(\"dsi\", $discountAmount, $discountReason, $buffetItemId);\n            if (!$stmtDiscount->execute()) {\n                throw new Exception(\"Failed to update buffet item with discount: \" . $stmtDiscount->error);\n            }\n            $stmtDiscount->close();\n            error_log(\"Discount applied: Amount=$discountAmount, Reason=$discountReason\");\n        }";

$replace = "// 2.1 If discount was applied, record it in the buffet_sale_adjustments table\n        if (isset($_POST['allow_discount']) && $_POST['allow_discount'] == 1 && $discountAmount > 0) {\n            // Insert into buffet_sale_adjustments table\n            $sqlDiscount = \"INSERT INTO buffet_sale_adjustments \n                           (buffet_item_id, adjustment_type, adjustment_amount, adjustment_reason, status) \n                           VALUES (?, 'discount', ?, ?, 'active')\";\n            $stmtDiscount = $conn->prepare($sqlDiscount);\n            if (!$stmtDiscount) {\n                // Try to create the table if it doesn't exist\n                $createTableSql = \"$createTableSql\";\n                \n                if ($conn->query($createTableSql)) {\n                    error_log(\"Created buffet_sale_adjustments table and retrying...\");\n                    $stmtDiscount = $conn->prepare($sqlDiscount);\n                } else {\n                    throw new Exception(\"Failed to create buffet_sale_adjustments table: \" . $conn->error);\n                }\n                \n                if (!$stmtDiscount) {\n                    throw new Exception(\"Failed to prepare buffet_sale_adjustments statement: \" . $conn->error);\n                }\n            }\n            \n            $stmtDiscount->bind_param(\"ids\", $buffetItemId, $discountAmount, $discountReason);\n            if (!$stmtDiscount->execute()) {\n                throw new Exception(\"Failed to insert discount record: \" . $stmtDiscount->error);\n            }\n            $adjustmentId = $stmtDiscount->insert_id;\n            $stmtDiscount->close();\n            error_log(\"Discount recorded in buffet_sale_adjustments: ID=$adjustmentId, Amount=$discountAmount, Reason=$discountReason\");\n        }";

// Replace all occurrences of the incorrect code with the correct one
$fixed = str_replace($search, $replace, $original);

// Save the fixed content to a new file
file_put_contents('sell_item_action_fixed.php', $fixed);

echo "Fixed file created: sell_item_action_fixed.php\n";
echo "Please review this file and then rename it to replace the original sell_item_action.php\n";
echo "You can do this with:\n";
echo "Copy-Item -Path sell_item_action_fixed.php -Destination sell_item_action.php -Force\n";
?>

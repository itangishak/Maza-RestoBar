<?php
// This file creates the buffet_sale_adjustments table
// and then fixes the discount code in sell_item_action.php

require_once 'connection.php';

// Create the buffet_sale_adjustments table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

echo "<h2>Step 1: Creating buffet_sale_adjustments table</h2>";

$result = $conn->query($createTableSql);
if ($result) {
    echo "<p style='color:green'>Successfully created buffet_sale_adjustments table!</p>";
} else {
    echo "<p style='color:red'>Error creating table: " . $conn->error . "</p>";
    if (strpos($conn->error, 'already exists') !== false) {
        echo "<p>Table already exists, which is fine.</p>";
    }
}

// Get sell_item_action.php content
echo "<h2>Step 2: Checking for discount handling code in sell_item_action.php</h2>";

$filePath = __DIR__ . '/sell_item_action.php';
if (!file_exists($filePath)) {
    echo "<p style='color:red'>Error: File sell_item_action.php not found!</p>";
    exit;
}

$content = file_get_contents($filePath);
if ($content === false) {
    echo "<p style='color:red'>Error: Could not read sell_item_action.php</p>";
    exit;
}

// Look for the problematic discount code
$pattern = "/UPDATE\s+buffet_sale_items\s+SET\s+discount_amount\s*=\s*\?/";
if (preg_match($pattern, $content)) {
    echo "<p style='color:orange'>Found problematic discount code that needs to be fixed.</p>";
    
    // Create backup
    $backupPath = __DIR__ . '/sell_item_action_backup_' . date('Ymd_His') . '.php';
    if (file_put_contents($backupPath, $content)) {
        echo "<p>Created backup at: $backupPath</p>";
    } else {
        echo "<p style='color:red'>Warning: Could not create backup file!</p>";
    }
    
    // Replace the problematic code
    $searchCode = "// 2.1 If discount was applied, record it with the sale item\n        if (isset(\$_POST['allow_discount']) && \$_POST['allow_discount'] == 1 && \$discountAmount > 0) {\n            // Update the buffet_sale_items record with discount information\n            \$sqlDiscount = \"UPDATE buffet_sale_items \n                           SET discount_amount = ?, discount_reason = ? \n                           WHERE buffet_item_id = ?\";\n            \$stmtDiscount = \$conn->prepare(\$sqlDiscount);\n            if (!\$stmtDiscount) {\n                throw new Exception(\"Failed to prepare discount update statement: \" . \$conn->error);\n            }\n            \$stmtDiscount->bind_param(\"dsi\", \$discountAmount, \$discountReason, \$buffetItemId);\n            if (!\$stmtDiscount->execute()) {\n                throw new Exception(\"Failed to update buffet item with discount: \" . \$stmtDiscount->error);\n            }\n            \$stmtDiscount->close();\n            error_log(\"Discount applied: Amount=\$discountAmount, Reason=\$discountReason\");\n        }";
    
    $replaceCode = "// 2.1 If discount was applied, record it in the buffet_sale_adjustments table\n        if (isset(\$_POST['allow_discount']) && \$_POST['allow_discount'] == 1 && \$discountAmount > 0) {\n            // Insert into buffet_sale_adjustments table\n            \$sqlDiscount = \"INSERT INTO buffet_sale_adjustments \n                           (buffet_item_id, adjustment_type, adjustment_amount, adjustment_reason, status) \n                           VALUES (?, 'discount', ?, ?, 'active')\";\n            \$stmtDiscount = \$conn->prepare(\$sqlDiscount);\n            if (!\$stmtDiscount) {\n                // Try to create the table if it doesn't exist\n                \$createTableSql = \"CREATE TABLE IF NOT EXISTS `buffet_sale_adjustments` (\n                    `adjustment_id` int(11) NOT NULL AUTO_INCREMENT,\n                    `buffet_item_id` int(11) NOT NULL,\n                    `adjustment_type` ENUM('discount', 'surcharge', 'tax') NOT NULL DEFAULT 'discount',\n                    `adjustment_amount` decimal(10,2) NOT NULL,\n                    `adjustment_reason` text DEFAULT NULL,\n                    `status` ENUM('active', 'canceled') DEFAULT 'active',\n                    `cancellation_reason` TEXT DEFAULT NULL,\n                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),\n                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n                    PRIMARY KEY (`adjustment_id`),\n                    FOREIGN KEY (`buffet_item_id`) REFERENCES `buffet_sale_items`(`buffet_item_id`)\n                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci\";\n                \n                if (\$conn->query(\$createTableSql)) {\n                    error_log(\"Created buffet_sale_adjustments table and retrying...\");\n                    \$stmtDiscount = \$conn->prepare(\$sqlDiscount);\n                } else {\n                    throw new Exception(\"Failed to create buffet_sale_adjustments table: \" . \$conn->error);\n                }\n                \n                if (!\$stmtDiscount) {\n                    throw new Exception(\"Failed to prepare buffet_sale_adjustments statement: \" . \$conn->error);\n                }\n            }\n            \n            \$stmtDiscount->bind_param(\"ids\", \$buffetItemId, \$discountAmount, \$discountReason);\n            if (!\$stmtDiscount->execute()) {\n                throw new Exception(\"Failed to insert discount record: \" . \$stmtDiscount->error);\n            }\n            \$adjustmentId = \$stmtDiscount->insert_id;\n            \$stmtDiscount->close();\n            error_log(\"Discount recorded in buffet_sale_adjustments: ID=\$adjustmentId, Amount=\$discountAmount, Reason=\$discountReason\");\n        }";
    
    // For automatic fixing attempt (risky)
    $modifiedContent = str_replace($searchCode, $replaceCode, $content);
    if ($modifiedContent != $content) {
        if (file_put_contents($filePath, $modifiedContent)) {
            echo "<p style='color:green'>Successfully fixed the discount code! Try submitting the form again.</p>";
        } else {
            echo "<p style='color:red'>Failed to write the modified file.</p>";
        }
    } else {
        echo "<p style='color:red'>Could not automatically replace the code. You'll need to manually update the file.</p>";
        echo "<h3>Find this code:</h3>";
        echo "<pre style='background-color:#f8f8f8;padding:10px;border:1px solid #ddd;max-height:300px;overflow:auto'>" . htmlspecialchars($searchCode) . "</pre>";
        echo "<h3>Replace with this code:</h3>";
        echo "<pre style='background-color:#f8f8f8;padding:10px;border:1px solid #ddd;max-height:300px;overflow:auto'>" . htmlspecialchars($replaceCode) . "</pre>";
    }
} else {
    echo "<p>Did not find the expected problematic discount code pattern.</p>";
    echo "<p>You might need to manually check the file and replace any code that tries to update 'discount_amount' in the buffet_sale_items table.</p>";
}

echo "<h2>Step 3: Manual Fix Instructions</h2>";
echo "<p>If the automatic fix didn't work, follow these steps:</p>";
echo "<ol>";
echo "<li>Open <code>sell_item_action.php</code> in a text editor</li>";
echo "<li>Search for <code>discount_amount</code></li>";
echo "<li>Replace any code that tries to update buffet_sale_items with discount_amount with the new code that inserts into buffet_sale_adjustments table</li>";
echo "</ol>";

echo "<h3>New Code to Use:</h3>";
echo "<pre style='background-color:#f8f8f8;padding:10px;border:1px solid #ddd'>";
echo htmlspecialchars("// 2.1 If discount was applied, record it in the buffet_sale_adjustments table\n");
echo htmlspecialchars("if (isset($_POST['allow_discount']) && $_POST['allow_discount'] == 1 && $discountAmount > 0) {\n");
echo htmlspecialchars("    // Insert into buffet_sale_adjustments table\n");
echo htmlspecialchars("    $sqlDiscount = \"INSERT INTO buffet_sale_adjustments \n");
echo htmlspecialchars("                   (buffet_item_id, adjustment_type, adjustment_amount, adjustment_reason, status) \n");
echo htmlspecialchars("                   VALUES (?, 'discount', ?, ?, 'active')\";\n");
echo htmlspecialchars("    $stmtDiscount = $conn->prepare($sqlDiscount);\n");
echo htmlspecialchars("    if (!$stmtDiscount) {\n");
echo htmlspecialchars("        error_log(\"Failed to prepare buffet_sale_adjustments statement: \" . $conn->error);\n");
echo htmlspecialchars("        throw new Exception(\"Failed to prepare buffet_sale_adjustments statement: \" . $conn->error);\n");
echo htmlspecialchars("    }\n");
echo htmlspecialchars("    $stmtDiscount->bind_param(\"ids\", $buffetItemId, $discountAmount, $discountReason);\n");
echo htmlspecialchars("    if (!$stmtDiscount->execute()) {\n");
echo htmlspecialchars("        throw new Exception(\"Failed to insert discount record: \" . $stmtDiscount->error);\n");
echo htmlspecialchars("    }\n");
echo htmlspecialchars("    $adjustmentId = $stmtDiscount->insert_id;\n");
echo htmlspecialchars("    $stmtDiscount->close();\n");
echo htmlspecialchars("    error_log(\"Discount recorded in buffet_sale_adjustments: ID=$adjustmentId, Amount=$discountAmount, Reason=$discountReason\");\n");
echo htmlspecialchars("}");
echo "</pre>";

echo "<p>After making these changes, try submitting the form again.</p>";

<?php
session_start();
require_once 'connection.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

header('Content-Type: application/json');

error_log("============================================");
error_log("SELL ITEM ACTION CALLED: " . date("Y-m-d H:i:s"));
error_log("POST DATA: " . print_r($_POST, true));

// Validate sale_type
if (!isset($_POST['sale_type'])) {
    error_log("Missing sale_type parameter");
    echo json_encode(['status' => 'error', 'message' => 'Missing sale_type parameter']);
    exit;
}

function subtractStock($conn, $inventoryId, $qtyNeeded) {
    $sqlCheck = "SELECT quantity_in_stock FROM inventory_items WHERE inventory_id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $inventoryId);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if (!$resCheck || $resCheck->num_rows === 0) {
        throw new Exception("Inventory item $inventoryId not found.");
    }
    $row = $resCheck->fetch_assoc();
    $currentStock = (float)$row['quantity_in_stock'];
    $stmtCheck->close();
    // Improved error handling for stock issues
    if ($currentStock <= 0) {
        // Get item name for better error message
        $itemName = '';
        $nameQuery = "SELECT item_name FROM inventory_items WHERE inventory_id = ?";
        $nameStmt = $conn->prepare($nameQuery);
        if ($nameStmt) {
            $nameStmt->bind_param("i", $inventoryId);
            $nameStmt->execute();
            $nameStmt->bind_result($itemName);
            $nameStmt->fetch();
            $nameStmt->close();
        }
        
        $errorMsg = $itemName ? "$itemName has zero stock available." : "Item (ID: $inventoryId) has zero stock.";
        throw new Exception($errorMsg);
    }
    
    if ($currentStock < $qtyNeeded) {
        // Get item name for better error message
        $itemName = '';
        $nameQuery = "SELECT item_name FROM inventory_items WHERE inventory_id = ?";
        $nameStmt = $conn->prepare($nameQuery);
        if ($nameStmt) {
            $nameStmt->bind_param("i", $inventoryId);
            $nameStmt->execute();
            $nameStmt->bind_result($itemName);
            $nameStmt->fetch();
            $nameStmt->close();
        }
        
        $errorMsg = $itemName 
            ? "Not enough $itemName in stock. Needed: $qtyNeeded, Available: $currentStock" 
            : "Not enough stock (ID: $inventoryId). Needed: $qtyNeeded, In Stock: $currentStock";
        throw new Exception($errorMsg);
    }
    $sqlUpd = "UPDATE inventory_items SET quantity_in_stock = quantity_in_stock - ? WHERE inventory_id = ?";
    $stmtUpd = $conn->prepare($sqlUpd);
    $stmtUpd->bind_param("di", $qtyNeeded, $inventoryId);
    $stmtUpd->execute();
    $stmtUpd->close();
}

function reduceMenuComponents($conn, $menuId, $qtySold) {
    $sql = "SELECT stock_item_id, quantity_used FROM menu_stock_items WHERE menu_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $menuId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $invId = (int)$row['stock_item_id'];
            $qtyPerUnit = (float)$row['quantity_used'];
            $totalNeeded = $qtyPerUnit * $qtySold;
            subtractStock($conn, $invId, $totalNeeded);
        }
    }
    $stmt->close();
}

$conn->begin_transaction();

try {
    $saleType = $_POST['sale_type'];
    $nowDate  = date('Y-m-d H:i:s');

    // ================= MENU SALES ===================
    if ($saleType === 'menu') {
        error_log("Processing MENU sale");
        if (empty($_POST['menu_items']) || !is_array($_POST['menu_items'])) {
            throw new Exception("No menu items provided");
        }
        foreach ($_POST['menu_items'] as $itm) {
            $menuId     = (int)$itm['item_id'];
            $qtySold    = (int)$itm['quantity'];
            $totalPrice = (float)$itm['total_price'];
            if ($menuId <= 0 || $qtySold <= 0) continue;

            // 1) Insert into menu_sales
            $sqlSales = "INSERT INTO menu_sales (menu_id, quantity_sold, sale_price, sale_date, status)
                         VALUES (?, ?, ?, ?, 'active')";
            $stmtSales = $conn->prepare($sqlSales);
            $stmtSales->bind_param("iids", $menuId, $qtySold, $totalPrice, $nowDate);
            if (!$stmtSales->execute()) throw new Exception("Failed to insert menu sale: " . $conn->error);
            $stmtSales->close();

            // 2) Reduce stock for all components
            reduceMenuComponents($conn, $menuId, $qtySold);
        }
    }

    // ================= DRINK SALES ==================
    elseif ($saleType === 'drink') {
        error_log("Processing DRINK sale");
        if (empty($_POST['drink_items']) || !is_array($_POST['drink_items'])) {
            throw new Exception("No drink items provided");
        }
        foreach ($_POST['drink_items'] as $itm) {
            $inventoryId = (int)$itm['item_id'];
            $qtySold     = (int)$itm['quantity'];
            $totalPrice  = (float)$itm['total_price'];
            if ($inventoryId <= 0 || $qtySold <= 0) continue;

            // 1) Insert into drink_sales
            $sqlDrink = "INSERT INTO drink_sales (inventory_id, quantity_sold, sale_price, sale_date, status)
                         VALUES (?, ?, ?, ?, 'active')";
            $stmtD = $conn->prepare($sqlDrink);
            $stmtD->bind_param("iids", $inventoryId, $qtySold, $totalPrice, $nowDate);
            if (!$stmtD->execute()) throw new Exception("Failed to insert drink sale: " . $conn->error);
            $stmtD->close();

            // 2) Reduce inventory
            subtractStock($conn, $inventoryId, $qtySold);
        }
    }

    // =============== BUFFET SALES ===================
    elseif ($saleType === 'buffet') {
        error_log("Processing BUFFET sale");

        // Basic validation
        if (!isset($_POST['buffet_date'], $_POST['buffet_time'], $_POST['dishes_sold'], $_POST['buffet_price'])) {
            throw new Exception("Missing required buffet parameters");
        }

        $buffetDate  = $_POST['buffet_date'];
        $buffetTime  = $_POST['buffet_time'];
        $dishesSold  = (int)$_POST['dishes_sold'];
        $buffetPrice = (float)$_POST['buffet_price'];
        $buffetTotal = (float)($_POST['total_amount'] ?? ($dishesSold * $buffetPrice));

        if ($dishesSold <= 0) throw new Exception("Invalid dishes sold");
        if ($buffetPrice <= 0) throw new Exception("Invalid buffet price");

        // 1. Insert into buffet_sales
        $sql = "INSERT INTO buffet_sales (sale_date, dishes_sold, total_price, status)
                VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sid", $nowDate, $dishesSold, $buffetTotal);
        if (!$stmt->execute()) throw new Exception("Failed to insert buffet sale: " . $stmt->error);
        $buffetSaleId = $stmt->insert_id;
        $stmt->close();

        // 2. Insert into buffet_sale_items
        $sqlBuffetItem = "INSERT INTO buffet_sale_items 
                         (sale_date, time_of_day, price, status)
                         VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($sqlBuffetItem);
        $stmt->bind_param("ssd", $buffetDate, $buffetTime, $buffetPrice);
        if (!$stmt->execute()) throw new Exception("Failed to insert buffet sale item: " . $stmt->error);
        $buffetItemId = $stmt->insert_id;
        $stmt->close();

        // 3. Insert accompaniments
        if (!empty($_POST['accompaniment_id'])) {
            foreach ($_POST['accompaniment_id'] as $key => $menuId) {
                if (empty($menuId)) continue;
                $quantity = (int)($_POST['accompaniment_qty'][$key] ?? 1);

                // Fetch name and price from menu table
                $sqlMenu = "SELECT menu_name, menu_price FROM menu WHERE menu_id = ?";
                $stmtMenu = $conn->prepare($sqlMenu);
                if (!$stmtMenu) {
                    // Error handling if prepare fails (likely table doesn't exist)
                    error_log("Failed to prepare menu query: " . $conn->error);
                    // Use a default name and zero price if we can't get menu data
                    $accompanimentName = 'Accompaniment #' . $menuId;
                    $accompanimentPrice = (float)($_POST['accompaniment_price'][$key] ?? 0);
                } else {
                    $stmtMenu->bind_param("i", $menuId);
                    $stmtMenu->execute();
                    $stmtMenu->bind_result($name, $price);
                    $accompanimentName = '';
                    $accompanimentPrice = 0;
                    if ($stmtMenu->fetch()) {
                        $accompanimentName = $name;
                        $accompanimentPrice = (float)($_POST['accompaniment_price'][$key] ?? ($price * $quantity));
                    }
                    $stmtMenu->close();
                }
                if (empty($accompanimentName)) continue;

                $sqlAcc = "INSERT INTO buffet_accompaniments 
                           (buffet_item_id, menu_id, accompaniment_price, status) 
                           VALUES (?, ?, ?, 'active')";
                $stmtAcc = $conn->prepare($sqlAcc);
                if (!$stmtAcc) {
                    error_log("Failed to prepare buffet_accompaniments insert: " . $conn->error);
                    // Skip this accompaniment if the prepare fails
                    continue;
                }
                $stmtAcc->bind_param("iid", $buffetItemId, $menuId, $accompanimentPrice);
                if (!$stmtAcc->execute()) {
                    error_log("Failed to insert accompaniment: " . $stmtAcc->error);
                } else {
                    error_log("Successfully inserted accompaniment for menu_id: $menuId with price $accompanimentPrice");
                }
                $stmtAcc->close();

                // Deduct stock for accompaniment if a menu item
                reduceMenuComponents($conn, $menuId, $quantity);
            }
        }

        // 4. Insert discount/adjustment
        if (isset($_POST['allow_discount']) && $_POST['allow_discount'] == 1 && !empty($_POST['discount_amount'])) {
            $discountAmount = (float)$_POST['discount_amount'];
            $discountReason = $_POST['discount_reason'] ?? null;
            $adjustmentType = 'Discount'; // must match ENUM

            // Check if buffet_sale_adjustments table exists, create if not
            $conn->query("CREATE TABLE IF NOT EXISTS `buffet_sale_adjustments` (
              `adjustment_id` INT(11) NOT NULL AUTO_INCREMENT,
              `buffet_item_id` INT(11) NOT NULL,
              `adjustment_type` ENUM('Discount', 'Refund', 'Other') NOT NULL,
              `adjustment_amount` DECIMAL(10,2) NOT NULL,
              `adjustment_reason` TEXT,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`adjustment_id`),
              FOREIGN KEY (`buffet_item_id`) REFERENCES `buffet_sale_items`(`buffet_item_id`)
            )");
            
            $stmtAdj = $conn->prepare("INSERT INTO buffet_sale_adjustments
                (buffet_item_id, adjustment_type, adjustment_amount, adjustment_reason)
                VALUES (?, ?, ?, ?)");
            
            if (!$stmtAdj) {
                error_log("Failed to prepare discount adjustment insert: " . $conn->error);
                error_log("SQL: INSERT INTO buffet_sale_adjustments (buffet_item_id, adjustment_type, adjustment_amount, adjustment_reason) VALUES ($buffetItemId, $adjustmentType, $discountAmount, $discountReason)");
                // We'll continue without the discount, but log this error
            } else {
                $stmtAdj->bind_param("isds", $buffetItemId, $adjustmentType, $discountAmount, $discountReason);
                if (!$stmtAdj->execute()) {
                    error_log("Failed to insert discount: " . $stmtAdj->error);
                } else {
                    error_log("Successfully applied discount of $discountAmount with reason: $discountReason");
                }
                $stmtAdj->close();
            }
        }

        // (Optional) Deduct stock for any extra buffet items if posted
        if (!empty($_POST['buffet_items'])) {
            foreach ($_POST['buffet_items'] as $bItem) {
                $fullId  = $bItem['item_id']; // e.g. "inv_10" or "menu_5"
                $qtyUsed = (float)$bItem['quantity'];
                if (strpos($fullId, "inv_") === 0) {
                    $actualItemId = (int)substr($fullId, 4);
                    subtractStock($conn, $actualItemId, $qtyUsed);
                } elseif (strpos($fullId, "menu_") === 0) {
                    $actualItemId = (int)substr($fullId, 5);
                    reduceMenuComponents($conn, $actualItemId, $qtyUsed);
                }
            }
        }
    }
    else {
        throw new Exception("Invalid sale_type: $saleType");
    }

    // COMMIT TRANSACTION
    $conn->commit();
    error_log("Sale processed and committed successfully");

    echo json_encode(['status' => 'success', 'message' => 'Sale processed successfully!']);
} catch (Exception $e) {
    error_log("SALE ERROR: " . $e->getMessage());
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

<?php
session_start();
require_once 'connection.php';

// Enable full error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Debug: Create a detailed debug log file
error_log("============================================");
error_log("===== SELL ITEM ACTION CALLED - DETAILED =====");
error_log("============================================");
error_log("POST DATA: " . print_r($_POST, true));

// Validate required fields based on sale type
if (!isset($_POST['sale_type'])) {
    error_log("ERROR: Missing sale_type parameter");
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Missing sale_type parameter']);
    exit;
}

// Check for specific data based on sale type
if ($_POST['sale_type'] === 'buffet') {
    error_log("Processing BUFFET sale");
    // Check buffet-specific fields
    if (!isset($_POST['buffet_date']) || !isset($_POST['buffet_time']) || !isset($_POST['dishes_sold']) || !isset($_POST['buffet_price'])) {
        error_log("ERROR: Missing required buffet parameters");
        error_log("buffet_date: " . (isset($_POST['buffet_date']) ? $_POST['buffet_date'] : 'MISSING'));
        error_log("buffet_time: " . (isset($_POST['buffet_time']) ? $_POST['buffet_time'] : 'MISSING'));
        error_log("dishes_sold: " . (isset($_POST['dishes_sold']) ? $_POST['dishes_sold'] : 'MISSING'));
        error_log("buffet_price: " . (isset($_POST['buffet_price']) ? $_POST['buffet_price'] : 'MISSING'));
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing required buffet parameters']);
        exit;
    }
} else if ($_POST['sale_type'] === 'menu') {
    error_log("Processing MENU sale");
    // Validate menu sale data
    if (!isset($_POST['menu_items']) || !is_array($_POST['menu_items'])) {
        error_log("ERROR: Missing or invalid menu_items");
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid menu items']);
        exit;
    }
} else if ($_POST['sale_type'] === 'drink') {
    error_log("Processing DRINK sale");
    // Validate drink sale data
    if (!isset($_POST['drink_items']) || !is_array($_POST['drink_items'])) {
        error_log("ERROR: Missing or invalid drink_items");
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid drink items']);
        exit;
    }
}

error_log("Validation passed, proceeding with sale processing");

// Set content type to JSON
header('Content-Type: application/json');

/**
 * Helper function: 
 * 1) Checks if an item in inventory has enough stock.
 * 2) If stock is zero or too low, throw an Exception (abort transaction).
 * 3) Otherwise subtract the needed quantity from `inventory_items`.
 */
function subtractStock($conn, $inventoryId, $qtyNeeded) {
    // 1) Check current stock
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

    // 2) If stock = 0 or insufficient => throw
    if ($currentStock <= 0) {
        throw new Exception("Item (ID: $inventoryId) has zero stock; cannot proceed.");
    }
    if ($currentStock < $qtyNeeded) {
        throw new Exception("Not enough stock (ID: $inventoryId). Needed: $qtyNeeded, In Stock: $currentStock");
    }

    // 3) Subtract 
    $sqlUpd = "UPDATE inventory_items
               SET quantity_in_stock = quantity_in_stock - ?
               WHERE inventory_id = ?";
    $stmtUpd = $conn->prepare($sqlUpd);
    $stmtUpd->bind_param("di", $qtyNeeded, $inventoryId);
    $stmtUpd->execute();
    $stmtUpd->close();
}

/**
 * For a "menu" item, reduce all needed inventory items from `menu_stock_items`.
 * - #15 TABLE `menu_stock_items` => columns: (id, menu_id, stock_item_id, quantity_used, created_at)
 * - quantity_used => how much of that `stock_item_id` is used per 1 "menu" item.
 */
function reduceMenuComponents($conn, $menuId, $qtySold) {
    $sql = "SELECT stock_item_id, quantity_used
            FROM menu_stock_items
            WHERE menu_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $menuId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $invId      = (int)$row['stock_item_id'];
            $qtyPerUnit = (float)$row['quantity_used'];
            $totalNeeded = $qtyPerUnit * $qtySold;
            // Subtract from `inventory_items`
            subtractStock($conn, $invId, $totalNeeded);
        }
    }
    $stmt->close();
}

// Start transaction
$conn->begin_transaction();

try {
    // 'menu', 'drink', or 'buffet'
    $saleType = $_POST['sale_type'] ?? 'menu';
    // We'll store the current date for sale_date
    $nowDate  = date('Y-m-d H:i:s');

     if ($saleType === 'menu') {
        // Log the start of menu sale processing
        error_log("Processing menu sale: " . json_encode($_POST['menu_items'] ?? []));
        
        // e.g. $_POST['menu_items'][i]['item_id'], ['quantity'], ['total_price']
        if (!empty($_POST['menu_items'])) {
            foreach ($_POST['menu_items'] as $itm) {
                $menuId     = (int)$itm['item_id'];  // referencing `menu_id` from table #17
                $qtySold    = (int)$itm['quantity'];
                $totalPrice = (float)$itm['total_price'];
                
                // Skip invalid entries
                if ($menuId <= 0 || $qtySold <= 0) {
                    continue;
                }

                // 1) Insert into `menu_sales` with proper status tracking
                $sqlSales = "INSERT INTO menu_sales (menu_id, quantity_sold, sale_price, sale_date, status)
                             VALUES (?, ?, ?, ?, 'active')";
                $stmtSales = $conn->prepare($sqlSales);
                $stmtSales->bind_param("iids", $menuId, $qtySold, $totalPrice, $nowDate);
                if (!$stmtSales->execute()) {
                    throw new Exception("Failed to insert menu sale: " . $conn->error);
                }
                $menuSaleId = $stmtSales->insert_id;
                $stmtSales->close();

                // 2) reduce all component items from stock
                reduceMenuComponents($conn, $menuId, $qtySold);
                
                error_log("Menu sale recorded: ID=$menuSaleId, Menu=$menuId, Qty=$qtySold, Price=$totalPrice");
            }
        }
    }
    elseif ($saleType === 'drink') {
        // Log the start of drink sale processing
        error_log("Processing drink sale: " . json_encode($_POST['drink_items'] ?? []));
        
        // e.g. $_POST['drink_items'][i]['item_id'], ['quantity'], ['total_price']
        if (!empty($_POST['drink_items'])) {
            foreach ($_POST['drink_items'] as $itm) {
                $inventoryId = (int)$itm['item_id'];  // referencing `inventory_items.inventory_id`
                $qtySold     = (int)$itm['quantity'];
                $totalPrice  = (float)$itm['total_price'];
                
                // Skip invalid entries
                if ($inventoryId <= 0 || $qtySold <= 0) {
                    continue;
                }

                // 1) Insert into `drink_sales` with proper status tracking
                $sqlDrink = "INSERT INTO drink_sales (inventory_id, quantity_sold, sale_price, sale_date, status)
                             VALUES (?, ?, ?, ?, 'active')";
                $stmtD = $conn->prepare($sqlDrink);
                $stmtD->bind_param("iids", $inventoryId, $qtySold, $totalPrice, $nowDate);
                if (!$stmtD->execute()) {
                    throw new Exception("Failed to insert drink sale: " . $conn->error);
                }
                $drinkSaleId = $stmtD->insert_id;
                $stmtD->close();

                // 2) subtract stock from `inventory_items`
                subtractStock($conn, $inventoryId, $qtySold);
                
                error_log("Drink sale recorded: ID=$drinkSaleId, Inventory=$inventoryId, Qty=$qtySold, Price=$totalPrice");
            }
        }
    }
    elseif ($saleType === 'buffet') {
        // Log the start of buffet sale processing
        error_log("Processing buffet sale: Dishes=" . ($_POST['dishes_sold'] ?? 0) . ", Price=" . ($_POST['buffet_price'] ?? 0));
        
        $buffetDate  = $_POST['buffet_date'] ?? date('Y-m-d');
        $buffetTime  = $_POST['buffet_time'] ?? 'Noon';
        $dishesSold  = (int)($_POST['dishes_sold'] ?? 0);
        $buffetPrice = (float)($_POST['buffet_price'] ?? 0);
        $buffetTotal = (float)($_POST['total_amount'] ?? ($dishesSold * $buffetPrice));
        $discountAmount = (float)($_POST['discount_amount'] ?? 0);
        $discountReason = $_POST['discount_reason'] ?? null;
        
        // Validate the data
        if ($dishesSold <= 0) {
            throw new Exception("Invalid number of dishes sold: $dishesSold");
        }
        if ($buffetPrice <= 0) {
            throw new Exception("Invalid buffet price: $buffetPrice");
        }
        
        // Current datetime for sale record
        $saleDateTime = date('Y-m-d H:i:s');
        
        // Base price before discount
        $baseTotal = $dishesSold * $buffetPrice;
        
        // 1. Insert into buffet_sales table with status tracking
        $sql = "INSERT INTO buffet_sales (sale_date, dishes_sold, total_price, status)
                VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare buffet_sales statement: " . $conn->error);
        }
        $stmt->bind_param("sid", $saleDateTime, $dishesSold, $buffetTotal);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert buffet sale: " . $stmt->error);
        }
        $buffetSaleId = $stmt->insert_id;
        $stmt->close();
        error_log("Buffet sale recorded: ID=$buffetSaleId, Dishes=$dishesSold, Total=$buffetTotal");
        
        // 2. Insert into buffet_sale_items table - linking to the buffet_sales record
        // Store the actual buffet item record with time period info
        $sqlBuffetItem = "INSERT INTO buffet_sale_items 
                         (sale_date, time_of_day, price, status) 
                         VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($sqlBuffetItem);
        if (!$stmt) {
            throw new Exception("Failed to prepare buffet_sale_items statement: " . $conn->error);
        }
        $stmt->bind_param("ssd", $buffetDate, $buffetTime, $buffetPrice);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert buffet sale item: " . $stmt->error);
        }
        $buffetItemId = $stmt->insert_id;
        $stmt->close();
        error_log("Buffet item recorded: ID=$buffetItemId, Date=$buffetDate, Time=$buffetTime, Price=$buffetPrice");

        // 2.1 If discount was applied, record it with the sale item
        if (isset($_POST['allow_discount']) && $_POST['allow_discount'] == 1 && $discountAmount > 0) {
            // Update the buffet_sale_items record with discount information
            $sqlDiscount = "UPDATE buffet_sale_items 
                           SET discount_amount = ?, discount_reason = ? 
                           WHERE buffet_item_id = ?";
            $stmtDiscount = $conn->prepare($sqlDiscount);
            if (!$stmtDiscount) {
                throw new Exception("Failed to prepare discount update statement: " . $conn->error);
            }
            $stmtDiscount->bind_param("dsi", $discountAmount, $discountReason, $buffetItemId);
            if (!$stmtDiscount->execute()) {
                throw new Exception("Failed to update buffet item with discount: " . $stmtDiscount->error);
            }
            $stmtDiscount->close();
            error_log("Discount applied: Amount=$discountAmount, Reason=$discountReason");
        }

        // 3. Process accompaniments if allowed
        if (isset($_POST['allow_accompaniments']) && $_POST['allow_accompaniments'] == 1) {
            // Check if we have accompaniment data
            if (!empty($_POST['accompaniment_id'])) {
                error_log("Processing accompaniments: " . count($_POST['accompaniment_id']) . " items");
                
                // Loop through all accompaniments
                foreach ($_POST['accompaniment_id'] as $key => $menuId) {
                    if (empty($menuId)) continue; // Skip empty selections
                    
                    // Get accompaniment details
                    $accompanimentName = '';
                    $accompanimentPrice = 0;
                    $quantity = (int)($_POST['accompaniment_qty'][$key] ?? 1);
                    
                    // Get the menu item name and price from the database
                    $sqlMenu = "SELECT menu_name, menu_price FROM menu WHERE menu_id = ?";
                    $stmtMenu = $conn->prepare($sqlMenu);
                    if (!$stmtMenu) {
                        error_log("Failed to prepare menu query: " . $conn->error);
                        continue;
                    }
                    $stmtMenu->bind_param("i", $menuId);
                    $stmtMenu->execute();
                    $stmtMenu->bind_result($name, $price);
                    if ($stmtMenu->fetch()) {
                        $accompanimentName = $name;
                        $accompanimentPrice = (float)($_POST['accompaniment_price'][$key] ?? ($price * $quantity));
                    }
                    $stmtMenu->close();
                    
                    if (empty($accompanimentName)) {
                        error_log("Warning: Could not find menu item #$menuId for accompaniment");
                        continue;
                    }
                    
                    // Insert into buffet_accompaniments table
                    $sqlAcc = "INSERT INTO buffet_accompaniments 
                               (buffet_item_id, accompaniment_name, accompaniment_price, status) 
                               VALUES (?, ?, ?, 'active')";
                    $stmtAcc = $conn->prepare($sqlAcc);
                    if (!$stmtAcc) {
                        error_log("Failed to prepare accompaniment insert: " . $conn->error);
                        continue;
                    }
                    $stmtAcc->bind_param("isd", $buffetItemId, $accompanimentName, $accompanimentPrice);
                    if (!$stmtAcc->execute()) {
                        error_log("Failed to insert accompaniment: " . $stmtAcc->error);
                    } else {
                        $accompanimentId = $stmtAcc->insert_id;
                        error_log("Accompaniment recorded: ID=$accompanimentId, Name='$accompanimentName', Price=$accompanimentPrice");
                    }
                    $stmtAcc->close();
                    
                    // If it's a menu item, reduce components from stock
                    reduceMenuComponents($conn, $menuId, $quantity);
                }
            }
        }
        
        // 4. Process any component items if they exist (legacy support)
        if (!empty($_POST['buffet_items'])) {
            error_log("Processing legacy buffet items: " . count($_POST['buffet_items']) . " items");
            
            foreach ($_POST['buffet_items'] as $bItem) {
                $fullId  = $bItem['item_id'];  // "inv_10" or "menu_5"
                $qtyUsed = (float)$bItem['quantity'];

                if (strpos($fullId, "inv_") === 0) {
                    $actualItemId = (int)substr($fullId, 4);
                } elseif (strpos($fullId, "menu_") === 0) {
                    $actualItemId = (int)substr($fullId, 5);
                } else {
                    $actualItemId = 0;
                }
                
                if ($actualItemId <= 0 || $qtyUsed <= 0) {
                    continue;
                }

                // Subtract from stock if "inv_"
                if (strpos($fullId, "inv_") === 0) {
                    error_log("Reducing inventory item #$actualItemId by $qtyUsed");
                    subtractStock($conn, $actualItemId, $qtyUsed);
                } else {
                    // "menu_" => reduce the menu's components
                    error_log("Reducing menu item #$actualItemId components by $qtyUsed");
                    reduceMenuComponents($conn, $actualItemId, $qtyUsed);
                }
            }
        }
    } 
    elseif ($saleType === 'buffet') {
        // Log the start of buffet sale processing
        error_log("Processing buffet sale: Dishes=" . ($_POST['dishes_sold'] ?? 0) . ", Price=" . ($_POST['buffet_price'] ?? 0));
        
        $buffetDate  = $_POST['buffet_date'] ?? date('Y-m-d');
        $buffetTime  = $_POST['buffet_time'] ?? 'Noon';
        $dishesSold  = (int)($_POST['dishes_sold'] ?? 0);
        $buffetPrice = (float)($_POST['buffet_price'] ?? 0);
        $buffetTotal = (float)($_POST['total_amount'] ?? ($dishesSold * $buffetPrice));
        $discountAmount = (float)($_POST['discount_amount'] ?? 0);
        $discountReason = $_POST['discount_reason'] ?? null;
    
        // Validate the data
        if ($dishesSold <= 0) {
            throw new Exception("Invalid number of dishes sold: $dishesSold");
        }
        if ($buffetPrice <= 0) {
            throw new Exception("Invalid buffet price: $buffetPrice");
        }
        
        // Current datetime for sale record
        $saleDateTime = date('Y-m-d H:i:s');
        
        // Base price before discount
        $baseTotal = $dishesSold * $buffetPrice;
    
        // 1. Insert into buffet_sales table with status tracking
        $sql = "INSERT INTO buffet_sales (sale_date, dishes_sold, total_price, status)
                VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare buffet_sales statement: " . $conn->error);
        }
        $stmt->bind_param("sid", $saleDateTime, $dishesSold, $buffetTotal);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert buffet sale: " . $stmt->error);
        }
        $buffetSaleId = $stmt->insert_id;
        $stmt->close();
        error_log("Buffet sale recorded: ID=$buffetSaleId, Dishes=$dishesSold, Total=$buffetTotal");
    
        // 2. Insert into buffet_sale_items table - linking to the buffet_sales record
        // Store the actual buffet item record with time period info
        $sqlBuffetItem = "INSERT INTO buffet_sale_items 
                         (sale_date, time_of_day, price, status) 
                         VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($sqlBuffetItem);
        if (!$stmt) {
            throw new Exception("Failed to prepare buffet_sale_items statement: " . $conn->error);
        }
        $stmt->bind_param("ssd", $buffetDate, $buffetTime, $buffetPrice);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert buffet sale item: " . $stmt->error);
        }
        $buffetItemId = $stmt->insert_id;
        $stmt->close();
        error_log("Buffet item recorded: ID=$buffetItemId, Date=$buffetDate, Time=$buffetTime, Price=$buffetPrice");

        // 2.1 If discount was applied, record it with the sale item
        if (isset($_POST['allow_discount']) && $_POST['allow_discount'] == 1 && $discountAmount > 0) {
            // Update the buffet_sale_items record with discount information
            $sqlDiscount = "UPDATE buffet_sale_items 
                           SET discount_amount = ?, discount_reason = ? 
                           WHERE buffet_item_id = ?";
            $stmtDiscount = $conn->prepare($sqlDiscount);
            if (!$stmtDiscount) {
                throw new Exception("Failed to prepare discount update statement: " . $conn->error);
            }
            $stmtDiscount->bind_param("dsi", $discountAmount, $discountReason, $buffetItemId);
            if (!$stmtDiscount->execute()) {
                throw new Exception("Failed to update buffet item with discount: " . $stmtDiscount->error);
            }
            $stmtDiscount->close();
            error_log("Discount applied: Amount=$discountAmount, Reason=$discountReason");
        }

        // 3. Process accompaniments if allowed
        if (isset($_POST['allow_accompaniments']) && $_POST['allow_accompaniments'] == 1) {
            // Check if we have accompaniment data
            if (!empty($_POST['accompaniment_id'])) {
                error_log("Processing accompaniments: " . count($_POST['accompaniment_id']) . " items");
                
                // Loop through all accompaniments
                foreach ($_POST['accompaniment_id'] as $key => $menuId) {
                    if (empty($menuId)) continue; // Skip empty selections
                    
                    // Get accompaniment details
                    $accompanimentName = '';
                    $accompanimentPrice = 0;
                    $quantity = (int)($_POST['accompaniment_qty'][$key] ?? 1);
                    
                    // Get the menu item name and price from the database
                    $sqlMenu = "SELECT menu_name, menu_price FROM menu WHERE menu_id = ?";
                    $stmtMenu = $conn->prepare($sqlMenu);
                    if (!$stmtMenu) {
                        error_log("Failed to prepare menu query: " . $conn->error);
                        continue;
                    }
                    $stmtMenu->bind_param("i", $menuId);
                    $stmtMenu->execute();
                    $stmtMenu->bind_result($name, $price);
                    if ($stmtMenu->fetch()) {
                        $accompanimentName = $name;
                        $accompanimentPrice = (float)($_POST['accompaniment_price'][$key] ?? ($price * $quantity));
                    }
                    $stmtMenu->close();
                    
                    if (empty($accompanimentName)) {
                        error_log("Warning: Could not find menu item #$menuId for accompaniment");
                        continue;
                    }
                    
                    // Insert into buffet_accompaniments table
                    $sqlAcc = "INSERT INTO buffet_accompaniments 
                               (buffet_item_id, accompaniment_name, accompaniment_price, status) 
                               VALUES (?, ?, ?, 'active')";
                    $stmtAcc = $conn->prepare($sqlAcc);
                    if (!$stmtAcc) {
                        error_log("Failed to prepare accompaniment insert: " . $conn->error);
                        continue;
                    }
                    $stmtAcc->bind_param("isd", $buffetItemId, $accompanimentName, $accompanimentPrice);
                    if (!$stmtAcc->execute()) {
                        error_log("Failed to insert accompaniment: " . $stmtAcc->error);
                    } else {
                        $accompanimentId = $stmtAcc->insert_id;
                        error_log("Accompaniment recorded: ID=$accompanimentId, Name='$accompanimentName', Price=$accompanimentPrice");
                    }
                    $stmtAcc->close();
                    
                    // If it's a menu item, reduce components from stock
                    reduceMenuComponents($conn, $menuId, $quantity);
                }
            }
        }
        
        // 4. Process any component items if they exist (legacy support)
        if (!empty($_POST['buffet_items'])) {
            error_log("Processing legacy buffet items: " . count($_POST['buffet_items']) . " items");
            
            foreach ($_POST['buffet_items'] as $bItem) {
                $fullId  = $bItem['item_id'];  // "inv_10" or "menu_5"
                $qtyUsed = (float)$bItem['quantity'];

                if (strpos($fullId, "inv_") === 0) {
                    $actualItemId = (int)substr($fullId, 4);
                } elseif (strpos($fullId, "menu_") === 0) {
                    $actualItemId = (int)substr($fullId, 5);
                } else {
                    $actualItemId = 0;
                }
                
                if ($actualItemId <= 0 || $qtyUsed <= 0) {
                    continue;
                }

                // Subtract from stock if "inv_"
                if (strpos($fullId, "inv_") === 0) {
                    error_log("Reducing inventory item #$actualItemId by $qtyUsed");
                    subtractStock($conn, $actualItemId, $qtyUsed);
                } else {
                    // "menu_" => reduce the menu's components
                    error_log("Reducing menu item #$actualItemId components by $qtyUsed");
                    reduceMenuComponents($conn, $actualItemId, $qtyUsed);
                }
            }
        }
}

// Add detailed database operation logs before committing
error_log("============================================");
error_log("====== DATABASE OPERATIONS SUMMARY ======");
error_log("============================================");

// Log a summary of what we've done
$saleDebug = [
    'sale_type' => $saleType,
    'time' => date('Y-m-d H:i:s'),
    'connection_status' => $conn->ping() ? 'Connected' : 'Disconnected',
    'in_transaction' => $conn->begin_transaction(MYSQLI_TRANS_READONLY) ? 'Yes' : 'No'
];

// For buffet sales, add more specific info
if ($saleType === 'buffet') {
    $saleDebug['buffet_date'] = $_POST['buffet_date'] ?? 'Missing';
    $saleDebug['buffet_time'] = $_POST['buffet_time'] ?? 'Missing';
    $saleDebug['dishes_sold'] = $_POST['dishes_sold'] ?? 'Missing';
    $saleDebug['buffet_price'] = $_POST['buffet_price'] ?? 'Missing';
    $saleDebug['has_discount'] = isset($_POST['allow_discount']) && $_POST['allow_discount'] == 1 ? 'Yes' : 'No';
    $saleDebug['has_accompaniments'] = isset($_POST['allow_accompaniments']) && $_POST['allow_accompaniments'] == 1 ? 'Yes' : 'No';
}

error_log('Database operations completed, about to commit: ' . json_encode($saleDebug));

// Test database connection again before commit
if (!$conn->ping()) {
    error_log("ERROR: Database connection lost before commit!");
    throw new Exception("Database connection lost before commit");
}
    
// CRITICAL: Commit the transaction to save all changes to the database
error_log("Executing commit operation...");
if (!$conn->commit()) {
    error_log("ERROR: Transaction commit failed: {$conn->error}");
    throw new Exception("Transaction commit failed: {$conn->error}");
}
    
error_log('Sale transaction successfully committed to database');
    
// Respond with success
echo json_encode([
    'status' => 'success',
    'message' => 'Sale processed successfully!'
]);
}
catch (Exception $e) {
    // Log the error with full details
    error_log('SALE ERROR: ' . $e->getMessage());
    error_log('SALE ERROR DETAILS: ' . json_encode($_POST));
    
    // Roll back transaction on any exception
    $conn->rollback();
    
    // Return error message to client
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>

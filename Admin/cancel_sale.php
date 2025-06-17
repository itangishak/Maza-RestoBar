<?php
// Basic configuration for error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to the browser
ini_set('log_errors', 1); // Log errors instead

// Start session
session_start();
require_once 'connection.php';

// Function to send JSON response and exit
function send_json_response($success, $message) {
    // Make sure nothing has been output yet
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
    }
    
    $response = ['success' => $success, 'message' => $message];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if user is logged in and has Boss privilege (check both variable cases)
$isBoss = false;
if ((isset($_SESSION['privilege']) && $_SESSION['privilege'] === 'Boss') || 
    (isset($_SESSION['Privilege']) && $_SESSION['Privilege'] === 'Boss')) {
    $isBoss = true;
}

if (!isset($_SESSION['UserId']) || !$isBoss) {
    // Log the failed authorization attempt
    error_log("Unauthorized cancellation attempt. Session data: " . print_r($_SESSION, true));
    send_json_response(false, 'Unauthorized. Only Boss users can cancel sales.');
}

// Log the cancellation action for audit purposes
error_log("Sale cancellation attempted by user ID: {$_SESSION['UserId']} with privilege: {$_SESSION['Privilege']}");
// Validate input
if (!isset($_POST['saleId']) || !isset($_POST['saleType']) || !isset($_POST['reason'])) {
    send_json_response(false, 'Missing required parameters');
}

$saleId = intval($_POST['saleId']);
$saleType = $_POST['saleType'];
$reason = $_POST['reason'];

// Validate sale type
$validSaleTypes = ['menu', 'drink', 'buffet', 'buffet_sale', 'buffet_accompaniment'];
if (!in_array($saleType, $validSaleTypes)) {
    send_json_response(false, 'Invalid sale type');
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    $success = false;
    $message = '';
    
    // Handle cancellation based on sale type
    switch ($saleType) {
        case 'menu':
            // Update menu_sales table
            $stmt = $conn->prepare("UPDATE menu_sales SET status = 'canceled', cancellation_reason = ? WHERE sale_id = ? AND status = 'active'");
            $stmt->bind_param("si", $reason, $saleId);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $success = true;
                $message = 'Menu sale canceled successfully';
                
                // Get menu item details to restore stock
                $menuSale = $conn->query("SELECT menu_id, quantity_sold FROM menu_sales WHERE sale_id = $saleId")->fetch_assoc();
                if ($menuSale) {
                    // Add stock items back to inventory
                    $menuId = $menuSale['menu_id'];
                    $quantity = $menuSale['quantity_sold'];
                    
                    // Get components used in the menu item
                    $componentsQuery = "SELECT stock_item_id, quantity_used FROM menu_stock_items WHERE menu_id = $menuId";
                    $componentsResult = $conn->query($componentsQuery);
                    
                    if ($componentsResult) {
                        while ($component = $componentsResult->fetch_assoc()) {
                            $inventoryId = $component['stock_item_id'];
                            $qtyToRestore = $component['quantity_used'] * $quantity;
                            
                            // Update inventory
                            $conn->query("UPDATE inventory_items SET quantity_in_stock = quantity_in_stock + $qtyToRestore WHERE inventory_id = $inventoryId");
                        }
                    }
                }
            } else {
                $message = 'Menu sale not found or already canceled';
            }
            $stmt->close();
            break;
            
        case 'drink':
            // Update drink_sales table
            $stmt = $conn->prepare("UPDATE drink_sales SET status = 'canceled', cancellation_reason = ? WHERE sale_id = ? AND status = 'active'");
            $stmt->bind_param("si", $reason, $saleId);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $success = true;
                $message = 'Drink sale canceled successfully';
                
                // Get drink sale details to restore inventory
                $drinkSale = $conn->query("SELECT inventory_id, quantity_sold FROM drink_sales WHERE sale_id = $saleId")->fetch_assoc();
                if ($drinkSale) {
                    // Return stock to inventory
                    $inventoryId = $drinkSale['inventory_id'];
                    $quantity = $drinkSale['quantity_sold'];
                    
                    // Update inventory
                    $conn->query("UPDATE inventory_items SET quantity_in_stock = quantity_in_stock + $quantity WHERE inventory_id = $inventoryId");
                }
            } else {
                $message = 'Drink sale not found or already canceled';
            }
            $stmt->close();
            break;
            
        case 'buffet':
            // Update buffet_sale_items table
            $stmt = $conn->prepare("UPDATE buffet_sale_items SET status = 'canceled', cancellation_reason = ? WHERE buffet_item_id = ? AND status = 'active'");
            $stmt->bind_param("si", $reason, $saleId);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $success = true;
                $message = 'Buffet sale canceled successfully';
                
                // Also cancel any accompaniments for this buffet item
                $conn->query("UPDATE buffet_accompaniments SET status = 'canceled', cancellation_reason = 'Main buffet sale was canceled' WHERE buffet_item_id = $saleId AND status = 'active'");
                
                // Also cancel any adjustments (discounts) for this buffet item
                $conn->query("UPDATE buffet_sale_adjustments SET status = 'canceled', cancellation_reason = 'Main buffet sale was canceled' WHERE buffet_item_id = $saleId");
            } else {
                $message = 'Buffet sale not found or already canceled';
            }
            $stmt->close();
            break;
            
        case 'buffet_sale':
            // Update buffet_sales table (separate from buffet_sale_items)
            $stmt = $conn->prepare("UPDATE buffet_sales SET status = 'canceled', cancellation_reason = ? WHERE sale_id = ? AND status = 'active'");
            $stmt->bind_param("si", $reason, $saleId);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $success = true;
                $message = 'Buffet sale entry canceled successfully';
            } else {
                $message = 'Buffet sale entry not found or already canceled';
            }
            $stmt->close();
            break;
            
        case 'buffet_accompaniment':
            // Update buffet_accompaniments table
            $stmt = $conn->prepare("UPDATE buffet_accompaniments SET status = 'canceled', cancellation_reason = ? WHERE accompaniment_id = ? AND status = 'active'");
            $stmt->bind_param("si", $reason, $saleId);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $success = true;
                $message = 'Buffet accompaniment canceled successfully';
            } else {
                $message = 'Buffet accompaniment not found or already canceled';
            }
            $stmt->close();
            break;
    }
    
    // Commit or rollback based on result
    if ($success) {
        $conn->commit();
        send_json_response(true, $message);
    } else {
        $conn->rollback();
        send_json_response(false, $message);
    }
    
} catch (Exception $e) {
    // Rollback on error
    if ($conn->ping()) {
        $conn->rollback();
    }
    
    // Log the error
    error_log('Cancel sale error: ' . $e->getMessage());
    
    // Return a clean error response
    send_json_response(false, 'A server error occurred during cancellation. Please try again.');
}

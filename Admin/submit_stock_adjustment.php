<?php
require_once 'connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Retrieve JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['type'], $data['reason'], $data['items']) || empty($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
    exit();
}

$type        = $data['type']; // "Add" or "Reduce"
$reason      = $data['reason'];
$supplier_id = $data['supplier_id'] ?? null;
$notes       = $data['notes']       ?? null;

// Start transaction
$conn->begin_transaction();

try {
    // Insert into stock_adjustments
    // (Optional) If you have a user_id from session, use it. Hard-coded as 1 for now:
    $stmt = $conn->prepare("
        INSERT INTO stock_adjustments (type, reason, supplier_id, notes, user_id)
        VALUES (?, ?, ?, ?, 1)
    ");
    $stmt->bind_param("ssis", $type, $reason, $supplier_id, $notes);
    $stmt->execute();
    $adjustment_id = $conn->insert_id;

    // Process each item
    foreach ($data['items'] as $item) {
        $product_id      = (int)$item['product_id'];
        $quantity_change = (float)$item['quantity'];
        $unit_cost       = (float)($item['unit_cost'] ?? 0);

        // Fetch current stock
        $result = $conn->query("SELECT quantity_in_stock FROM inventory_items WHERE inventory_id = $product_id");
        $row = $result->fetch_assoc();
        $current_stock = (float)$row['quantity_in_stock'];

        // Check reduce case
        if ($type === 'Reduce' && $quantity_change > $current_stock) {
            throw new Exception("Cannot reduce more stock than available for product ID: $product_id. Current stock: $current_stock");
        }

        // Calculate new stock
        $new_stock = ($type === 'Add')
            ? ($current_stock + $quantity_change)
            : ($current_stock - $quantity_change);

        // Update inventory_items
        $update_stmt = $conn->prepare("
            UPDATE inventory_items
            SET quantity_in_stock = ?
            WHERE inventory_id = ?
        ");
        $update_stmt->bind_param("di", $new_stock, $product_id);
        $update_stmt->execute();

        // Insert detail into stock_adjustment_items
        $insert_stmt = $conn->prepare("
            INSERT INTO stock_adjustment_items
            (adjustment_id, inventory_id, quantity_before, quantity_change, unit_cost, quantity_after)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert_stmt->bind_param("iiiddd", 
            $adjustment_id,
            $product_id,
            $current_stock,
            $quantity_change,
            $unit_cost,
            $new_stock
        );
        $insert_stmt->execute();
    }

    // Commit
    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Stock adjustment recorded successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Close connection
$conn->close();

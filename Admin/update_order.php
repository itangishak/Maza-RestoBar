<?php
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? null;
    $new_status = $_POST['status'] ?? null;

    if (!$order_id || !$new_status) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Invalid input data.'
        ]);
        exit;
    }

    // Start transaction so we can roll back if something fails
    $conn->begin_transaction();

    try {
        // 1️⃣ Update the orders table
        $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $order_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating orders: " . $stmt->error);
        }

        // 2️⃣ If status is "confirmed", process sales and kitchen orders
        if ($new_status === 'confirmed') {
            // Get order details
            $detailsSql = "
                SELECT od.menu_id, od.quantity, od.price
                FROM order_details od
                WHERE od.order_id = ?
            ";
            $detailsStmt = $conn->prepare($detailsSql);
            $detailsStmt->bind_param("i", $order_id);
            $detailsStmt->execute();
            $result = $detailsStmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("No order details found for order ID: " . $order_id);
            }

            // Insert each detail into menu_sales
            $saleSql = "INSERT INTO menu_sales (menu_id, quantity_sold, sale_price) 
                        VALUES (?, ?, ?)";
            $saleStmt = $conn->prepare($saleSql);

            // Insert into kitchen_orders first
            $insertKOTSql = "INSERT INTO kitchen_orders (order_id) VALUES (?)";
            $stmt = $conn->prepare($insertKOTSql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $kot_id = $conn->insert_id; // Get the new KOT ID

            // Insert each item into menu_sales & kitchen_order_items
            $insertKOTItemSql = "INSERT INTO kitchen_order_items (kot_id, menu_id, quantity) 
                                 VALUES (?, ?, ?)";
            $kotItemStmt = $conn->prepare($insertKOTItemSql);

            while ($row = $result->fetch_assoc()) {
                $menu_id = $row['menu_id'];
                $qtySold = $row['quantity'];
                $price   = $row['price']; // decimal(10,2)

                // Insert into menu_sales
                $saleStmt->bind_param("iid", $menu_id, $qtySold, $price);
                if (!$saleStmt->execute()) {
                    throw new Exception("Error inserting into menu_sales: " . $saleStmt->error);
                }

                // Insert into kitchen_order_items
                $kotItemStmt->bind_param("iii", $kot_id, $menu_id, $qtySold);
                if (!$kotItemStmt->execute()) {
                    throw new Exception("Error inserting into kitchen_order_items: " . $kotItemStmt->error);
                }
            }
        }

        $conn->commit();
        echo json_encode([
            'status'  => 'success',
            'message' => 'Order updated successfully! Kitchen Order Created!'
        ]);
    } catch (Exception $ex) {
        $conn->rollback();
        echo json_encode([
            'status'  => 'error',
            'message' => $ex->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>

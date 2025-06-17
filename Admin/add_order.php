<?php
require_once 'connection.php';

// Make sure we're receiving the data we expect from the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Grab posted data
    $customer_id = $_POST['customer_id'] ?? null;
    $menu_items  = $_POST['menu_items']  ?? array();
    $quantities  = $_POST['quantities']  ?? array();
    $order_total = $_POST['order_total'] ?? 0;  // total price from front end

    // Basic validation
    if (!$customer_id || empty($menu_items) || empty($quantities)) {
        // Return an error JSON response
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid input data.'
        ]);
        exit;
    }

    // Start a transaction so we can roll back if anything fails
    $conn->begin_transaction();

    try {
        // 1) Insert into orders table
        // We assume `status` defaults to 'pending' if not provided,
        // or we explicitly set 'pending'
        $sql = "INSERT INTO orders (customer_id, total_price, status)
                VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $customer_id, $order_total);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert into orders: " . $stmt->error);
        }

        // Retrieve the newly inserted order_id
        $order_id = $conn->insert_id;

        // 2) Insert each row into order_details
        // We'll get the price from the DB again for consistency,
        // OR we can trust the front end if we prefer.  
        // Using DB ensures no tampering from the client side.
        $detailSql = "INSERT INTO order_details (order_id, menu_id, quantity, price)
                      VALUES (?, ?, ?, ?)";
        $detailStmt = $conn->prepare($detailSql);

        for ($i = 0; $i < count($menu_items); $i++) {
            $menu_id = $menu_items[$i];
            $qty     = $quantities[$i];

            // Retrieve the correct price for this menu item from DB
            $priceQuery = "SELECT price FROM menu_items WHERE menu_id = ?";
            $priceStmt = $conn->prepare($priceQuery);
            $priceStmt->bind_param("i", $menu_id);
            $priceStmt->execute();
            $priceResult = $priceStmt->get_result();
            $menuRow = $priceResult->fetch_assoc();

            if (!$menuRow) {
                throw new Exception("Menu item not found (ID: $menu_id).");
            }

            $itemPrice = $menuRow['price']; // decimal(10,2)

            // Now insert into order_details
            $detailStmt->bind_param("iiid", $order_id, $menu_id, $qty, $itemPrice);
            if (!$detailStmt->execute()) {
                throw new Exception("Failed to insert into order_details: " . $detailStmt->error);
            }
        }

        // If we reach here, everything is good. Commit.
        $conn->commit();

        // Return success JSON
        echo json_encode([
            'status' => 'success',
            'message' => 'Order added successfully!'
        ]);

    } catch (Exception $ex) {
        // Something went wrong, roll back
        $conn->rollback();

        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add order: ' . $ex->getMessage()
        ]);
    }
} else {
    // If not POST, maybe show a 405 or a simple message
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>

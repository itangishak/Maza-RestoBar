<?php

require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form fields
    $name = trim($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $availability = trim($_POST['availability'] ?? '');
    $ingredients = $_POST['ingredients'] ?? []; // Array of ingredient IDs

    // Validate required fields
    if (!$name || !$category_id || !$price || !$availability || empty($ingredients)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }
    
    // Process image file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Allowed file extensions
        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array($fileExtension, $allowedfileExtensions)) {
            echo json_encode(['status' => 'error', 'message' => 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions)]);
            exit;
        }
        
        // Set the directory to store uploaded images
        $uploadFileDir = './assets/img/uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        // Generate a unique file name
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $dest_path = $uploadFileDir . $newFileName;
        
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $imagePath = '../Admin/assets/img/uploads/' . $newFileName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'There was an error moving the uploaded file.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Image is required.']);
        exit;
    }
    
    // Insert menu item (including image path) into menu_items table
    $stmt = $conn->prepare("INSERT INTO menu_items (name, category_id, price, availability, image) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        // Binding: name (s), category_id (i), price (d), availability (s), image (s)
        $stmt->bind_param("sidss", $name, $category_id, $price, $availability, $imagePath);
        if ($stmt->execute()) {
            $menu_id = $stmt->insert_id; // Newly created menu item ID

            // Insert ingredients into menu_stock_items table (assuming quantity_used is fixed as 1)
            $ingredientStmt = $conn->prepare("INSERT INTO menu_stock_items (menu_id, stock_item_id, quantity_used) VALUES (?, ?, 1)");
            foreach ($ingredients as $ingredient_id) {
                $ingredient_id = intval($ingredient_id);
                $ingredientStmt->bind_param("ii", $menu_id, $ingredient_id);
                $ingredientStmt->execute();
            }
            $ingredientStmt->close();

            echo json_encode(['status' => 'success', 'message' => 'Menu created successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Menu created successfully.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare SQL statement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
$conn->close();
?>

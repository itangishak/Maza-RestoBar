<?php
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_id     = intval($_POST['menu_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price       = floatval($_POST['price'] ?? 0);
    $availability= trim($_POST['availability'] ?? '');
    // ingredients => array of [inventory_id, unit, quantity]
    $ingredients = $_POST['ingredients'] ?? [];

    if ($menu_id <= 0 || !$name || !$category_id || !$price || !$availability || empty($ingredients)) {
        echo json_encode(['status'=>'error','message'=>'All fields are required.']);
        exit;
    }

    // Check if a new image is uploaded
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName    = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg','jpeg','png','gif'];
        if(!in_array($ext, $allowedExt)){
            echo json_encode(['status'=>'error','message'=>'Invalid image extension.']);
            exit;
        }
        $uploadDir = './assets/img/uploads/';
        if(!is_dir($uploadDir)){
            mkdir($uploadDir,0755,true);
        }
        $newFileName = md5(time() . $fileName) . '.' . $ext;
        $destPath = $uploadDir . $newFileName;
        if(!move_uploaded_file($fileTmpPath,$destPath)){
            echo json_encode(['status'=>'error','message'=>'Error moving uploaded file.']);
            exit;
        }
        // store relative path
        $imagePath = '../Admin/assets/img/uploads/' . $newFileName;
    }

    // If $imagePath is not null => we update image
    if($imagePath !== null) {
        $stmt = $conn->prepare("
          UPDATE menu_items
             SET name=?, category_id=?, price=?, availability=?, image=?
           WHERE menu_id=?
        ");
        $stmt->bind_param("sidssi", $name, $category_id, $price, $availability, $imagePath, $menu_id);
    } else {
        // keep old image
        $stmt = $conn->prepare("
          UPDATE menu_items
             SET name=?, category_id=?, price=?, availability=?
           WHERE menu_id=?
        ");
        $stmt->bind_param("sidsi", $name, $category_id, $price, $availability, $menu_id);
    }

    if(!$stmt->execute()){
        echo json_encode(['status'=>'error','message'=>'Failed to update menu.']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // now handle ingredients
    // remove old
    $conn->query("DELETE FROM menu_stock_items WHERE menu_id = $menu_id");
    // re-insert
    $ingStmt = $conn->prepare("
      INSERT INTO menu_stock_items (menu_id, stock_item_id, quantity_used)
      VALUES (?,?,?)
    ");
    foreach($ingredients as $ing) {
        $stock_item_id = intval($ing['inventory_id'] ?? 0);
        $quantity_used = floatval($ing['quantity'] ?? 1);
        if($stock_item_id>0 && $quantity_used>0){
            $ingStmt->bind_param("iid",$menu_id,$stock_item_id,$quantity_used);
            $ingStmt->execute();
        }
    }
    $ingStmt->close();

    echo json_encode(['status'=>'success','message'=>'Menu updated successfully.']);
} else {
    echo json_encode(['status'=>'error','message'=>'Invalid request method.']);
}
$conn->close();
?>

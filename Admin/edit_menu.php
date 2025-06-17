<?php
session_start();
require_once 'connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1) Grab the ?id from GET
$menu_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($menu_id <= 0) {
    die("Invalid menu ID.");
}

// 2) Fetch menu item from DB
$stmt = $conn->prepare("SELECT * FROM menu_items WHERE menu_id = ?");
$stmt->bind_param("i", $menu_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Menu item not found.");
}
$menuItem = $result->fetch_assoc();
$stmt->close();

// 3) Fetch existing ingredients from menu_stock_items
$ingredients = [];
$sqlIng = "
   SELECT ms.id,
          ms.stock_item_id,
          ms.quantity_used,
          inv.item_name,
          inv.unit AS unit_id,
          u.unit_name
     FROM menu_stock_items ms
     JOIN inventory_items inv ON ms.stock_item_id = inv.inventory_id
     JOIN units u ON inv.unit = u.unit_id
    WHERE ms.menu_id = $menu_id
";
$resIng = $conn->query($sqlIng);
while ($row = $resIng->fetch_assoc()) {
    $ingredients[] = $row;
}

// 4) Fetch categories for the category <select>
$catRes = $conn->query("SELECT category_id, name FROM menu_categories");
$categories = [];
while ($row = $catRes->fetch_assoc()) {
    $categories[] = $row;
}

// 5) Fetch inventory items with their real unit name
$invSql = "
   SELECT i.inventory_id,
          i.item_name,
          i.unit AS unit_id,
          u.unit_name
     FROM inventory_items i
     JOIN units u ON i.unit = u.unit_id
";
$inventoryRes = $conn->query($invSql);
$inventoryItems = [];
while($row = $inventoryRes->fetch_assoc()){
    $inventoryItems[] = [
      'inventory_id' => $row['inventory_id'],
      'item_name'    => $row['item_name'],
      'unit_name'    => $row['unit_name']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu</title>
    <!-- Include Bootstrap, select2, jQuery, etc. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once './header.php'; ?> <!-- if you have a custom header -->
    
    <style>
        .main-container {
            margin-top: 50px;
            margin-left: 70px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-control, .form-select {
            margin-bottom: 15px;
        }
        .preview-img {
            max-height: 150px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<?php include_once './navbar.php'; ?>
<!-- Sidebar -->
<?php include_once 'sidebar.php'; ?>

<div class="container main-container">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Menu</h4>
        </div>
        <div class="card-body">
            <form id="editMenuForm" enctype="multipart/form-data">
                <!-- Hidden menu_id field -->
                <input type="hidden" name="menu_id" value="<?php echo $menuItem['menu_id']; ?>">

                <div class="row">
                    <div class="col-md-6">
                        <label for="name">Menu Name</label>
                        <input type="text" class="form-control" id="name"
                               name="name" required
                               value="<?php echo htmlspecialchars($menuItem['name']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="category_id">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            foreach ($categories as $cat) {
                                $selected = ($cat['category_id'] == $menuItem['category_id']) ? 'selected' : '';
                                echo "<option value='{$cat['category_id']}' $selected>{$cat['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <label for="price">Price</label>
                        <input type="number" step="0.01" class="form-control"
                               id="price" name="price" required
                               value="<?php echo htmlspecialchars($menuItem['price']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="availability">Availability</label>
                        <select class="form-select" id="availability" name="availability" required>
                            <option value="available"   <?php if($menuItem['availability']=='available') echo 'selected';?>>Available</option>
                            <option value="unavailable" <?php if($menuItem['availability']=='unavailable') echo 'selected';?>>Unavailable</option>
                        </select>
                    </div>
                </div>
                
                <!-- Existing image preview + optional new upload -->
                <div class="row">
                    <div class="col-md-12">
                        <label for="image">Menu Image (Optional)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if(!empty($menuItem['image'])): ?>
                            <img id="imagePreview" class="preview-img"
                                 src="<?php echo htmlspecialchars($menuItem['image']); ?>"
                                 alt="Image Preview" style="display:block;">
                        <?php else: ?>
                            <img id="imagePreview" class="preview-img" src="#" alt="Image Preview" style="display:none;">
                        <?php endif; ?>
                    </div>
                </div>

                <h5 class="mt-4">Ingredients</h5>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody id="ingredientsTableBody">
                        <!-- We will fill from existing $ingredients in JS below -->
                    </tbody>
                </table>
                
                <button type="submit" class="btn btn-success mt-3">Update Menu</button>
            </form>
        </div>
    </div>
</div>

<?php include_once './footer.php'; ?>

<script>
let inventoryData = <?php echo json_encode($inventoryItems); ?>;
// existing ingredients => each has stock_item_id, quantity_used, unit_name, etc.
let existingIngredients = <?php echo json_encode($ingredients); ?>;

// Build <option> tags with data-unit="..."
let inventoryOptions = inventoryData.map(item => {
   return `<option value="${item.inventory_id}" data-unit="${item.unit_name}">
              ${item.item_name}
           </option>`;
}).join('');

$(document).ready(function(){
    // If user picks a new image => show preview
    $('#image').on('change', function(){
        const file = this.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = function(e){
                $('#imagePreview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Populate existing ingredients
    if(existingIngredients.length > 0){
        existingIngredients.forEach(ing => {
            addIngredientRow(ing.stock_item_id, ing.quantity_used, ing.unit_name);
        });
    } else {
        addIngredientRow();
    }

    // Handle row +/-
    $('#ingredientsTableBody').on('click','.addRowBtn',function(){
        let currentRow = $(this).closest('tr');
        let product    = currentRow.find('.ingredientSelect').val();
        let quantity   = currentRow.find('.quantityField').val();
        if(!product || !quantity || quantity<=0) {
            Swal.fire('Warning','Complete the current row first.','warning');
            return;
        }
        $(this)
          .removeClass('btn-primary addRowBtn')
          .addClass('btn-danger removeRowBtn')
          .text('-');
        addIngredientRow();
    });
    $('#ingredientsTableBody').on('click','.removeRowBtn', function(){
        $(this).closest('tr').remove();
    });
    $('#ingredientsTableBody').on('change','.ingredientSelect', function(){
        let unit = $(this).find(':selected').data('unit') || '';
        $(this).closest('tr').find('.unitField').val(unit);
    });

    // Submit => update_menu.php
    $('#editMenuForm').on('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: 'update_menu.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp){
                if(resp.status === 'success'){
                    Swal.fire('Success', resp.message, 'success').then(()=>{
                        window.location.href = 'menu_dashboard.php';
                    });
                } else {
                    Swal.fire('Error', resp.message, 'error');
                }
            },
            error: function(){
                Swal.fire('Error','Failed to update menu.','error');
            }
        });
    });
});
   // Sidebar toggling logic
   const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
            const sidebar = document.querySelector('.sidebar');
            const mainContainer = document.querySelector('.main-container');
            const card = document.querySelector('.card');

            // Adjust card size on page load
            if (sidebar && card) {
                if (!sidebar.classList.contains('collapsed')) {
                    card.style.width = '87%';
                    card.style.marginLeft = '20%';
                } else {
                    card.style.width = '100%';
                    card.style.marginLeft = '5%';
                }
            }

            // Toggle sidebar functionality
            if (toggleSidebarBtn && sidebar && mainContainer && card) {
                toggleSidebarBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('collapsed');
                    mainContainer.classList.toggle('full');

                    if (!sidebar.classList.contains('collapsed')) {
                        card.style.width = '87%';
                        card.style.marginLeft = '20%';
                    } else {
                        card.style.width = '100%';
                        card.style.marginLeft = '5%';
                    }
                });
            }

// Add row helper
function addIngredientRow(stockItemID=null, quantityUsed=1, unitName='') {
    let rowCount = $('#ingredientsTableBody tr').length;
    let newRow = `
    <tr>
      <td>
        <select name="ingredients[${rowCount}][inventory_id]" class="form-select ingredientSelect" required>
          <option value="">Select Ingredient</option>
          ${inventoryOptions}
        </select>
      </td>
      <td>
        <input type="text" name="ingredients[${rowCount}][unit]" 
               class="form-control unitField" readonly 
               value="${unitName}">
      </td>
      <td>
        <input type="number" name="ingredients[${rowCount}][quantity]"
               class="form-control quantityField" required
               value="${quantityUsed}">
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-primary addRowBtn">+</button>
      </td>
    </tr>
    `;
    $('#ingredientsTableBody').append(newRow);
    // For newly added row
    let lastRow = $('#ingredientsTableBody tr').last();
    let select  = lastRow.find('.ingredientSelect');
    select.html(inventoryOptions);
    select.val(stockItemID || '');
    // If we have stockItemID => set the unit
    if(stockItemID) {
        select.val(stockItemID);
    }
}
</script>
</body>
</html>

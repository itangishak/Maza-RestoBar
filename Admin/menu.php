<?php
session_start();
require_once 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once './header.php'; ?>
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
<!-- Navbar (if desired) -->
<?php  include_once './navbar.php'; ?>

 <!-- ======= Sidebar ======= -->
 <?php include_once 'sidebar.php'; ?>
<!-- End Sidebar -->
<div class="container main-container">
    <div class="card shadow">
        <div class="card-header">
            <h4>Create New Menu</h4>
        </div>
        <div class="card-body">
            <form id="menuForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <label for="name">Menu Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category_id">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php
                            $query = "SELECT category_id, name FROM menu_categories";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['category_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="price">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="col-md-6">
                        <label for="availability">Availability</label>
                        <select class="form-select" id="availability" name="availability" required>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="image">Upload Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <img id="imagePreview" class="preview-img" src="#" alt="Image Preview" style="display:none;">
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
                        <!-- Rows added dynamically -->
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success mt-3">Create Menu</button>
            </form>
        </div>
    </div>
</div>
   <!-- Include Footer -->
   <?php include_once './footer.php'; ?>
<script>
    $(document).ready(function () {
        let ingredientOptions = '';

        // Load ingredients
        function loadIngredients() {
            $.getJSON('fetch_inventory_items.php', function (data) {
                ingredientOptions = data.map(item => `<option value="${item.inventory_id}" data-unit="${item.unit}">${item.item_name}</option>`).join('');
                addIngredientRow(); // Add the first row on load
            });
        }

        loadIngredients();

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


        // Function to add a new ingredient row
        function addIngredientRow() {
            const rowCount = $('#ingredientsTableBody tr').length;
            const newRow = `
                <tr>
                    <td>
                        <select name="ingredients[${rowCount}][inventory_id]" class="form-select ingredientSelect" required>
                            <option value="">Select Ingredient</option>
                            ${ingredientOptions}
                        </select>
                    </td>
                    <td><input type="text" name="ingredients[${rowCount}][unit]" class="form-control unitField" readonly></td>
                    <td><input type="number" name="ingredients[${rowCount}][quantity]" class="form-control quantityField" required></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary addRowBtn">+</button>
                    </td>
                </tr>`;
            $('#ingredientsTableBody').append(newRow);

            // Initialize Select2 for the new row
            $('.ingredientSelect').select2({
                placeholder: "Search for an ingredient",
                allowClear: true,
                width: '100%'
            });
        }

        // Add Ingredient Row
        $('#ingredientsTableBody').on('click', '.addRowBtn', function () {
            const currentRow = $(this).closest('tr');
            const product = currentRow.find('.ingredientSelect').val();
            const quantity = currentRow.find('.quantityField').val();

            // Validate the current row before adding a new one
            if (!product || !quantity || quantity <= 0) {
                Swal.fire('Warning', 'Please complete the current row before adding a new one.', 'warning');
                return;
            }

            // Change the current button to a Remove button (-)
            $(this)
                .removeClass('btn-primary addRowBtn')
                .addClass('btn-danger removeRowBtn')
                .text('-');

            // Add a new blank row
            addIngredientRow();
        });

        // Remove Ingredient Row
        $('#ingredientsTableBody').on('click', '.removeRowBtn', function () {
            $(this).closest('tr').remove();
        });

        // Populate Unit Field When an Ingredient is Selected
        $('#ingredientsTableBody').on('change', '.ingredientSelect', function () {
            const unit = $(this).find(':selected').data('unit');
            $(this).closest('tr').find('.unitField').val(unit || '');
        });

        // Show Image Preview
        $('#image').on('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#imagePreview').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Submit Menu Form
        $('#menuForm').on('submit', function (e) {
            e.preventDefault();
            const isValid = validateIngredients();

            if (!isValid) {
                Swal.fire('Warning', 'Please complete all ingredient rows.', 'warning');
                return;
            }

            const formData = new FormData(this);
            $.ajax({
                url: 'add_menu.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            window.location.href = 'menu_dashboard.php';
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Failed to create menu.', 'error');
                }
            });
        });

        function validateIngredients() {
            let isValid = true;
            $('#ingredientsTableBody tr').each(function () {
                const ingredient = $(this).find('.ingredientSelect').val();
                const quantity = $(this).find('.quantityField').val();
                if (!ingredient || !quantity || quantity <= 0) {
                    isValid = false;
                }
            });
            return isValid;
        }
    });
</script>
</body>
</html>

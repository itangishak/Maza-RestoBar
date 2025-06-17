<?php
session_start();
require_once 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-key="menu.createTitle">Create Menu</title>

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
<?php include_once './navbar.php'; ?>

<!-- ======= Sidebar ======= -->
<?php include_once 'sidebar.php'; ?>
<!-- End Sidebar -->

<div class="container main-container">
    <div class="card shadow">
        <div class="card-header">
            <h4 data-key="menu.createNewMenu">Create New Menu</h4>
        </div>
        <div class="card-body">
            <form id="menuForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <label for="name" data-key="menu.menuName">Menu Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category_id" data-key="menu.category">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="" data-key="menu.selectCategory">Select Category</option>
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
                        <label for="price" data-key="menu.price">Price</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="col-md-6">
                        <label for="availability" data-key="menu.availability">Availability</label>
                        <select class="form-select" id="availability" name="availability" required>
                            <option value="available" data-key="menu.available">Available</option>
                            <option value="unavailable" data-key="menu.unavailable">Unavailable</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="image" data-key="menu.uploadImage">Upload Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <img id="imagePreview" class="preview-img" src="#" alt="Image Preview" style="display:none;">
                    </div>
                </div>
                <h5 class="mt-4" data-key="menu.ingredients">Ingredients</h5>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th data-key="menu.ingredient">Ingredient</th>
                        <th data-key="menu.unit">Unit</th>
                        <th data-key="menu.quantity">Quantity</th>
                        <th data-key="menu.action">Action</th>
                    </tr>
                    </thead>
                    <tbody id="ingredientsTableBody">
                        <!-- Rows added dynamically -->
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success mt-3" data-key="menu.createMenu">Create Menu</button>
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
                            <option value="" data-key="menu.selectIngredient">Select Ingredient</option>
                            ${ingredientOptions}
                        </select>
                    </td>
                    <td><input type="text" name="ingredients[${rowCount}][unit]" class="form-control unitField" readonly></td>
                    <td><input type="number" name="ingredients[${rowCount}][quantity]" class="form-control quantityField" required></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary addRowBtn" data-key="menu.addRow">+</button>
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
                Swal.fire(getTranslation('menu.warning'), getTranslation('menu.completeCurrentRow'), 'warning');
                return;
            }

            // Change the current button to a Remove button (-)
            $(this)
                .removeClass('btn-primary addRowBtn')
                .addClass('btn-danger removeRowBtn')
                .attr('data-key', 'menu.removeRow')
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
                Swal.fire(getTranslation('menu.warning'), getTranslation('menu.completeAllIngredients'), 'warning');
                return;
            }

            const formData = new FormData(this);
            const lang = localStorage.getItem('userLang') || 'en';
            formData.append('lang', lang); // Add language to form data

            $.ajax({
                url: 'add_menu.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire(getTranslation('menu.success'), response.message, 'success').then(() => {
                            window.location.href = 'menu_dashboard.php';
                        });
                    } else {
                        Swal.fire(getTranslation('menu.success'), response.message, 'success');
                    }
                },
                error: function () {
                    Swal.fire(getTranslation('menu.success'),'Menu crée', 'success');
                   
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

        // Language Switching Logic
        const systemLang = navigator.language || navigator.userLanguage;
        let defaultLang = 'en';
        if (systemLang.startsWith('fr')) {
            defaultLang = 'fr';
        }

        let currentLang = localStorage.getItem('userLang') || defaultLang;

        loadTranslations(currentLang);

        function loadTranslations(lang) {
            fetch('../languages/' + lang + '.json')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to load language file: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(translations => {
                    localStorage.setItem('translations', JSON.stringify(translations));
                    translatePage(translations);
                })
                .catch(error => {
                    console.error('Error loading translations:', error);
                    // Fallback to English
                    fetch('../languages/en.json')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Fallback to English failed: ' + response.statusText);
                            }
                            return response.json();
                        })
                        .then(translations => {
                            localStorage.setItem('translations', JSON.stringify(translations));
                            translatePage(translations);
                        })
                        .catch(fallbackError => console.error('Fallback error:', fallbackError));
                });
        }

        function translatePage(translations) {
            document.querySelectorAll('[data-key]').forEach(element => {
                const key = element.getAttribute('data-key');
                if (translations[key]) {
                    if (element.tagName === 'INPUT') {
                        element.value = translations[key]; // Use value for input fields
                    } else if (element.tagName === 'BUTTON') {
                        element.textContent = translations[key]; // Use textContent for button text
                    } else if (element.tagName === 'SELECT' || element.tagName === 'OPTION') {
                        element.textContent = translations[key]; // Use textContent for select/options
                    } else {
                        element.textContent = translations[key]; // Use textContent for other elements
                    }
                }
            });
        }

        function getTranslation(key) {
            const translations = JSON.parse(localStorage.getItem('translations')) || {};
            return translations[key] || (currentLang === 'en' ? 'Default English Text' : 'Texte par défaut en français');
        }

        function switchLanguage(lang) {
            localStorage.setItem('userLang', lang);
            loadTranslations(lang);
        }
    });
</script>
</body>
</html>
<?php
session_start();
require_once 'connection.php';
require_once __DIR__ . '/../includes/auth.php';
require_privilege(['Boss','Manager','User']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Title replaced with data-key -->
  <title data-key="report.sellItems"></title>

  <?php include_once './header.php'; ?>
 
  <style>
    .main-container {
      margin-top: 100px;
      margin-left: 70px;
    }
    .card {
      box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
    }
    .sidebar.collapsed {
      width: 70px;
      transition: width 0.3s;
    }
    .main-container.full {
      margin-left: 0 !important;
    }
    .hidden {
      display: none;
    }
   /* Receipt styling for printing */
    #receiptContent {
      display: none; /* Hidden by default */
      width: 200px; /* Width optimized for Epson TM-T20 */
      margin: 0 auto; /* Changed from '0 0 0 10px' to '0 auto' for proper centering */
      padding: 0;
      font-family: Arial, sans-serif;
      text-align: center;
      font-size: 12px;
      line-height: 1.2;
    }
    #receiptContent img {
      width: 100px;
      margin: 0 auto 0px auto;
      display: block;
    }
    #receiptContent h2 {
      margin: 0;
      font-size: 16px;
      font-weight: bold;
    }
    #receiptContent p {
      margin: 5px 0;
      font-size: 12px;
    }
    #receiptContent hr {
      border: none;
      border-top: 1px dashed #000;
      margin: 8px 0;
      width: 100%;
    }
    #receiptContent table {
      width: 100%;
      margin: 5px 0;
      border-collapse: collapse;
      font-size: 11px;
    }
    #receiptContent th, #receiptContent td {
      border: 1px solid #000;
      padding: 3px;
      text-align: center;
    }
    #receiptContent .text-end {
      text-align: right;
    }
  </style>
</head>
<body>
  <?php include_once './navbar.php'; ?>
  <?php include_once 'sidebar.php'; ?>

  <div class="container main-container">
    <div class="card">
      <div class="card-header">
        <!-- Header title replaced -->
        <h4 data-key="report.sellItems"></h4>
      </div>
      <div class="card-body">
        <form id="sellItemForm">
          <!-- Hidden field for sale_type -->
          <input type="hidden" id="sale_type" name="sale_type" value="buffet">
          <!-- (1) ORDER SELECTION -->
          <div class="mb-3">
            <h5 data-key="report.orderSelection">Order Selection</h5>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" id="includeMenu" name="include_menu" value="1">
              <label class="form-check-label" for="includeMenu" data-key="report.menu"></label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" id="includeDrink" name="include_drink" value="1">
              <label class="form-check-label" for="includeDrink" data-key="report.drink"></label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" id="includeBuffet" name="include_buffet" value="1" checked>
              <label class="form-check-label" for="includeBuffet" data-key="report.buffet"></label>
            </div>
          </div>

          <!-- (2) MENU SECTION (Hidden by default) -->
          <div id="menuSection" class="hidden">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th data-key="report.menuItem"></th>
                  <th data-key="report.quantity"></th>
                  <th data-key="report.totalPrice"></th>
                  <th data-key="report.action"></th>
                </tr>
              </thead>
              <tbody id="menuTableBody">
                <!-- Dynamically added rows for Menu -->
              </tbody>
            </table>
            <div class="d-flex justify-content-end mt-2">
              <h5>
                <span data-key="report.grandTotal"></span>
                <span id="menuGrandTotal">0.00</span> BIF
              </h5>
            </div>
          </div>

          <!-- (3) DRINK SECTION -->
          <div id="drinkSection" class="hidden">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th data-key="report.drink"></th>
                  <th data-key="report.quantity"></th>
                  <th data-key="report.totalPrice"></th>
                  <th data-key="report.action"></th>
                </tr>
              </thead>
              <tbody id="drinkTableBody">
                <!-- Dynamically added rows for Drink -->
              </tbody>
            </table>
            <div class="d-flex justify-content-end mt-2">
              <h5>
                <span data-key="report.grandTotal"></span>
                <span id="drinkGrandTotal">0.00</span> BIF
              </h5>
            </div>
          </div>

          <!-- (4) BUFFET SECTION (Default) -->
          <div id="buffetSection">
            <!-- Hidden fields for auto-populated date and time -->
            <input type="hidden" id="buffetDate" name="buffet_date">
            <input type="hidden" id="buffetTime" name="buffet_time">
            
            <!-- Buffet Selection -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" data-key="report.dishesSold">Dishes Sold</label>
                <input type="number" class="form-control" id="dishesSold" name="dishes_sold" min="1" value="1">
              </div>
              <div class="col-md-6">
                <label class="form-label" data-key="report.buffetPrice">Buffet Price</label>
                <input type="number" class="form-control" id="buffetPrice" name="buffet_price" min="0" step="100" readonly>
              </div>
            </div>
            
            <!-- Accompaniments Permission Checkbox -->
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="allow-accompaniments" name="allow_accompaniments" value="1">
                <label class="form-check-label" for="allow-accompaniments">
                  <span data-key="report.allowAccompaniments">Allow Accompaniments</span>
                </label>
              </div>
            </div>
            
            <!-- Accompaniments Section (Hidden by default) -->
            <div class="card mb-3 hidden" id="accompaniments-section">
              <div class="card-header">
                <h5 data-key="report.accompaniments">Accompaniments</h5>
              </div>
              <div class="card-body">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Accompaniment</th>
                      <th>Quantity</th>
                      <th>Total Price</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="accompanimentTableBody">
                    <!-- Accompaniment rows will be added here dynamically -->
                  </tbody>
                </table>
              </div>
              <div class="card-footer">
                <button type="button" class="btn btn-primary btn-sm" id="add-accompaniment" data-key="report.addAccompaniment">Add Accompaniment</button>
              </div>
            </div>
            
            <!-- Discount Permission Checkbox -->
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="allow-discount" name="allow_discount" value="1">
                <label class="form-check-label" for="allow-discount">
                  <span data-key="report.allowDiscount">Allow Discount</span>
                </label>
              </div>
            </div>
            
            <!-- Discount Section (Hidden by default) -->
            <div class="card mb-3 hidden" id="discount-section">
              <div class="card-header">
                <h5 data-key="report.discount">Discount</h5>
              </div>
              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-md-4">
                    <label class="form-label" data-key="report.discountAmount">Discount Amount</label>
                    <input type="number" class="form-control" id="discount-amount" name="discount_amount" value="0" min="0" readonly>
                  </div>
                  <div class="col-md-8">
                    <label class="form-label" data-key="report.discountReason">Reason</label>
                    <input type="text" class="form-control" id="discount-reason" name="discount_reason">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Buffet Total -->
            <div class="d-flex justify-content-end mt-2">
              <div class="text-end">
                <div class="mb-2">
                  <span data-key="report.subtotal"></span>: <span id="buffet-subtotal">0.00</span> BIF
                </div>
                <div class="mb-2">
                  <span data-key="report.accompanimentTotal"></span>: <span id="accompaniment-total">0.00</span> BIF
                </div>
                <div class="mb-2">
                  <span data-key="report.discount"></span>: <span id="discount-total">0.00</span> BIF
                </div>
                <h5>
                  <span data-key="report.grandTotal"></span>: <span id="buffet-grand-total">0.00</span> BIF
                </h5>
              </div>
            </div>
            
            <!-- Component table removed as per user request -->
          </div>

          <!-- Submit button (translated using textContent fix) -->
          <button type="submit" class="btn btn-success mt-3" data-key="report.sellItems">Sell</button>
        </form>
      </div>
    </div>
  </div>


  <!-- Modal for Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" data-key="report.salePreview"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="previewContent"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.cancel">Cancel</button>
        <button type="button" id="confirmSale" class="btn btn-primary" data-key="report.confirmSale">Confirm</button>
        <div class="form-check form-check-inline ms-3">
          <input class="form-check-input" type="checkbox" id="printTwoSections" name="print_two_sections" value="1" checked>
          <label class="form-check-label" for="printTwoSections">Print Client & Audit Copies</label>
        </div>
       
      </div>
    </div>
  </div>
</div>

<!-- Hidden receipt for printing -->
<div id="receiptContent"></div>
  <?php include_once './footer.php'; ?>

<script>
// -----------------------------
// Sidebar toggling (if applicable)
// -----------------------------
$(document).ready(function() {
  const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
  const sidebar = document.querySelector('.sidebar');
  const mainContainer = document.querySelector('.main-container');
  const card = document.querySelector('.card');

  if (sidebar && card) {
    if (!sidebar.classList.contains('collapsed')) {
      card.style.width = '87%';
      card.style.marginLeft = '20%';
    } else {
      card.style.width = '100%';
      card.style.marginLeft = '5%';
    }
  }
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
});

// -----------------------------
// Load Options (Menus, Drinks, Buffet, Accompaniments)
// -----------------------------
let menuOptions = "";
let drinkOptions = "";
let buffetOptions = "";
let accompanimentOptions = "";

// Ensure discount checkbox is unchecked by default
$(document).ready(function() {
  $("#allow-discount").prop("checked", false);
});

function loadOptions() {
  // a) Menus
  $.getJSON("fetch_menus_sell.php", function(menuData) {
    menuOptions = menuData.map(m => `
      <option value="${m.menu_id}" data-price="${m.price}">
        ${m.name} - ${m.price} BIF
      </option>
    `).join("");
  });
  // b) Drinks
  $.getJSON("fetch_drinks.php", function(drinkData) {
    drinkOptions = drinkData.map(d => `
      <option value="${d.inventory_id}" data-price="${d.price}">
        ${d.item_name} - ${d.price} BIF
      </option>
    `).join("");
  });
  // c) Buffet (inventory + menu)
  $.getJSON("fetch_inventory_items.php", function(invData) {
    let invPart = invData.map(i => `
      <option value="inv_${i.inventory_id}">
        ${i.item_name} (${i.unit})
      </option>
    `).join("");
    $.getJSON("fetch_menus_sell.php", function(allMenus) {
      let menuPart = allMenus.map(m => `
        <option value="menu_${m.menu_id}">
          ${m.name}
        </option>
      `).join("");
      buffetOptions = invPart + menuPart;
      toggleSections();
    });
  });
}
loadOptions();

// -----------------------------
// Toggle Sections and disable hidden ones
// -----------------------------
function disableSection(sectionId) {
  $(`#${sectionId}`).addClass("hidden");
  $(`#${sectionId} input, #${sectionId} select`).prop("disabled", true).removeAttr("required");
}
function enableSection(sectionId) {
  $(`#${sectionId}`).removeClass("hidden");
  $(`#${sectionId} input, #${sectionId} select`).prop("disabled", false);
}
function makeBuffetRequired() {
  // Make buffet fields required
  $('#dishesSold').attr('required', true);
}

// Handle section visibility with checkboxes
$("#includeMenu, #includeDrink, #includeBuffet").on("change", function() {
  const includeMenu = $("#includeMenu").prop("checked");
  const includeDrink = $("#includeDrink").prop("checked");
  const includeBuffet = $("#includeBuffet").prop("checked"); // Allow buffet to be disabled when unchecked
  
  // Toggle sections based on checkbox state
  if (includeMenu) {
    enableSection("menuSection");
    if ($("#menuTableBody tr").length === 0) {
      addMenuRow();
    }
  } else {
    disableSection("menuSection");
  }
  
  if (includeDrink) {
    enableSection("drinkSection");
    if ($("#drinkTableBody tr").length === 0) {
      addDrinkRow();
    }
  } else {
    disableSection("drinkSection");
  }
  
  // Always enable buffet section by default
  enableSection("buffetSection");
  makeBuffetRequired();
  autoSelectDateAndDayTime();
  updateBuffetPrice();
  
  // Set focus on dishes sold input
  $("#dishesSold").focus();
  
  // Set up event handlers for accompaniments and discounts
  $("#add-accompaniment").on('click', function() {
    addAccompanimentRow();
  });
  
  // Event delegation for dynamically added elements
  $(document).on('click', '.remove-accompaniment', function() {
    $(this).closest('.accompaniment-row').remove();
    updateBuffetTotals();
  });
  
  $(document).on('change', '.accompaniment-select', function() {
    const selectedOption = $(this).find('option:selected');
    const price = selectedOption.data('price') || 0;
    $(this).closest('.accompaniment-row').find('.accompaniment-price').val(price);
    updateBuffetTotals();
  });
  
  // Set up change handlers for buffet inputs
  $("#dishesSold, #discount-amount").on('input', function() {
    updateBuffetTotals();
  });
  
  // Define toggleSections function
  window.toggleSections = function() {
    const includeMenu = $("#includeMenu").prop("checked");
    const includeDrink = $("#includeDrink").prop("checked");
    const includeBuffet = $("#includeBuffet").prop("checked");
    
    // Toggle visibility of sections based on checkboxes
    if (includeMenu) {
      enableSection("menuSection");
    } else {
      disableSection("menuSection");
    }
    
    if (includeDrink) {
      enableSection("drinkSection");
    } else {
      disableSection("drinkSection");
    }
    
    if (includeBuffet) {
      enableSection("buffetSection");
    } else {
      disableSection("buffetSection");
    }
  };
});

// -----------------------------
// Calculate accompaniment total function
// -----------------------------
function calculateAccompanimentTotal() {
  let total = 0;
  
  // Loop through all accompaniment rows
  $("#accompanimentTableBody tr").each(function() {
    const qty = parseInt($(this).find('.accompanimentQty').val() || 0);
    const price = parseFloat($(this).find('.accompanimentPrice').val() || 0);
    total += qty * price;
  });
  
  return total;
}

// -----------------------------
// Duplicate Checking function
// -----------------------------
function checkDuplicate(selectEl, selectorClass) {
  let val = selectEl.val();
  if (!val) return;
  let duplicateFound = false;
  $(selectorClass).each(function(){
    if (this !== selectEl[0] && $(this).val() === val) {
      duplicateFound = true;
      return false;
    }
  });
  if (duplicateFound) {
    Swal.fire("Warning", "This item is already selected. Choose another.", "warning");
    selectEl.val("");
  }
}

// -----------------------------
// MENU SECTION functions
// -----------------------------
function addMenuRow() {
  if ($("#menuSection").hasClass("hidden")) return;
  const rowCount = $("#menuTableBody tr").length;
  const newRow = `
    <tr>
      <td>
        <select class="form-select menuItemSelect" name="menu_items[${rowCount}][item_id]" required>
          <option value="">Select Menu Item</option>
          ${menuOptions}
        </select>
      </td>
      <td>
        <input type="number" class="form-control menuQty" name="menu_items[${rowCount}][quantity]" min="1">
      </td>
      <td>
        <input type="number" class="form-control menuTotal" name="menu_items[${rowCount}][total_price]" readonly>
      </td>
      <td>
        <button type="button" class="btn btn-primary addMenuRowBtn">+</button>
        <button type="button" class="btn btn-danger removeMenuRowBtn">−</button>
      </td>
    </tr>
  `;
  const newRowJQuery = $(newRow); // Convert HTML string to jQuery object
  $("#menuTableBody").append(newRowJQuery);
  if ($.fn.select2) {
    newRowJQuery.find(".menuItemSelect").select2({
        placeholder: "Select Menu Item",
        allowClear: true,
        width: "100%"
    });
  }
}
function updateMenuTotal(row) {
  const sel = row.find(".menuItemSelect option:selected");
  const price = parseFloat(sel.data("price") || 0);
  let qtyVal = parseFloat(row.find(".menuQty").val() || 0);
  const total = price * qtyVal;
  row.find(".menuTotal").val(total.toFixed(2));
}
function updateMenuGrandTotal() {
  let sum = 0;
  $(".menuTotal").each(function(){
    sum += parseFloat($(this).val() || 0);
  });
  $("#menuGrandTotal").text(sum.toFixed(2));
}
$("#menuTableBody").on("change keyup", ".menuItemSelect, .menuQty", function(){
  const row = $(this).closest("tr");
  if ($(this).hasClass("menuItemSelect")) {
    checkDuplicate($(this), ".menuItemSelect");
    let qtyF = row.find(".menuQty");
    if (!qtyF.val() || parseFloat(qtyF.val()) <= 0) {
      qtyF.val("1");
    }
  }
  updateMenuTotal(row);
  updateMenuGrandTotal();
});
$("#menuTableBody").on("click", ".addMenuRowBtn", function(){
  const row = $(this).closest("tr");
  const itemSel = row.find(".menuItemSelect").val();
  const qtyVal = parseFloat(row.find(".menuQty").val() || 0);
  if (!itemSel || qtyVal <= 0) {
    Swal.fire("Warning", "Select a menu item and enter a valid quantity before adding another row.", "warning");
    return;
  }
  addMenuRow();
});
$("#menuTableBody").on("click", ".removeMenuRowBtn", function(){
  $(this).closest("tr").remove();
  updateMenuGrandTotal();
});

// -----------------------------
// DRINK SECTION functions
// -----------------------------
function addDrinkRow() {
  if ($("#drinkSection").hasClass("hidden")) return;
  const rowCount = $("#drinkTableBody tr").length;
  const newRow = `
    <tr>
      <td>
        <select class="form-select drinkItemSelect" name="drink_items[${rowCount}][item_id]" required>
          <option value="">Select Drink</option>
          ${drinkOptions}
        </select>
      </td>
      <td>
        <input type="number" class="form-control drinkQty" name="drink_items[${rowCount}][quantity]" min="1">
      </td>
      <td>
        <input type="number" class="form-control drinkTotal" name="drink_items[${rowCount}][total_price]" readonly>
      </td>
      <td>
        <button type="button" class="btn btn-primary addDrinkRowBtn">+</button>
        <button type="button" class="btn btn-danger removeDrinkRowBtn">−</button>
      </td>
    </tr>
  `;
  const newRowJQuery = $(newRow); // Convert HTML string to jQuery object
  $("#drinkTableBody").append(newRowJQuery);
  if ($.fn.select2) {
    newRowJQuery.find(".drinkItemSelect").select2({
        placeholder: "Select Drink", // Consider using getTranslation here if available
        allowClear: true,
        width: "100%"
    });
  }
}
function updateDrinkTotal(row) {
  const sel = row.find(".drinkItemSelect option:selected");
  const price = parseFloat(sel.data("price") || 0);
  let qtyVal = parseFloat(row.find(".drinkQty").val() || 0);
  row.find(".drinkTotal").val((price * qtyVal).toFixed(2));
}
function updateDrinkGrandTotal() {
  let sum = 0;
  $(".drinkTotal").each(function(){
    sum += parseFloat($(this).val() || 0);
  });
  $("#drinkGrandTotal").text(sum.toFixed(2));
}
$("#drinkTableBody").on("change keyup", ".drinkItemSelect, .drinkQty", function(){
  const row = $(this).closest("tr");
  if ($(this).hasClass("drinkItemSelect")) {
    checkDuplicate($(this), ".drinkItemSelect");
    let qtyF = row.find(".drinkQty");
    if (!qtyF.val() || parseFloat(qtyF.val()) <= 0) {
      qtyF.val("1");
    }
  }
  updateDrinkTotal(row);
  updateDrinkGrandTotal();
});
$("#drinkTableBody").on("click", ".addDrinkRowBtn", function(){
  const row = $(this).closest("tr");
  const itemSel = row.find(".drinkItemSelect").val();
  const qtyVal = parseFloat(row.find(".drinkQty").val() || 0);
  if (!itemSel || qtyVal <= 0) {
    Swal.fire("Warning", "Select a drink and enter a valid quantity before adding another row.", "warning");
    return;
  }
  addDrinkRow();
});
$("#drinkTableBody").on("click", ".removeDrinkRowBtn", function(){
  $(this).closest("tr").remove();
  updateDrinkGrandTotal();
});

// -----------------------------
// BUFFET SECTION functions
// -----------------------------

// Form Submit => Build Preview
// -----------------------------
$("#sellItemForm").on("submit", function(e){
  e.preventDefault();
  
  // Check if at least one section is selected
  const includeMenu = $("#includeMenu").prop("checked");
  const includeDrink = $("#includeDrink").prop("checked");
  const includeBuffet = $("#includeBuffet").prop("checked");
  
  if (!includeMenu && !includeDrink && !includeBuffet) {
    Swal.fire("Warning", "Please select at least one item type (Menu, Drink, or Buffet)", "warning");
    return;
  }
  
  let previewContent = `
    <div class="text-center mb-4">
      <h3>MAZA RESTO-BAR</h3>
      <p>Boulevard Melchior Ndadaye, Peace Corner</p>
      <p>Bujumbura, Burundi</p>
      <p>Tel: +257 69 80 58 98</p>
      <p>Email: barmazaresto@gmail.com</p>
      <hr>
    </div>
  `;
  
  // Initialize grand total variable
  let combinedGrandTotal = 0;
  
  // (1) MENU SECTION
  if (includeMenu) {
    previewContent += `
      <h4>MENU ITEMS</h4>
      <table>
        <thead><tr>
          <th>Menu Item</th>
          <th>Qty</th>
          <th>Total</th>
        </tr></thead>
        <tbody>
    `;
    $("#menuTableBody tr").each(function(){
      const itemTxt = $(this).find(".menuItemSelect option:selected").text();
      const qtyVal = $(this).find(".menuQty").val() || "0";
      const totVal = $(this).find(".menuTotal").val() || "0.00";
      if (itemTxt && parseFloat(qtyVal) > 0) {
        previewContent += `<tr><td>${itemTxt.split(" - ")[0]}</td><td>${qtyVal}</td><td>${totVal} BIF</td></tr>`;
      }
    });
    const mgT = $("#menuGrandTotal").text();
    combinedGrandTotal += parseFloat(mgT);
    previewContent += `</tbody></table>
    <table class="w-100">
      <tr>
        <th class="text-end">Menu Subtotal</th>
        <td class="text-end">${mgT} BIF</td>
      </tr>
    </table><hr>`;
  }
  
  // (2) DRINK SECTION
  if (includeDrink) {
    previewContent += `
      <h4>DRINKS</h4>
      <table>
        <thead><tr>
          <th>Drink</th>
          <th>Qty</th>
          <th>Total</th>
        </tr></thead>
        <tbody>
    `;
    $("#drinkTableBody tr").each(function(){
      const itemTxt = $(this).find(".drinkItemSelect option:selected").text();
      const qtyVal = $(this).find(".drinkQty").val() || "0";
      const totVal = $(this).find(".drinkTotal").val() || "0.00";
      if (itemTxt && parseFloat(qtyVal) > 0) {
        previewContent += `<tr><td>${itemTxt.split(" - ")[0]}</td><td>${qtyVal}</td><td>${totVal} BIF</td></tr>`;
      }
    });
    const dgT = $("#drinkGrandTotal").text();
    combinedGrandTotal += parseFloat(dgT);
    previewContent += `</tbody></table>
    <table class="w-100">
      <tr>
        <th class="text-end">Drinks Subtotal</th>
        <td class="text-end">${dgT} BIF</td>
      </tr>
    </table><hr>`;
  }
  
  // (3) BUFFET SECTION
  if (includeBuffet) {
    const bDate = $("#buffetDate").val();
    const bTime = $("#buffetTime").val();
    const bDishes = $("#dishesSold").val();
    const bPrice = $("#buffetPrice").val();
    const buffetTotal = parseFloat(bDishes || 0) * parseFloat(bPrice || 0);
    const bTotal = buffetTotal.toFixed(2);
    
    // Get current buffet count for the time period (from global variables)
    const timeLabel = bTime;
    const currentCount = window[`buffetCount${timeLabel}`] || 0;
    const newCount = parseInt(currentCount) + 1; // Show what the new count will be after this sale
    
    // Update buffet counts in the background
    window[`buffetCount${timeLabel}`] = newCount;
    
    previewContent += `
      <h4>BUFFET</h4>
      <table class="w-100">
        <tr><th>Date</th><td>${bDate}</td></tr>
        <tr><th>Time Period</th><td>${timeLabel}</td></tr>
        <tr><th>Dishes Sold</th><td>${bDishes}</td></tr>
        <tr><th>Price per Dish</th><td>${bPrice} BIF</td></tr>
      </table>
    `;
    
    // Add accompaniments if allowed
    let accompanimentTotal = 0;
    if ($("#allow-accompaniments").is(':checked') && $("#accompanimentTableBody tr").length > 0) {
      previewContent += `<h5>Accompaniments:</h5><table class="w-100"><thead><tr><th>Item</th><th>Qty</th><th>Price</th></tr></thead><tbody>`;
      
      // Loop through all accompaniment rows
      $("#accompanimentTableBody tr").each(function() {
        const select = $(this).find('.accompanimentSelect');
        const itemName = select.find('option:selected').text();
        const qty = $(this).find('.accompanimentQty').val();
        const price = parseFloat($(this).find('.accompanimentTotal').val() || 0);
        
        if (select.val()) { // Only add if an item is selected
          previewContent += `<tr><td>${itemName}</td><td>${qty}</td><td>${price.toFixed(2)} BIF</td></tr>`;
          accompanimentTotal += price;
        }
      });
      
      previewContent += `</tbody></table>`;
      previewContent += `<table class="w-100">
        <tr>
          <th class="text-end">Accompaniments Total</th>
          <td class="text-end">${accompanimentTotal.toFixed(2)} BIF</td>
        </tr>
      </table>`;
    }
    
    // Add discount if allowed
    let discountAmount = 0;
    if ($("#allow-discount").is(':checked')) {
      discountAmount = parseFloat($("#discount-amount").val() || 0);
      const discountReason = $("#discount-reason").val();
      
      if (discountAmount > 0) {
        previewContent += `<table class="w-100">
          <tr>
            <th>Discount</th>
            <td>${discountAmount.toFixed(2)} BIF</td>
          </tr>`;
        
        if (discountReason) {
          previewContent += `<tr>
            <th>Reason</th>
            <td>${discountReason}</td>
          </tr>`;
        }
        
        previewContent += `</table>`;
      }
    }
    
    // Calculate final buffet total including accompaniments and discount
    const finalBuffetTotal = buffetTotal + accompanimentTotal - discountAmount;
    previewContent += `<table class="w-100">
      <tr>
        <th class="text-end">Buffet Subtotal</th>
        <td class="text-end">${finalBuffetTotal.toFixed(2)} BIF</td>
      </tr>
    </table><hr>`;
    combinedGrandTotal += finalBuffetTotal;
  }
  
  // Add grand total to receipt
  previewContent += `<table class="w-100">
    <tr>
      <th class="text-end"><strong>GRAND TOTAL</strong></th>
      <td class="text-end"><strong>${combinedGrandTotal.toFixed(2)} BIF</strong></td>
    </tr>
  </table><hr><p>Thank you for your purchase!</p>`;
  
  $("#previewContent").html(previewContent);
  $("#previewModal").modal("show");
  
  // Store the combinedGrandTotal for later use
  $("#combinedGrandTotal").val(combinedGrandTotal.toFixed(2));
});

// -----------------------------
// Confirm Sale via AJAX
// -----------------------------
$("#confirmSale").on("click", async function() {
  // Set the appropriate sale_type based on what's selected
  if ($("#includeMenu").prop("checked")) {
    $("#sale_type").val("menu");
  } else if ($("#includeDrink").prop("checked")) {
    $("#sale_type").val("drink");
  } else if ($("#includeBuffet").prop("checked")) {
    $("#sale_type").val("buffet");
  }
  
  // Make sure date and time are properly set for buffet
  if ($("#includeBuffet").prop("checked")) {
    // Ensure date is set to today if not already set
    if (!$("#buffetDate").val()) {
      const today = new Date().toISOString().split('T')[0];
      $("#buffetDate").val(today);
      console.log("Buffet date was empty, now set to:", today);
    }
    
    // Ensure time period is set if not already set
    if (!$("#buffetTime").val()) {
      const now = new Date();
      const hours = now.getHours();
      let timePeriod = 'Noon'; // Default to Noon
      
      if (hours < 12) {
        timePeriod = 'Morning';
      } else if (hours >= 17) {
        timePeriod = 'Evening';
      }
      
      $("#buffetTime").val(timePeriod);
      console.log("Buffet time was empty, now set to:", timePeriod);
    }
    
    // Calculate and add total amount
    const dishesSold = parseInt($("#dishesSold").val() || 0);
    const buffetPrice = parseFloat($("#buffetPrice").val() || 0);
    const accompanimentTotal = calculateAccompanimentTotal();
    const discountAmount = parseFloat($("#discount-amount").val() || 0);
    const totalAmount = (dishesSold * buffetPrice) + accompanimentTotal - discountAmount;
    
    console.log("Calculated total amount:", totalAmount.toFixed(2));
    console.log("  - Dishes sold:", dishesSold);
    console.log("  - Buffet price per dish:", buffetPrice);
    console.log("  - Accompaniment total:", accompanimentTotal);
    console.log("  - Discount amount:", discountAmount);
    
    // Add hidden field for total_amount if it doesn't exist
    if ($("#total_amount").length === 0) {
      $("#sellItemForm").append(`<input type="hidden" id="total_amount" name="total_amount" value="${totalAmount.toFixed(2)}">`);
    } else {
      $("#total_amount").val(totalAmount.toFixed(2));
    }
  }

  // Get all form data
  const formData = $("#sellItemForm").serializeArray();
  
  // Enhanced debugging
  console.log("==========================================");
  console.log("DEBUGGING FORM SUBMISSION:");
  console.log("==========================================");
  console.log("1. Form data being submitted:", formData);
  
  // Convert to an object for easier debugging
  const formDataObj = {};
  formData.forEach(item => {
    formDataObj[item.name] = item.value;
  });
  console.log("2. Form data as object:", formDataObj);
  console.log("3. Sale type:", formDataObj.sale_type);
  
  // Check buffet-specific fields if it's a buffet sale
  if (formDataObj.sale_type === 'buffet' || $("#includeBuffet").prop("checked")) {
    console.log("4. Buffet date:", formDataObj.buffet_date);
    console.log("5. Buffet time:", formDataObj.buffet_time);
    console.log("6. Dishes sold:", formDataObj.dishes_sold);
    console.log("7. Buffet price:", formDataObj.buffet_price);
    console.log("8. Allow accompaniments:", formDataObj.allow_accompaniments);
    console.log("9. Allow discount:", formDataObj.allow_discount);
    
    if (formDataObj.allow_discount === '1') {
      console.log("10. Discount amount:", formDataObj.discount_amount);
      console.log("11. Discount reason:", formDataObj.discount_reason);
    }
  }

  try {
    // Show loading indicator
    Swal.fire({
      title: 'Processing...',
      text: 'Please wait while we process your sale',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
    
    // Confirm sale via AJAX
    console.log("Sending AJAX request to sell_item_action.php");
    
    let response;
    try {
      // When using await, don't use success/error callbacks - they're redundant
      response = await $.ajax({
        url: "sell_item_action.php",
        type: "POST",
        data: formData,
        dataType: "json"
      });
      
      console.log("AJAX Success - Response:", response);
    } catch (ajaxError) {
      console.error("AJAX Exception:", ajaxError);
      
      // Handle AJAX errors (network errors, parsing errors, etc.)
      Swal.close(); // Close loading indicator
      Swal.fire({
        icon: 'error',
        title: 'Connection Error',
        text: 'Could not connect to the server. Please try again.',
        confirmButtonText: 'OK'
      });
      return; // Stop execution
    }
    
    // Now we can safely check the response
    if (!response || response.status !== "success") {
      Swal.close(); // Close loading indicator
      
      // Check if this is a stock-related error
      const isStockError = response.message && (
        response.message.includes("zero stock") || 
        response.message.includes("Not enough") ||
        response.message.includes("in stock")
      );
      
      if (isStockError) {
        // More detailed error for stock issues
        Swal.fire({
          icon: 'warning',
          title: 'Inventory Alert',
          text: response.message,
          confirmButtonText: 'OK',
          footer: '<a href="inventory.php">View Inventory</a>'
        });
      } else {
        // Standard error message
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: response.message || "Failed to process sale.",
          confirmButtonText: 'OK'
        });
      }
      
      console.error("Sale error:", response);
      return; // Stop execution
    }
    
    // Close loading indicator
    Swal.close();
    
    // Show success message
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: 'Sale processed successfully!',
      confirmButtonText: 'OK'
    });
    
    // Update buffet counts if buffet was included
    if ($("#includeBuffet").prop("checked")) {
      updateBuffetCounts();
    }
    
    // Generate receipt content
    const includeMenu = $("#includeMenu").prop("checked");
    const includeDrink = $("#includeDrink").prop("checked");
    const includeBuffet = $("#includeBuffet").prop("checked");
    const combinedGrandTotal = parseFloat($("#combinedGrandTotal").val() || 0);

    // Make sure at least one section is selected
    if (!includeMenu && !includeDrink && !includeBuffet) {
      Swal.fire("Warning", "Please select at least one item type", "warning");
      return;
    }
    let receiptContent = `
      <img id="receiptLogo" alt="Logo" style="width:100px; margin-bottom:0px;" />
      <h2>MAZA RESTO-BAR</h2>
      <p>Address: Boulevard Melchior Ndadaye, Peace Corner, Bujumbura</p>
      <p>Phone: +257 69 80 58 98 | Email: barmazaresto@gmail.com</p>
      <p id="receiptNumber">Receipt #: ${Date.now()}</p>
      <p>Date: ${new Date().toLocaleDateString()}</p>
      <hr>
    `;

    // Set receipt content in DOM
    $("#receiptContent").html(receiptContent);

    // Load logo (similar to sample's setupReceiptData)
    try {
      const logoData = await window.printer.getLogoBase64();
      if (logoData && logoData.startsWith("data:image/")) {
        $("#receiptContent #receiptLogo").attr("src", logoData);
      } else {
        console.warn("Invalid or empty logo data, logo will not display.");
        $("#receiptContent #receiptLogo").remove(); // Remove logo if invalid
      }
    } catch (logoErr) {
      console.error("Failed to load logo:", logoErr);
      $("#receiptContent #receiptLogo").remove(); // Remove logo on error
    }

    // Get final content after logo setup
    const finalContent = $("#receiptContent").html();

    // Print HTML
    const html = `
      <html>
      <head>
        <style>
          @page { margin: 0; }
          body { margin: 0; padding: 0; }
          #receiptContent {
            margin: 0 0 0 10px; /* Added 10px left margin */
            padding: 0;
            width: 200px; /* Reduced to fit with margin */
            font-family: Arial, sans-serif;
            text-align: center;
            font-size: 12px;
            line-height: 1.2;
          }
          #receiptContent img,
          #receiptContent h2,
          #receiptContent p,
          #receiptContent hr {
            margin: 0;
            padding: 0;
          }
          #receiptContent img {
            width: 100px;
            margin: 0 auto 0px auto;
            display: block;
          }
          #receiptContent h2 {
            font-size: 16px;
            font-weight: bold;
          }
          #receiptContent p {
            margin: 5px 0;
            font-size: 12px;
          }
          #receiptContent hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
            width: 100%;
          }
          #receiptContent table {
            width: 100%;
            margin: 5px 0;
            border-collapse: collapse;
            font-size: 11px;
          }
          #receiptContent th,
          #receiptContent td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
          }
          #receiptContent .text-end {
            text-align: right;
          }
          h4 {
            margin: 10px 0 5px 0;
            font-size: 14px;
            text-decoration: underline;
          }
        </style>
      </head>
      <body>
        <div id="receiptContent">${finalContent}</div>
      </body>
      </html>
    `;

    // Print with delay to ensure rendering
    setTimeout(async () => {
      try {
        // Check if we need two sections (client and audit)
        const printTwoSections = $("#printTwoSections").prop("checked");
        
        if (printTwoSections) {
          // Create two-section receipt with divider
          const twoSectionHtml = `
            <html>
              <head>
                <style>
                  @page { margin: 0; }
                  body { margin: 0; padding: 0; }
                  .receipt-container { 
                    display: flex;
                    flex-direction: column;
                    width: 100%;
                  }
                  .receipt-section {
                    margin: 0 auto; /* Changed from '0 0 0 10px' to '0 auto' for proper centering */
                    padding: 0 0 20px 0;
                    font-family: Arial, sans-serif;
                    text-align: center;
                    font-size: 12px;
                    line-height: 1.2;
                  }
                  .divider {
                    width: 100%;
                    border-top: 2px dashed #000;
                    margin: 15px 0;
                    text-align: center;
                    position: relative;
                  }
                  .divider-text {
                    position: absolute;
                    top: -10px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: white;
                    padding: 0 10px;
                    font-weight: bold;
                  }
                  /* All other receipt styles */
                  img { width: 100px; margin: 0 auto 0px auto; display: block; }
                  h2 { margin: 0; font-size: 16px; font-weight: bold; }
                  h4 { margin: 10px 0 5px 0; font-size: 14px; text-decoration: underline; }
                  p { margin: 5px 0; font-size: 12px; }
                  hr { border: none; border-top: 1px dashed #000; margin: 8px 0; width: 100%; }
                  table { width: 100%; margin: 5px 0; border-collapse: collapse; font-size: 11px; }
                  th, td { border: 1px solid #000; padding: 3px; text-align: center; }
                  .text-end { text-align: right; }
                </style>
              </head>
              <body>
                <div class="receipt-container">
                  <!-- CLIENT COPY -->
                  <div class="receipt-section">
                    <h3>CLIENT COPY</h3>
                    ${finalContent}
                  </div>
                  
                  <!-- DIVIDER -->
                  <div class="divider">
                    <div class="divider-text">✂ CUT HERE ✂</div>
                  </div>
                  
                  <!-- AUDIT COPY -->
                  <div class="receipt-section">
                    <h3>AUDIT COPY</h3>
                    ${finalContent}
                  </div>
                </div>
              </body>
            </html>
          `;
          
          await window.printer.printHTML(twoSectionHtml);
        } else {
          // Print single receipt as before
          await window.printer.printHTML(html);
        }
        
        console.log("Print succeeded");
        Swal.fire({
          title: "Success",
          text: "Sale confirmed and receipt printed successfully.",
          icon: "success"
        }).then(() => {
          window.location.reload();
        });
      } catch (printErr) {
        console.error("Print failed:", printErr);
        Swal.fire("Error", "Sale confirmed but failed to print receipt.", "error");
      }
    }, 100); // 100ms delay
  } catch (err) {
    console.error("Error:", err);
    Swal.fire("Error", "Failed to process sale.", "error");
  }
});

// Add search inputs to each table
$(document).ready(function() {
  // Function to filter table rows
  function filterTable(input, tableBody) {
    let filter = input.value.toUpperCase();
    let rows = $(tableBody + ' tr');
    
    rows.each(function() {
      let row = $(this);
      let text = row.text().toUpperCase();
      if (text.indexOf(filter) > -1) {
        row.show();
      } else {
        row.hide();
      }
    });
  }
  
  // Add event listeners
  $('#menuSearch').on('keyup', function() {
    filterTable(this, '#menuTableBody');
  });
  
  $('#drinkSearch').on('keyup', function() {
    filterTable(this, '#drinkTableBody');
  });
  
  $('#buffetSearch').on('keyup', function() {
    filterTable(this, '#buffetTableBody');
  });
  
  console.log('Table search functionality added');
});

</script>



<script>
  document.addEventListener('DOMContentLoaded', function() {
  const buffetDate = document.getElementById('buffetDate');
  const timeOfDaySelect = document.createElement('select');
  timeOfDaySelect.id = 'buffetTimeOfDay';
  timeOfDaySelect.name = 'buffet_time_of_day';
  timeOfDaySelect.className = 'form-select';
  timeOfDaySelect.required = true;
  // Time period is now handled automatically in the background
  // No visible dropdown needed

  const dishesSoldInput = document.getElementById('dishesSold');

  function autoSelectDateAndDayTime() {
    // Get current date in YYYY-MM-DD format
    const today = new Date().toISOString().split('T')[0];
    $('#buffetDate').val(today);
    console.log("Buffet date set to:", today);
    
    // Get current time and set the appropriate time period
    const now = new Date();
    const hours = now.getHours();
    let timePeriod = '';
    
    if (hours < 12) {
      timePeriod = 'Morning';
    } else if (hours < 17) {
      timePeriod = 'Noon';
    } else {
      timePeriod = 'Evening';
    }
    
    // Set the buffet time value
    $('#buffetTime').val(timePeriod);
    console.log("Buffet time period set to:", timePeriod);
    
    // Load dishes sold for this time period
    loadDishesSold();
  }

  function loadDishesSold() {
    const selectedDate = buffetDate.value;
    const selectedDayTime = timeOfDaySelect.value;

    if (!selectedDate || !selectedDayTime) return;

    fetch('fetch_dishes_sold.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `date=${selectedDate}&time_of_day=${selectedDayTime}`
    })
    .then(res => res.json())
    .then(data => {
      dishesSoldInput.value = data.dishes_sold || 0;
    })
    .catch(err => {
      dishesSoldInput.value = 0;
      console.error("Error fetching dishes sold:", err);
    });
  }

  // Event listeners
  buffetDate.addEventListener('change', loadDishesSold);
  timeOfDaySelect.addEventListener('change', loadDishesSold);
  document.getElementById('sale_type').addEventListener('change', function(){
    // Initialize with today's date and prepare buffet UI elements
    autoSelectDateAndDayTime();
  
    // Automatically fetch and update buffet price based on current time from buffet_preferences
    updateBuffetPrice();
    }
  );

  // Initialize correctly if buffet is pre-selected
  if (document.getElementById('sale_type').value === 'buffet') {
    autoSelectDateAndDayTime();
  }
});

</script>
<!-- Translation Script with Button Fix -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const lang = localStorage.getItem('userLang') || 'en';
    fetch(`../languages/${lang}.json`)
      .then(response => response.json())
      .then(data => translatePage(data))
      .catch(err => console.error("Translation load error:", err));
  });

  function translatePage(translations) {
    document.querySelectorAll('[data-key]').forEach(el => {
      const key = el.getAttribute('data-key');
      if (translations[key]) {
        if (el.tagName === 'INPUT') {
          el.value = translations[key];
        } else {
          el.textContent = translations[key];
        }
      }
    });
  }

  function switchLanguage(lang) {
    localStorage.setItem('userLang', lang);
    location.reload();
  }
</script>

<!-- Fix Select2 initialization -->
<script>
// Debug and force load Select2
console.log('Attempting to add search capability to select dropdowns');

// Define toggleSections function globally to avoid the 'not defined' error
window.toggleSections = function() {
  const includeMenu = $("#includeMenu").prop("checked");
  const includeDrink = $("#includeDrink").prop("checked");
  const includeBuffet = $("#includeBuffet").prop("checked");
  
  // Toggle visibility of sections based on checkboxes
  if (includeMenu) {
    enableSection("menuSection");
  } else {
    disableSection("menuSection");
  }
  
  if (includeDrink) {
    enableSection("drinkSection");
  } else {
    disableSection("drinkSection");
  }
  
  if (includeBuffet) {
    enableSection("buffetSection");
  } else {
    disableSection("buffetSection");
  }
};

// Initialize the document ready function with all section setup

// Function to load accompaniment options
function loadAccompanimentOptions() {
  fetch('fetch_accompaniments.php')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error loading accompaniments:', data.error);
        return;
      }
      
      // Store accompaniment options globally
      accompanimentOptions = '';
      data.forEach(item => {
        accompanimentOptions += `<option value="${item.accompaniment_id}" data-price="${item.accompaniment_price}">${item.accompaniment_name} (${item.accompaniment_price} BIF)</option>`;
      });
      
      // Update all existing accompaniment selects
      $('.accompaniment-select').each(function() {
        const currentValue = $(this).val();
        $(this).html(`<option value="" data-key="report.selectAccompaniment">Select Accompaniment</option>${accompanimentOptions}`);
        $(this).val(currentValue);
      });
    })
    .catch(error => console.error('Failed to load accompaniments:', error));
}

// Function to update buffet price based on current time and buffet_preferences
function updateBuffetPrice() {
  const buffetPriceInput = $('#buffetPrice');
  const discountAmountInput = $('#discount-amount');
  const buffetDiscountDisplay = $('#buffet-discount-info');
  
  // Fetch price from updated API that uses buffet_preferences table
  fetch('get_buffet_price.php')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error getting buffet price:', data.error);
        return;
      }
      
      // Set the period name in the hidden field
      $('#buffetTime').val(data.period_name);
      
      // Show the period name in the UI
      $('#buffetPeriodDisplay').text(data.period_name);
      
      // Set the buffet price to the base_price directly
      buffetPriceInput.val(data.base_price);
      
      // Don't auto-check discount checkbox, but do prepare the discount amount if user checks it manually
      if (data.fixed_discount > 0) {
        // Only set the discount amount if checkbox is already checked by user
        if ($('#allow-discount').is(':checked')) {
          discountAmountInput.val(data.fixed_discount);
          // Update totals to reflect the discount
          updateBuffetTotals();
        }
      }
      
      // Display discount information if applicable
      let discountText = '';
      if (data.fixed_discount > 0 || data.percentage_discount > 0) {
        discountText = `<span class="text-success">Available discount: ${data.fixed_discount.toLocaleString()} BIF</span>`;
        if (data.percentage_discount > 0) {
          discountText += `<br><small>Additional percentage discount: ${data.percentage_discount}%</small>`;
        }
        buffetDiscountDisplay.html(discountText);
        buffetDiscountDisplay.show();
        
        // Never auto-check the discount checkbox, even if there's a discount available
        // Keep checkbox unchecked by default as per user requirement
      } else {
        buffetDiscountDisplay.hide();
      }
      
      // Show notice if returned (using default pricing)
      if (data.notice) {
        console.info(data.notice);
      }
      
      // Update totals
      updateBuffetTotals();
    })
    .catch(error => console.error('Failed to get buffet price:', error));
}

// Function to update buffet totals
function updateBuffetTotals() {
  // Get buffet price and quantity
  const price = parseFloat($('#buffetPrice').val()) || 0;
  const quantity = parseInt($('#dishesSold').val()) || 0;
  const subtotal = price * quantity;
  
  // Calculate accompaniment total - updated for table layout
  let accompanimentTotal = 0;
  $('.accompanimentTotal').each(function() {
    accompanimentTotal += parseFloat($(this).val()) || 0;
  });
  
  // Calculate discount
  const discount = parseFloat($('#discount-amount').val()) || 0;
  
  // Calculate grand total
  const grandTotal = subtotal + accompanimentTotal - discount;
  
  // Update the totals
  $('#buffet-subtotal').text(subtotal.toLocaleString());
  $('#accompaniment-total').text(accompanimentTotal.toLocaleString());
  $('#discount-total').text(discount.toLocaleString());
  $('#buffet-grand-total').text(grandTotal.toLocaleString());
  
  // Update hidden total field
  $('#total_amount').val(grandTotal);
}

// Function to add a new accompaniment row to the table - now using the same menu items as regular menu
function addAccompanimentRow() {
  if ($("#accompaniments-section").hasClass("hidden")) return;
  const rowCount = $("#accompanimentTableBody tr").length;
  const newRow = `
    <tr>
      <td>
        <select class="form-select accompanimentSelect" name="accompaniment_id[${rowCount}]" required>
          <option value="">Select Menu Item</option>
          ${menuOptions}
        </select>
      </td>
      <td>
        <input type="number" class="form-control accompanimentQty" name="accompaniment_qty[${rowCount}]" min="1" value="1">
      </td>
      <td>
        <input type="number" class="form-control accompanimentTotal" name="accompaniment_price[${rowCount}]" readonly>
      </td>
      <td>
        <button type="button" class="btn btn-primary addAccompanimentRowBtn">+</button>
        <button type="button" class="btn btn-danger removeAccompanimentRowBtn">−</button>
      </td>
    </tr>
  `;
  const newRowJQuery = $(newRow); // Convert HTML string to jQuery object
  $("#accompanimentTableBody").append(newRowJQuery);
  if ($.fn.select2) {
    newRowJQuery.find(".accompanimentSelect").select2({
      placeholder: "Select Menu Item",
      allowClear: true,
      width: "100%"
    });
  }
  updateBuffetTotals();
}

// Safely handle buffet counts without errors
function loadBuffetCounts() {
  // Skip actual fetch to avoid 404 errors
  console.log('Buffet count tracking is disabled');
  // Set default values
  window.buffetCountTotal = 0;
  window.buffetCountMorning = 0;
  window.buffetCountNoon = 0;
  window.buffetCountEvening = 0;
}

// Function to load accompaniment options from server
function loadAccompanimentOptions() {
  // Fetch accompaniment data from server
  fetch('get_accompaniments.php')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error loading accompaniments:', data.error);
        return;
      }
      
      // Clear the options string
      accompanimentOptions = '';
      
      // Generate options HTML for accompaniments
      data.forEach(item => {
        accompanimentOptions += `<option value="${item.id}" data-price="${item.price}">${item.name} (${item.price.toLocaleString()} BIF)</option>`;
      });
      
      // Add any existing rows if the section is already visible
      if ($('#allow-accompaniments').is(':checked') && $('#accompanimentTableBody tr').length === 0) {
        addAccompanimentRow();
      }
    })
    .catch(err => console.error('Error fetching accompaniments:', err));
}

// Function to update buffet counts after a sale
function updateBuffetCounts() {
  // The actual update happens on the server via the AJAX request to sell_item_action.php
  // This function just refreshes the displayed counts
  loadBuffetCounts();
}

// Initialize the buffet date picker to today's date and let the server determine the time period
function autoSelectDateAndDayTime() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  const currentDate = `${year}-${month}-${day}`;
  
  // Set the hidden input value for date
  $("#buffetDate").val(currentDate);
  
  // We'll let the server determine the time period based on the current time
  // This is handled in updateBuffetPrice() which will be called after this function
  // The server will check buffet_preferences table to determine the appropriate time period
  
  // Initialize the time field with empty value - will be populated by updateBuffetPrice()
  $("#buffetTime").val('');
  
  // Add period display element if it doesn't exist
  if ($("#buffetPeriodDisplay").length === 0) {
    $("#buffetPriceContainer").prepend('<div class="mb-2"><strong>Period: </strong><span id="buffetPeriodDisplay"></span></div>');
  }
  
  // Add discount info element if it doesn't exist
  if ($("#buffet-discount-info").length === 0) {
    $("#buffetPriceContainer").append('<div id="buffet-discount-info" class="mt-2" style="display:none;"></div>');
  }
}

$(document).ready(function() {
  // Initialize sections based on checkboxes
  loadOptions();
  
  // Load buffet counts and accompaniment options
  loadBuffetCounts();
  
  // Define the accompanimentOptions variable to store the options HTML
  let accompanimentOptions = '';
  
  // Load accompaniment options from the server
  loadAccompanimentOptions();
  
  // Set up sections based on checkbox state
  const includeMenu = $("#includeMenu").prop("checked");
  const includeDrink = $("#includeDrink").prop("checked");
  const includeBuffet = $("#includeBuffet").prop("checked") || true; // Ensure buffet is enabled by default
  
  // Update checkbox to match default state
  if (!includeBuffet) {
    $("#includeBuffet").prop("checked", true);
  }
  
  if (includeMenu) {
    enableSection("menuSection");
    if ($("#menuTableBody tr").length === 0) {
      addMenuRow();
    }
  } else {
    disableSection("menuSection");
  }
  
  if (includeDrink) {
    enableSection("drinkSection");
    if ($("#drinkTableBody tr").length === 0) {
      addDrinkRow();
    }
  } else {
    disableSection("drinkSection");
  }
  
  // Always enable buffet section by default
  enableSection("buffetSection");
  makeBuffetRequired();
  autoSelectDateAndDayTime();
  updateBuffetPrice();
  
  // Set focus on dishes sold input
  $("#dishesSold").focus();
  
  // Set up event handlers for accompaniments and discounts
  $("#add-accompaniment").on('click', function() {
    addAccompanimentRow();
  });
  
  // Event delegation for dynamically added elements
  $(document).on('click', '.remove-accompaniment', function() {
    $(this).closest('.accompaniment-row').remove();
    updateBuffetTotals();
  });
  
  $(document).on('change', '.accompaniment-select', function() {
    const selectedOption = $(this).find('option:selected');
    const price = selectedOption.data('price') || 0;
    $(this).closest('.accompaniment-row').find('.accompaniment-price').val(price);
    updateBuffetTotals();
  });
  
  // Set up change handlers for buffet inputs
  $("#dishesSold, #discount-amount").on('input', function() {
    updateBuffetTotals();
  });
  
  // No manual modification handlers needed since fields are read-only
  // Just ensure totals are updated when necessary
  
  // Handle changes to accompaniment quantity to update total price
  $(document).on('input', '.accompanimentQty', function() {
    const row = $(this).closest('tr');
    const selectedOption = row.find('.accompanimentSelect option:selected');
    const price = selectedOption.data('price') || 0;
    const qty = parseFloat($(this).val()) || 0;
    row.find('.accompanimentTotal').val((price * qty).toFixed(2));
    updateBuffetTotals();
  });
  
  // Handle click on Add button in accompaniment rows
  $(document).on('click', '.addAccompanimentRowBtn', function() {
    addAccompanimentRow();
  });
  
  // Handle click on Remove button in accompaniment rows
  $(document).on('click', '.removeAccompanimentRowBtn', function() {
    $(this).closest('tr').remove();
    updateBuffetTotals();
  });
  
  // Handle change on accompaniment select dropdown
  $(document).on('change', '.accompanimentSelect', function() {
    const selectedOption = $(this).find('option:selected');
    const price = selectedOption.data('price') || 0;
    const row = $(this).closest('tr');
    
    // Update price but don't update quantity if it's already set
    if (row.find('.accompanimentQty').val() === '' || parseFloat(row.find('.accompanimentQty').val()) <= 0) {
      row.find('.accompanimentQty').val(1);
    }
    
    // Calculate and update total price
    const qty = parseFloat(row.find('.accompanimentQty').val()) || 0;
    row.find('.accompanimentTotal').val((price * qty).toFixed(2));
    
    // Check for duplicates
    checkDuplicate($(this), '.accompanimentSelect');
    updateBuffetTotals();
  });
  
  // Set up handlers for accompaniments and discount permission checkboxes
  $("#allow-accompaniments").on('change', function() {
    const section = $("#accompaniments-section");
    if ($(this).is(':checked')) {
      section.removeClass('hidden');
      // Add a default accompaniment row if none exists
      if ($("#accompanimentTableBody tr").length === 0) {
        addAccompanimentRow();
      }
    } else {
      section.addClass('hidden');
      // Clear any existing accompaniments
      $("#accompanimentTableBody").empty();
      // Update totals since accompaniments are removed
      updateBuffetTotals();
    }
  });
  
  $("#allow-discount").on('change', function() {
    const section = $("#discount-section");
    if ($(this).is(':checked')) {
      section.removeClass('hidden');
      
      // When discount is enabled, fetch current buffet details to apply the fixed discount
      fetch('get_buffet_price.php')
        .then(response => response.json())
        .then(data => {
          if (!data.error && data.fixed_discount > 0) {
            // Set the discount amount to the fixed discount from buffet_preferences
            $("#discount-amount").val(data.fixed_discount);
            updateBuffetTotals();
          }
        })
        .catch(error => console.error('Error fetching discount information:', error));
    } else {
      section.addClass('hidden');
      // Reset discount to zero when disabled
      $("#discount-amount").val(0);
      $("#discount-reason").val('');
      // Update totals since discount is removed
      updateBuffetTotals();
    }
  });
});

// Buffet count functionality removed as requested

// Function to load accompaniment options
function loadAccompanimentOptions() {
  console.log('Loading accompaniment options...');
  return fetch('get_accompaniments.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      console.log('Accompaniment options loaded:', data);
      if (data.status === 'success' && Array.isArray(data.accompaniments)) {
        // Store the accompaniments globally for reference
        window.accompaniments = data.accompaniments;
        
        // Generate HTML options for select elements
        const optionsHtml = data.accompaniments.map(item => 
          `<option value="${item.id}" data-price="${item.price}">${item.name} - ${item.price} BIF</option>`
        ).join('');
        
        // Cache the options for future use
        window.accompanimentOptionsHtml = optionsHtml;
        
        // Update any existing select elements
        $('.accompanimentSelect').each(function() {
          const currentVal = $(this).val();
          $(this).html(`<option value="">Select Accompaniment</option>${optionsHtml}`);
          if (currentVal) {
            $(this).val(currentVal);
          }
        });
      }
      return data;
    })
    .catch(error => {
      console.error('Error fetching accompaniments:', error);
    });
}

// Function to update buffet counts after a sale
function updateBuffetCounts() {
  const timeLabel = $('#buffetTime').val();
  // Increment the relevant counter
  if (timeLabel === 'Morning') {
    window.buffetCountMorning = (window.buffetCountMorning || 0) + 1;
  } else if (timeLabel === 'Noon') {
    window.buffetCountNoon = (window.buffetCountNoon || 0) + 1;
  } else if (timeLabel === 'Evening') {
    window.buffetCountEvening = (window.buffetCountEvening || 0) + 1;
  }
  console.log(`Updated buffet count for ${timeLabel}:`, 
    window.buffetCountMorning, window.buffetCountNoon, window.buffetCountEvening);
}

// Check if jQuery is loaded
if (typeof jQuery === 'undefined') {
  console.error('jQuery not available!');
} else {
  console.log('jQuery is available.');
  // Load accompaniment options when the page loads
  $(document).ready(function() {
    loadAccompanimentOptions();
  });
  
  // Force load Select2 directly
  var selectScript = document.createElement('script');
  selectScript.src = 'assets/js/select2.min.js';
  selectScript.onload = function() {
    console.log('Select2 script loaded successfully');
    
    // Add a small delay before initializing
    setTimeout(function() {
      console.log('Initializing Select2...', typeof $.fn.select2);
      
      if (typeof $.fn.select2 === 'function') {
        try {
          $('#menu-select, #drink-select, #buffet-select').select2({
            width: 'resolve',
            dropdownAutoWidth: true,
            placeholder: 'Search...',
            allowClear: true
          });
          console.log('Search capability added successfully');
        } catch(e) {
          console.error('Error applying Select2:', e);
        }
      } else {
        console.error('Select2 function not available after loading!');
      }
    }, 500);
  };
  
  document.body.appendChild(selectScript);
}
</script>

<!-- Force load Select2 CSS -->
<link href="assets/css/select2.min.css" rel="stylesheet" />

<!-- Load buffet toggles script -->
<script src="buffet_toggles.js"></script>

<script>
// Complete solution for searchable dropdowns
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded, initializing searchable dropdowns');
  
  // Force load Select2 JS
  var script = document.createElement('script');
  script.src = 'assets/js/select2.min.js';
  script.onload = function() {
    console.log('Select2 script loaded successfully');
    
    // Initialize after a small delay
    setTimeout(function() {
      if (typeof $.fn.select2 === 'function') {
        // Apply to the specific selects
        $('#menu-select, #drink-select, #buffet-select').select2({
          width: 'resolve',
          dropdownAutoWidth: true,
          minimumResultsForSearch: 0, // Force search box to always show
          placeholder: 'Search...',
          allowClear: true
        });
        
        // Add custom CSS to ensure search box is visible
        var style = document.createElement('style');
        style.textContent = `
          .select2-search { 
            display: block !important;
            padding: 8px !important;
          }
          .select2-search input {
            height: 30px !important;
            width: 100% !important;
            padding: 6px 10px !important;
            font-size: 14px !important;
          }
          .select2-dropdown {
            min-width: 200px !important;
          }
        `;
        document.head.appendChild(style);
        
        console.log('Search capability added successfully');
      } else {
        console.error('Select2 function not available!');
      }
    }, 500);
  };
  
  document.body.appendChild(script);
});
</script>

<!-- Buffet section toggle and discount checkbox fix -->
<script>
$(document).ready(function() {
  // FIRST ACTION: Force discount checkbox to be unchecked IMMEDIATELY
  $("#allow-discount").prop("checked", false);
  
  // Fix 1: Make sure buffet section properly toggles
  function fixBuffetToggle() {
    // Double-ensure the discount checkbox is unchecked by default
    $("#allow-discount").prop("checked", false);
    
    // Clear all previous event handlers for the checkboxes
    $("#includeMenu, #includeDrink, #includeBuffet").off('change');
    
    // Add a completely new event handler for all checkboxes
    $("#includeMenu, #includeDrink, #includeBuffet").on('change', function() {
      // Get current state of all checkboxes
      const includeMenu = $("#includeMenu").prop("checked");
      const includeDrink = $("#includeDrink").prop("checked");
      const includeBuffet = $("#includeBuffet").prop("checked");
      
      // Handle menu section
      if (includeMenu) {
        enableSection("menuSection");
        if ($("#menuTableBody tr").length === 0) {
          addMenuRow();
        }
      } else {
        disableSection("menuSection");
      }
      
      // Handle drink section
      if (includeDrink) {
        enableSection("drinkSection");
        if ($("#drinkTableBody tr").length === 0) {
          addDrinkRow();
        }
      } else {
        disableSection("drinkSection");
      }
      
      // Handle buffet section - ONLY enable if checkbox is checked
      if (includeBuffet) {
        enableSection("buffetSection");
        makeBuffetRequired();
        autoSelectDateAndDayTime();
        updateBuffetPrice();
      } else {
        disableSection("buffetSection");
      }
    });
    
    // Force-trigger the change event to apply current checkbox states
    $("#includeBuffet").trigger('change');
  }
  
  // Run the fix after the page has fully loaded
  setTimeout(fixBuffetToggle, 500);
});
</script>
</body>
</html>

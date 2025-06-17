<?php
session_start();
require_once 'connection.php';
// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title data-key="order.history">Order History</title>
  
  <?php include_once './header.php'; ?>
  <style>
    .main-container {
      margin-top: 100px;
      margin-left: 70px;
    }
    .card {
      box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    }
    /* Adjust styling if sidebar toggling is used */
    .sidebar.collapsed {
      width: 70px;
      transition: width 0.3s;
    }
    .main-container.full {
      margin-left: 0 !important;
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
      <h1 data-key="order.history">Order History</h1>
    </div>
    <div class="card-body">
      <table id="historyTable" class="table table-bordered display" style="width:100%">
        <thead>
          <tr>
            <th data-key="order.orderId">Order ID</th>
            <th data-key="order.customerName">Customer Name</th>
            <th data-key="order.totalPrice">Total Price</th>
            <th data-key="order.orderDate">Order Date</th>
            <th data-key="order.status">Status</th>
            <th data-key="order.action">Action</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Modal for Viewing Order Details -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" data-key="order.orderDetails">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="orderDetailsContent">
          <!-- Order details will be loaded here dynamically -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Include Footer -->
<?php include_once './footer.php'; ?>

<script>
$(document).ready(function () {
  // Initialize DataTable for order history
  const historyTable = $('#historyTable').DataTable({
    processing: true, // Show processing indicator
    serverSide: true, // Server-side processing
    ajax: {
      url: 'fetch_orders_history.php',
      type: 'GET'
    },
    order: [[0, "desc"]],
    columns: [
      { data: 'order_id' },
      { data: 'customer_name' },
      { data: 'total_price' },
      { data: 'order_date' },
      { data: 'status' },
      {
        data: null,
        orderable: false,
        render: function (data, type, row) {
          return `
            <button class="btn btn-sm btn-info viewDetailsBtn" data-id="${row.order_id}" data-key="order.view">
              ${getTranslation('order.view')}
            </button>
          `;
        }
      }
    ],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100]
  });
  
  // Sidebar toggling logic
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
  
  // Load order details and show in modal when "View" is clicked
  $('#historyTable').on('click', '.viewDetailsBtn', function() {
    const orderId = $(this).data('id');
    $.ajax({
      url: 'fetch_single_order_details.php',
      type: 'GET',
      data: { order_id: orderId },
      dataType: 'html',
      success: function(response) {
        $('#orderDetailsContent').html(response);
        $('#viewDetailsModal').modal('show');
      },
      error: function() {
        Swal.fire(getTranslation('order.error'), 'Failed to load order details.', 'error');
      }
    });
  });
  
  // Language Switching & Translation Logic
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
          element.value = translations[key];
        } else if (element.tagName === 'BUTTON') {
          element.textContent = translations[key];
        } else {
          element.textContent = translations[key];
        }
      }
    });
  }
  
  function getTranslation(key, params = {}) {
    const translations = JSON.parse(localStorage.getItem('translations')) || {};
    let translation = translations[key] || (currentLang === 'en' ? 'Default English Text' : 'Texte par défaut en français');
    if (params && Object.keys(params).length > 0) {
      Object.keys(params).forEach(param => {
        translation = translation.replace(`{${param}}`, params[param]);
      });
    }
    return translation;
  }
  
  function switchLanguage(lang) {
    localStorage.setItem('userLang', lang);
    loadTranslations(lang);
  }
});
</script>
</body>
</html>

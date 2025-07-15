<?php
session_start();
require_once 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- The title will be translated -->
  <title data-key="report.kotHistory"></title>

  <!-- DataTables CSS -->
  <!-- Bootstrap CSS -->

  <!-- DataTables JS -->
  <!-- Bootstrap JS -->

  <?php include_once './header.php'; ?>
  <style>
    .main-container {
      margin-top: 100px;
      margin-left: 70px;
    }
    .card {
      box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    }
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
        <!-- Heading translated using data-key -->
        <h1 data-key="report.kotHistoryHeader"></h1>
      </div>
      <div class="card-body">
        <table id="kotHistoryTable" class="table table-bordered display" style="width:100%">
          <thead>
            <tr>
              <th data-key="report.kotID"></th>
              <th data-key="report.orderID"></th>
              <th data-key="report.createdAt"></th>
              <th data-key="report.status"></th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>

  <!-- Include Footer -->
  <?php include_once './footer.php'; ?>

  <script>
  $(document).ready(function () {
    $('#kotHistoryTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: 'fetch_kot_history.php',
        type: 'GET'
      },
      order: [[2, "desc"]], // Sort by created_at descending
      columns: [
        { data: 'kot_id' },
        { data: 'order_id' },
        { data: 'created_at' },
        { data: 'kot_status' }
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
  });
  </script>

  <!-- Translation Script (with button fix) -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const lang = localStorage.getItem('userLang') || 'en';
      fetch(`../languages/${lang}.json`)
        .then(response => response.json())
        .then(data => translatePage(data));
    });

    function translatePage(translations) {
      document.querySelectorAll('[data-key]').forEach(el => {
        const key = el.getAttribute('data-key');
        if (translations[key]) {
          // If it's an INPUT element, use .value; otherwise, use .textContent
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
       <!-- Your jQuery logic for sidebars, etc. -->
       <script>
        $(document).ready(function () {
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
                    card.style.marginLeft = '2%';
                    card.style.marginTop= '5%';
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
    </script>


</body>
</html>

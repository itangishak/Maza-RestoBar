<?php
session_start();
require_once 'connection.php';
require_once __DIR__ . '/../includes/auth.php';

require_privilege(['Storekeeper']);

// Calculate key stock metrics
function fetch_scalar($conn, $sql) {
    $res = $conn->query($sql);
    if ($res && ($row = $res->fetch_assoc())) {
        return array_values($row)[0];
    }
    return 0;
}

$totalItems = fetch_scalar($conn, "SELECT COUNT(*) FROM inventory_items");
$lowStock   = fetch_scalar($conn, "SELECT COUNT(*) FROM inventory_items WHERE quantity_in_stock <= reorder_level");
$inventoryValue = fetch_scalar($conn, "SELECT COALESCE(SUM(quantity_in_stock * unit_cost),0) FROM inventory_items");
$pendingOrders  = fetch_scalar($conn, "SELECT COUNT(*) FROM purchase_orders WHERE status <> 'received'");

$lowStockItems = [];
$lowRes = $conn->query("SELECT item_name, quantity_in_stock, reorder_level FROM inventory_items WHERE quantity_in_stock <= reorder_level ORDER BY quantity_in_stock ASC");
if ($lowRes) {
    while ($row = $lowRes->fetch_assoc()) {
        $lowStockItems[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stock Dashboard</title>
  <?php include_once 'header.php'; ?>
  <style>
    :root {
      --primary-color: #2c3e50;
      --secondary-color: #3498db;
      --accent-color: #e74c3c;
      --success-color: #27ae60;
      --warning-color: #f39c12;
      --light-bg: #f8f9fa;
      --dark-text: #2c3e50;
      --light-text: #7f8c8d;
      --border-color: #e9ecef;
      --shadow: 0 2px 15px rgba(0,0,0,0.1);
      --shadow-hover: 0 5px 25px rgba(0,0,0,0.15);
      --border-radius: 12px;
      --transition: all 0.3s ease;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      margin: 0;
      padding: 0;
    }

    .main-container {
      margin-top: 120px;
      margin-left: 70px;
      padding: 0 20px;
      transition: var(--transition);
    }

    .sidebar.collapsed {
      width: 70px;
      transition: var(--transition);
    }

    .main-container.full {
      margin-left: 20px !important;
    }

    .pagetitle {
      margin-bottom: 30px;
      text-align: center;
    }

    .pagetitle h1 {
      color: var(--primary-color);
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
      position: relative;
    }

    .pagetitle h1::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, var(--secondary-color), var(--success-color));
      border-radius: 2px;
    }

    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }

    .stat-card {
      background: white;
      border-radius: var(--border-radius);
      padding: 30px;
      box-shadow: var(--shadow);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      border: none;
      text-align: center;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--secondary-color), var(--success-color));
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }

    .stat-card.inventory {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .stat-card.alerts {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      color: white;
    }

    .stat-card.value {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
    }

    .stat-card.orders {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
      color: white;
    }

    .stat-icon {
      font-size: 3rem;
      margin-bottom: 15px;
      opacity: 0.9;
    }

    .stat-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 10px;
      opacity: 0.9;
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }

    .low-stock-section {
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      overflow: hidden;
      margin-top: 30px;
    }

    .card-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 20px 30px;
      border: none;
      position: relative;
    }

    .card-header h5 {
      margin: 0;
      font-size: 1.4rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card-header::before {
      content: '⚠️';
      font-size: 1.5rem;
    }

    .table-container {
      padding: 0;
      overflow-x: auto;
    }

    .modern-table {
      width: 100%;
      border-collapse: collapse;
      margin: 0;
      font-size: 0.95rem;
    }

    .modern-table thead {
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    }

    .modern-table th {
      padding: 18px 20px;
      text-align: left;
      font-weight: 600;
      color: var(--dark-text);
      border-bottom: 2px solid var(--border-color);
      position: relative;
    }

    .modern-table td {
      padding: 15px 20px;
      border-bottom: 1px solid var(--border-color);
      transition: var(--transition);
    }

    .modern-table tbody tr {
      transition: var(--transition);
    }

    .modern-table tbody tr:hover {
      background: linear-gradient(135deg, #f8f9ff, #fff5f5);
      transform: scale(1.01);
    }

    .modern-table tbody tr:nth-child(even) {
      background: rgba(248, 249, 250, 0.5);
    }

    .quantity-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.85rem;
    }

    .quantity-critical {
      background: linear-gradient(135deg, #ff6b6b, #ee5a52);
      color: white;
    }

    .quantity-low {
      background: linear-gradient(135deg, #ffa726, #fb8c00);
      color: white;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--light-text);
    }

    .empty-state .icon {
      font-size: 4rem;
      margin-bottom: 20px;
      opacity: 0.5;
    }

    .empty-state h3 {
      margin: 0 0 10px 0;
      color: var(--dark-text);
    }

    .loading-spinner {
      display: none;
      text-align: center;
      padding: 40px;
    }

    .spinner {
      width: 40px;
      height: 40px;
      border: 4px solid var(--border-color);
      border-top: 4px solid var(--secondary-color);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .fade-in {
      animation: fadeIn 0.6s ease-out;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .main-container {
        margin-left: 0;
        margin-top: 80px;
        padding: 0 15px;
      }

      .pagetitle h1 {
        font-size: 2rem;
      }

      .dashboard-cards {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .stat-card {
        padding: 25px;
      }

      .stat-value {
        font-size: 2rem;
      }

      .modern-table {
        font-size: 0.9rem;
      }

      .modern-table th,
      .modern-table td {
        padding: 12px 15px;
      }
    }

    @media (max-width: 480px) {
      .pagetitle h1 {
        font-size: 1.8rem;
      }

      .stat-card {
        padding: 20px;
      }

      .stat-value {
        font-size: 1.8rem;
      }

      .modern-table th,
      .modern-table td {
        padding: 10px 12px;
      }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
      :root {
        --light-bg: #1a1a1a;
        --dark-text: #ffffff;
        --light-text: #cccccc;
        --border-color: #333333;
      }

      body {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      }

      .stat-card {
        background: #2c3e50;
        color: white;
      }

      .low-stock-section {
        background: #2c3e50;
      }

      .modern-table tbody tr:nth-child(even) {
        background: rgba(52, 73, 94, 0.3);
      }
    }

    /* Navbar Dropdown Fixes */
    .navbar-nav .dropdown-menu {
      position: absolute;
      top: 100%;
      left: 0;
      z-index: 1000;
      display: none;
      float: left;
      min-width: 160px;
      padding: 5px 0;
      margin: 2px 0 0;
      font-size: 14px;
      text-align: left;
      background-color: #fff;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.175);
      background-clip: padding-box;
    }

    .navbar-nav .dropdown-menu.show {
      display: block;
    }

    .navbar-nav .dropdown-menu > li > a {
      display: block;
      padding: 3px 20px;
      clear: both;
      font-weight: normal;
      line-height: 1.42857143;
      color: #333;
      white-space: nowrap;
      text-decoration: none;
    }

    .navbar-nav .dropdown-menu > li > a:hover,
    .navbar-nav .dropdown-menu > li > a:focus {
      color: #262626;
      background-color: #f5f5f5;
    }

    .navbar-nav .dropdown-toggle::after {
      display: inline-block;
      margin-left: 0.255em;
      vertical-align: 0.255em;
      content: "";
      border-top: 0.3em solid;
      border-right: 0.3em solid transparent;
      border-bottom: 0;
      border-left: 0.3em solid transparent;
    }

    .navbar-nav .dropdown-toggle:empty::after {
      margin-left: 0;
    }

    .navbar-nav .dropdown.show .dropdown-toggle::after {
      transform: rotate(180deg);
    }

    /* Ensure dropdown works on mobile */
    @media (max-width: 767px) {
      .navbar-nav .dropdown-menu {
        position: static;
        float: none;
        width: auto;
        margin-top: 0;
        background-color: transparent;
        border: 0;
        box-shadow: none;
      }
    }
    @media print {
      .main-container {
        margin: 0;
        padding: 0;
      }

      .stat-card {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ddd;
      }

      .low-stock-section {
        box-shadow: none;
        border: 1px solid #ddd;
      }
    }
  </style>
</head>
<body>
  <?php include_once 'navbar.php'; ?>
  <?php include_once 'sidebar.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1 data-key="report.stockDashboard">Stock Dashboard</h1>
    </div>
    
    <section class="section dashboard">
      <div class="container main-container">
        <div class="dashboard-cards fade-in">
          <div class="stat-card inventory">
            <div class="stat-icon">📦</div>
            <div class="stat-title" data-key="report.inventoryItems">Inventory Items</div>
            <div class="stat-value"><?php echo number_format($totalItems, 0); ?></div>
          </div>
          
          <div class="stat-card alerts">
            <div class="stat-icon">⚠️</div>
            <div class="stat-title" data-key="report.lowStockAlerts">Low Stock Alerts</div>
            <div class="stat-value"><?php echo number_format($lowStock, 0); ?></div>
          </div>
          
          <div class="stat-card value">
            <div class="stat-icon">💰</div>
            <div class="stat-title" data-key="dashboard.inventoryValue">Inventory Value</div>
            <div class="stat-value">BIF <?php echo number_format($inventoryValue, 2); ?></div>
          </div>
          
          <div class="stat-card orders">
            <div class="stat-icon">📋</div>
            <div class="stat-title" data-key="report.pendingOrders">Pending Orders</div>
            <div class="stat-value"><?php echo number_format($pendingOrders, 0); ?></div>
          </div>
        </div>

        <?php if ($lowStockItems): ?>
        <div class="low-stock-section fade-in">
          <div class="card-header">
            <h5 data-key="report.lowStockItems">Low Stock Items</h5>
          </div>
          <div class="table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th data-key="inventory.itemName">Item Name</th>
                  <th data-key="inventory.quantity">Current Stock</th>
                  <th data-key="inventory.reorderLevel">Reorder Level</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($lowStockItems as $item): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                    </td>
                    <td><?php echo number_format($item['quantity_in_stock'], 2); ?></td>
                    <td><?php echo number_format($item['reorder_level'], 2); ?></td>
                    <td>
                      <?php 
                      $ratio = $item['quantity_in_stock'] / max($item['reorder_level'], 1);
                      if ($ratio <= 0.5): ?>
                        <span class="quantity-badge quantity-critical">Critical</span>
                      <?php else: ?>
                        <span class="quantity-badge quantity-low">Low Stock</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php else: ?>
        <div class="low-stock-section fade-in">
          <div class="empty-state">
            <div class="icon">✅</div>
            <h3>All Stock Levels Are Good!</h3>
            <p>No items are currently below their reorder levels.</p>
          </div>
        </div>
        <?php endif; ?>

        <div class="loading-spinner" id="loadingSpinner">
          <div class="spinner"></div>
          <p>Loading dashboard data...</p>
        </div>
      </div>
    </section>
  </main>

  <?php include_once './footer.php'; ?>

  <!-- Bootstrap JavaScript for dropdown functionality -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Fix for navbar dropdown functionality - works offline
    function initializeNavbarDropdowns() {
      // Handle dropdown toggles manually if Bootstrap JS fails
      const dropdownToggleBtns = document.querySelectorAll('[data-bs-toggle="dropdown"], [data-toggle="dropdown"]');
      
      dropdownToggleBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const dropdown = this.nextElementSibling;
          if (dropdown && dropdown.classList.contains('dropdown-menu')) {
            // Close other dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
              if (menu !== dropdown) {
                menu.classList.remove('show');
                menu.parentElement.classList.remove('show');
              }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('show');
            this.parentElement.classList.toggle('show');
            
            // Handle aria attributes
            const expanded = dropdown.classList.contains('show');
            this.setAttribute('aria-expanded', expanded);
          }
        });
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
          document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
            menu.parentElement.classList.remove('show');
            const toggle = menu.parentElement.querySelector('[data-bs-toggle="dropdown"], [data-toggle="dropdown"]');
            if (toggle) {
              toggle.setAttribute('aria-expanded', 'false');
            }
          });
        }
      });
      
      // Handle keyboard navigation
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
            menu.parentElement.classList.remove('show');
            const toggle = menu.parentElement.querySelector('[data-bs-toggle="dropdown"], [data-toggle="dropdown"]');
            if (toggle) {
              toggle.setAttribute('aria-expanded', 'false');
              toggle.focus();
            }
          });
        }
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      // Initialize navbar dropdowns
      initializeNavbarDropdowns();
      // Initialize language
      const systemLang = navigator.language || 'en';
      const currentLang = localStorage.getItem('userLang') || systemLang;
      loadTranslations(currentLang);

      // Sidebar toggle functionality
      const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
      const sidebar = document.querySelector('.sidebar');
      const mainContainer = document.querySelector('.main-container');
      
      if (toggleSidebarBtn && sidebar && mainContainer) {
        toggleSidebarBtn.addEventListener('click', function () {
          sidebar.classList.toggle('collapsed');
          mainContainer.classList.toggle('full');
        });
      }

      // Add fade-in animation to cards
      const cards = document.querySelectorAll('.stat-card');
      cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
      });

      // Add hover effects and animations
      addInteractiveEffects();

      // Refresh data periodically (every 5 minutes)
      setInterval(refreshDashboard, 300000);
    });

    function loadTranslations(lang) {
      const spinner = document.getElementById('loadingSpinner');
      if (spinner) spinner.style.display = 'block';

      fetch('../languages/' + lang + '.json')
        .then(r => r.ok ? r.json() : null)
        .then(data => {
          if (data) {
            localStorage.setItem('translations', JSON.stringify(data));
            translatePage(data);
          }
        })
        .catch(err => console.warn('Translation loading failed:', err))
        .finally(() => {
          if (spinner) spinner.style.display = 'none';
        });
    }

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

    function addInteractiveEffects() {
      // Add ripple effect to stat cards
      const statCards = document.querySelectorAll('.stat-card');
      statCards.forEach(card => {
        card.addEventListener('click', function(e) {
          const ripple = document.createElement('span');
          const rect = this.getBoundingClientRect();
          const size = Math.max(rect.width, rect.height);
          const x = e.clientX - rect.left - size / 2;
          const y = e.clientY - rect.top - size / 2;
          
          ripple.style.cssText = `
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: scale(0);
            animation: ripple 0.6s linear;
            left: ${x}px;
            top: ${y}px;
            width: ${size}px;
            height: ${size}px;
            pointer-events: none;
          `;
          
          this.appendChild(ripple);
          setTimeout(() => ripple.remove(), 600);
        });
      });

      // Add CSS for ripple animation
      const style = document.createElement('style');
      style.textContent = `
        @keyframes ripple {
          to { transform: scale(2); opacity: 0; }
        }
        .stat-card { position: relative; overflow: hidden; cursor: pointer; }
      `;
      document.head.appendChild(style);
    }

    function refreshDashboard() {
      // This would typically make an AJAX call to refresh the data
      // For now, we'll just show a subtle indication that data is being refreshed
      const cards = document.querySelectorAll('.stat-card');
      cards.forEach(card => {
        card.style.opacity = '0.7';
        setTimeout(() => {
          card.style.opacity = '1';
        }, 500);
      });
    }

    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
      if (e.key === 'r' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        refreshDashboard();
      }
    });

    // Add touch gestures for mobile
    let touchStartX = 0;
    let touchStartY = 0;

    document.addEventListener('touchstart', function(e) {
      touchStartX = e.touches[0].clientX;
      touchStartY = e.touches[0].clientY;
    });

    document.addEventListener('touchmove', function(e) {
      if (!touchStartX || !touchStartY) return;
      
      const touchEndX = e.touches[0].clientX;
      const touchEndY = e.touches[0].clientY;
      const diffX = touchStartX - touchEndX;
      const diffY = touchStartY - touchEndY;
      
      if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
        const sidebar = document.querySelector('.sidebar');
        const mainContainer = document.querySelector('.main-container');
        
        if (diffX > 0) {
          // Swipe left - hide sidebar
          sidebar?.classList.add('collapsed');
          mainContainer?.classList.add('full');
        } else {
          // Swipe right - show sidebar
          sidebar?.classList.remove('collapsed');
          mainContainer?.classList.remove('full');
        }
      }
      
      touchStartX = 0;
      touchStartY = 0;
    });
  </script>
</body>
</html>
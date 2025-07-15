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
  <title>Stock Dashboard</title>
  <?php include_once 'header.php'; ?>
  <style>
    .card { margin-bottom: 20px; }
    .main-container { margin-top: 100px; margin-left: 70px; }
    .sidebar.collapsed { width: 70px; transition: width 0.3s; }
    .main-container.full { margin-left: 0 !important; }
  </style>
</head>
<body>
  <?php include_once 'navbar.php'; ?>
  <?php include_once 'sidebar.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle"><h1 data-key="report.stockDashboard">Stock Dashboard</h1></div>
    <section class="section dashboard">
      <div class="container main-container">
        <div class="row">
          <div class="col-md-3">
            <div class="card text-center">
              <div class="card-body">
                <h4 data-key="report.inventoryItems">Inventory Items</h4>
                <h3><?php echo number_format($totalItems,0); ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card text-center">
              <div class="card-body">
                <h4 data-key="report.lowStockAlerts">Low Stock Alerts</h4>
                <h3><?php echo number_format($lowStock,0); ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card text-center">
              <div class="card-body">
                <h4 data-key="dashboard.inventoryValue">Inventory Value</h4>
                <h3>BIF<?php echo number_format($inventoryValue,2); ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card text-center">
              <div class="card-body">
                <h4 data-key="report.pendingOrders">Pending Orders</h4>
                <h3><?php echo number_format($pendingOrders,0); ?></h3>
              </div>
            </div>
          </div>
        </div>

        <?php if ($lowStockItems): ?>
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 data-key="report.lowStockItems">Low Stock Items</h5>
              </div>
              <div class="card-body">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th data-key="inventory.itemName">Item Name</th>
                      <th data-key="inventory.quantity">Quantity</th>
                      <th data-key="inventory.reorderLevel">Reorder Level</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lowStockItems as $item): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo number_format($item['quantity_in_stock'],2); ?></td>
                        <td><?php echo number_format($item['reorder_level'],2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include_once './footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const systemLang = navigator.language || 'en';
      const currentLang = localStorage.getItem('userLang') || systemLang;
      loadTranslations(currentLang);

      const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
      const sidebar = document.querySelector('.sidebar');
      const mainContainer = document.querySelector('.main-container');
      if (toggleSidebarBtn && sidebar && mainContainer) {
        toggleSidebarBtn.addEventListener('click', function () {
          sidebar.classList.toggle('collapsed');
          mainContainer.classList.toggle('full');
        });
      }
    });

    function loadTranslations(lang) {
      fetch('../languages/' + lang + '.json')
        .then(r => r.ok ? r.json() : null)
        .then(data => { if (data) { localStorage.setItem('translations', JSON.stringify(data)); translatePage(data); } });
    }

    function translatePage(translations) {
      document.querySelectorAll('[data-key]').forEach(el => {
        const key = el.getAttribute('data-key');
        if (translations[key]) {
          if (el.tagName === 'INPUT') el.value = translations[key];
          else el.textContent = translations[key];
        }
      });
    }
  </script>
</body>
</html>

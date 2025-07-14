<?php
// manager-dashboard.php
session_start();
require_once 'connection.php';


// Ensure user is Manager
if (!isset($_SESSION['UserId']) || $_SESSION['privilege'] !== 'Manager') {
    header("Location: login.php");
    exit;
}

$todaysDate = date('Y-m-d');
$todayStart = $todaysDate . " 00:00:00";
$todayEnd   = $todaysDate . " 23:59:59";

// 1. Today's Sales: Sum of total_price from orders with status 'confirmed' for today
$sqlSales = "SELECT COALESCE(SUM(total_price), 0) AS sales 
             FROM orders 
             WHERE status = 'confirmed' 
               AND order_date BETWEEN '$todayStart' AND '$todayEnd'";
$resSales = $conn->query($sqlSales);
if ($resSales) {
    $rowSales = $resSales->fetch_assoc();
    $todaySales = $rowSales['sales'];
} else {
    $todaySales = 0;
    error_log("Sales query failed: " . $conn->error);
}

// 2. Inventory Alerts: Count of inventory_items where quantity_in_stock <= reorder_level
$sqlInv = "SELECT COUNT(*) AS alerts 
           FROM inventory_items 
           WHERE quantity_in_stock <= reorder_level";
$resInv = $conn->query($sqlInv);
if ($resInv) {
    $rowInv = $resInv->fetch_assoc();
    $inventoryAlerts = $rowInv['alerts'];
} else {
    $inventoryAlerts = 0;
    error_log("Inventory alerts query failed: " . $conn->error);
}

// 3. Open Orders: Count of orders where status = 'pending'
$sqlOrders = "SELECT COUNT(*) AS openOrders 
              FROM orders 
              WHERE status = 'pending'";
$resOrders = $conn->query($sqlOrders);
if ($resOrders) {
    $rowOrders = $resOrders->fetch_assoc();
    $openOrders = $rowOrders['openOrders'];
} else {
    $openOrders = 0;
    error_log("Open orders query failed: " . $conn->error);
}

// 4. Today's Attendance - Absent count from attendance_records for today
$sqlAtt = "SELECT COUNT(*) AS absentCount 
           FROM attendance_records 
           WHERE attendance_date = '$todaysDate' AND status = 'Absent'";
$resAtt = $conn->query($sqlAtt);
if ($resAtt) {
    $rowAtt = $resAtt->fetch_assoc();
    $absentCount = $rowAtt['absentCount'];
} else {
    $absentCount = 0;
    error_log("Attendance query failed: " . $conn->error);
}

// 5. Sales Trend Chart for the Current Week
$now = new DateTime();
$dow = (int)$now->format('N'); // 1 = Monday
$monday = clone $now;
$monday->modify('-' . ($dow - 1) . ' days');
$currentWeekStart = $monday->format('Y-m-d 00:00:00');
$currentWeekEnd   = $todayEnd; // today

$sqlWeekTrend = "SELECT DATE(order_date) AS orderDay, COALESCE(SUM(total_price), 0) AS dailySales
                 FROM orders 
                 WHERE status = 'confirmed' 
                   AND order_date BETWEEN '$currentWeekStart' AND '$currentWeekEnd'
                 GROUP BY DATE(order_date)
                 ORDER BY orderDay ASC";
$resWeekTrend = $conn->query($sqlWeekTrend);
$weekTrendLabels = [];
$weekTrendData = [];
if ($resWeekTrend) {
    while ($row = $resWeekTrend->fetch_assoc()) {
        $weekTrendLabels[] = $row['orderDay'];
        $weekTrendData[] = $row['dailySales'];
    }
} else {
    error_log("Week trend query failed: " . $conn->error);
}
$weekTrendLabelsJson = json_encode($weekTrendLabels);
$weekTrendDataJson = json_encode($weekTrendData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Translated title -->
  <title data-key="report.managerDashboard"></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <?php include_once 'header.php'; ?>
  <style>
    .card { margin-bottom: 20px; }
    .main-container {
      margin-top: 100px;
      margin-left: 70px;
      transition: margin-left 0.3s;
    }
    .card {
      box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    }
    /* Sidebar toggling styles */
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
    <?php include_once 'navbar.php'; ?>
    <?php include_once 'sidebar.php'; ?>
    
    <!-- Hidden element for translated "Daily Sales" text used in the chart -->
    <span id="dailySalesLabel" data-key="report.dailySales" style="display:none;"></span>
    
    <div class="container main-container">
      <div class="card shadow">
        <div class="card-header">
          <h1 data-key="report.managerDashboard"></h1>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Today's Sales Card -->
            <div class="col-md-3">
              <div class="card text-center">
                <div class="card-body">
                  <h4 data-key="report.todaysSales"></h4>
                  <h3>$<?php echo number_format($todaySales, 2); ?></h3>
                </div>
              </div>
            </div>
            <!-- Inventory Alerts Card -->
            <div class="col-md-3">
              <div class="card text-center">
                <div class="card-body">
                  <h4 data-key="report.inventoryAlerts"></h4>
                  <h3><?php echo number_format($inventoryAlerts, 0); ?></h3>
                  <a href="inventory-reports.php" class="btn btn-warning btn-sm" data-key="report.viewInventory"></a>
                </div>
              </div>
            </div>
            <!-- Open Orders Card -->
            <div class="col-md-3">
              <div class="card text-center">
                <div class="card-body">
                  <h4 data-key="report.openOrders"></h4>
                  <h3><?php echo number_format($openOrders, 0); ?></h3>
                  <a href="current-orders.php" class="btn btn-primary btn-sm" data-key="report.viewOrders"></a>
                </div>
              </div>
            </div>
            <!-- Today's Absent Card -->
            <div class="col-md-3">
              <div class="card text-center">
                <div class="card-body">
                  <h4 data-key="report.todaysAbsent"></h4>
                  <h3><?php echo number_format($absentCount, 0); ?></h3>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Sales Trend Chart for the Current Week -->
          <div class="row mt-4">
            <div class="col-md-12">
              <h4 data-key="report.salesTrendCurrentWeek"></h4>
              <canvas id="weekTrendChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <!-- Additional sections (tables, etc.) can be added here -->
    </div>
    
    <?php include_once './footer.php'; ?>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <script>
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
  </script>
  
  <!-- Translation Script (FINAL with button fix) -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const systemLang = navigator.language || navigator.userLanguage;
      let defaultLang = 'en';
      if (systemLang.startsWith('fr')) {
        defaultLang = 'fr';
      }
      const currentLang = localStorage.getItem('userLang') || defaultLang;
      loadTranslations(currentLang);
    });
  
    function loadTranslations(lang) {
      fetch(`../languages/${lang}.json`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`Cannot fetch translations: ${response.statusText}`);
          }
          return response.text();
        })
        .then(text => {
          // Assuming your file is valid JSON
          const translations = JSON.parse(text);
          localStorage.setItem('translations', JSON.stringify(translations));
          translatePage(translations);
        })
        .catch(error => {
          console.error('Error loading translations:', error);
          fetch('../languages/en.json')
            .then(response => {
              if (!response.ok) {
                throw new Error(`Fallback error: ${response.statusText}`);
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
          } else {
            element.textContent = translations[key];
          }
        }
      });
    }
  
    function switchLanguage(lang) {
      localStorage.setItem('userLang', lang);
      loadTranslations(lang);
    }
  </script>
  
  <!-- Sales Trend Chart Rendering -->
  <script>
    const weekLabels = <?php echo $weekTrendLabelsJson; ?>;
    const weekData = <?php echo $weekTrendDataJson; ?>;
    const ctx = document.getElementById('weekTrendChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: weekLabels,
        datasets: [{
          label: document.getElementById('dailySalesLabel').textContent || "Daily Sales",
          data: weekData,
          borderColor: 'rgba(54, 162, 235, 1)',
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          fill: false,
          tension: 0.2
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: document.querySelector('[data-key="report.salesTrendCurrentWeek"]').textContent }
        }
      }
    });
  </script>
</body>
</html>

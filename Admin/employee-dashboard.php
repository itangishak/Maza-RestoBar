<?php
session_start();
require_once 'connection.php';

// Ensure user is Employee (assuming privilege "User" represents employees)
if (!isset($_SESSION['UserId']) || $_SESSION['privilege'] !== 'User') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['UserId'];

// Get the employee record for the logged in user (from employees table)
$sqlEmp = "SELECT employee_id FROM employees WHERE user_id = '$userId' LIMIT 1";
$resEmp = $conn->query($sqlEmp);
$empRow = $resEmp ? $resEmp->fetch_assoc() : null;
$employeeId = $empRow['employee_id'] ?? 0;

// Define today's date range
$todaysDate = date('Y-m-d');
$todayStart = $todaysDate . " 00:00:00";
$todayEnd   = $todaysDate . " 23:59:59";

// Get today's attendance for this employee
$sqlAtt = "SELECT * FROM attendance_records 
           WHERE employee_id = '$employeeId' 
             AND attendance_date = '$todaysDate'";
$resAtt = $conn->query($sqlAtt);
$attendance = $resAtt ? $resAtt->fetch_assoc() : null;

// Compute working hours for today (if clock_out_time is available)
$todayWorkedHours = 0;
if ($attendance && !empty($attendance['clock_out_time'])) {
    $sqlDiff = "SELECT TIMESTAMPDIFF(MINUTE, '{$attendance['clock_in_time']}', '{$attendance['clock_out_time']}') AS minutes";
    $resDiff = $conn->query($sqlDiff);
    $rowDiff = $resDiff ? $resDiff->fetch_assoc() : null;
    if ($rowDiff) {
        $todayWorkedHours = round($rowDiff['minutes'] / 60, 2);
    }
}

// Placeholder for tasks/orders assigned to the employee
$myTasks = 2;

// ----- Build Attendance History for the Past 7 Days -----
$sqlHistory = "
    SELECT 
        DATE(attendance_date) AS attDate,
        SUM(
          CASE 
            WHEN status = 'Present' AND clock_out_time IS NOT NULL 
            THEN TIMESTAMPDIFF(MINUTE, clock_in_time, clock_out_time) 
            ELSE 0 
          END
        ) AS totalMinutes,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS absentCount
    FROM attendance_records
    WHERE employee_id = '$employeeId'
      AND attendance_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
    GROUP BY DATE(attendance_date)
    ORDER BY attDate ASC
";
$resHistory = $conn->query($sqlHistory);
$historyLabels = [];
$historyHours = [];
$historyAbsent = [];
if ($resHistory) {
    while ($row = $resHistory->fetch_assoc()) {
        $historyLabels[] = $row['attDate'];
        $hours = round($row['totalMinutes'] / 60, 2);
        $historyHours[] = $hours;
        $historyAbsent[] = (int)$row['absentCount'];
    }
} else {
    error_log('Error fetching history: ' . $conn->error);
}

$chartLabelsJson = json_encode($historyLabels);
$chartHoursJson  = json_encode($historyHours);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Title translated -->
  <title data-key="dashboard.employeeDashboard"></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <?php include_once 'header.php'; ?>
  <style>
    .card { margin-bottom: 20px; }
    .summary-row {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
    }
    .summary-card {
      flex: 1 1 300px;
      box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
      border-radius: 8px;
      padding: 20px;
      background: #f8f9fa;
      text-align: center;
    }
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
  <?php include_once 'navbar.php'; ?>
  <?php include_once 'sidebar.php'; ?>

  <div class="container main-container">
    <div class="card shadow">
      <div class="card-header">
        <h1 data-key="dashboard.employeeDashboard"></h1>
      </div>
      <div class="card-body">
        <!-- Summary Cards -->
        <div class="summary-row">
          <div class="summary-card">
            <h4 data-key="dashboard.todaysAttendance"></h4>
            <?php if ($attendance): ?>
              <p><span data-key="dashboard.status"></span> <?php echo htmlspecialchars($attendance['status']); ?></p>
              <p><span data-key="dashboard.clockIn"></span> <?php echo htmlspecialchars($attendance['clock_in_time']); ?></p>
              <p><span data-key="dashboard.clockOut"></span> <?php echo htmlspecialchars($attendance['clock_out_time']); ?></p>
              <p><span data-key="dashboard.worked"></span> <?php echo number_format($todayWorkedHours,2); ?> hrs</p>
            <?php else: ?>
              <p data-key="dashboard.noAttendanceRecorded"></p>
            <?php endif; ?>
          </div>
          <div class="summary-card">
            <h4 data-key="dashboard.myTasks"></h4>
            <p>
              <span data-key="dashboard.youHaveTasks"></span> <?php echo number_format($myTasks, 0); ?> <span data-key="dashboard.tasksAssigned"></span>
            </p>
            <a href="employee-tasks.php" class="btn btn-success btn-sm" data-key="dashboard.viewTasks"></a>
          </div>
        </div>
        
        <!-- Hidden element for chart title -->
        <span id="chartTitle" data-key="dashboard.workingHoursChartTitle" style="display:none;"></span>
        
        <!-- Attendance History Chart (Past 7 Days) -->
        <div class="card mb-4">
          <div class="card-header">
            <h4 data-key="dashboard.attendanceHistory"></h4>
          </div>
          <div class="card-body">
            <canvas id="attendanceChart"></canvas>
          </div>
        </div>
        
        <!-- Detailed Attendance History Table -->
        <div class="card">
          <div class="card-header">
            <h4 data-key="dashboard.detailedAttendanceHistory"></h4>
          </div>
          <div class="card-body">
            <?php if (count($historyLabels) > 0): ?>
              <table class="table table-bordered">
                <thead class="table-light">
                  <tr>
                    <th data-key="dashboard.date"></th>
                    <th data-key="dashboard.workingHours"></th>
                    <th data-key="dashboard.absentCount"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($historyLabels as $i => $date): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($date); ?></td>
                      <td><?php echo number_format($historyHours[$i], 2); ?> hrs</td>
                      <td><?php echo number_format($historyAbsent[$i], 0); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p class="text-muted" data-key="dashboard.noAttendanceData"></p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php include_once './footer.php'; ?>
  
 
  
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
          // Assuming your language file is valid JSON
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
  
  <!-- Attendance History Chart -->
  <script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?php echo $chartLabelsJson; ?>,
        datasets: [{
          label: 'Working Hours',
          data: <?php echo $chartHoursJson; ?>,
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          fill: false,
          tension: 0.2
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: document.querySelector('[data-key="dashboard.workingHoursChartTitle"]')?.textContent
          }
        }
      }
    });
  </script>
</body>
</html>

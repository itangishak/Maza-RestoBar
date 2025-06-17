<?php
session_start();
require_once 'connection.php';

// Ensure the logged-in user is Boss
if (!isset($_SESSION['UserId']) || $_SESSION['privilege'] !== 'Boss') {
    header("Location: login.php");
    exit;
}

// Date range selection (default to current month)
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');
$startDateTime = $startDate . ' 00:00:00';
$endDateTime = $endDate . ' 23:59:59';

// Log date range for debugging
error_log("startDateTime: $startDateTime, endDateTime: $endDateTime");

// Total Revenue (menu_sales + drink_sales + buffet_sales + paid debts)
$sqlRevenue = "
    SELECT COALESCE((
        (SELECT SUM(sale_price * quantity_sold) FROM menu_sales WHERE sale_date BETWEEN ? AND ?) +
        (SELECT SUM(sale_price * quantity_sold) FROM drink_sales WHERE sale_date BETWEEN ? AND ?) +
        (SELECT SUM(total_price) FROM buffet_sales WHERE sale_date BETWEEN ? AND ?) +
        (SELECT SUM(amount) FROM debts WHERE status = 'Paid' AND due_date BETWEEN ? AND ?)
    ), 0) AS revenue
";
$stmt = $conn->prepare($sqlRevenue);
if (!$stmt) {
    error_log("Revenue Query Prepare Error: " . $conn->error);
}
$stmt->bind_param("ssssssss", $startDateTime, $endDateTime, $startDateTime, $endDateTime, $startDateTime, $endDateTime, $startDateTime, $endDateTime);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$totalRevenue = (float)($result['revenue'] ?? 0);
error_log("Total Revenue: $totalRevenue");
if ($stmt->error) {
    error_log("Revenue Query Error: " . $stmt->error);
}
$stmt->close();

// Total Expenses (expenses + payroll + purchases)
$sqlExpenses = "
    SELECT COALESCE((
        (SELECT SUM(amount) FROM expenses WHERE expense_date BETWEEN ? AND ?) +
        (SELECT SUM(net_pay) FROM payroll_records WHERE payment_date BETWEEN ? AND ?) +
        (SELECT SUM(total) FROM purchase_records WHERE order_date BETWEEN ? AND ?)
    ), 0) AS expenses
";
$stmt = $conn->prepare($sqlExpenses);
if (!$stmt) {
    error_log("Expenses Query Prepare Error: " . $conn->error);
}
$stmt->bind_param("ssssss", $startDateTime, $endDateTime, $startDateTime, $endDateTime, $startDateTime, $endDateTime);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$totalExpenses = (float)($result['expenses'] ?? 0);
error_log("Total Expenses: $totalExpenses");
if ($stmt->error) {
    error_log("Expenses Query Error: " . $stmt->error);
}
$stmt->close();

// Net Profit
$netProfit = $totalRevenue - $totalExpenses;

// Total Orders
$stmt = $conn->prepare("SELECT COUNT(*) AS orderCount 
                       FROM orders 
                       WHERE status = 'confirmed' 
                         AND order_date BETWEEN ? AND ?");
if (!$stmt) {
    error_log("Orders Query Prepare Error: " . $conn->error);
}
$stmt->bind_param("ss", $startDateTime, $endDateTime);
$stmt->execute();
$totalOrders = (int)($stmt->get_result()->fetch_assoc()['orderCount'] ?? 0);
error_log("Total Orders: $totalOrders");
$stmt->close();

// Total Employees
$stmt = $conn->prepare("SELECT COUNT(*) AS empCount FROM employees");
if (!$stmt) {
    error_log("Employees Query Prepare Error: " . $conn->error);
}
$stmt->execute();
$totalEmployees = (int)($stmt->get_result()->fetch_assoc()['empCount'] ?? 0);
error_log("Total Employees: $totalEmployees");
$stmt->close();

// Inventory Value
$stmt = $conn->prepare("SELECT COALESCE(SUM(quantity_in_stock * unit_cost), 0) AS invValue 
                        FROM inventory_items");
if (!$stmt) {
    error_log("Inventory Query Prepare Error: " . $conn->error);
}
$stmt->execute();
$totalInventoryValue = (float)($stmt->get_result()->fetch_assoc()['invValue'] ?? 0);
error_log("Inventory Value: $totalInventoryValue");
$stmt->close();

// Average Feedback Rating
$stmt = $conn->prepare("SELECT AVG(rating) AS avgRating FROM feedback");
if (!$stmt) {
    error_log("Feedback Query Prepare Error: " . $conn->error);
}
$stmt->execute();
$avgRating = (float)($stmt->get_result()->fetch_assoc()['avgRating'] ?? 0);
error_log("Avg Feedback Rating: $avgRating");
$stmt->close();

// Today's Reservations
$todaysDate = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) AS resCount FROM reservations WHERE reservation_date = ?");
if (!$stmt) {
    error_log("Reservations Query Prepare Error: " . $conn->error);
}
$stmt->bind_param("s", $todaysDate);
$stmt->execute();
$totalReservations = (int)($stmt->get_result()->fetch_assoc()['resCount'] ?? 0);
error_log("Today's Reservations: $totalReservations");
$stmt->close();

// Expense Breakdown for Pie Chart
$stmt = $conn->prepare("SELECT category, SUM(amount) as category_amount 
                       FROM expenses 
                       WHERE expense_date BETWEEN ? AND ? 
                       GROUP BY category 
                       ORDER BY category_amount DESC");
if (!$stmt) {
    error_log("Expense Breakdown Query Prepare Error: " . $conn->error);
}
$stmt->bind_param("ss", $startDateTime, $endDateTime);
$stmt->execute();
$expenseCategories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
error_log("Expense Categories: " . print_r($expenseCategories, true));
$stmt->close();

$expenseLabels = [];
$expenseData = [];
foreach ($expenseCategories as $cat) {
    $expenseLabels[] = $cat['category'] ?: 'Uncategorized';
    $expenseData[] = (float)$cat['category_amount'];
}
$expenseLabelsJson = json_encode($expenseLabels);
$expenseDataJson = json_encode($expenseData);
error_log("Expense Labels: " . $expenseLabelsJson);
error_log("Expense Data: " . $expenseDataJson);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="dashboard.title">Boss Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include_once './header.php'; ?>
    <style>
        .card { margin-bottom: 20px; }
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
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1050;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <?php include_once 'sidebar.php'; ?>
    </nav>

    <main class="container main-container">
        <div class="card">
            <div class="card-header">
                <h1 data-key="dashboard.title">Dashboard</h1>
            </div>
            <div class="card-body">
                <!-- Date Range Form -->
                <form method="GET" class="my-3">
                    <label for="start_date" class="form-label" data-key="dashboard.startDate">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>" class="form-control" style="width:auto;display:inline-block;">
                    <label for="end_date" class="form-label" data-key="dashboard.endDate">End Date:</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo $endDate; ?>" class="form-control" style="width:auto;display:inline-block;">
                    <button type="submit" class="btn btn-primary" data-key="dashboard.apply">Apply</button>
                </form>

                <!-- Summary Cards Row -->
                <div class="row">
                   
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 data-key="dashboard.totalOrders">Total Orders</h4>
                                <h3><?php echo number_format($totalOrders, 0); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 data-key="dashboard.totalEmployees">Total Employees</h4>
                                <h3><?php echo number_format($totalEmployees, 0); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 data-key="dashboard.inventoryValue">Inventory Value</h4>
                                <h3>BIF<?php echo number_format($totalInventoryValue, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 data-key="dashboard.avgRating">Avg. Feedback Rating</h4>
                                <h3><?php echo number_format($avgRating, 2); ?>/5</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 data-key="dashboard.todaysReservations">Today’s Reservations</h4>
                                <h3><?php echo number_format($totalReservations, 0); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expense Breakdown Pie Chart -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 data-key="dashboard.expenseBreakdown">Expense Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="expenseChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div class="loading" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden" data-key="dashboard.loading">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once './footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
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
                    card.style.marginTop = '5%';
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

            // Show loading spinner on form submission
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function () {
                    document.getElementById('loadingSpinner').style.display = 'block';
                    setTimeout(() => document.getElementById('loadingSpinner').style.display = 'none', 2000);
                });
            });

            // Expense Breakdown Pie Chart
            const expenseLabels = <?php echo $expenseLabelsJson; ?>;
            const expenseData = <?php echo $expenseDataJson; ?>;

            new Chart(document.getElementById('expenseChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: expenseLabels,
                    datasets: [{
                        data: expenseData,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: {
                            display: true,
                            text: getTranslation('dashboard.expenseBreakdown')
                        }
                    }
                }
            });

            // Translation Script
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
                        } else {
                            element.textContent = translations[key];
                        }
                    }
                });
            }

            function getTranslation(key) {
                const translations = JSON.parse(localStorage.getItem('translations')) || {};
                return translations[key] || key;
            }

            function switchLanguage(lang) {
                localStorage.setItem('userLang', lang);
                loadTranslations(lang);
            }

            const systemLang = navigator.language || navigator.userLanguage;
            let defaultLang = 'en';
            if (systemLang.startsWith('fr')) {
                defaultLang = 'fr';
            }
            let currentLang = localStorage.getItem('userLang') || defaultLang;
            loadTranslations(currentLang);
        });
    </script>
</body>
</html>
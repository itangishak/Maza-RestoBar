<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  header("Location: login.php");
  exit();
}

// Date range handling
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Key Performance Indicators Queries
// 1. Total Revenue from all sources
$revenue_query = "
    SELECT 
        COALESCE(menu_revenue, 0) + COALESCE(drink_revenue, 0) + COALESCE(buffet_revenue, 0) + COALESCE(debt_payment_revenue, 0) as total_revenue,
        menu_revenue,
        drink_revenue,
        buffet_revenue,
        debt_payment_revenue
    FROM (
        SELECT 
            (SELECT COALESCE(SUM(ms.quantity_sold * ms.sale_price), 0) 
             FROM menu_sales ms 
             WHERE ms.sale_date BETWEEN '$start_date' AND '$end_date' AND ms.status = 'active') as menu_revenue,
             
            (SELECT COALESCE(SUM(ds.quantity_sold * ds.sale_price), 0) 
             FROM drink_sales ds 
             WHERE ds.sale_date BETWEEN '$start_date' AND '$end_date' AND ds.status = 'active') as drink_revenue,
             
            (SELECT COALESCE(SUM(bsi.price), 0) 
             FROM buffet_sale_items bsi 
             WHERE bsi.sale_date BETWEEN '$start_date' AND '$end_date' AND bsi.status = 'active') as buffet_revenue,
             
            (SELECT COALESCE(SUM(dp.paid_amount), 0) 
             FROM debt_payments dp 
             WHERE DATE(dp.paid_date) BETWEEN '$start_date' AND '$end_date') as debt_payment_revenue
    ) revenue_data
";
$revenue_result = $conn->query($revenue_query);
$revenue_data = $revenue_result->fetch_assoc();

// 2. Total Expenses and Cost of Goods Sold (supplies)
$expense_query = "SELECT 
    COALESCE(SUM(amount), 0) as total_expenses,
    COALESCE(SUM(CASE WHEN category = 'supplies' THEN amount ELSE 0 END), 0) as supplies_cost
FROM expenses 
WHERE expense_date BETWEEN '$start_date' AND '$end_date'";
$expense_result = $conn->query($expense_query);
$expense_data = $expense_result->fetch_assoc();

// 3. Inventory Cost
$inventory_query = "SELECT COALESCE(SUM(quantity_in_stock * unit_cost), 0) as inventory_value FROM inventory_items";
$inventory_result = $conn->query($inventory_query);
$inventory_data = $inventory_result->fetch_assoc();

// 4. Outstanding Debts
$debt_query = "SELECT COALESCE(SUM(amount), 0) as total_debts FROM debts WHERE status IN ('pending', 'partial', 'overdue')";
$debt_result = $conn->query($debt_query);
$debt_data = $debt_result->fetch_assoc();

// 5. Employee Costs - Using a more comprehensive query
$payroll_query = "SELECT 
    COALESCE(SUM(net_pay), 0) as payroll_costs,
    COALESCE(SUM(gross_pay), 0) as gross_payroll,
    COALESCE(SUM(bonus), 0) as total_bonuses,
    COUNT(DISTINCT employee_id) as employee_count
FROM payroll_records 
WHERE 
    (pay_period_start BETWEEN '$start_date' AND '$end_date'
    OR pay_period_end BETWEEN '$start_date' AND '$end_date'
    OR (pay_period_start <= '$start_date' AND pay_period_end >= '$end_date'))
    OR payment_date BETWEEN '$start_date' AND '$end_date'";
$payroll_result = $conn->query($payroll_query);
$payroll_data = $payroll_result->fetch_assoc();

// 6. Top Selling Menu Items
$top_menu_query = "
    SELECT mi.name, SUM(ms.quantity_sold) as total_sold, SUM(ms.quantity_sold * ms.sale_price) as revenue
    FROM menu_sales ms 
    JOIN menu_items mi ON ms.menu_id = mi.menu_id 
    WHERE ms.sale_date BETWEEN '$start_date' AND '$end_date' AND ms.status = 'active'
    GROUP BY ms.menu_id, mi.name 
    ORDER BY total_sold DESC 
    LIMIT 10
";
$top_menu_result = $conn->query($top_menu_query);

// 7. Daily Sales Trend
$daily_sales_query = "
    SELECT 
        DATE(sale_date) as sale_day,
        COALESCE(SUM(quantity_sold * sale_price), 0) as daily_revenue
    FROM (
        SELECT sale_date, quantity_sold, sale_price FROM menu_sales WHERE status = 'active'
        UNION ALL
        SELECT sale_date, quantity_sold, sale_price FROM drink_sales WHERE status = 'active'
    ) all_sales
    WHERE sale_date BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE(sale_date)
    ORDER BY sale_day DESC
    LIMIT 30
";
$daily_sales_result = $conn->query($daily_sales_query);

// 8. Expense Breakdown by Category
$expense_breakdown_query = "
    SELECT category, SUM(amount) as category_total 
    FROM expenses 
    WHERE expense_date BETWEEN '$start_date' AND '$end_date' 
    GROUP BY category 
    ORDER BY category_total DESC
";
$expense_breakdown_result = $conn->query($expense_breakdown_query);

// 9. Customer Feedback Analysis
$feedback_query = "
    SELECT 
        AVG(rating) as avg_rating,
        COUNT(*) as total_feedback,
        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_feedback
    FROM feedback 
    WHERE created_at BETWEEN '$start_date' AND '$end_date'
";
$feedback_result = $conn->query($feedback_query);
$feedback_data = $feedback_result->fetch_assoc();

// 10. Low Stock Alerts
$low_stock_query = "
    SELECT item_name, quantity_in_stock, reorder_level, unit_cost
    FROM inventory_items 
    WHERE quantity_in_stock <= reorder_level 
    ORDER BY quantity_in_stock ASC
";
$low_stock_result = $conn->query($low_stock_query);

// Calculate key metrics
// Simplified, direct revenue calculation
// Make sure we always have numbers, even if DB returns null or missing values
$menu_revenue = isset($revenue_data['menu_revenue']) ? floatval($revenue_data['menu_revenue']) : 0;
$drink_revenue = isset($revenue_data['drink_revenue']) ? floatval($revenue_data['drink_revenue']) : 0;
$buffet_revenue = isset($revenue_data['buffet_revenue']) ? floatval($revenue_data['buffet_revenue']) : 0;
$debt_payment_revenue = isset($revenue_data['debt_payment_revenue']) ? floatval($revenue_data['debt_payment_revenue']) : 0;

// Calculate total revenue directly from components to ensure accuracy
$total_revenue = $menu_revenue + $drink_revenue + $buffet_revenue + $debt_payment_revenue;

// Process other financials
$total_expenses = isset($expense_data['total_expenses']) ? floatval($expense_data['total_expenses']) : 0;
$supplies_cost = isset($expense_data['supplies_cost']) ? floatval($expense_data['supplies_cost']) : 0;
$payroll_costs = isset($payroll_data['payroll_costs']) ? floatval($payroll_data['payroll_costs']) : 0;

// Calculate Gross Profit as Total Revenue - Cost of Goods Sold (supplies)
$gross_profit = $total_revenue - $supplies_cost;

// Calculate Net Profit as Gross Profit - (All other expenses + Payroll Costs)
$other_expenses = $total_expenses - $supplies_cost; // Remove supplies to avoid double counting
$net_profit = $gross_profit - ($other_expenses + $payroll_costs);

// Calculate profit margin based on net profit
$profit_margin = $total_revenue > 0 ? ($net_profit / $total_revenue) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Reports & Business Analytics</title>
  <?php include_once './header.php'; ?>
  
  <style>
    /* General layout styles */
    .main-container {
      margin-top: 20px;
      padding: 0 20px;
    }
    
    /* Sales Report Specific Styles */
    
        .kpi-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .kpi-card.positive { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .kpi-card.negative { background: linear-gradient(135deg, #ee5a24 0%, #ffc048 100%); }
        .kpi-card.warning { background: linear-gradient(135deg, #ffb347 0%, #ffcc33 100%); }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .metric-value {
            font-size: 28px;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .metric-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .metric-details {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-top: 5px;
        }
        
        .metric-details small {
            display: block;
            line-height: 1.4;
        }

        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }

        .alert-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
  <?php include_once './navbar.php'; ?>
  <?php include_once 'sidebar.php'; ?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1><i class="bi bi-graph-up"></i> Sales & Business Analytics</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Sales Reports</li>
        </ol>
      </nav>
    </div>
    
    <div class="float-end">
      <button onclick="exportToPDF()" class="btn btn-sm btn-outline-secondary me-1">
        <i class="bi bi-file-pdf"></i> PDF
      </button>
      <button onclick="exportToExcel()" class="btn btn-sm btn-outline-secondary me-1">
        <i class="bi bi-file-excel"></i> Excel
      </button>
      <button onclick="printReport()" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-printer"></i> Print
      </button>
    </div>
    
    <!-- Date Filter -->
    <div class="card mb-4">
      <div class="card-body">
        <form action="sales-reports.php" method="GET" class="row g-3">
          <div class="col-md-4">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
          </div>
          <div class="col-md-4">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Apply Filter</button>
          </div>
        </form>
      </div>
    </div>

        <!-- Key Performance Indicators -->
        <div class="row">
            <div class="col-md-3">
                <div class="kpi-card positive">
                    <div class="metric-value">BIF <?php echo number_format($total_revenue, 2); ?></div>
                    <div class="metric-label">Total Revenue</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card <?php echo $supplies_cost > 0 ? 'warning' : 'positive'; ?>">
                    <div class="metric-value">BIF <?php echo number_format($supplies_cost, 2); ?></div>
                    <div class="metric-label">Cost of Goods</div>
                    <div class="metric-details">
                        <small>Direct supplies & materials</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card <?php echo $gross_profit > 0 ? 'positive' : 'negative'; ?>">
                    <div class="metric-value">BIF <?php echo number_format($gross_profit, 2); ?></div>
                    <div class="metric-label">Gross Profit</div>
                    <div class="metric-details">
                        <small>Revenue minus cost of goods</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card <?php echo $net_profit > 0 ? 'positive' : 'negative'; ?>">
                    <div class="metric-value">BIF <?php echo number_format($net_profit, 2); ?></div>
                    <div class="metric-label">Net Profit</div>
                    <div class="metric-details">
                        <small>After all expenses & payroll</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Secondary KPIs -->
        <div class="row">
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="metric-value">BIF <?php echo number_format($other_expenses, 2); ?></div>
                    <div class="metric-label">Operating Expenses</div>
                    <div class="metric-details">
                        <small>Excludes supplies costs</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card <?php echo $profit_margin > 15 ? 'positive' : ($profit_margin > 5 ? 'warning' : 'negative'); ?>">
                    <div class="metric-value"><?php echo number_format($profit_margin, 1); ?>%</div>
                    <div class="metric-label">Net Profit Margin</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card warning position-relative">
                    <div class="metric-value">BIF <?php echo number_format($inventory_data['inventory_value'], 2); ?></div>
                    <div class="metric-label">Inventory Value</div>
                    <?php if ($low_stock_result->num_rows > 0): ?>
                        <div class="alert-badge"><?php echo $low_stock_result->num_rows; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card <?php echo $debt_data['total_debts'] > 0 ? 'warning' : 'positive'; ?>">
                    <div class="metric-value">BIF <?php echo number_format($debt_data['total_debts'], 2); ?></div>
                    <div class="metric-label">Outstanding Debts</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card">
                    <div class="metric-value">BIF <?php echo number_format($payroll_data['payroll_costs'], 2); ?></div>
                    <div class="metric-label">Payroll Costs</div>
                    <div class="metric-details">
                        <small><?php echo $payroll_data['employee_count']; ?> employees</small>
                        <?php if ($payroll_data['total_bonuses'] > 0): ?>
                        <small>Includes BIF <?php echo number_format($payroll_data['total_bonuses'], 2); ?> bonuses</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-card <?php echo ($feedback_data['avg_rating'] ?? 0) >= 4 ? 'positive' : 'warning'; ?>">
                    <div class="metric-value"><?php echo number_format($feedback_data['avg_rating'] ?? 0, 1); ?>/5</div>
                    <div class="metric-label">Avg. Customer Rating</div>
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="row">
            <div class="col-md-12">
                <div class="chart-container">
                    <h5><i class="bi bi-pie-chart"></i> Revenue Breakdown</h5>
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Daily Sales Trend -->
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h5><i class="bi bi-graph-up"></i> Daily Sales Trend (Last 30 Days)</h5>
                    <canvas id="salesTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Tables -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="bi bi-trophy"></i> Top Selling Menu Items</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Qty Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $top_menu_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo $item['total_sold']; ?></td>
                                    <td>BIF <?php echo number_format($item['revenue'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="bi bi-exclamation-triangle text-warning"></i> Low Stock Alerts</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Current Stock</th>
                                    <th>Reorder Level</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($low_stock_result, 0);
                                while ($stock = $low_stock_result->fetch_assoc()): 
                                ?>
                                <tr class="<?php echo $stock['quantity_in_stock'] == 0 ? 'table-danger' : 'table-warning'; ?>">
                                    <td><?php echo htmlspecialchars($stock['item_name']); ?></td>
                                    <td><?php echo $stock['quantity_in_stock']; ?></td>
                                    <td><?php echo $stock['reorder_level']; ?></td>
                                    <td>BIF <?php echo number_format($stock['quantity_in_stock'] * $stock['unit_cost'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if ($low_stock_result->num_rows == 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-success">All items are well stocked!</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Health Summary -->
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h5><i class="bi bi-heart-pulse"></i> Business Health Summary</h5>
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h6>Financial Health</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar <?php echo $profit_margin > 15 ? 'bg-success' : ($profit_margin > 5 ? 'bg-warning' : 'bg-danger'); ?>" 
                                     style="width: <?php echo min(100, max(0, $profit_margin * 2)); ?>%"></div>
                            </div>
                            <small><?php echo $profit_margin > 15 ? 'Excellent' : ($profit_margin > 5 ? 'Good' : 'Needs Attention'); ?></small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Customer Satisfaction</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar <?php echo ($feedback_data['avg_rating'] ?? 0) >= 4 ? 'bg-success' : 'bg-warning'; ?>" 
                                     style="width: <?php echo (($feedback_data['avg_rating'] ?? 0) / 5) * 100; ?>%"></div>
                            </div>
                            <small><?php echo ($feedback_data['avg_rating'] ?? 0) >= 4 ? 'Excellent' : 'Good'; ?></small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Inventory Management</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar <?php echo $low_stock_result->num_rows == 0 ? 'bg-success' : ($low_stock_result->num_rows <= 5 ? 'bg-warning' : 'bg-danger'); ?>" 
                                     style="width: <?php echo $low_stock_result->num_rows == 0 ? 100 : max(20, 100 - ($low_stock_result->num_rows * 10)); ?>%"></div>
                            </div>
                            <small><?php echo $low_stock_result->num_rows == 0 ? 'Excellent' : ($low_stock_result->num_rows <= 5 ? 'Good' : 'Needs Attention'); ?></small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Cash Flow</h6>
                            <div class="progress mb-2">
                                <div class="progress-bar <?php echo $debt_data['total_debts'] == 0 ? 'bg-success' : 'bg-warning'; ?>" 
                                     style="width: <?php echo $debt_data['total_debts'] == 0 ? 100 : 60; ?>%"></div>
                            </div>
                            <small><?php echo $debt_data['total_debts'] == 0 ? 'Excellent' : 'Monitor Closely'; ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="row">
            <div class="col-12">
                <div class="chart-container">
                    <h5><i class="bi bi-download"></i> Export Reports</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="exportToPDF()">
                            <i class="bi bi-file-pdf"></i> Export to PDF
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="exportToExcel()">
                            <i class="bi bi-file-excel"></i> Export to Excel
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="printReport()">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        // Revenue Breakdown Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: ['Menu Items', 'Drinks', 'Buffet'],
                datasets: [{
                    data: [
                        <?php echo $revenue_data['menu_revenue'] ?? 0; ?>,
                        <?php echo $revenue_data['drink_revenue'] ?? 0; ?>,
                        <?php echo $revenue_data['buffet_revenue'] ?? 0; ?>
                    ],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Expense Categories Chart has been removed

        // Daily Sales Trend Chart
        const trendCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $trend_labels = [];
                    $trend_values = [];
                    mysqli_data_seek($daily_sales_result, 0);
                    while ($day = $daily_sales_result->fetch_assoc()) {
                        $trend_labels[] = '"' . date('M d', strtotime($day['sale_day'])) . '"';
                        $trend_values[] = $day['daily_revenue'];
                    }
                    echo implode(',', array_reverse($trend_labels));
                    ?>
                ],
                datasets: [{
                    label: 'Daily Revenue (BIF)',
                    data: [<?php echo implode(',', array_reverse($trend_values)); ?>],
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Export Functions
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.setFontSize(20);
            doc.text('Maza Resto-Bar Sales Report', 20, 20);
            
            doc.setFontSize(12);
            doc.text('Period: <?php echo $start_date; ?> to <?php echo $end_date; ?>', 20, 35);
            
            let yPos = 50;
            doc.text('Key Metrics:', 20, yPos);
            yPos += 10;
            doc.text('Total Revenue: BIF <?php echo number_format($total_revenue, 2); ?>', 25, yPos);
            yPos += 7;
            doc.text('Cost of Goods (Supplies): BIF <?php echo number_format($supplies_cost, 2); ?>', 25, yPos);
            yPos += 7;
            doc.text('Gross Profit: BIF <?php echo number_format($gross_profit, 2); ?>', 25, yPos);
            yPos += 7;
            doc.text('Operating Expenses: BIF <?php echo number_format($other_expenses, 2); ?>', 25, yPos);
            yPos += 7;
            doc.text('Payroll Costs: BIF <?php echo number_format($payroll_costs, 2); ?>', 25, yPos);
            yPos += 7;
            doc.text('Net Profit: BIF <?php echo number_format($net_profit, 2); ?>', 25, yPos);
            yPos += 7;
            doc.text('Net Profit Margin: <?php echo number_format($profit_margin, 1); ?>%', 25, yPos);
            
            doc.save('sales-report-<?php echo date("Y-m-d"); ?>.pdf');
        }

        function exportToExcel() {
            // Create a simple CSV export
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Maza Resto-Bar Sales Report\n";
            csvContent += "Period: <?php echo $start_date; ?> to <?php echo $end_date; ?>\n\n";
            csvContent += "Metric,Value\n";
            csvContent += "Total Revenue,<?php echo $total_revenue; ?>\n";
            csvContent += "Cost of Goods (Supplies),<?php echo $supplies_cost; ?>\n";
            csvContent += "Gross Profit,<?php echo $gross_profit; ?>\n";
            csvContent += "Operating Expenses,<?php echo $other_expenses; ?>\n";
            csvContent += "Payroll Costs,<?php echo $payroll_costs; ?>\n";
            csvContent += "Net Profit,<?php echo $net_profit; ?>\n";
            csvContent += "Net Profit Margin,<?php echo $profit_margin; ?>%\n";
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "sales-report-<?php echo date('Y-m-d'); ?>.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printReport() {
            window.print();
        }

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
        
        // Auto-refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>

  <!-- Scripts for page functionality -->
  <script>
    // Sidebar toggling logic
    const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
    const sidebar = document.querySelector('.sidebar');
    const mainContainer = document.querySelector('.main');
    
    if (toggleSidebarBtn && sidebar && mainContainer) {
      toggleSidebarBtn.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
        mainContainer.classList.toggle('full');
      });
    }
    
    // Auto-refresh every 5 minutes
    setTimeout(function() {
      location.reload();
    }, 300000);
  </script>
</body>
</html>
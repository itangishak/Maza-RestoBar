<?php
// Process report generation based on report type
$reportData = [];
$reportTitle = '';

switch ($reportType) {
    case 'daily':
        $reportData = getDailySalesData($conn, $startDate, $endDate);
        $reportTitle = 'Daily Sales Report (' . date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)) . ')';
        break;
    
    case 'monthly':
        $reportData = getMonthlySalesData($conn, $selectedYear);
        $reportTitle = 'Monthly Sales Report for ' . $selectedYear;
        break;
    
    case 'yearly':
        // Default to 5 years back if not specified
        $startYear = isset($_GET['start_year']) ? $_GET['start_year'] : ($currentYear - 5);
        $endYear = isset($_GET['end_year']) ? $_GET['end_year'] : $currentYear;
        $reportData = getYearlySalesData($conn, $startYear, $endYear);
        $reportTitle = 'Yearly Sales Report (' . $startYear . ' - ' . $endYear . ')';
        break;
}

// Function to format currency
function formatCurrency($amount) {
    return number_format($amount, 2);
}

// Calculate totals for summary
$totalMenuSales = 0;
$totalDrinkSales = 0;
$totalBuffetSales = 0;
$totalSales = 0;
$totalExpenses = 0;
$totalProfit = 0;

foreach ($reportData as $row) {
    $totalMenuSales += $row['menu_sales'];
    $totalDrinkSales += $row['drink_sales'];
    $totalBuffetSales += $row['buffet_sales'];
    $totalSales += $row['total_sales'];
    $totalExpenses += $row['expenses'];
    $totalProfit += $row['profit'];
}

// Format page title based on report type
$pageTitle = 'Sales Reports';

// Include header
include '../includes/header.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $pageTitle; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active"><?php echo $pageTitle; ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Report Type Selection Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="report-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($reportType == 'daily') ? 'active' : ''; ?>" 
                               href="?report_type=daily" role="tab">
                                Daily
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($reportType == 'monthly') ? 'active' : ''; ?>" 
                               href="?report_type=monthly" role="tab">
                                Monthly
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($reportType == 'yearly') ? 'active' : ''; ?>" 
                               href="?report_type=yearly" role="tab">
                                Yearly
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <form method="GET" action="" class="form-inline">
                                    <input type="hidden" name="report_type" value="<?php echo $reportType; ?>">
                                    
                                    <?php if ($reportType == 'daily'): ?>
                                        <div class="form-group mr-3">
                                            <label for="start_date" class="mr-2">Start Date:</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                                   value="<?php echo $startDate; ?>">
                                        </div>
                                        <div class="form-group mr-3">
                                            <label for="end_date" class="mr-2">End Date:</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                                   value="<?php echo $endDate; ?>">
                                        </div>
                                    <?php elseif ($reportType == 'monthly'): ?>
                                        <div class="form-group mr-3">
                                            <label for="year" class="mr-2">Year:</label>
                                            <select class="form-control" id="year" name="year">
                                                <?php 
                                                $currentYear = date('Y');
                                                for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
                                                    $selected = ($i == $selectedYear) ? 'selected' : '';
                                                    echo "<option value=\"$i\" $selected>$i</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    <?php elseif ($reportType == 'yearly'): ?>
                                        <div class="form-group mr-3">
                                            <label for="start_year" class="mr-2">Start Year:</label>
                                            <select class="form-control" id="start_year" name="start_year">
                                                <?php 
                                                $currentYear = date('Y');
                                                for ($i = $currentYear; $i >= $currentYear - 20; $i--) {
                                                    $selected = ($i == $startYear) ? 'selected' : '';
                                                    echo "<option value=\"$i\" $selected>$i</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group mr-3">
                                            <label for="end_year" class="mr-2">End Year:</label>
                                            <select class="form-control" id="end_year" name="end_year">
                                                <?php 
                                                $currentYear = date('Y');
                                                for ($i = $currentYear; $i >= $currentYear - 20; $i--) {
                                                    $selected = ($i == $endYear) ? 'selected' : '';
                                                    echo "<option value=\"$i\" $selected>$i</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Report Summary -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><?php echo $reportTitle; ?> - Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-2 text-center mb-3">
                                                <div class="small text-muted">Menu Sales</div>
                                                <div class="h4"><?php echo formatCurrency($totalMenuSales); ?></div>
                                            </div>
                                            <div class="col-md-2 text-center mb-3">
                                                <div class="small text-muted">Drink Sales</div>
                                                <div class="h4"><?php echo formatCurrency($totalDrinkSales); ?></div>
                                            </div>
                                            <div class="col-md-2 text-center mb-3">
                                                <div class="small text-muted">Buffet Sales</div>
                                                <div class="h4"><?php echo formatCurrency($totalBuffetSales); ?></div>
                                            </div>
                                            <div class="col-md-2 text-center mb-3">
                                                <div class="small text-muted">Total Sales</div>
                                                <div class="h4"><?php echo formatCurrency($totalSales); ?></div>
                                            </div>
                                            <div class="col-md-2 text-center mb-3">
                                                <div class="small text-muted">Expenses</div>
                                                <div class="h4"><?php echo formatCurrency($totalExpenses); ?></div>
                                            </div>
                                            <div class="col-md-2 text-center mb-3">
                                                <div class="small text-muted">Profit</div>
                                                <div class="h4 <?php echo ($totalProfit >= 0) ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo formatCurrency($totalProfit); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Detailed Report Table -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Detailed Report</h5>
                                            <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel()">
                                                <i class="fas fa-file-excel mr-1"></i> Export to Excel
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-striped" id="report-table">
                                            <thead>
                                                <tr>
                                                    <?php if ($reportType == 'daily'): ?>
                                                        <th>Date</th>
                                                    <?php elseif ($reportType == 'monthly'): ?>
                                                        <th>Month</th>
                                                    <?php else: ?>
                                                        <th>Year</th>
                                                    <?php endif; ?>
                                                    <th>Menu Sales</th>
                                                    <th>Drink Sales</th>
                                                    <th>Buffet Sales</th>
                                                    <th>Total Sales</th>
                                                    <th>Expenses</th>
                                                    <th>Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($reportData as $row): ?>
                                                    <tr>
                                                        <?php if ($reportType == 'daily'): ?>
                                                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                                        <?php elseif ($reportType == 'monthly'): ?>
                                                            <td><?php echo $row['month'] . ' ' . $row['year']; ?></td>
                                                        <?php else: ?>
                                                            <td><?php echo $row['year']; ?></td>
                                                        <?php endif; ?>
                                                        <td><?php echo formatCurrency($row['menu_sales']); ?></td>
                                                        <td><?php echo formatCurrency($row['drink_sales']); ?></td>
                                                        <td><?php echo formatCurrency($row['buffet_sales']); ?></td>
                                                        <td><?php echo formatCurrency($row['total_sales']); ?></td>
                                                        <td><?php echo formatCurrency($row['expenses']); ?></td>
                                                        <td class="<?php echo ($row['profit'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                                            <?php echo formatCurrency($row['profit']); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

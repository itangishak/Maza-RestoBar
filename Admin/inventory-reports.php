<?php
session_start();
require_once 'connection.php';
// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

/*========================================
  Date filter handling
========================================*/
// Default date range (last 30 days if not specified)
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));

// Validate dates
if (!strtotime($start_date) || !strtotime($end_date)) {
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-30 days'));
}

// Convert to DateTime objects for calculations
$start_date_obj = new DateTime($start_date);
$end_date_obj = new DateTime($end_date);
$start_date_obj->setTime(0, 0, 0);
$end_date_obj->setTime(23, 59, 59);

// For backward compatibility with existing code
$aggStart = $start_date_obj;
$aggEnd = $end_date_obj;

// Calculate interval type for display purposes
$interval_days = $start_date_obj->diff($end_date_obj)->days;
if ($interval_days <= 1) {
    $interval = 'day';
} elseif ($interval_days <= 7) {
    $interval = 'week';
} elseif ($interval_days <= 31) {
    $interval = 'month';
} elseif ($interval_days <= 100) {
    $interval = 'quarter';
} elseif ($interval_days <= 200) {
    $interval = 'semester';
} else {
    $interval = 'year';
}

// Enable history and chart based on date range
$showHistory = ($interval_days > 7);
$showChart = ($interval_days > 7); // Show chart for longer periods

/**
 * FUNCTIONS
 */
function getAggregatorRange($interval) {
    $now = new DateTime();
    switch ($interval) {
        case 'day':
            $start = (clone $now)->setTime(0, 0, 0);
            $end   = (clone $now)->setTime(23, 59, 59);
            break;
        case 'week':
            $dow = (int)$now->format('N');
            $weekMon = (clone $now)->modify('-' . ($dow - 1) . ' days')->setTime(0, 0, 0);
            $weekSun = (clone $weekMon)->modify('+6 days')->setTime(23, 59, 59);
            $start = $weekMon;
            $end   = $weekSun;
            break;
        case 'month':
            $y = $now->format('Y');
            $m = $now->format('m');
            $start = new DateTime("$y-$m-01 00:00:00");
            $endObj = clone $start;
            $endObj->modify('last day of this month')->setTime(23, 59, 59);
            $end = $endObj;
            break;
        case 'quarter':
            $y = (int)$now->format('Y');
            $m = (int)$now->format('m');
            $q = ceil($m / 3);
            $startMonth = 1 + 3 * ($q - 1);
            $start = new DateTime("$y-$startMonth-01 00:00:00");
            $endMonth = 3 * $q;
            $endObj = new DateTime("$y-$endMonth-01 00:00:00");
            $endObj->modify('last day of this month')->setTime(23, 59, 59);
            $end = $endObj;
            break;
        case 'semester':
            $y = (int)$now->format('Y');
            $m = (int)$now->format('m');
            if ($m <= 6) {
                $start = new DateTime("$y-01-01 00:00:00");
                $end   = new DateTime("$y-06-30 23:59:59");
            } else {
                $start = new DateTime("$y-07-01 00:00:00");
                $end   = new DateTime("$y-12-31 23:59:59");
            }
            break;
        case 'year':
            $yr = (int)$now->format('Y');
            $start = new DateTime("$yr-01-01 00:00:00");
            $end   = new DateTime("$yr-12-31 23:59:59");
            break;
        default:
            $start = (clone $now)->setTime(0, 0, 0);
            $end   = (clone $now)->setTime(23, 59, 59);
    }
    return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
}

function getHistoryRange($interval) {
    $now = new DateTime();
    switch ($interval) {
        case 'week':
            $histEnabled = true;
            $y = (int)$now->format('Y');
            $start = new DateTime("$y-01-01 00:00:00");
            $dow = (int)$now->format('N');
            $thisWeekStart = (clone $now)->modify('-' . ($dow - 1) . ' days')->setTime(0, 0, 0);
            $end = (clone $thisWeekStart)->modify('-1 second');
            break;
        case 'month':
            $histEnabled = true;
            $y = (int)$now->format('Y');
            $start = new DateTime("$y-01-01 00:00:00");
            $m = (int)$now->format('m');
            if ($m === 1) {
                $endYr = $y - 1;
                $endMo = 12;
            } else {
                $endYr = $y;
                $endMo = $m - 1;
            }
            $endObj = new DateTime("$endYr-$endMo-01 00:00:00");
            $endObj->modify('last day of this month')->setTime(23, 59, 59);
            $end = $endObj;
            break;
        case 'year':
            $histEnabled = true;
            $y = (int)$now->format('Y');
            $start = new DateTime(($y - 5) . "-01-01 00:00:00");
            $end = new DateTime(($y - 1) . "-12-31 23:59:59");
            break;
        default:
            $histEnabled = false;
            $start = new DateTime("1970-01-01 00:00:00");
            $end = new DateTime("1970-01-01 00:00:00");
    }
    return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'), $histEnabled];
}

/**
 * Compute inventory aggregators from the live snapshot
 */
function computeInventoryAggregator($startDate, $endDate, $conn) {
    $escStart = $conn->real_escape_string($startDate);
    $escEnd   = $conn->real_escape_string($endDate);

    // Total stock, value, and below reorder for all categories
    $sql1 = "SELECT 
                COUNT(inventory_id) AS total_stock,
                COALESCE(SUM(quantity_in_stock * unit_cost), 0) AS total_value,
                SUM(CASE WHEN quantity_in_stock <= reorder_level THEN 1 ELSE 0 END) AS below_reorder 
             FROM inventory_items";
    $res1 = $conn->query($sql1);
    $row1 = $res1->fetch_assoc();
    $totalStock   = (int)($row1['total_stock'] ?? 0); // Cast to integer since we're counting items
    $totalValue   = (float)($row1['total_value'] ?? 0); // Added null check
    $belowReorder = (int)($row1['below_reorder'] ?? 0); // Cast to integer since it's a count

    // Category-specific metrics
    $categories = ['Drink', 'Equipment', 'Food'];
    $categoryData = [];
    foreach ($categories as $category) {
        $sqlCat = "SELECT 
                     COUNT(inventory_id) AS cat_stock,
                     COALESCE(SUM(quantity_in_stock * unit_cost), 0) AS cat_value,
                     SUM(CASE WHEN quantity_in_stock <= reorder_level THEN 1 ELSE 0 END) AS cat_below_reorder
                   FROM inventory_items 
                   WHERE category = '$category'";
        $resCat = $conn->query($sqlCat);
        $rowCat = $resCat->fetch_assoc();
        $categoryData[$category] = [
            'stock' => (int)($rowCat['cat_stock'] ?? 0), // Cast to integer and handle null
            'value' => (float)($rowCat['cat_value'] ?? 0), // Handle null
            'below_reorder' => (int)($rowCat['cat_below_reorder'] ?? 0) // Cast to integer and handle null
        ];
    }

    // Total suppliers
    $sql2 = "SELECT COUNT(*) AS cnt FROM suppliers";
    $res2 = $conn->query($sql2);
    $supplierCount = (int)($res2->fetch_assoc()['cnt'] ?? 0);

    // Purchases over the date range (total and by category)
    $sqlPurch = "SELECT COALESCE(SUM(total), 0) AS purchaseCost 
                 FROM purchase_records 
                 WHERE order_date BETWEEN '$escStart' AND '$escEnd'";
    $resPurch = $conn->query($sqlPurch);
    $purchaseVal = (float)$resPurch->fetch_assoc()['purchaseCost'];

    // Initialize category purchases with defaults
    $categoryPurchases = [
        'drink' => 0,
        'equipment' => 0,
        'food' => 0,
        'Drink' => 0,
        'Equipment' => 0,
        'Food' => 0
    ];
    
    // Query to get category purchases using item-level calculations
    $sqlAllCatPurch = "SELECT 
                           ii.category AS category_group,
                           COALESCE(SUM(poi.quantity * poi.unit_price), 0) AS cat_purchase
                       FROM purchase_records pr
                       JOIN purchase_order_items poi ON pr.purchase_id = poi.purchase_order_id
                       JOIN inventory_items ii ON poi.inventory_id = ii.inventory_id
                       WHERE pr.order_date BETWEEN '$escStart' AND '$escEnd'
                       GROUP BY ii.category";
    
    $resAllCatPurch = $conn->query($sqlAllCatPurch);
    
    if ($resAllCatPurch && $resAllCatPurch->num_rows > 0) {
        while ($catRow = $resAllCatPurch->fetch_assoc()) {
            $categoryGroup = $catRow['category_group'];
            $purchaseValue = (float)($catRow['cat_purchase'] ?? 0);
            
            // Store both exact case and lowercase versions for flexibility
            $categoryPurchases[$categoryGroup] = $purchaseValue;
            $categoryPurchases[strtolower($categoryGroup)] = $purchaseValue;
            
            // Also map to our standard categories to ensure UI compatibility
            if (stripos($categoryGroup, 'Drink') !== false) {
                $categoryPurchases['drink'] = $purchaseValue;
                $categoryPurchases['Drink'] = $purchaseValue;
            } elseif (stripos($categoryGroup, 'Equipment') !== false) {
                $categoryPurchases['equipment'] = $purchaseValue;
                $categoryPurchases['Equipment'] = $purchaseValue;
            } elseif (stripos($categoryGroup, 'Food') !== false) {
                $categoryPurchases['food'] = $purchaseValue;
                $categoryPurchases['Food'] = $purchaseValue;
            }
        }
    }

    // For debugging - output the actual content of categoryPurchases
    error_log("Category Purchases: " . json_encode($categoryPurchases));
    
    // Direct approach: Set the expected values explicitly in the array that's used in the UI
    // This ensures the keys 'drink', 'equipment', and 'food' exist and have the proper values
    $finalCategoryPurchases = [
        'drink' => isset($categoryPurchases['Drink']) ? $categoryPurchases['Drink'] : (isset($categoryPurchases['drink']) ? $categoryPurchases['drink'] : $purchaseVal * 0.3),
        'equipment' => isset($categoryPurchases['Equipment']) ? $categoryPurchases['Equipment'] : (isset($categoryPurchases['equipment']) ? $categoryPurchases['equipment'] : $purchaseVal * 0.2),
        'food' => isset($categoryPurchases['Food']) ? $categoryPurchases['Food'] : (isset($categoryPurchases['food']) ? $categoryPurchases['food'] : $purchaseVal * 0.5)
    ];
    
    return [
        $totalStock,
        $totalValue,
        $belowReorder,
        $supplierCount,
        $purchaseVal,
        $categoryData,
        $finalCategoryPurchases
    ];
}

/**
 * Build history data for purchases (grouped by interval and category)
 */
function buildInventoryHistory($startDate, $endDate, $interval, $conn) {
    $escStart = $conn->real_escape_string($startDate);
    $escEnd   = $conn->real_escape_string($endDate);

    list($grp, $lbl) = buildGroupLabel($interval, 'order_date');

    // 1) Total purchases
    $sql = "SELECT $lbl AS period, SUM(total) AS purchaseCost
            FROM purchase_records
            WHERE order_date BETWEEN '$escStart' AND '$escEnd'
            GROUP BY $grp
            ORDER BY period ASC";
    $res = $conn->query($sql);
    $mapPurch = [];
    while ($r = $res->fetch_assoc()) {
        $mapPurch[$r['period']] = (float)$r['purchaseCost'];
    }

    // 2) Category-specific purchases
    $categories = ['Drink', 'Equipment', 'Food'];
    $categoryHistory = [];
    foreach ($categories as $category) {
        $sqlCat = "SELECT $lbl AS period, SUM(pr.total) AS cat_purchase
                   FROM purchase_records pr
                   JOIN purchase_order_items poi ON pr.purchase_id = poi.purchase_order_id
                   JOIN inventory_items ii ON poi.inventory_id = ii.inventory_id
                   WHERE ii.category = '$category'
                     AND pr.order_date BETWEEN '$escStart' AND '$escEnd'
                   GROUP BY $grp
                   ORDER BY period ASC";
        $resCat = $conn->query($sqlCat);
        $mapCatPurch = [];
        while ($r = $resCat->fetch_assoc()) {
            $mapCatPurch[$r['period']] = (float)$r['cat_purchase'];
        }
        $categoryHistory[$category] = $mapCatPurch;
    }

    // Combine all "period" keys
    $allPeriods = array_keys($mapPurch);
    foreach ($categoryHistory as $cat => $data) {
        $allPeriods = array_unique(array_merge($allPeriods, array_keys($data)));
    }
    natsort($allPeriods);

    // Build final arrays
    $historyMap           = [];
    $chartLabels          = [];
    $chartDataTotal       = [];
    $chartDataDrink       = [];
    $chartDataEquipment   = [];
    $chartDataFood        = [];

    foreach ($allPeriods as $period) {
        $totalPurch = isset($mapPurch[$period]) ? $mapPurch[$period] : 0;
        $drinkPurch = isset($categoryHistory['Drink'][$period]) ? $categoryHistory['Drink'][$period] : 0;
        $equipPurch = isset($categoryHistory['Equipment'][$period]) ? $categoryHistory['Equipment'][$period] : 0;
        $foodPurch  = isset($categoryHistory['Food'][$period]) ? $categoryHistory['Food'][$period] : 0;

        $historyMap[$period] = [
            'purchases'          => $totalPurch,
            'drinkPurchases'     => $drinkPurch,
            'equipmentPurchases' => $equipPurch,
            'foodPurchases'      => $foodPurch
        ];

        $chartLabels[]         = $period;
        $chartDataTotal[]      = $totalPurch;
        $chartDataDrink[]      = $drinkPurch;
        $chartDataEquipment[]  = $equipPurch;
        $chartDataFood[]       = $foodPurch;
    }

    return [
        $historyMap,
        json_encode($chartLabels),
        json_encode($chartDataTotal),
        json_encode($chartDataDrink),
        json_encode($chartDataEquipment),
        json_encode($chartDataFood)
    ];
}

function buildGroupLabel($interval, $col) {
    switch ($interval) {
        case 'day':
            return ["DATE($col)", "DATE_FORMAT($col, '%Y-%m-%d')"];
        case 'week':
            return ["YEAR($col), WEEK($col)", "CONCAT(YEAR($col), '-W', WEEK($col))"];
        case 'month':
            return ["YEAR($col), MONTH($col)", "DATE_FORMAT($col, '%Y-%m')"];
        case 'quarter':
            return ["YEAR($col), QUARTER($col)", "CONCAT(YEAR($col), '-Q', QUARTER($col))"];
        case 'semester':
            return ["YEAR($col), IF(MONTH($col) <= 6, 'S1', 'S2')", "CONCAT(YEAR($col), '-S', IF(MONTH($col) <= 6, '1', '2'))"];
        case 'year':
            return ["YEAR($col)", "YEAR($col)"];
        default:
            return ["DATE($col)", "DATE_FORMAT($col, '%Y-%m-%d')"];
    }
}

/**
 * MAIN LOGIC
 */
list($aggStart, $aggEnd) = getAggregatorRange($interval);
list($histStart, $histEnd, $histEnabled) = getHistoryRange($interval);
$showHistory = $histEnabled;
$showChart   = in_array($interval, ['month', 'year']);

list(
    $invTotalStock,
    $invTotalValue,
    $invBelowReorder,
    $invSupplierCount,
    $aggPurchases,
    $categoryData,
    $categoryPurchases
) = computeInventoryAggregator($aggStart, $aggEnd, $conn);

$historyRows              = [];
$chartLabelsJson          = '[]';
$chartDataTotalJson       = '[]';
$chartDataDrinkJson       = '[]';
$chartDataEquipmentJson   = '[]';
$chartDataFoodJson        = '[]';

if ($showHistory) {
    list(
        $histMap,
        $chartLabelsJson,
        $chartDataTotalJson,
        $chartDataDrinkJson,
        $chartDataEquipmentJson,
        $chartDataFoodJson
    ) = buildInventoryHistory($histStart, $histEnd, $interval, $conn);

    foreach ($histMap as $period => $vals) {
        $historyRows[] = [
            'period'             => $period,
            'purchases'          => $vals['purchases'],
            'drinkPurchases'     => $vals['drinkPurchases'],
            'equipmentPurchases' => $vals['equipmentPurchases'],
            'foodPurchases'      => $vals['foodPurchases']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Reports & Analytics</title>
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 20px;
        }
        .main-container.full {
            margin-left: 80px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: width 0.3s, margin-left 0.3s;
        }
        
        /* KPI Card Styles */
        .row {
            margin-bottom: 20px;
        }
        .kpi-card {
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            background-color: #fff;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: #ccc;
        }
        .kpi-card.positive::before {
            background-color: #28a745;
        }
        .kpi-card.negative::before {
            background-color: #dc3545;
        }
        .kpi-card.warning::before {
            background-color: #ffc107;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .metric-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .metric-details {
            margin-top: 8px;
            font-size: 12px;
            color: #6c757d;
        }
        .alert-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        /* Chart Styles */
        .chart-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .chart-container h5 {
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
        
        /* Table Styles */
        .table-responsive {
            margin-top: 20px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>
    
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-box-seam"></i> Inventory Reports & Analytics</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item">/ Inventory Reports</li>
                </ol>
            </nav>
        </div>
        
        <!-- Date Filter & Export Buttons -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex">
                    <div class="me-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                    </div>
                    <div class="me-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="d-flex align-items-end">
                        <button type="button" onclick="applyFilter()" class="btn btn-primary">Apply Filter</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end d-flex justify-content-end align-items-end">
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
        </div>
        
        <!-- Hidden form for date filter submission -->
        <form id="dateFilterForm" action="inventory-reports.php" method="GET" style="display:none;">
            <input type="hidden" id="hidden_start_date" name="start_date">
            <input type="hidden" id="hidden_end_date" name="end_date">
        </form>
        
        <!-- Key Performance Indicators -->
        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <div class="kpi-card text-white" style="background: linear-gradient(45deg, #29cc97, #3dde9c);">
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo number_format($invTotalStock); ?></div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px;">TOTAL ITEMS IN STOCK</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="kpi-card text-white" style="background: linear-gradient(45deg, #29cc97, #3dde9c);">
                    <div style="font-size: 2rem; font-weight: bold;">BIF <?php echo number_format($invTotalValue, 2); ?></div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px;">TOTAL INVENTORY VALUE</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="kpi-card text-white" style="background: linear-gradient(45deg, #ffc107, #ffe169);">
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo $invBelowReorder; ?> <?php if ($invBelowReorder > 0): ?><span class="badge bg-danger">!</span><?php endif; ?></div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px;">ITEMS BELOW REORDER</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="kpi-card text-white" style="background: linear-gradient(45deg, #29cc97, #3dde9c);">
                    <div style="font-size: 2rem; font-weight: bold;">BIF <?php echo number_format($aggPurchases, 2); ?></div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px;">TOTAL PURCHASES</div>
                    <div style="font-size: 0.75rem; margin-top: 5px;"><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Chart: Purchases Trend -->
        <?php if ($showChart): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-graph-up"></i> Purchases Trend</h5>
                        <h6 class="card-subtitle mb-3 text-muted"><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></h6>
                        <canvas id="inventoryChart" style="width:100%; height:300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- History Table (for week, month, year) -->
        <?php if ($showHistory): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-clock-history"></i> Inventory History</h5>
                        <h6 class="card-subtitle mb-3 text-muted"><?php echo date('M d, Y', strtotime($start_date)); ?> - <?php echo date('M d, Y', strtotime($end_date)); ?></h6>
                        
                        <?php if (empty($historyRows)): ?>
                        <div class="alert alert-info">No past data found for the selected period.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Period</th>
                                        <th>Total Purchases</th>
                                        <th>Items in Stock</th>
                                        <th>Value in Stock</th>
                                        <th>Avg Cost per Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historyRows as $hr): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($hr['period']); ?></strong></td>
                                            <td>BIF <?php echo number_format($hr['purchases'], 2); ?></td>
                                            <td><?php echo number_format($hr['itemsInStock'], 0); ?></td>
                                            <td>BIF <?php echo number_format($hr['valueInStock'], 2); ?></td>
                                            <td>BIF <?php echo number_format($hr['avgCostPerItem'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Include Footer -->
    <?php include_once './footer.php'; ?>

    <!-- Chart.js is included from local assets -->
    <script src="assets/vendor/chart.js/chart.umd.min.js"></script>
    <script>
    $(document).ready(function () {
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('#sidebar');
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
    });
    </script>

    <!-- Translation Script (FINAL) -->
    <script>
      // This code runs on DOMContentLoaded, loads the language file, and calls translatePage()
      document.addEventListener('DOMContentLoaded', function () {
        // Example: autodetect or pick from localStorage
        const systemLang = navigator.language || navigator.userLanguage;
        let defaultLang = 'en';
        if (systemLang.startsWith('fr')) {
          defaultLang = 'fr';
        }

        const currentLang = localStorage.getItem('userLang') || defaultLang;
        loadTranslations(currentLang);

        // If you have a <select id="languageSelect"> somewhere, set it
        // const selectEl = document.getElementById('languageSelect');
        // if (selectEl) {
        //   selectEl.value = currentLang;
        // }
      });

      function loadTranslations(lang) {
        // Adjust path as needed
        fetch(`../languages/${lang}.json`)
          .then(response => {
            if (!response.ok) {
              throw new Error(`Cannot fetch translations: ${response.statusText}`);
            }
            return response.text();
          })
          .then(text => {
            // If your "json" files are actually lines with trailing commas,
            // you can parse them carefully here, or store them as real JSON.
            // For example, if you have real JSON, do:
            //   const translations = JSON.parse(text);
            //
            // If not real JSON, parse lines yourself. 
            // (We'll assume you do have real JSON or a JSON-like format that parseable.)
            const translations = JSON.parse(text);
            localStorage.setItem('translations', JSON.stringify(translations));
            translatePage(translations);
          })
          .catch(error => {
            console.error('Error loading translations:', error);
            // fallback to English if fails
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
    </script>

    <!-- Chart Initialization -->
    <script>
        // Initialize the inventory chart
        <?php if ($showChart): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('inventoryChart').getContext('2d');
            const inventoryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartData['labels'] ?? []); ?>,
                    datasets: [{
                        label: 'Purchases Amount (BIF)',
                        data: <?php echo json_encode($chartData['values'] ?? []); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'BIF ' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'BIF ' + context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
        <?php endif; ?>
    </script>
    
    <!-- Export Functions -->
    <script>
        // Apply filter function
        function applyFilter() {
            document.getElementById('hidden_start_date').value = document.getElementById('start_date').value;
            document.getElementById('hidden_end_date').value = document.getElementById('end_date').value;
            document.getElementById('dateFilterForm').submit();
        }
        
        // Export to PDF function
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Add title
            doc.setFontSize(18);
            doc.text('Inventory Reports & Analytics', 14, 20);
            
            // Add date range
            doc.setFontSize(11);
            doc.text('Report Period: <?php echo date("M d, Y", strtotime($start_date)); ?> - <?php echo date("M d, Y", strtotime($end_date)); ?>', 14, 30);
            doc.text('Generated on: ' + new Date().toLocaleDateString(), 14, 35);
            
            // Add KPI data
            doc.setFontSize(14);
            doc.text('Key Inventory Metrics', 14, 45);
            
            const kpiData = [
                ['Total Items in Stock', '<?php echo number_format($invTotalStock, 0); ?>'],
                ['Total Inventory Value', 'BIF <?php echo number_format($invTotalValue, 2); ?>'],
                ['Items Below Reorder', '<?php echo number_format($invBelowReorder, 0); ?>'],
                ['Total Purchases', 'BIF <?php echo number_format($aggPurchases, 2); ?>'],
                ['Inventory Turnover Rate', '<?php echo number_format($turnoverRate, 2); ?>%']
            ];
            
            doc.autoTable({
                startY: 50,
                head: [['Metric', 'Value']],
                body: kpiData,
                theme: 'grid',
                headStyles: { fillColor: [41, 128, 185], textColor: 255 }
            });
            
            // Add purchase breakdown
            doc.setFontSize(14);
            doc.text('Purchase Breakdown', 14, doc.lastAutoTable.finalY + 15);
            
            const purchaseData = [
                ['Drink Purchases', 'BIF <?php echo number_format($categoryPurchases["Drink"], 2); ?>'],
                ['Equipment Purchases', 'BIF <?php echo number_format($categoryPurchases["Equipment"], 2); ?>'],
                ['Food Purchases', 'BIF <?php echo number_format($categoryPurchases["Food"], 2); ?>']
            ];
            
            doc.autoTable({
                startY: doc.lastAutoTable.finalY + 20,
                head: [['Category', 'Value']],
                body: purchaseData,
                theme: 'grid',
                headStyles: { fillColor: [41, 128, 185], textColor: 255 }
            });
            
            // Save the PDF
            doc.save('inventory-report-<?php echo date("Y-m-d"); ?>.pdf');
        }
        
        // Export to Excel (CSV) function
        function exportToExcel() {
            // Create CSV content
            let csvContent = 'data:text/csv;charset=utf-8,';
            
            // Add header
            csvContent += 'Inventory Reports & Analytics\n';
            csvContent += 'Report Period: <?php echo date("M d, Y", strtotime($start_date)); ?> - <?php echo date("M d, Y", strtotime($end_date)); ?>\n';
            csvContent += 'Generated on: ' + new Date().toLocaleDateString() + '\n\n';
            
            // Add KPI data
            csvContent += 'Key Inventory Metrics\n';
            csvContent += 'Metric,Value\n';
            csvContent += 'Total Items in Stock,<?php echo number_format($invTotalStock, 0); ?>\n';
            csvContent += 'Total Inventory Value,BIF <?php echo number_format($invTotalValue, 2); ?>\n';
            csvContent += 'Items Below Reorder,<?php echo number_format($invBelowReorder, 0); ?>\n';
            csvContent += 'Total Purchases,BIF <?php echo number_format($aggPurchases, 2); ?>\n';
            csvContent += 'Inventory Turnover Rate,<?php echo number_format($turnoverRate, 2); ?>%\n\n';
            
            // Add purchase breakdown
            csvContent += 'Purchase Breakdown\n';
            csvContent += 'Category,Value\n';
            csvContent += 'Drink Purchases,BIF <?php echo number_format($categoryPurchases["Drink"], 2); ?>\n';
            csvContent += 'Equipment Purchases,BIF <?php echo number_format($categoryPurchases["Equipment"], 2); ?>\n';
            csvContent += 'Food Purchases,BIF <?php echo number_format($categoryPurchases["Food"], 2); ?>\n';
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'inventory-report-<?php echo date("Y-m-d"); ?>.csv');
            document.body.appendChild(link);
            
            // Trigger download and remove link
            link.click();
            document.body.removeChild(link);
        }
        
        // Print report function
        function printReport() {
            window.print();
        }
    </script>
</body>
</html>

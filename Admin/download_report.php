<?php
session_start();
require_once 'connection.php';
require_once '../vendor/autoload.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// -------------------------------------------------------------
// SESSION CHECK: Ensure a valid user session
if (!isset($_SESSION['UserId'])) {
    die("Unauthorized access.");
}

// -------------------------------------------------------------
// Set Interval for report (default "day")
$validIntervals = ['day', 'week', 'month', 'quarter', 'semester', 'year'];
$interval = isset($_GET['interval']) ? $_GET['interval'] : 'day';
if (!in_array($interval, $validIntervals)) {
    $interval = 'day';
}

// -------------------------------------------------------------
// FUNCTIONS (Aggregation & Grouping)

// Returns start and end of current interval for aggregator (for summary cards)
function getAggregatorRange($interval) {
    $now = new DateTime();
    switch ($interval) {
        case 'day':
            $start = (clone $now)->setTime(0, 0, 0);
            $end   = (clone $now)->setTime(23, 59, 59);
            break;
        case 'week':
            $dow   = (int)$now->format('N'); // 1 = Monday
            $start = (clone $now)->modify('-' . ($dow - 1) . ' days')->setTime(0, 0, 0);
            $end   = (clone $start)->modify('+6 days')->setTime(23, 59, 59);
            break;
        case 'month':
            $year  = $now->format('Y');
            $month = $now->format('m');
            $start = new DateTime("$year-$month-01 00:00:00");
            $endObj= clone $start;
            $endObj->modify('last day of this month')->setTime(23, 59, 59);
            $end   = $endObj;
            break;
        case 'quarter':
            $year  = $now->format('Y');
            $month = (int)$now->format('m');
            $q     = ceil($month / 3);
            $startMonth = 1 + 3 * ($q - 1);
            $start = new DateTime("$year-$startMonth-01 00:00:00");
            $endMonth   = 3 * $q;
            $endObj = new DateTime("$year-$endMonth-01 00:00:00");
            $endObj->modify('last day of this month')->setTime(23, 59, 59);
            $end   = $endObj;
            break;
        case 'semester':
            $year = $now->format('Y');
            $month = (int)$now->format('m');
            if ($month <= 6) {
                $start = new DateTime("$year-01-01 00:00:00");
                $end   = new DateTime("$year-06-30 23:59:59");
            } else {
                $start = new DateTime("$year-07-01 00:00:00");
                $end   = new DateTime("$year-12-31 23:59:59");
            }
            break;
        case 'year':
            $year = $now->format('Y');
            $start = new DateTime("$year-01-01 00:00:00");
            $end   = new DateTime("$year-12-31 23:59:59");
            break;
        default:
            $start = (clone $now)->setTime(0, 0, 0);
            $end   = (clone $now)->setTime(23, 59, 59);
    }
    return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
}

// Returns history range that excludes the current interval (for history table)
function getHistoryRange($interval) {
    $now = new DateTime();
    $histEnabled = false;
    switch ($interval) {
        case 'week':
            $histEnabled = true;
            $year = $now->format('Y');
            $start = new DateTime("$year-01-01 00:00:00");
            $dow   = (int)$now->format('N');
            $thisWeekStart = (clone $now)->modify('-' . ($dow - 1) . ' days')->setTime(0,0,0);
            $end   = (clone $thisWeekStart)->modify('-1 second');
            break;
        case 'month':
            $histEnabled = true;
            $year = $now->format('Y');
            $month = (int)$now->format('m');
            $start = new DateTime("$year-01-01 00:00:00");
            $endObj = new DateTime("$year-$month-01 00:00:00");
            $endObj->modify('-1 second');
            $end = $endObj;
            break;
        case 'quarter':
            $histEnabled = true;
            $year = $now->format('Y');
            $month = (int)$now->format('m');
            $q = ceil($month / 3);
            $startMonth = 1 + 3 * ($q - 1);
            $start = new DateTime("$year-01-01 00:00:00");
            $endObj = new DateTime("$year-$startMonth-01 00:00:00");
            $endObj->modify('-1 second');
            $end = $endObj;
            break;
        case 'semester':
            $histEnabled = true;
            $year = $now->format('Y');
            $month = (int)$now->format('m');
            if ($month <= 6) {
                $start = new DateTime("$year-01-01 00:00:00");
                $end = new DateTime("$year-06-30 23:59:59");
            } else {
                $start = new DateTime("$year-01-01 00:00:00");
                $end = new DateTime("$year-12-31 23:59:59");
            }
            break;
        case 'year':
            $histEnabled = true;
            $year = $now->format('Y');
            $start = new DateTime("$year-01-01 00:00:00");
            $end = new DateTime("$year-12-31 23:59:59");
            break;
        default:
            $histEnabled = false;
            $start = new DateTime();
            $end = new DateTime();
    }
    return [$histEnabled, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
}

// Compute aggregator values for summary cards
function computeAggregator($startDateTime, $endDateTime, $conn) {
    $escStart = $conn->real_escape_string($startDateTime);
    $escEnd   = $conn->real_escape_string($endDateTime);
    
    // Revenue from menu_sales and paid debts
    $sqlMS = "SELECT COALESCE(SUM(sale_price * quantity_sold), 0) AS ms FROM menu_sales WHERE sale_date BETWEEN '$escStart' AND '$escEnd'";
    $resMS = $conn->query($sqlMS);
    $msVal = (float)$resMS->fetch_assoc()['ms'];
    
    $sqlPd = "SELECT COALESCE(SUM(amount), 0) AS pd FROM debts WHERE status = 'Paid' AND due_date BETWEEN '$escStart' AND '$escEnd'";
    $resPd = $conn->query($sqlPd);
    $pdVal = (float)$resPd->fetch_assoc()['pd'];
    
    $totalRevenue = $msVal + $pdVal;
    
    // Expenses from expenses table
    $sqlEx = "SELECT COALESCE(SUM(amount), 0) AS ex FROM expenses WHERE expense_date BETWEEN '$escStart' AND '$escEnd'";
    $resEx = $conn->query($sqlEx);
    $exVal = (float)$resEx->fetch_assoc()['ex'];
    
    // Payroll from payroll_records table
    $sqlPy = "SELECT COALESCE(SUM(net_pay), 0) AS py FROM payroll_records WHERE payment_date BETWEEN '$escStart' AND '$escEnd'";
    $resPy = $conn->query($sqlPy);
    $pyVal = (float)$resPy->fetch_assoc()['py'];
    
    // Purchases from purchase_records table
    $sqlPu = "SELECT COALESCE(SUM(total), 0) AS pu FROM purchase_records WHERE order_date BETWEEN '$escStart' AND '$escEnd'";
    $resPu = $conn->query($sqlPu);
    $puVal = (float)$resPu->fetch_assoc()['pu'];
    
    // All debts (ignoring status)
    $sqlDb = "SELECT COALESCE(SUM(amount), 0) AS dt FROM debts WHERE due_date BETWEEN '$escStart' AND '$escEnd'";
    $resDb = $conn->query($sqlDb);
    $dtVal = (float)$resDb->fetch_assoc()['dt'];
    
    $totalExpenses = $exVal + $pyVal + $puVal;
    $netProfit = $totalRevenue - $totalExpenses;
    
    return [$totalRevenue, $totalExpenses, $pyVal, $puVal, $dtVal, $netProfit];
}

// Build grouping label for SQL GROUP BY
function buildGroupLabel($interval, $col) {
    switch ($interval) {
        case 'day':
            return date('H:i', strtotime($col));
        case 'week':
            return date('D', strtotime($col));
        case 'month':
            return date('d', strtotime($col));
        case 'quarter':
            return date('M', strtotime($col));
        case 'semester':
            return date('M', strtotime($col));
        case 'year':
            return date('M', strtotime($col));
        default:
            return $col;
    }
}

// Build history data (aggregator for each period and chart data)
function buildHistoryData($startDate, $endDate, $interval, $conn) {
    $sql = "SELECT 
                DATE_FORMAT(sale_date, '%Y-%m-%d %H:00:00') as sale_date,
                COUNT(*) as total_sales,
                SUM(quantity_sold) as total_items,
                SUM(sale_price) as total_revenue
            FROM sales 
            WHERE sale_date BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(sale_date, '%Y-%m-%d %H:00:00')
            ORDER BY sale_date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'date' => buildGroupLabel($interval, $row['sale_date']),
            'total_sales' => $row['total_sales'],
            'total_items' => $row['total_items'],
            'total_revenue' => $row['total_revenue']
        ];
    }
    
    return $data;
}

// -------------------------------------------------------------
// Get aggregator and history ranges
list($aggStart, $aggEnd) = getAggregatorRange($interval);
list($histEnabled, $histStart, $histEnd) = getHistoryRange($interval);
$showHistory = $histEnabled;
$showChart   = in_array($interval, ['month', 'year']);

// Compute summary aggregator values (cards)
list($cardsRevenue, $cardsExpenses, $cardsPayroll, $cardsPurchases, $cardsDebts, $cardsNetProfit) = computeAggregator($aggStart, $aggEnd, $conn);

// Build history data if applicable
$historyRows = [];
$chartLabelsJson = '[]';
$chartDataJson   = '[]';
if ($showHistory) {
    $historyData = buildHistoryData($histStart, $histEnd, $interval, $conn);
    foreach ($historyData as $row) {
        $historyRows[] = [
            'date' => $row['date'],
            'total_sales' => $row['total_sales'],
            'total_items' => $row['total_items'],
            'total_revenue' => $row['total_revenue']
        ];
    }
}

// Additional data (e.g., low stock alerts, top-selling items, expense breakdown)
$stmt = $conn->prepare("
    SELECT item_name, quantity_in_stock, reorder_level
    FROM inventory_items
    WHERE quantity_in_stock <= reorder_level
    ORDER BY quantity_in_stock ASC
    LIMIT 5
");
$stmt->execute();
$lowStockItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("
    SELECT mi.name, SUM(ms.quantity_sold) as total_sold, SUM(ms.sale_price * ms.quantity_sold) as total_revenue
    FROM menu_sales ms
    JOIN menu_items mi ON ms.menu_id = mi.menu_id
    WHERE ms.sale_date BETWEEN ? AND ?
    GROUP BY mi.menu_id, mi.name
    ORDER BY total_sold DESC
    LIMIT 5
");
$stmt->bind_param("ss", $aggStart, $aggEnd);
$stmt->execute();
$topMenuItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("
    SELECT category, SUM(amount) as category_amount
    FROM expenses
    WHERE expense_date BETWEEN ? AND ?
    GROUP BY category
    ORDER BY category_amount DESC
");
$stmt->bind_param("ss", $aggStart, $aggEnd);
$stmt->execute();
$expenseCategories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$expenseLabels = [];
$expenseData   = [];
foreach ($expenseCategories as $cat) {
    $expenseLabels[] = $cat['category'] ?: 'Uncategorized';
    $expenseData[]   = (float)$cat['category_amount'];
}
$expenseLabelsJson = json_encode($expenseLabels);
$expenseDataJson   = json_encode($expenseData);

// -------------------------------------------------------------
// Create PDF document using TCPDF

class MYPDF extends TCPDF {
    // Custom Header
    public function Header() {
        $logo = '../Client/img/LogoMaza.png';
        if (file_exists($logo)) {
            $this->Image($logo, 10, 10, 30, '', 'PNG', '', 'T', false, 300);
        }
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'Financial Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
    // Custom Footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('Maza Resto-Bar');
$pdf->SetAuthor('Maza Resto-Bar');
$pdf->SetTitle('Financial Report');
$pdf->SetSubject('Financial Report');
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetMargins(PDF_MARGIN_LEFT, 50, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->AddPage();

// Build HTML content for the PDF report
$html = '<h2>Financial Summary ('.ucfirst($interval).')</h2>';
$html .= '<table border="1" cellspacing="0" cellpadding="4">';
$html .= '<tr>
    <th>Total Revenue</th>
    <th>Total Expenses</th>
    <th>Total Payroll</th>
    <th>Total Purchases</th>
    <th>Total Debts</th>
    <th>Net Profit</th>
</tr>';
$html .= '<tr>
    <td>$'.number_format($cardsRevenue, 2).'</td>
    <td>$'.number_format($cardsExpenses, 2).'</td>
    <td>$'.number_format($cardsPayroll, 2).'</td>
    <td>$'.number_format($cardsPurchases, 2).'</td>
    <td>$'.number_format($cardsDebts, 2).'</td>
    <td>$'.number_format($cardsNetProfit, 2).'</td>
</tr>';
$html .= '</table>';

$html .= '<h3>History</h3>';
if(empty($historyRows)) {
    $html .= '<p>No historical data available for this interval.</p>';
} else {
    $html .= '<table border="1" cellspacing="0" cellpadding="4">';
    $html .= '<tr>
        <th>Date</th>
        <th>Sales</th>
        <th>Items</th>
        <th>Revenue</th>
    </tr>';
    foreach ($historyRows as $hr) {
        $html .= '<tr>
            <td>'.htmlspecialchars($hr['date']).'</td>
            <td>'.htmlspecialchars($hr['total_sales']).'</td>
            <td>'.htmlspecialchars($hr['total_items']).'</td>
            <td>BIF'.number_format($hr['total_revenue'], 2).'</td>
        </tr>';
    }
    $html .= '</table>';
}

// You can add additional sections (like low stock alerts, top-selling items, or expense breakdown) if needed

$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF to browser (inline view)
$pdf->Output('Financial_Report_'.date('YmdHis').'.pdf', 'I');

$conn->close();
?>

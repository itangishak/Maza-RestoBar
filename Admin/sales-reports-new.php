<?php
session_start();
require_once './connection.php';

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION['UserId'])) {
    header('Location: ../index.php');
    exit();
}

// Check if user has 'Boss' privilege, otherwise redirect to dashboard with access denied message
if (!isset($_SESSION['privilege']) || $_SESSION['privilege'] !== 'Boss') {
    header('Location: dashboard.php?error=access_denied');
    exit();
}

// Get current date for default values
$today = date('Y-m-d');
$currentMonth = date('m');
$currentYear = date('Y');

// Get filter parameters
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'daily';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : $today;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : $today;
$selectedYear = isset($_GET['year']) ? $_GET['year'] : $currentYear;
$startYear = isset($_GET['start_year']) ? $_GET['start_year'] : ($currentYear - 5);
$endYear = isset($_GET['end_year']) ? $_GET['end_year'] : $currentYear;

// Function to get daily sales data
function getDailySalesData($conn, $startDate, $endDate) {
    $data = [];
    
    // Initialize the date range
    $current = new DateTime($startDate);
    $end = new DateTime($endDate);
    $end->modify('+1 day'); // Include the end date
    
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($current, $interval, $end);
    
    // Initialize data structure with zeros for all dates
    foreach ($period as $dt) {
        $date = $dt->format('Y-m-d');
        $data[$date] = [
            'date' => $date,
            'menu_sales' => 0,
            'drink_sales' => 0,
            'buffet_sales' => 0,
            'total_sales' => 0,
            'expenses' => 0,
            'profit' => 0
        ];
    }
    
    // Get menu sales data from orders and order_details tables
    $stmt = $conn->prepare("
        SELECT 
            DATE(o.order_date) as sale_date,
            SUM(od.quantity * od.price) as total
        FROM 
            orders o
        JOIN 
            order_details od ON o.order_id = od.order_id
        WHERE 
            DATE(o.order_date) BETWEEN ? AND ?
        GROUP BY 
            DATE(o.order_date)
    ");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_date']])) {
            $data[$row['sale_date']]['menu_sales'] += $row['total'];
        }
    }
    
    // Get drink sales data
    $stmt = $conn->prepare("
        SELECT 
            DATE(sale_date) as sale_date,
            SUM(total_amount) as total
        FROM 
            drink_sales
        WHERE 
            DATE(sale_date) BETWEEN ? AND ?
        GROUP BY 
            DATE(sale_date)
    ");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_date']])) {
            $data[$row['sale_date']]['drink_sales'] += $row['total'];
        }
    }
    
    // Get buffet sales data
    $stmt = $conn->prepare("
        SELECT 
            DATE(sale_date) as sale_date,
            SUM(total_price) as total
        FROM 
            buffet_sales
        WHERE 
            DATE(sale_date) BETWEEN ? AND ?
        GROUP BY 
            DATE(sale_date)
    ");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_date']])) {
            $data[$row['sale_date']]['buffet_sales'] += $row['total'];
        }
    }
    
    // Get expenses data
    $stmt = $conn->prepare("
        SELECT 
            DATE(expense_date) as expense_date,
            SUM(amount) as total
        FROM 
            expenses
        WHERE 
            DATE(expense_date) BETWEEN ? AND ?
        GROUP BY 
            DATE(expense_date)
    ");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['expense_date']])) {
            $data[$row['expense_date']]['expenses'] += $row['total'];
        }
    }
    
    // Calculate total sales and profit for each date
    foreach ($data as $date => $values) {
        $data[$date]['total_sales'] = $data[$date]['menu_sales'] + $data[$date]['drink_sales'] + $data[$date]['buffet_sales'];
        $data[$date]['profit'] = $data[$date]['total_sales'] - $data[$date]['expenses'];
    }
    
    return array_values($data);
}

// Function to get monthly sales data
function getMonthlySalesData($conn, $year) {
    $data = [];
    
    // Initialize all months with zero values
    for ($month = 1; $month <= 12; $month++) {
        $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
        $data[$month] = [
            'month' => $monthName,
            'year' => $year,
            'menu_sales' => 0,
            'drink_sales' => 0,
            'buffet_sales' => 0,
            'total_sales' => 0,
            'expenses' => 0,
            'profit' => 0
        ];
    }
    
    // Get menu sales data from orders and order_details tables
    $stmt = $conn->prepare("
        SELECT 
            MONTH(o.order_date) as sale_month,
            SUM(od.quantity * od.price) as total
        FROM 
            orders o
        JOIN 
            order_details od ON o.order_id = od.order_id
        WHERE 
            YEAR(o.order_date) = ?
        GROUP BY 
            MONTH(o.order_date)
    ");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_month']])) {
            $data[$row['sale_month']]['menu_sales'] += $row['total'];
        }
    }
    
    // Get drink sales data
    $stmt = $conn->prepare("
        SELECT 
            MONTH(sale_date) as sale_month,
            SUM(total_amount) as total
        FROM 
            drink_sales
        WHERE 
            YEAR(sale_date) = ?
        GROUP BY 
            MONTH(sale_date)
    ");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_month']])) {
            $data[$row['sale_month']]['drink_sales'] += $row['total'];
        }
    }
    
    // Get buffet sales data
    $stmt = $conn->prepare("
        SELECT 
            MONTH(sale_date) as sale_month,
            SUM(total_price) as total
        FROM 
            buffet_sales
        WHERE 
            YEAR(sale_date) = ?
        GROUP BY 
            MONTH(sale_date)
    ");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_month']])) {
            $data[$row['sale_month']]['buffet_sales'] += $row['total'];
        }
    }
    
    // Get expenses data
    $stmt = $conn->prepare("
        SELECT 
            MONTH(expense_date) as expense_month,
            SUM(amount) as total
        FROM 
            expenses
        WHERE 
            YEAR(expense_date) = ?
        GROUP BY 
            MONTH(expense_date)
    ");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['expense_month']])) {
            $data[$row['expense_month']]['expenses'] += $row['total'];
        }
    }
    
    // Calculate total sales and profit for each month
    foreach ($data as $month => $values) {
        $data[$month]['total_sales'] = $data[$month]['menu_sales'] + $data[$month]['drink_sales'] + $data[$month]['buffet_sales'];
        $data[$month]['profit'] = $data[$month]['total_sales'] - $data[$month]['expenses'];
    }
    
    return array_values($data);
}

// Function to get yearly sales data
function getYearlySalesData($conn, $startYear, $endYear) {
    $data = [];
    
    // Initialize all years with zero values
    for ($year = $startYear; $year <= $endYear; $year++) {
        $data[$year] = [
            'year' => $year,
            'menu_sales' => 0,
            'drink_sales' => 0,
            'buffet_sales' => 0,
            'total_sales' => 0,
            'expenses' => 0,
            'profit' => 0
        ];
    }
    
    // Get menu sales data from orders and order_details tables
    $stmt = $conn->prepare("
        SELECT 
            YEAR(o.order_date) as sale_year,
            SUM(od.quantity * od.price) as total
        FROM 
            orders o
        JOIN 
            order_details od ON o.order_id = od.order_id
        WHERE 
            YEAR(o.order_date) BETWEEN ? AND ?
        GROUP BY 
            YEAR(o.order_date)
    ");
    $stmt->bind_param("ii", $startYear, $endYear);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_year']])) {
            $data[$row['sale_year']]['menu_sales'] += $row['total'];
        }
    }
    
    // Get drink sales data
    $stmt = $conn->prepare("
        SELECT 
            YEAR(sale_date) as sale_year,
            SUM(total_amount) as total
        FROM 
            drink_sales
        WHERE 
            YEAR(sale_date) BETWEEN ? AND ?
        GROUP BY 
            YEAR(sale_date)
    ");
    $stmt->bind_param("ii", $startYear, $endYear);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_year']])) {
            $data[$row['sale_year']]['drink_sales'] += $row['total'];
        }
    }
    
    // Get buffet sales data
    $stmt = $conn->prepare("
        SELECT 
            YEAR(sale_date) as sale_year,
            SUM(total_price) as total
        FROM 
            buffet_sales
        WHERE 
            YEAR(sale_date) BETWEEN ? AND ?
        GROUP BY 
            YEAR(sale_date)
    ");
    $stmt->bind_param("ii", $startYear, $endYear);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['sale_year']])) {
            $data[$row['sale_year']]['buffet_sales'] += $row['total'];
        }
    }
    
    // Get expenses data
    $stmt = $conn->prepare("
        SELECT 
            YEAR(expense_date) as expense_year,
            SUM(amount) as total
        FROM 
            expenses
        WHERE 
            YEAR(expense_date) BETWEEN ? AND ?
        GROUP BY 
            YEAR(expense_date)
    ");
    $stmt->bind_param("ii", $startYear, $endYear);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['expense_year']])) {
            $data[$row['expense_year']]['expenses'] += $row['total'];
        }
    }
    
    // Calculate total sales and profit for each year
    foreach ($data as $year => $values) {
        $data[$year]['total_sales'] = $data[$year]['menu_sales'] + $data[$year]['drink_sales'] + $data[$year]['buffet_sales'];
        $data[$year]['profit'] = $data[$year]['total_sales'] - $data[$year]['expenses'];
    }
    
    return array_values($data);
}

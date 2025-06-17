<?php
session_start();
require_once 'connection.php';
// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check if user has 'Boss' privilege for accessing sensitive data
$isBoss = (
    (isset($_SESSION['Privilege']) && (strtolower($_SESSION['Privilege']) === 'boss' || $_SESSION['Privilege'] === 'Boss')) || 
    (isset($_SESSION['privilege']) && (strtolower($_SESSION['privilege']) === 'boss' || $_SESSION['privilege'] === 'Boss'))
);

// Get all employees for dropdown menu
$sqlEmployees = "SELECT e.employee_id, CONCAT(u.firstname, ' ', u.lastname) AS employee_name, e.position 
                FROM employees e 
                JOIN user u ON e.user_id = u.UserId 
                ORDER BY u.firstname, u.lastname";
$employeesResult = $conn->query($sqlEmployees);
$employees = [];
while ($row = $employeesResult->fetch_assoc()) {
    $employees[] = $row;
}

// Get time periods
$validPeriods = ['month', 'trimester', 'semester', 'year'];
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
if (!in_array($period, $validPeriods)) {
    $period = 'month';
}

// Check if specific employee is selected
$selectedEmployeeId = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

// Functions to get date ranges
function getDateRangeForPeriod($period) {
    $now = new DateTime();
    $startDate = clone $now;
    $endDate = clone $now;
    
    switch($period) {
        case 'month':
            // Show previous month instead of current month
            $startDate->modify('first day of last month')->setTime(0, 0, 0);
            $endDate->modify('last day of last month')->setTime(23, 59, 59);
            break;
        case 'trimester':
            $month = intval($now->format('m'));
            $trimester = ceil($month / 3);
            $startMonth = ($trimester - 1) * 3 + 1;
            $endMonth = $trimester * 3;
            
            $startDate->setDate($now->format('Y'), $startMonth, 1)->setTime(0, 0, 0);
            $endDate->setDate($now->format('Y'), $endMonth, 1);
            $endDate->modify('last day of this month')->setTime(23, 59, 59);
            break;
        case 'semester':
            $month = intval($now->format('m'));
            if ($month <= 6) {
                $startDate->setDate($now->format('Y'), 1, 1)->setTime(0, 0, 0);
                $endDate->setDate($now->format('Y'), 6, 30)->setTime(23, 59, 59);
            } else {
                $startDate->setDate($now->format('Y'), 7, 1)->setTime(0, 0, 0);
                $endDate->setDate($now->format('Y'), 12, 31)->setTime(23, 59, 59);
            }
            break;
        case 'year':
            $startDate->setDate($now->format('Y'), 1, 1)->setTime(0, 0, 0);
            $endDate->setDate($now->format('Y'), 12, 31)->setTime(23, 59, 59);
            break;
    }
    
    return [
        'start' => $startDate->format('Y-m-d H:i:s'),
        'end' => $endDate->format('Y-m-d H:i:s'),
        'start_display' => $startDate->format('M j, Y'),
        'end_display' => $endDate->format('M j, Y'),
        'period_name' => $period
    ];
}

// Get summary data for all employees
function getEmployeeSummary($conn) {
    // Total employees count
    $sqlCount = "SELECT COUNT(*) AS total FROM employees";
    $countResult = $conn->query($sqlCount);
    $totalEmployees = $countResult->fetch_assoc()['total'];
    
    // Get present today count
    $today = date('Y-m-d');
    $sqlPresent = "SELECT COUNT(DISTINCT s.employee_id) AS present 
                  FROM schedules s
                  WHERE s.work_date = '$today' AND s.status = 'Present'";
    $presentResult = $conn->query($sqlPresent);
    $presentToday = $presentResult ? $presentResult->fetch_assoc()['present'] : 0;
    
    // Get absent today count
    $sqlAbsent = "SELECT COUNT(DISTINCT s.employee_id) AS absent 
                 FROM schedules s
                 WHERE s.work_date = '$today' AND s.status = 'Absent'";
    $absentResult = $conn->query($sqlAbsent);
    $absentToday = $absentResult ? $absentResult->fetch_assoc()['absent'] : 0;
    
    // Get ill today count
    $sqlIll = "SELECT COUNT(DISTINCT s.employee_id) AS ill 
              FROM schedules s
              WHERE s.work_date = '$today' AND s.status = 'Ill'";
    $illResult = $conn->query($sqlIll);
    $illToday = $illResult ? $illResult->fetch_assoc()['ill'] : 0;
    
    return [
        'total' => $totalEmployees,
        'present' => $presentToday,
        'absent' => $absentToday,
        'ill' => $illToday
    ];
}

// Get employee details if selected
function getEmployeeDetails($employeeId, $conn) {
    $employeeId = intval($employeeId);
    $sql = "SELECT e.*, u.firstname, u.lastname, u.email, u.image, u.reg_date 
           FROM employees e 
           JOIN user u ON e.user_id = u.UserId 
           WHERE e.employee_id = $employeeId";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Get attendance statistics for an employee
function getAttendanceStats($employeeId, $period, $conn) {
    $employeeId = intval($employeeId);
    $dateRange = getDateRangeForPeriod($period);
    $start = $conn->real_escape_string($dateRange['start']);
    $end = $conn->real_escape_string($dateRange['end']);
    
    // Get attendance counts and calculate working hours - link to schedules table
    $sql = "SELECT 
              SUM(CASE WHEN s.status = 'Present' THEN 1 ELSE 0 END) AS present_days,
              SUM(CASE WHEN s.status = 'Absent' THEN 1 ELSE 0 END) AS absent_days,
              SUM(CASE WHEN s.status = 'Ill' THEN 1 ELSE 0 END) AS ill_days,
              -- Days off and justified absences
              SUM(CASE WHEN s.status NOT IN ('Present', 'Absent', 'Ill') THEN 1 ELSE 0 END) AS holiday_days,
              -- Calculate working time only when clock-out time exists
              SUM(CASE WHEN s.status = 'Present' AND ar.clock_out_time IS NOT NULL THEN 
                    TIMESTAMPDIFF(MINUTE, ar.clock_in_time, ar.clock_out_time) 
                  ELSE 0 END) AS total_minutes_worked
            FROM attendance_records ar
            JOIN schedules s ON ar.schedule_id = s.schedule_id
            WHERE ar.employee_id = $employeeId 
            AND ar.attendance_date BETWEEN DATE('$start') AND DATE('$end')";
    
    $result = $conn->query($sql);
    
    $stats = [
        'present_days' => 0,
        'absent_days' => 0,
        'holiday_days' => 0,
        'ill_days' => 0,
        'total_hours' => 0,
        'avg_hours_per_day' => 0
    ];
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['present_days'] = intval($row['present_days']);
        $stats['absent_days'] = intval($row['absent_days']);
        $stats['ill_days'] = intval($row['ill_days']);
        $stats['holiday_days'] = intval($row['holiday_days']);
        
        // Calculate hours from minutes
        $totalMinutes = intval($row['total_minutes_worked']);
        $stats['total_hours'] = round($totalMinutes / 60, 1);
        
        // Calculate average hours per day when present
        if ($stats['present_days'] > 0) {
            $stats['avg_hours_per_day'] = round($stats['total_hours'] / $stats['present_days'], 1);
        }
    }
    
    return $stats;
}

// Get payroll history for an employee
function getPayrollHistory($employeeId, $period, $conn) {
    $employeeId = intval($employeeId);
    $dateRange = getDateRangeForPeriod($period);
    $start = $conn->real_escape_string($dateRange['start']);
    $end = $conn->real_escape_string($dateRange['end']);
    
    $sql = "SELECT * FROM payroll_records 
           WHERE employee_id = $employeeId 
           AND (
               (pay_period_start BETWEEN DATE('$start') AND DATE('$end')) OR
               (pay_period_end BETWEEN DATE('$start') AND DATE('$end')) OR
               (pay_period_start <= DATE('$start') AND pay_period_end >= DATE('$end'))
           )
           ORDER BY pay_period_end DESC";
    
    $result = $conn->query($sql);
    $payroll = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $payroll[] = $row;
        }
    }
    
    return $payroll;
}

// Get recent attendance records for an employee
function getRecentAttendance($employeeId, $period, $conn) {
    $employeeId = intval($employeeId);
    $dateRange = getDateRangeForPeriod($period);
    $start = $conn->real_escape_string($dateRange['start']);
    $end = $conn->real_escape_string($dateRange['end']);
    
    // Join with schedules to get the actual attendance status
    $sql = "SELECT ar.*, s.status, s.schedule_id, 
                  CONCAT(st.start_time, ' - ', st.end_time) as shift_time
           FROM attendance_records ar
           JOIN schedules s ON ar.schedule_id = s.schedule_id
           LEFT JOIN shift_templates st ON s.shift_id = st.shift_id
           WHERE ar.employee_id = $employeeId 
           AND ar.attendance_date BETWEEN DATE('$start') AND DATE('$end')
           ORDER BY ar.attendance_date DESC, ar.clock_in_time DESC";
    
    $result = $conn->query($sql);
    $attendance = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
    } else if ($result === false) {
        // Log SQL error for debugging
        error_log("SQL Error in getRecentAttendance: " . $conn->error);
    }
    
    return $attendance;
}

// Get attendance data for charts
function getAttendanceChartData($employeeId, $period, $conn) {
    $employeeId = intval($employeeId);
    $dateRange = getDateRangeForPeriod($period);
    $start = $conn->real_escape_string($dateRange['start']);
    $end = $conn->real_escape_string($dateRange['end']);
    
    $sql = "";
    $labels = [];
    $presentData = [];
    $absentData = [];
    $illData = [];
    $holidayData = [];
    $justifiedData = []; // Add justified absences as a separate category
    $dayoffData = []; // Add day off as a separate category
    
    switch($period) {
        case 'month':
            // Group by day of month
            $sql = "SELECT 
                      DAY(ar.attendance_date) AS label,
                      SUM(CASE WHEN s.status = 'Present' THEN 1 ELSE 0 END) AS present,
                      SUM(CASE WHEN s.status = 'Absent' THEN 1 ELSE 0 END) AS absent,
                      SUM(CASE WHEN s.status = 'Ill' THEN 1 ELSE 0 END) AS ill,
                      SUM(CASE WHEN s.status = 'Justified' THEN 1 ELSE 0 END) AS justified,
                      SUM(CASE WHEN s.status = 'DayOff' THEN 1 ELSE 0 END) AS dayoff
                    FROM attendance_records ar
                    JOIN schedules s ON ar.schedule_id = s.schedule_id
                    WHERE ar.employee_id = $employeeId 
                    AND ar.attendance_date BETWEEN DATE('$start') AND DATE('$end')
                    GROUP BY DAY(ar.attendance_date)
                    ORDER BY DAY(ar.attendance_date)";
            break;
        case 'trimester':
        case 'semester':
            // Group by month
            $sql = "SELECT 
                      MONTH(ar.attendance_date) AS label,
                      SUM(CASE WHEN s.status = 'Present' THEN 1 ELSE 0 END) AS present,
                      SUM(CASE WHEN s.status = 'Absent' THEN 1 ELSE 0 END) AS absent,
                      SUM(CASE WHEN s.status = 'Ill' THEN 1 ELSE 0 END) AS ill,
                      SUM(CASE WHEN s.status = 'Justified' THEN 1 ELSE 0 END) AS justified,
                      SUM(CASE WHEN s.status = 'DayOff' THEN 1 ELSE 0 END) AS dayoff
                    FROM attendance_records ar
                    JOIN schedules s ON ar.schedule_id = s.schedule_id
                    WHERE ar.employee_id = $employeeId 
                    AND ar.attendance_date BETWEEN DATE('$start') AND DATE('$end')
                    GROUP BY MONTH(ar.attendance_date)
                    ORDER BY MONTH(ar.attendance_date)";
            break;
        case 'year':
            // Group by quarter
            $sql = "SELECT 
                      QUARTER(ar.attendance_date) AS label,
                      SUM(CASE WHEN s.status = 'Present' THEN 1 ELSE 0 END) AS present,
                      SUM(CASE WHEN s.status = 'Absent' THEN 1 ELSE 0 END) AS absent,
                      SUM(CASE WHEN s.status = 'Ill' THEN 1 ELSE 0 END) AS ill,
                      SUM(CASE WHEN s.status = 'Justified' THEN 1 ELSE 0 END) AS justified,
                      SUM(CASE WHEN s.status = 'DayOff' THEN 1 ELSE 0 END) AS dayoff
                    FROM attendance_records ar
                    JOIN schedules s ON ar.schedule_id = s.schedule_id
                    WHERE ar.employee_id = $employeeId 
                    AND ar.attendance_date BETWEEN DATE('$start') AND DATE('$end')
                    GROUP BY QUARTER(ar.attendance_date)
                    ORDER BY QUARTER(ar.attendance_date)";
            break;
    }
    
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format the label based on period
            $labelValue = $row['label'];
            if ($period == 'trimester' || $period == 'semester' || $period == 'year') {
                // Convert month number to name
                $monthNames = [
                    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                    7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
                ];
                $labelValue = $monthNames[intval($labelValue)];
            }
            
            $labels[] = $labelValue;
            $presentData[] = intval($row['present']);
            $absentData[] = intval($row['absent']);
            $illData[] = intval($row['ill']);
            $justifiedData[] = intval($row['justified'] ?? 0);
            $dayoffData[] = intval($row['dayoff'] ?? 0);
        }
    } else if ($result === false) {
        // Log SQL error for debugging
        error_log("SQL Error in getAttendanceChartData: " . $conn->error);
    }
    
    return [
        'labels' => $labels,
        'present' => $presentData,
        'absent' => $absentData,
        'ill' => $illData,
        'justified' => $justifiedData,
        'dayoff' => $dayoffData
    ];
}

// New function for general attendance overview of all employees
function getGeneralAttendanceOverview($conn, $period) {
    $dateRange = getDateRangeForPeriod($period);
    $start = $conn->real_escape_string($dateRange['start']);
    $end = $conn->real_escape_string($dateRange['end']);
    
    // Get top employees with most present days
    $sqlTopPresent = "SELECT 
                      CONCAT(u.firstname, ' ', u.lastname) AS employee_name,
                      COUNT(*) AS present_count
                    FROM attendance_records a
                    JOIN employees e ON a.employee_id = e.employee_id
                    JOIN user u ON e.user_id = u.UserId
                    WHERE a.status = 'Present'
                    AND a.attendance_date BETWEEN DATE('$start') AND DATE('$end')
                    GROUP BY a.employee_id
                    ORDER BY present_count DESC
                    LIMIT 5";
    
    $topPresentResult = $conn->query($sqlTopPresent);
    $topPresentNames = [];
    $topPresentValues = [];
    
    if ($topPresentResult && $topPresentResult->num_rows > 0) {
        while ($row = $topPresentResult->fetch_assoc()) {
            $topPresentNames[] = $row['employee_name'];
            $topPresentValues[] = $row['present_count'];
        }
    }
    
    // Get daily attendance stats
    $sqlDaily = "SELECT 
                    DATE_FORMAT(attendance_date, '%d/%m') AS date,
                    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present_count,
                    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
                    SUM(CASE WHEN status = 'Ill' THEN 1 ELSE 0 END) AS ill_count,
                    SUM(CASE WHEN status NOT IN ('Present', 'Absent', 'Ill') THEN 1 ELSE 0 END) AS holiday_count
                FROM attendance_records
                WHERE attendance_date BETWEEN DATE('$start') AND DATE('$end')
                GROUP BY attendance_date
                ORDER BY attendance_date
                LIMIT 30";
                
    $dailyResult = $conn->query($sqlDaily);
    $dailyDates = [];
    $dailyPresent = [];
    $dailyAbsent = [];
    $dailyIll = [];
    $dailyHoliday = [];
    
    if ($dailyResult && $dailyResult->num_rows > 0) {
        while ($row = $dailyResult->fetch_assoc()) {
            $dailyDates[] = $row['date'];
            $dailyPresent[] = intval($row['present_count']);
            $dailyAbsent[] = intval($row['absent_count']);
            $dailyIll[] = intval($row['ill_count']);
            $dailyHoliday[] = intval($row['holiday_count']);
        }
    }
    
    // Get status distribution
    $sqlStatus = "SELECT 
                    -- Group all non-standard statuses as 'Holiday'
                    CASE 
                        WHEN status IN ('Present', 'Absent', 'Ill') THEN status 
                        ELSE 'Holiday' 
                    END AS status_group,
                    COUNT(*) AS count
                FROM attendance_records
                WHERE attendance_date BETWEEN DATE('$start') AND DATE('$end')
                GROUP BY status_group";
                
    $statusResult = $conn->query($sqlStatus);
    $statusLabels = [];
    $statusCounts = [];
    $statusColors = [
        'Present' => 'rgba(25, 135, 84, 0.8)',
        'Absent' => 'rgba(220, 53, 69, 0.8)',
        'Ill' => 'rgba(255, 193, 7, 0.8)',
        'Holiday' => 'rgba(13, 110, 253, 0.8)'
    ];
    $backgroundColors = [];
    
    if ($statusResult && $statusResult->num_rows > 0) {
        while ($row = $statusResult->fetch_assoc()) {
            $statusLabels[] = $row['status_group'];
            $statusCounts[] = intval($row['count']);
            $backgroundColors[] = $statusColors[$row['status_group']] ?? 'rgba(108, 117, 125, 0.8)';
        }
    }
    
    // Get top employees by working hours
    $sqlWorkingHours = "SELECT 
                        CONCAT(u.firstname, ' ', u.lastname) AS employee_name,
                        SUM(TIMESTAMPDIFF(MINUTE, a.clock_in_time, a.clock_out_time)) / 60 AS total_hours
                      FROM attendance_records a
                      JOIN employees e ON a.employee_id = e.employee_id
                      JOIN user u ON e.user_id = u.UserId
                      WHERE a.status = 'Present' 
                      AND a.clock_out_time IS NOT NULL
                      AND a.attendance_date BETWEEN DATE('$start') AND DATE('$end')
                      GROUP BY a.employee_id
                      ORDER BY total_hours DESC
                      LIMIT 5";
                      
    $hoursResult = $conn->query($sqlWorkingHours);
    $hoursNames = [];
    $hoursValues = [];
    
    if ($hoursResult && $hoursResult->num_rows > 0) {
        while ($row = $hoursResult->fetch_assoc()) {
            $hoursNames[] = $row['employee_name'];
            $hoursValues[] = round(floatval($row['total_hours']), 1);
        }
    }
    
    // Get overall working hours statistics
    $sqlOverallHours = "SELECT 
                          COUNT(DISTINCT employee_id) AS total_employees,
                          SUM(TIMESTAMPDIFF(MINUTE, clock_in_time, clock_out_time)) / 60 AS total_hours,
                          AVG(TIMESTAMPDIFF(MINUTE, clock_in_time, clock_out_time)) / 60 AS avg_hours_per_record
                        FROM attendance_records
                        WHERE status = 'Present' 
                        AND clock_out_time IS NOT NULL
                        AND attendance_date BETWEEN DATE('$start') AND DATE('$end')";
                        
    $overallResult = $conn->query($sqlOverallHours);
    $workingStats = [
        'total_employees' => 0,
        'total_hours' => 0,
        'avg_hours_per_record' => 0
    ];
    
    if ($overallResult && $overallResult->num_rows > 0) {
        $row = $overallResult->fetch_assoc();
        $workingStats['total_employees'] = intval($row['total_employees']);
        $workingStats['total_hours'] = round(floatval($row['total_hours']), 1);
        $workingStats['avg_hours_per_record'] = round(floatval($row['avg_hours_per_record']), 1);
    }
    
    return [
        'topEmployees' => [
            'labels' => $topPresentNames,
            'values' => $topPresentValues
        ],
        'dailyAttendance' => [
            'dates' => $dailyDates,
            'present' => $dailyPresent,
            'absent' => $dailyAbsent,
            'ill' => $dailyIll,
            'holiday' => $dailyHoliday
        ],
        'statusDistribution' => [
            'labels' => $statusLabels,
            'values' => $statusCounts,
            'colors' => $backgroundColors
        ],
        'workingHours' => [
            'labels' => $hoursNames,
            'values' => $hoursValues
        ],
        'overallStats' => $workingStats
    ];
}

// Get employee summary for general overview
$employeeSummary = getEmployeeSummary($conn);

// Initialize general overview data
$generalOverview = getGeneralAttendanceOverview($conn, $period);

// Get selected employee details if any
$employeeDetails = null;
$attendanceStats = null;
$payrollHistory = [];
$recentAttendance = [];
$chartData = [];
$dateRange = getDateRangeForPeriod($period);

if ($selectedEmployeeId > 0) {
    $employeeDetails = getEmployeeDetails($selectedEmployeeId, $conn);
    $attendanceStats = getAttendanceStats($selectedEmployeeId, $period, $conn);
    $payrollHistory = getPayrollHistory($selectedEmployeeId, $period, $conn);
    $recentAttendance = getRecentAttendance($selectedEmployeeId, $period, $conn);
    $chartData = getAttendanceChartData($selectedEmployeeId, $period, $conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="report.employeeReports">Employee Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
            transition: margin-left 0.3s;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            flex: 1 1 200px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .summary-card.present {
            background-color: rgba(25, 135, 84, 0.1);
            border-left: 4px solid #198754;
        }
        .summary-card.absent {
            background-color: rgba(220, 53, 69, 0.1);
            border-left: 4px solid #dc3545;
        }
        .summary-card.ill {
            background-color: rgba(255, 193, 7, 0.1);
            border-left: 4px solid #ffc107;
        }
        .summary-card.total {
            background-color: rgba(13, 110, 253, 0.1);
            border-left: 4px solid #0d6efd;
        }
        .employee-profile {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .employee-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #f0f0f0;
        }
        .employee-info {
            flex: 1;
        }
        .stats-card {
            text-align: center;
            padding: 15px;
        }
        .stats-card h3 {
            margin-bottom: 0;
            font-size: 1.8rem;
        }
        .stats-card p {
            color: #6c757d;
            margin: 0;
        }
        .tab-content {
            padding: 20px 0;
        }
        .attendance-table th, .attendance-table td,
        .payroll-table th, .payroll-table td {
            padding: 10px;
            text-align: center;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-present {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        .status-absent {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        .status-holiday {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
        .status-ill {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }
        .time-period-badge {
            padding: 8px 12px;
            border-radius: 20px;
            background-color: #f8f9fa;
            display: inline-block;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .employee-profile {
                flex-direction: column;
                text-align: center;
            }
            .employee-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
    <!-- Add page load debugging -->
    <script>
        console.log('Employee reports page starting to load');
        window.onload = function() {
            console.log('Employee reports page fully loaded');
        };
    </script>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebar.php'; ?>
    <script>
        console.log('Navbar and sidebar loaded');
        
        // Check if canvas support is available
        function checkCanvasSupport() {
            try {
                document.createElement('canvas').getContext('2d');
                console.log('Canvas is supported in this browser');
                return true;
            } catch (e) {
                console.error('Canvas is not supported in this browser:', e);
                return false;
            }
        }
        
        if (!checkCanvasSupport()) {
            document.addEventListener('DOMContentLoaded', function() {
                const fallbackEl = document.getElementById('attendanceChartFallback');
                if (fallbackEl) {
                    fallbackEl.style.display = 'block';
                    const canvas = document.getElementById('attendanceChart');
                    if (canvas) canvas.style.display = 'none';
                }
            });
        }
    </script>

    <main class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 data-key="report.employeeReports">Employee Reports</h2>
            </div>
            
            <div class="card-body">
                <!-- Employee Selection & Period Filter -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="employee_id" class="form-label" data-key="report.selectEmployee">Select Employee</label>
                        <select name="employee_id" id="employee_id" class="form-select">
                            <option value="0" data-key="report.allEmployees">All Employees</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['employee_id']; ?>" 
                                    <?php echo ($selectedEmployeeId == $employee['employee_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['employee_name']); ?> 
                                    (<?php echo htmlspecialchars($employee['position']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="period" class="form-label" data-key="report.period">Period</label>
                        <select name="period" id="period" class="form-select">
                            <option value="month" <?php echo ($period == 'month') ? 'selected' : ''; ?> data-key="report.month">Previous Month</option>
                            <option value="trimester" <?php echo ($period == 'trimester') ? 'selected' : ''; ?> data-key="report.trimester">Trimester</option>
                            <option value="semester" <?php echo ($period == 'semester') ? 'selected' : ''; ?> data-key="report.semester">Semester</option>
                            <option value="year" <?php echo ($period == 'year') ? 'selected' : ''; ?> data-key="report.year">Year</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100" data-key="report.apply">Apply</button>
                    </div>
                </form>

                <?php if (!$selectedEmployeeId): ?>
                <!-- Employee Overview Section -->
                <div class="summary-row">
                    <div class="summary-card total">
                        <h4 data-key="report.totalEmployees">Total Employees</h4>
                        <h3><?php echo number_format($employeeSummary['total']); ?></h3>
                    </div>
                    <div class="summary-card present">
                        <h4 data-key="report.presentToday">Present Today</h4>
                        <h3><?php echo number_format($employeeSummary['present']); ?></h3>
                    </div>
                    <div class="summary-card absent">
                        <h4 data-key="report.absentToday">Absent Today</h4>
                        <h3><?php echo number_format($employeeSummary['absent']); ?></h3>
                    </div>
                    <div class="summary-card ill">
                        <h4 data-key="report.illToday">Ill Today</h4>
                        <h3><?php echo number_format($employeeSummary['ill']); ?></h3>
                    </div>
                </div>
                
                <!-- General Attendance Overview Charts -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 data-key="report.generalAttendanceOverview">General Attendance Overview</h5>
                        <div class="time-period-badge">
                            <i class="far fa-calendar-alt"></i> 
                            <?php echo $dateRange['start_display']; ?> - <?php echo $dateRange['end_display']; ?>
                            <?php if ($dateRange['period_name'] === 'month'): ?>
                                <span class="badge bg-info ms-2">Previous Month</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Working Hours Overview -->
                        <div class="row mb-4 pb-3 border-bottom">
                            <div class="col-md-4">
                                <div class="stats-card text-center">
                                    <i class="fas fa-users-cog fs-2 text-primary mb-2"></i>
                                    <h3><?php echo $generalOverview['overallStats']['total_employees']; ?></h3>
                                    <p class="text-muted" data-key="report.activeEmployees">Active Employees</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card text-center">
                                    <i class="fas fa-clock fs-2 text-success mb-2"></i>
                                    <h3><?php echo number_format($generalOverview['overallStats']['total_hours'], 1); ?></h3>
                                    <p class="text-muted" data-key="report.totalHoursWorked">Total Hours Worked</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card text-center">
                                    <i class="fas fa-hourglass-half fs-2 text-info mb-2"></i>
                                    <h3><?php echo number_format($generalOverview['overallStats']['avg_hours_per_record'], 1); ?></h3>
                                    <p class="text-muted" data-key="report.avgHoursPerShift">Avg Hours Per Shift</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Daily Attendance Trend -->
                            <div class="col-md-8">
                                <h6 class="text-center" data-key="report.dailyAttendanceTrend">Daily Attendance Trend</h6>
                                <div class="chart-container">
                                    <canvas id="dailyAttendanceChart"></canvas>
                                    <div id="dailyAttendanceChartFallback" style="display: none;" class="alert alert-warning">
                                        Chart data could not be loaded.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Distribution -->
                            <div class="col-md-4">
                                <h6 class="text-center" data-key="report.statusDistribution">Status Distribution</h6>
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="statusDistributionChart"></canvas>
                                    <div id="statusDistChartFallback" style="display: none;" class="alert alert-warning">
                                        Chart data could not be loaded.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <!-- Top Employees by Attendance -->
                            <div class="col-md-6">
                                <h6 class="text-center" data-key="report.topAttendingEmployees">Top Attending Employees</h6>
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="topEmployeesChart"></canvas>
                                    <div id="topEmployeesChartFallback" style="display: none;" class="alert alert-warning">
                                        Chart data could not be loaded.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Top Employees by Working Hours -->
                            <div class="col-md-6">
                                <h6 class="text-center" data-key="report.topByWorkingHours">Top Employees by Working Hours</h6>
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="workingHoursChart"></canvas>
                                    <div id="workingHoursChartFallback" style="display: none;" class="alert alert-warning">
                                        Chart data could not be loaded.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <span data-key="report.selectEmployeeForDetails">Please select an employee to view detailed information</span>
                </div>
                
                <?php else: ?>
                <!-- Employee Detail Section -->
                <?php if ($employeeDetails): ?>
                <div class="time-period-badge">
                    <i class="far fa-calendar-alt"></i> 
                    <?php echo $dateRange['start_display']; ?> - <?php echo $dateRange['end_display']; ?>
                    <?php if ($dateRange['period_name'] === 'month'): ?>
                        <span class="badge bg-info ms-2">Previous Month</span>
                    <?php endif; ?>
                </div>
                
                <div class="employee-profile">
                    <img src="<?php echo $employeeDetails['image'] ? $employeeDetails['image'] : 'assets/images/default-avatar.jpg'; ?>" 
                         class="employee-avatar" alt="Employee photo">
                    <div class="employee-info">
                        <h3><?php echo htmlspecialchars($employeeDetails['firstname'] . ' ' . $employeeDetails['lastname']); ?></h3>
                        <p class="text-muted mb-2">
                            <strong data-key="report.position">Position</strong>: <?php echo htmlspecialchars($employeeDetails['position']); ?>
                        </p>
                        <p class="text-muted mb-2">
                            <strong data-key="report.email">Email</strong>: <?php echo htmlspecialchars($employeeDetails['email']); ?>
                        </p>
                        <p class="text-muted mb-2">
                            <strong data-key="report.salary">Salary</strong>: 
                            <?php echo number_format($employeeDetails['salary'], 2); ?> BIF
                        </p>
                        <p class="text-muted mb-2">
                            <strong data-key="report.hireDate">Hire Date</strong>: 
                            <?php echo date('M j, Y', strtotime($employeeDetails['hire_date'])); ?>
                        </p>
                    </div>
                </div>

                <!-- Attendance Statistics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card card present">
                            <h3><?php echo number_format($attendanceStats['present_days']); ?></h3>
                            <p data-key="report.presentDays">Present Days</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card card absent">
                            <h3><?php echo number_format($attendanceStats['absent_days']); ?></h3>
                            <p data-key="report.absentDays">Absent Days</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card card ill">
                            <h3><?php echo number_format($attendanceStats['ill_days']); ?></h3>
                            <p data-key="report.illDays">Ill Days</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card card">
                            <h3><?php echo number_format($attendanceStats['holiday_days']); ?></h3>
                            <p data-key="report.holidayDays">Holiday Days</p>
                        </div>
                    </div>
                </div>
                
                <!-- Working Hours Statistics -->
                <div class="card mb-4 mt-4">
                    <div class="card-header">
                        <h5 data-key="report.workingHoursStats">Working Hours Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 text-muted" data-key="report.totalHours">Total Hours</h6>
                                            <h2 class="mb-0 mt-2"><?php echo number_format($attendanceStats['total_hours'], 1); ?></h2>
                                        </div>
                                        <div class="fs-1 text-primary">
                                            <i class="far fa-clock"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted small mt-2">
                                        <span data-key="report.totalHoursDesc">Total hours worked in this period</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 text-muted" data-key="report.avgHoursPerDay">Avg Hours/Day</h6>
                                            <h2 class="mb-0 mt-2"><?php echo number_format($attendanceStats['avg_hours_per_day'], 1); ?></h2>
                                        </div>
                                        <div class="fs-1 text-success">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted small mt-2">
                                        <span data-key="report.avgHoursDesc">Average hours worked per day when present</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Working Time Efficiency -->
                        <?php
                        $expectedHoursPerDay = 8; // Assuming 8-hour workday as standard
                        $efficiency = 0;
                        if ($attendanceStats['avg_hours_per_day'] > 0) {
                            $efficiency = min(100, round(($attendanceStats['avg_hours_per_day'] / $expectedHoursPerDay) * 100));
                        }
                        
                        // Determine color based on efficiency
                        $efficiencyClass = 'bg-danger';
                        if ($efficiency >= 90) {
                            $efficiencyClass = 'bg-success';
                        } elseif ($efficiency >= 75) {
                            $efficiencyClass = 'bg-info';
                        } elseif ($efficiency >= 50) {
                            $efficiencyClass = 'bg-warning';
                        }
                        ?>
                        <div class="mt-4">
                            <h6 class="d-flex justify-content-between">
                                <span data-key="report.workingEfficiency">Working Time Efficiency</span>
                                <span><?php echo $efficiency; ?>%</span>
                            </h6>
                            <div class="progress">
                                <div class="progress-bar <?php echo $efficiencyClass; ?>" role="progressbar" 
                                     style="width: <?php echo $efficiency; ?>%" 
                                     aria-valuenow="<?php echo $efficiency; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p class="text-muted small mt-2">
                                <span data-key="report.efficiencyDesc">Based on standard 8-hour workday</span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs for Attendance and Payroll History -->
                <ul class="nav nav-tabs mt-4" id="employeeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" 
                                data-bs-target="#attendance-tab-pane" type="button" role="tab" 
                                aria-controls="attendance-tab-pane" aria-selected="true">
                            <span data-key="report.attendanceHistory">Attendance History</span>
                            <?php 
                            $periodDisplayName = ucfirst($dateRange['period_name']);
                            if ($dateRange['period_name'] === 'month') {
                                $periodDisplayName = 'Previous Month';
                            }
                            ?>
                            <span class="badge bg-secondary ms-1"><?php echo $periodDisplayName; ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payroll-tab" data-bs-toggle="tab" 
                                data-bs-target="#payroll-tab-pane" type="button" role="tab" 
                                aria-controls="payroll-tab-pane" aria-selected="false">
                            <span data-key="report.payrollHistory">Payroll History</span>
                            <span class="badge bg-secondary ms-1"><?php echo $periodDisplayName; ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="debt-tab" data-bs-toggle="tab" data-bs-target="#debt-history" type="button" role="tab" aria-controls="debt-history" aria-selected="false">
                            Debt History
                        </button>
                    </li>   
                </ul>
                
                <!-- Employee Tabs Content -->
                <div class="tab-content" id="employeeTabsContent">
                    <!-- Attendance History Tab -->
                    <div class="tab-pane fade show active" id="attendance-tab-pane" role="tabpanel" 
                         aria-labelledby="attendance-tab" tabindex="0">
                        <?php if (empty($recentAttendance)): ?>
                            <div class="alert alert-info">
                                <span data-key="report.noAttendanceRecords">No attendance records found for this employee</span>
                                <span class="d-block mt-1">
                                    <small>Period: <?php echo $dateRange['start_display']; ?> - <?php echo $dateRange['end_display']; ?></small>
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-light border-0 mb-3">
                                <strong>Showing attendance records for:</strong>
                                <span class="ms-2"><?php echo $dateRange['start_display']; ?> - <?php echo $dateRange['end_display']; ?></span>
                                <span class="badge bg-info ms-2"><?php echo $recentAttendance ? count($recentAttendance) : 0; ?> Records</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered attendance-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th data-key="report.date">Date</th>
                                            <th data-key="report.status">Status</th>
                                            <th data-key="report.clockIn">Clock In</th>
                                            <th data-key="report.clockOut">Clock Out</th>
                                            <th data-key="report.attendanceType">Type</th>
                                            <th data-key="report.notes">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentAttendance as $record): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($record['attendance_date'])); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo strtolower($record['status']); ?>">
                                                        <?php echo $record['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $record['clock_in_time']; ?></td>
                                                <td><?php echo $record['clock_out_time'] ?? '-'; ?></td>
                                                <td><?php echo $record['attendance_type']; ?></td>
                                                <td><?php echo $record['notes'] ? htmlspecialchars($record['notes']) : '-'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Payroll History Tab -->
                        <div class="tab-pane fade" id="payroll-tab-pane" role="tabpanel" 
                             aria-labelledby="payroll-tab" tabindex="0">
                            <?php if (!$isBoss): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-lock"></i>
                                    <span data-key="report.payrollAccessRestricted">Access to payroll information is restricted to Boss users</span>
                                </div>
                            <?php elseif (empty($payrollHistory)): ?>
                                <div class="alert alert-info">
                                    <span data-key="report.noPayrollRecords">No payroll records found for this employee</span>
                                    <span class="d-block mt-1">
                                        <small>Period: <?php echo $dateRange['start_display']; ?> - <?php echo $dateRange['end_display']; ?></small>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light border-0 mb-3">
                                    <strong>Showing payroll records for:</strong>
                                    <span class="ms-2"><?php echo $dateRange['start_display']; ?> - <?php echo $dateRange['end_display']; ?></span>
                                    <span class="badge bg-info ms-2"><?php echo $payrollHistory ? count($payrollHistory) : 0; ?> Records</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered payroll-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th data-key="report.payPeriod">Pay Period</th>
                                                <th data-key="report.paymentDate">Payment Date</th>
                                                <th data-key="report.grossPay">Gross Pay</th>
                                                <th data-key="report.bonus">Bonus</th>
                                                <th data-key="report.deductions">Deductions</th>
                                                <th data-key="report.netPay">Net Pay</th>
                                                <th data-key="report.notes">Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payrollHistory as $payroll): ?>
                                                <tr>
                                                    <td>
                                                        <?php echo date('M j, Y', strtotime($payroll['pay_period_start'])); ?> -
                                                        <?php echo date('M j, Y', strtotime($payroll['pay_period_end'])); ?>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($payroll['payment_date'])); ?></td>
                                                    <td><?php echo number_format($payroll['gross_pay'], 2); ?> BIF</td>
                                                    <td><?php echo number_format($payroll['bonus'], 2); ?> BIF</td>
                                                    <td><?php echo number_format($payroll['deductions'], 2); ?> BIF</td>
                                                    <td><strong><?php echo number_format($payroll['net_pay'], 2); ?> BIF</strong></td>
                                                    <td><?php echo $payroll['notes'] ? htmlspecialchars($payroll['notes']) : '-'; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
 <!-- Debt History -->
<div class="tab-pane fade" id="debt-history" role="tabpanel" aria-labelledby="debt-tab">
    <?php 
    $debtHistory = [];
    if ($selectedEmployeeId) {
        // First get the user_id from employees table
        $employeeUserId = null;
        $sqlEmployee = "SELECT user_id FROM employees WHERE employee_id = ?";
        $stmtEmployee = $conn->prepare($sqlEmployee);
        
        if ($stmtEmployee) {
            $stmtEmployee->bind_param("i", $selectedEmployeeId);
            $stmtEmployee->execute();
            $employeeResult = $stmtEmployee->get_result();
            
            if ($employeeResult->num_rows > 0) {
                $employeeData = $employeeResult->fetch_assoc();
                $employeeUserId = $employeeData['user_id'];
                
                // Now get debts created by this user
                if ($employeeUserId) {
                    $sqlDebts = "SELECT d.debt_id, c.name AS customer_name, d.amount, d.created_at, d.status 
                                FROM debts d 
                                JOIN customers c ON d.customer_id = c.customer_id 
                                WHERE d.created_by = ?";
                    $stmtDebts = $conn->prepare($sqlDebts);
                    
                    if ($stmtDebts) {
                        $stmtDebts->bind_param("i", $employeeUserId);
                        $stmtDebts->execute();
                        $debtResult = $stmtDebts->get_result();
                        $debtHistory = $debtResult->fetch_all(MYSQLI_ASSOC);
                        $stmtDebts->close();
                    } else {
                        error_log("Failed to prepare debt query: " . $conn->error);
                    }
                }
            }
            $stmtEmployee->close();
        } else {
            error_log("Failed to prepare employee query: " . $conn->error);
        }
    }
    ?>
    
    <?php if (!empty($debtHistory)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Created At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalAmount = 0;
                foreach ($debtHistory as $debt): 
                    $totalAmount += $debt['amount'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($debt['customer_name']) ?></td>
                        <td><?= number_format($debt['amount'], 2) ?></td>
                        <td><?= date('M j, Y', strtotime($debt['created_at'])) ?></td>
                        <td><?= ucfirst($debt['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-primary">
                    <td><strong>Total</strong></td>
                    <td><strong><?= number_format($totalAmount, 2) ?></strong></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">
            <?php 
            if ($selectedEmployeeId) {
                echo "No debt history found for this employee.";
            } else {
                echo "Please select an employee to view debt history.";
            }
            ?>
        </p>
    <?php endif; ?>
</div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <span data-key="report.employeeNotFound"></span>
                    </div>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include_once './footer.php'; ?>
    
    <!-- Add Chart.js library directly if not in footer -->
    <script src="assets/js/chart.js"></script>
    <!-- Fallback to CDN if local file not available -->
    <script>
        // Check if Chart is loaded
        if (typeof Chart === 'undefined') {
            console.log('Chart.js not loaded from local file, attempting to load from CDN');
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = function() {
                console.log('Chart.js loaded successfully from CDN');
                // Initialize charts after loading
                initializeCharts();
            };
            script.onerror = function() {
                console.error('Failed to load Chart.js from CDN');
                showChartFallbacks();
            };
            document.head.appendChild(script);
        } else {
            console.log('Chart.js already loaded');
            // Initialize charts immediately
            document.addEventListener('DOMContentLoaded', initializeCharts);
        }
        
        function showChartFallbacks() {
            const fallbackElements = [
                'dailyAttendanceChartFallback',
                'statusDistChartFallback',
                'topEmployeesChartFallback',
                'workingHoursChartFallback'
            ];
            
            fallbackElements.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.display = 'block';
                    const canvasContainer = el.parentElement;
                    if (canvasContainer) {
                        const canvas = canvasContainer.querySelector('canvas');
                        if (canvas) canvas.style.display = 'none';
                    }
                }
            });
        }
        
        function initializeCharts() {
            <?php if (!$selectedEmployeeId): // General overview charts ?>
            
            try {
                // 1. Daily Attendance Chart
                const dailyCtx = document.getElementById('dailyAttendanceChart');
                if (dailyCtx) {
                    new Chart(dailyCtx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($generalOverview['dailyAttendance']['dates']); ?>,
                            datasets: [
                                {
                                    label: 'Present',
                                    data: <?php echo json_encode($generalOverview['dailyAttendance']['present']); ?>,
                                    borderColor: 'rgb(25, 135, 84)',
                                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                },
                                {
                                    label: 'Absent',
                                    data: <?php echo json_encode($generalOverview['dailyAttendance']['absent']); ?>,
                                    borderColor: 'rgb(220, 53, 69)',
                                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                },
                                {
                                    label: 'Ill',
                                    data: <?php echo json_encode($generalOverview['dailyAttendance']['ill']); ?>,
                                    borderColor: 'rgb(255, 193, 7)',
                                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                },
                                {
                                    label: 'Justified Absence',
                                    data: <?php echo json_encode($generalOverview['dailyAttendance']['justified'] ?? []); ?>,
                                    borderColor: 'rgb(13, 110, 253)',
                                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                },
                                {
                                    label: 'Day Off',
                                    data: <?php echo json_encode($generalOverview['dailyAttendance']['dayoff'] ?? []); ?>,
                                    borderColor: 'rgb(153, 102, 255)',
                                    backgroundColor: 'rgba(153, 102, 255, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: false
                                },
                                legend: {
                                    position: 'top'
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Number of Employees'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }
                
                // 2. Status Distribution Chart
                const statusCtx = document.getElementById('statusDistributionChart');
                if (statusCtx) {
                    new Chart(statusCtx, {
                        type: 'pie',
                        data: {
                            labels: <?php echo json_encode($generalOverview['statusDistribution']['labels']); ?>,
                            datasets: [{
                                data: <?php echo json_encode($generalOverview['statusDistribution']['values']); ?>,
                                backgroundColor: <?php echo json_encode($generalOverview['statusDistribution']['colors']); ?>,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right'
                                }
                            }
                        }
                    });
                }
                
                // 3. Top Employees Chart
                const topEmpCtx = document.getElementById('topEmployeesChart');
                if (topEmpCtx) {
                    new Chart(topEmpCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($generalOverview['topEmployees']['labels']); ?>,
                            datasets: [{
                                label: 'Present Days',
                                data: <?php echo json_encode($generalOverview['topEmployees']['values']); ?>,
                                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                                borderColor: 'rgb(13, 110, 253)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Days Present'
                                    },
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }
                
                // 4. Working Hours Chart
                const hoursCtx = document.getElementById('workingHoursChart');
                if (hoursCtx) {
                    new Chart(hoursCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($generalOverview['workingHours']['labels']); ?>,
                            datasets: [{
                                label: 'Working Hours',
                                data: <?php echo json_encode($generalOverview['workingHours']['values']); ?>,
                                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                                borderColor: 'rgb(25, 135, 84)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Hours'
                                    }
                                }
                            }
                        }
                    });
                }
                
                console.log('All charts initialized successfully');
            } catch (error) {
                console.error('Error initializing charts:', error);
                showChartFallbacks();
            }
            <?php endif; ?>
        }
    </script>
    
    <!-- Fix translation functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load translations
        const lang = localStorage.getItem('userLang') || 'en';
        console.log('Loading translations for language:', lang);
        fetch(`../languages/${lang}.json`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Failed to fetch translations: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Translations loaded successfully');
                translatePage(data);
            })
            .catch(error => {
                console.error('Translation error:', error);
                // Continue with untranslated content
            });
            
        function translatePage(translations) {
            document.querySelectorAll('[data-key]').forEach(el => {
                const key = el.getAttribute('data-key');
                if(translations[key]){
                    if(el.tagName === 'INPUT') {
                        el.value = translations[key];
                    } else {
                        el.textContent = translations[key];
                    }
                }
            });
        }
        
        function switchLanguage(lang) {
            localStorage.setItem('userLang', lang);
            location.reload();
        }
        
        // Handle employee selection dropdown change
        document.getElementById('employee_id').addEventListener('change', function() {
            if (this.value != "") {
                document.querySelector('form').submit();
            }
        });
        
        // Handle period dropdown change
        document.getElementById('period').addEventListener('change', function() {
            document.querySelector('form').submit();
        });
    });
    </script>

    <!-- Sidebar toggling script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
                card.style.marginLeft = '2%';
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
</body>
</html>



<?php
session_start();
require_once 'connection.php';

// Define schedules table if it doesn't exist
$checkTableQuery = "SHOW TABLES LIKE 'schedules'"; 
$tableExists = $conn->query($checkTableQuery)->num_rows > 0;

if (!$tableExists) {
    $createTableQuery = "CREATE TABLE `schedules` (
      `schedule_id`     INT(11)      NOT NULL AUTO_INCREMENT,
      `employee_id`     INT(11)      NOT NULL,
      `shift_id`        INT(11)      NOT NULL,
      `status`          ENUM(
                           'Present',
                           'Absent',
                           'DayOff',
                           'Ill',
                           'Justified'
                         )           NOT NULL DEFAULT 'Absent',
      `work_date`       DATE         NOT NULL,
      PRIMARY KEY (`schedule_id`),
      INDEX `idx_employee_work_date` (`employee_id`, `work_date`),
      CONSTRAINT `fk_schedules_employee`
        FOREIGN KEY (`employee_id`)
        REFERENCES `employees`(`employee_id`),
      CONSTRAINT `fk_schedules_shift`
        FOREIGN KEY (`shift_id`)
        REFERENCES `shift_templates`(`shift_id`)
    ) ENGINE=InnoDB
      DEFAULT CHARSET=utf8mb4
      COLLATE=utf8mb4_general_ci;";
    
    // Execute the create table query
    if (!$conn->query($createTableQuery)) {
        error_log("Error creating schedules table: " . $conn->error);
    }
}

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check if user has appropriate privileges (Boss or Manager)
$userIsBoss = false;
$userId = $_SESSION['UserId'];

$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$stmtPrivilege = $conn->prepare($privilegeQuery);
$stmtPrivilege->bind_param("i", $userId);
$stmtPrivilege->execute();
$privilegeResult = $stmtPrivilege->get_result();

if ($privilegeResult && $row = $privilegeResult->fetch_assoc()) {
    $userIsBoss = ($row['privilege'] === 'Boss');
    // If not boss, redirect to dashboard
    if (!$userIsBoss) {
        header("Location: index.php");
        exit();
    }
}
$stmtPrivilege->close();

// Get current date for default filter - using server time, not system time
$serverTimeQuery = "SELECT CURDATE() as current_date";
$serverTimeResult = $conn->query($serverTimeQuery);
if ($serverTimeResult) {
    $row = $serverTimeResult->fetch_assoc();
    $currentDate = $row['current_date'];
} else {
    // Fallback to PHP's date function if the query fails
    $currentDate = date('Y-m-d');
}

// Calculate start and end of current week (Monday to Sunday)
$dayOfWeek = date('N', strtotime($currentDate)); // 1 (Monday) through 7 (Sunday)
$startOfWeek = date('Y-m-d', strtotime($currentDate . ' - ' . ($dayOfWeek - 1) . ' days'));
$endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' + 6 days'));

// Get filter parameters
$filterStartDate = isset($_GET['start_date']) ? $_GET['start_date'] : $startOfWeek;
$filterEndDate = isset($_GET['end_date']) ? $_GET['end_date'] : $endOfWeek;
$filterEmployee = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

// Validate dates
if (!strtotime($filterStartDate)) $filterStartDate = $startOfWeek;
if (!strtotime($filterEndDate)) $filterEndDate = $endOfWeek;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="schedule.management">Schedule Management</title>
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
        .sidebar.collapsed {
            width: 70px;
            transition: width 0.3s;
        }
        .main-container.full {
            margin-left: 0 !important;
        }
        .schedule-grid {
            display: grid;
            grid-template-columns: 200px repeat(7, 1fr);
            gap: 5px;
        }
        .grid-header {
            background-color: #f8f9fa;
            padding: 10px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .grid-employee {
            background-color: #f8f9fa;
            padding: 10px;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }
        .grid-cell {
            min-height: 100px;
            border: 1px solid #dee2e6;
            padding: 10px;
            cursor: pointer;
            position: relative;
        }
        .grid-cell:hover {
            background-color: #f1f1f1;
        }
        .shift-item {
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 4px;
            font-size: 13px;
            background-color: #e6f2ff;
            border-left: 4px solid #007bff;
        }
        .shift-time {
            font-weight: bold;
        }
        .filter-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-container > div {
            flex: 1;
            min-width: 200px;
        }
        .btn-bulk-assign {
            min-width: 150px;
        }
        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .calendar-title {
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        /* Print styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .print-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .print-table {
                width: 100%;
                border-collapse: collapse;
            }
            .print-table th, .print-table td {
                border: 1px solid #dee2e6;
                padding: 10px;
                text-align: center;
            }
        }
        
        @media (max-width: 992px) {
            .schedule-grid {
                grid-template-columns: 150px repeat(7, 1fr);
                font-size: 0.9rem;
            }
            .grid-cell {
                min-height: 80px;
                padding: 5px;
            }
        }
        @media (max-width: 768px) {
            .schedule-grid {
                display: block;
                overflow-x: auto;
            }
            .calendar-view {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebar.php'; ?>

    <main class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 data-key="schedule.management">Schedule Management</h2>
                <div>
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addScheduleModal" id="addScheduleBtn">
                        <i class="bi bi-plus"></i> <span data-key="schedule.addSchedule">Add Schedule</span>
                    </button>
                    <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                        <i class="bi bi-calendar-plus"></i> <span data-key="schedule.bulkAssign">Bulk Assign</span>
                    </button>
                    <button class="btn btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#copyWeekModal" id="copyWeekBtn">
                        <i class="bi bi-clipboard"></i> <span data-key="schedule.copyWeek">Copy Week</span>
                    </button>
                    <button class="btn btn-outline-secondary" id="printScheduleBtn">
                        <i class="bi bi-printer"></i> <span data-key="schedule.print">Print</span>
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filters -->
                <div class="filter-container">
                    <div>
                        <label for="filter_employee" class="form-label" data-key="schedule.employee">Employee</label>
                        <select class="form-select" id="filter_employee" name="employee_id">
                            <option value="0" data-key="schedule.allEmployees">All Employees</option>
                            <!-- Employees loaded via Ajax -->
                        </select>
                    </div>
                    <div>
                        <label for="filter_start_date" class="form-label" data-key="schedule.startDate">Start Date</label>
                        <input type="date" class="form-control" id="filter_start_date" name="start_date" value="<?php echo $filterStartDate; ?>">
                    </div>
                    <div>
                        <label for="filter_end_date" class="form-label" data-key="schedule.endDate">End Date</label>
                        <input type="date" class="form-control" id="filter_end_date" name="end_date" value="<?php echo $filterEndDate; ?>">
                    </div>
                    <div class="d-flex align-items-end">
                        <button id="applyFilters" class="btn btn-primary me-2 w-100" data-key="schedule.applyFilters">Apply Filters</button>
                    </div>
                    <div class="d-flex align-items-end">
                        <button id="resetFilters" class="btn btn-outline-secondary w-100" data-key="schedule.resetFilters">Reset</button>
                    </div>
                </div>
                
                <!-- Calendar Navigation -->
                <div class="calendar-nav">
                    <button id="prevWeek" class="btn btn-outline-primary">
                        <i class="bi bi-chevron-left"></i> 
                        <span data-key="schedule.previousWeek">Previous Week</span>
                    </button>
                    
                    <div class="calendar-title" id="calendarTitle">
                        <!-- Will be populated via JS -->
                    </div>
                    
                    <button id="nextWeek" class="btn btn-outline-primary">
                        <span data-key="schedule.nextWeek">Next Week</span>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <!-- Calendar View -->
                <div class="calendar-container">
                    <div class="calendar-view">
                        <div class="schedule-grid" id="scheduleGrid">
                            <!-- Will be populated via JavaScript -->
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> 
                    <span data-key="schedule.clickToAssign">Click on a cell to assign a shift for that employee on that day.</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Schedule Modal -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addScheduleForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="schedule.addScheduleTitle">Assign Shift</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="employee_id" class="form-label" data-key="schedule.employee">Employee</label>
                            <select class="form-select" id="employee_id" name="employee_id" required>
                                <!-- Options loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="work_date" class="form-label" data-key="schedule.date">Work Date</label>
                            <input type="date" class="form-control" id="work_date" name="work_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shift_id" class="form-label" data-key="schedule.shift">Shift</label>
                            <select class="form-select" id="shift_id" name="shift_id" required>
                                <!-- Options loaded dynamically -->
                            </select>
                            <small class="text-muted" id="shift_details"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="schedule.cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-key="schedule.save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editScheduleForm">
                    <input type="hidden" id="edit_schedule_id" name="schedule_id">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="schedule.editScheduleTitle">Edit Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_employee_id" class="form-label" data-key="schedule.employee">Employee</label>
                            <select class="form-select" id="edit_employee_id" name="employee_id" required>
                                <!-- Options loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_work_date" class="form-label" data-key="schedule.date">Work Date</label>
                            <input type="date" class="form-control" id="edit_work_date" name="work_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_shift_id" class="form-label" data-key="schedule.shift">Shift</label>
                            <select class="form-select" id="edit_shift_id" name="shift_id" required>
                                <!-- Options loaded dynamically -->
                            </select>
                            <small class="text-muted" id="edit_shift_details"></small>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-danger" id="deleteScheduleBtn" data-key="schedule.delete">Delete</button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="schedule.cancel">Cancel</button>
                            <button type="submit" class="btn btn-primary" data-key="schedule.update">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div class="modal fade" id="bulkAssignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="bulkAssignForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="schedule.bulkAssignTitle">Bulk Assign Shifts</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="bulk_employee_id" class="form-label" data-key="schedule.employee">Employee</label>
                            <select class="form-select" id="bulk_employee_id" name="employee_id" required>
                                <!-- Options loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bulk_shift_id" class="form-label" data-key="schedule.shift">Shift</label>
                            <select class="form-select" id="bulk_shift_id" name="shift_id" required>
                                <!-- Options loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" data-key="schedule.dateRange">Date Range</label>
                            <div class="row">
                                <div class="col">
                                    <input type="date" class="form-control" id="bulk_start_date" name="start_date" required>
                                </div>
                                <div class="col">
                                    <input type="date" class="form-control" id="bulk_end_date" name="end_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" data-key="schedule.daysOfWeek">Days of Week</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="day_1" name="days[]" value="1">
                                    <label class="form-check-label" for="day_1" data-key="schedule.monday">Monday</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="day_2" name="days[]" value="2">
                                    <label class="form-check-label" for="day_2" data-key="schedule.tuesday">Tuesday</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="day_3" name="days[]" value="3">
                                    <label class="form-check-label" for="day_3" data-key="schedule.wednesday">Wednesday</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="day_4" name="days[]" value="4">
                                    <label class="form-check-label" for="day_4" data-key="schedule.thursday">Thursday</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="day_5" name="days[]" value="5">
                                    <label class="form-check-label" for="day_5" data-key="schedule.friday">Friday</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="day_6" name="days[]" value="6">
                                    <label class="form-check-label" for="day_6" data-key="schedule.saturday">Saturday</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="day_0" name="days[]" value="0">
                                    <label class="form-check-label" for="day_0" data-key="schedule.sunday">Sunday</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="schedule.cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-key="schedule.assign">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Copy Week Modal -->
    <div class="modal fade" id="copyWeekModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="copyWeekForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="schedule.copyWeekTitle">Copy Week Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <span>This will copy all schedules from the source week to the target week, starting on the specified date.</span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="copy_employee_id" class="form-label" data-key="schedule.employee">Employee (Optional)</label>
                            <select class="form-select" id="copy_employee_id" name="employee_id">
                                <option value="0">All Employees</option>
                                <!-- Options loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" data-key="schedule.sourceWeek">Source Week</label>
                            <div class="row">
                                <div class="col">
                                    <label class="form-label small">Start Date</label>
                                    <input type="date" class="form-control" id="source_start_date" name="source_start_date" required>
                                </div>
                                <div class="col">
                                    <label class="form-label small">End Date</label>
                                    <input type="date" class="form-control" id="source_end_date" name="source_end_date" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="target_start_date" class="form-label" data-key="schedule.targetWeekStart">Target Week Start Date</label>
                            <input type="date" class="form-control" id="target_start_date" name="target_start_date" required>
                            <small class="text-muted">The schedule will be copied to a week starting on this date</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="schedule.cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-key="schedule.copy">Copy Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Print Schedule Template (hidden) -->
    <div id="printScheduleTemplate" class="d-none">
        <div class="print-header">
            <h2 style="text-align: center; font-weight: bold; text-decoration: underline;">HORAIRE DE LA SEMAINE DU <span class="print-start-date"></span> AU <span class="print-end-date"></span></h2>
        </div>
        <!-- Weekday Table -->
        <table class="print-table" border="1" cellpadding="5" cellspacing="0" style="width: 100%; margin-bottom: 20px;">
            <thead>
                <tr id="print-header-row">
                    <th style="background-color: #a8c4e5; width: 15%;">Jour</th>
                    <th style="background-color: #a8c4e5; width: 28%;">7h30 – 14h00</th>
                    <th style="background-color: #a8c4e5; width: 28%;">14h00-19h00</th>
                    <th style="background-color: #a8c4e5; width: 28%;">18h00-22h30</th>
                </tr>
            </thead>
            <tbody id="print-weekday-body">
                <!-- Weekday rows will be added dynamically -->
            </tbody>
        </table>
        
        <!-- Weekend Header -->
        <h2 style="text-align: center; font-weight: bold; text-decoration: underline;">WEEK – END</h2>
        
        <!-- Weekend Table -->
        <table class="print-table" border="1" cellpadding="5" cellspacing="0" style="width: 100%;">
            <thead>
                <tr>
                    <th style="background-color: #a8c4e5; width: 15%;">Jour</th>
                    <th style="background-color: #a8c4e5; width: 28%;">7h30 – 14h</th>
                    <th style="background-color: #a8c4e5; width: 28%;">14h00-19h00</th>
                    <th style="background-color: #a8c4e5; width: 28%;">18h00-22h30</th>
                </tr>
            </thead>
            <tbody id="print-weekend-body">
                <!-- Weekend rows will be added dynamically -->
            </tbody>
        </table>
    </div>

    <?php include_once './footer.php'; ?>

    <script>
        $(document).ready(function () {
            // Initialize date variables
            let currentStartDate = '<?php echo $filterStartDate; ?>';
            let currentEndDate = '<?php echo $filterEndDate; ?>';
            let selectedEmployeeId = <?php echo $filterEmployee; ?>;
            
            // Load employees and shifts on page load
            loadEmployees();
            loadShifts();
            loadScheduleData();
            
            // Calendar navigation
            $('#prevWeek').on('click', function() {
                changeWeek(-7);
            });
            
            $('#nextWeek').on('click', function() {
                changeWeek(7);
            });
            
            function changeWeek(days) {
                let start = new Date(currentStartDate);
                let end = new Date(currentEndDate);
                
                start.setDate(start.getDate() + days);
                end.setDate(end.getDate() + days);
                
                currentStartDate = start.toISOString().split('T')[0];
                currentEndDate = end.toISOString().split('T')[0];
                
                $('#filter_start_date').val(currentStartDate);
                $('#filter_end_date').val(currentEndDate);
                
                loadScheduleData();
            }
            
            // Apply filters
            $('#applyFilters').on('click', function() {
                currentStartDate = $('#filter_start_date').val();
                currentEndDate = $('#filter_end_date').val();
                selectedEmployeeId = $('#filter_employee').val();
                loadScheduleData();
            });
            
            // Reset filters
            $('#resetFilters').on('click', function() {
                let today = new Date();
                let startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Monday
                let endOfWeek = new Date(today);
                endOfWeek.setDate(today.getDate() - today.getDay() + 7); // Sunday
                
                currentStartDate = startOfWeek.toISOString().split('T')[0];
                currentEndDate = endOfWeek.toISOString().split('T')[0];
                selectedEmployeeId = 0;
                
                $('#filter_start_date').val(currentStartDate);
                $('#filter_end_date').val(currentEndDate);
                $('#filter_employee').val('0');
                
                loadScheduleData();
            });
            
            // Load employees for dropdown
            function loadEmployees() {
                $.get('fetch_employees.php', function(response) {
                    if (response.status === 'success') {
                        const employees = response.data;
                        populateEmployeeDropdowns(employees);
                    } else {
                        Swal.fire('Error', response.message || 'Failed to load employees', 'error');
                    }
                }, 'json');
            }
            
            // Load shifts for dropdown
            function loadShifts() {
                $.get('fetch_shifts.php', function(response) {
                    if (response.status === 'success') {
                        const shifts = response.data;
                        populateShiftDropdowns(shifts);
                        
                        // Attach event handler to show shift details
                        $('#shift_id, #edit_shift_id, #bulk_shift_id').on('change', function() {
                            const shiftId = $(this).val();
                            const shift = shifts.find(s => s.shift_id == shiftId);
                            const detailElement = this.id === 'shift_id' ? '#shift_details' : 
                                                (this.id === 'edit_shift_id' ? '#edit_shift_details' : null);
                            
                            if (shift && detailElement) {
                                const startTime = formatTimeForDisplay(shift.start_time);
                                const endTime = formatTimeForDisplay(shift.end_time);
                                $(detailElement).html(`Time: ${startTime} - ${endTime}, Grace period: ${shift.grace_period} minutes`);
                            }
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to load shifts', 'error');
                    }
                }, 'json');
            }
            
            // Helper function to populate employee dropdowns
            function populateEmployeeDropdowns(employees) {
                const dropdowns = ['#employee_id', '#edit_employee_id', '#bulk_employee_id', '#filter_employee'];
                
                dropdowns.forEach(selector => {
                    const dropdown = $(selector);
                    
                    // Clear existing options except the default "All Employees" for filter dropdown
                    if (selector === '#filter_employee') {
                        dropdown.find('option:not([value="0"])').remove();
                    } else {
                        dropdown.empty();
                        dropdown.append('<option value="">Select employee...</option>');
                    }
                    
                    employees.forEach(employee => {
                        dropdown.append(`<option value="${employee.employee_id}">${employee.name}</option>`);
                    });
                    
                    // Set the selected employee value for the filter
                    if (selector === '#filter_employee' && selectedEmployeeId > 0) {
                        dropdown.val(selectedEmployeeId);
                    }
                });
            }
            
            // Helper function to populate shift dropdowns
            function populateShiftDropdowns(shifts) {
                const dropdowns = ['#shift_id', '#edit_shift_id', '#bulk_shift_id'];
                
                dropdowns.forEach(selector => {
                    const dropdown = $(selector);
                    dropdown.empty();
                    dropdown.append('<option value="">Select shift...</option>');
                    
                    shifts.forEach(shift => {
                        const startTime = formatTimeForDisplay(shift.start_time);
                        const endTime = formatTimeForDisplay(shift.end_time);
                        dropdown.append(`<option value="${shift.shift_id}">${shift.name} (${startTime} - ${endTime})</option>`);
                    });
                });
            }
            
            // Helper function to format time for display
            function formatTimeForDisplay(timeString) {
                if (!timeString) return '';
                
                const [hours, minutes] = timeString.split(':');
                const hour = parseInt(hours, 10);
                const period = hour >= 12 ? 'PM' : 'AM';
                const displayHour = hour % 12 || 12;
                
                return `${displayHour}:${minutes} ${period}`;
            }
            
            // Load schedule data
            function loadScheduleData() {
                $.ajax({
                    url: 'fetch_schedules.php',
                    type: 'GET',
                    data: {
                        start_date: currentStartDate,
                        end_date: currentEndDate,
                        employee_id: selectedEmployeeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            renderCalendar(response.data, response.dates, response.employees);
                            updateCalendarTitle();
                        } else {
                            Swal.fire('Error', response.message || 'Failed to load schedule data', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load schedule data. Please try again.', 'error');
                    }
                });
            }
            
            // Render the calendar view
            function renderCalendar(schedules, dates, employees) {
                const grid = $('#scheduleGrid');
                grid.empty();
                
                // Add header cells for dates
                grid.append('<div class="grid-header">Employee</div>');
                
                dates.forEach(date => {
                    const day = new Date(date);
                    const dayName = day.toLocaleDateString('en-US', { weekday: 'short' });
                    const dayNum = day.getDate();
                    const monthName = day.toLocaleDateString('en-US', { month: 'short' });
                    grid.append(`<div class="grid-header">${dayName}, ${dayNum} ${monthName}</div>`);
                });
                
                // Add employee rows and cells
                employees.forEach(employee => {
                    grid.append(`<div class="grid-employee">${employee.name}</div>`);
                    
                    // Add cells for each day
                    dates.forEach(date => {
                        const cell = $(`<div class="grid-cell" data-employee="${employee.employee_id}" data-date="${date}"></div>`);
                        
                        // Find schedules for this employee on this day
                        const daySchedules = schedules.filter(s => 
                            s.employee_id == employee.employee_id && s.work_date === date);
                        
                        // Add shift items to cell
                        daySchedules.forEach(schedule => {
                            const shift = $(`<div class="shift-item" data-schedule-id="${schedule.schedule_id}">
                                <div class="shift-name">${schedule.shift_name}</div>
                                <div class="shift-time">${formatTimeForDisplay(schedule.start_time)} - ${formatTimeForDisplay(schedule.end_time)}</div>
                            </div>`);
                            
                            cell.append(shift);
                        });
                        
                        grid.append(cell);
                    });
                });
                
                // Add click handlers for cells and shifts
                attachCellEventHandlers();
            }
            
            // Update calendar title
            function updateCalendarTitle() {
                const startDate = new Date(currentStartDate);
                const endDate = new Date(currentEndDate);
                
                const startMonth = startDate.toLocaleDateString('en-US', { month: 'short' });
                const endMonth = endDate.toLocaleDateString('en-US', { month: 'short' });
                
                let title = '';
                if (startDate.getMonth() === endDate.getMonth()) {
                    title = `${startMonth} ${startDate.getDate()} - ${endDate.getDate()}, ${startDate.getFullYear()}`;
                } else {
                    title = `${startMonth} ${startDate.getDate()} - ${endMonth} ${endDate.getDate()}, ${startDate.getFullYear()}`;
                }
                
                $('#calendarTitle').text(title);
            }
            
            // Attach event handlers to cells and shifts
            function attachCellEventHandlers() {
                // Cell click for adding a new schedule
                $('.grid-cell').on('click', function(e) {
                    // Only trigger if we clicked directly on the cell, not on a shift
                    if (e.target === this || $(e.target).hasClass('grid-cell')) {
                        const employeeId = $(this).data('employee');
                        const workDate = $(this).data('date');
                        
                        // Populate modal and show it
                        $('#employee_id').val(employeeId);
                        $('#work_date').val(workDate);
                        $('#shift_id').val('');
                        $('#addScheduleModal').modal('show');
                    }
                });
                
                // Shift click for editing
                $('.shift-item').on('click', function(e) {
                    e.stopPropagation(); // Prevent cell click
                    const scheduleId = $(this).data('schedule-id');
                    
                    // Load schedule details and show edit modal
                    $.ajax({
                        url: 'get_schedule.php',
                        type: 'GET',
                        data: { schedule_id: scheduleId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                const schedule = response.data;
                                
                                $('#edit_schedule_id').val(schedule.schedule_id);
                                $('#edit_employee_id').val(schedule.employee_id);
                                $('#edit_work_date').val(schedule.work_date);
                                $('#edit_shift_id').val(schedule.shift_id);
                                
                                // Trigger change event to update shift details
                                $('#edit_shift_id').trigger('change');
                                
                                $('#editScheduleModal').modal('show');
                            } else {
                                Swal.fire('Error', response.message || 'Failed to load schedule details', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to load schedule details. Please try again.', 'error');
                        }
                    });
                });
            }
            
            // Add Schedule Form Submit
            $('#addScheduleForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: 'add_schedule.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#addScheduleModal').modal('hide');
                            $('#addScheduleForm')[0].reset();
                            loadScheduleData(); // Reload the calendar
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message || 'Failed to add schedule', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to add schedule. Please try again.', 'error');
                    }
                });
            });
            
            // Edit Schedule Form Submit
            $('#editScheduleForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: 'update_schedule.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editScheduleModal').modal('hide');
                            loadScheduleData(); // Reload the calendar
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message || 'Failed to update schedule', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to update schedule. Please try again.', 'error');
                    }
                });
            });
            
            // Delete Schedule
            $('#deleteScheduleBtn').on('click', function() {
                const scheduleId = $('#edit_schedule_id').val();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will delete this schedule entry.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_schedule.php',
                            type: 'POST',
                            data: { schedule_id: scheduleId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    $('#editScheduleModal').modal('hide');
                                    loadScheduleData(); // Reload the calendar
                                    Swal.fire('Deleted', response.message, 'success');
                                } else {
                                    Swal.fire('Error', response.message || 'Failed to delete schedule', 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete schedule. Please try again.', 'error');
                            }
                        });
                    }
                });
            });
            
            // Bulk Assign Form Submit
            $('#bulkAssignForm').on('submit', function(e) {
                e.preventDefault();
                
                // Validate that at least one day is selected
                if (!$('input[name="days[]"]:checked').length) {
                    Swal.fire('Error', 'Please select at least one day of the week', 'error');
                    return;
                }
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: 'bulk_assign_schedule.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#bulkAssignModal').modal('hide');
                            $('#bulkAssignForm')[0].reset();
                            loadScheduleData(); // Reload the calendar
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message || 'Failed to assign shifts', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to assign shifts. Please try again.', 'error');
                    }
                });
            });
            
            // Set default dates for bulk assign
            $('#bulkAssignModal').on('show.bs.modal', function() {
                $('#bulk_start_date').val(currentStartDate);
                $('#bulk_end_date').val(currentEndDate);
                
                // Check Monday through Friday by default
                $('input[name="days[]"]').prop('checked', false);
                for (let i = 1; i <= 5; i++) {
                    $(`#day_${i}`).prop('checked', true);
                }
            });
            
            // Set default dates for copy week
            $('#copyWeekModal').on('show.bs.modal', function() {
                $('#source_start_date').val(currentStartDate);
                $('#source_end_date').val(currentEndDate);
                
                // Set target date to next week by default
                let targetDate = new Date(currentStartDate);
                targetDate.setDate(targetDate.getDate() + 7); // One week later
                $('#target_start_date').val(targetDate.toISOString().split('T')[0]);
                
                // Populate employee dropdown
                $.get('fetch_employees.php', function(response) {
                    if (response.status === 'success') {
                        const employees = response.data;
                        const dropdown = $('#copy_employee_id');
                        
                        // Clear existing options except the default "All Employees"
                        dropdown.find('option:not([value="0"])').remove();
                        
                        employees.forEach(employee => {
                            dropdown.append(`<option value="${employee.employee_id}">${employee.name}</option>`);
                        });
                    }
                }, 'json');
            });
            
            // Copy Week Form Submit
            $('#copyWeekForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: 'copy_week_schedule.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#copyWeekModal').modal('hide');
                            loadScheduleData(); // Reload the calendar
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message || 'Failed to copy schedule', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to copy schedule. Please try again.', 'error');
                    }
                });
            });
            
            // Print Schedule
            $('#printScheduleBtn').on('click', function() {
                // Create a printable view of the current schedule
                const printTemplate = $('#printScheduleTemplate').clone();
                printTemplate.removeClass('d-none');
                
                // Add custom styles for printing
                const printStyles = `
                    <style type="text/css" media="print">
                        @page { size: landscape; margin: 0.5cm; }
                        body { font-family: Arial, sans-serif; }
                        .print-header { margin-bottom: 15px; }
                        table { border-collapse: collapse; width: 100%; }
                        th { background-color: #a8c4e5 !important; -webkit-print-color-adjust: exact; font-weight: bold; }
                        td { background-color: #e6e8eb !important; -webkit-print-color-adjust: exact; vertical-align: middle; }
                        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
                        td { height: 60px; font-size: 14px; font-weight: bold; line-height: 1.5; }
                    </style>
                `;
                
                // Format dates for the header
                const startDate = new Date(currentStartDate);
                const endDate = new Date(currentEndDate);
                
                // Format dates in French style
                const formatDateFrench = (date) => {
                    return date.getDate().toString().padStart(2, '0') + '/' + 
                           (date.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                           date.getFullYear();
                };
                
                printTemplate.find('.print-start-date').text(formatDateFrench(startDate));
                printTemplate.find('.print-end-date').text(formatDateFrench(endDate));
                
                // Get current schedule data
                $.ajax({
                    url: 'fetch_schedules.php',
                    type: 'GET',
                    data: {
                        start_date: currentStartDate,
                        end_date: currentEndDate,
                        employee_id: selectedEmployeeId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const schedules = response.data;
                            const dates = response.dates;
                            const employees = response.employees;
                            
                      
                            
                            // Create a map of employee IDs to names for quick lookup
                            const employeeMap = {};
                            employees.forEach(employee => {
                                employeeMap[employee.employee_id] = employee.name;
                            });
                            
                            // French day names mapping
                            const frenchDays = {
                                'Mon': 'Lundi',
                                'Tue': 'Mardi',
                                'Wed': 'Mercredi',
                                'Thu': 'Jeudi',
                                'Fri': 'Vendredi',
                                'Sat': 'Samedi',
                                'Sun': 'Dimanche'
                            };
                            
                            // Group schedules by date and time slot
                            const schedulesByDayAndSlot = {};
                            
                            // Define time slots
                            const timeSlots = [
                                { start: '07:30:00', end: '14:00:00' },
                                { start: '14:00:00', end: '19:00:00' },
                                { start: '18:00:00', end: '22:30:00' }
                            ];
                            
                            // Process each schedule and organize by day and time slot
                            schedules.forEach(schedule => {
                                const date = schedule.work_date;
                                
                                if (!schedulesByDayAndSlot[date]) {
                                    schedulesByDayAndSlot[date] = {
                                        slot1: [],
                                        slot2: [],
                                        slot3: []
                                    };
                                }
                                
                                // Detailed debug: Log every property in the schedule object
                                console.log("Schedule Object Full Properties:");
                                for (const key in schedule) {
                                    console.log(`${key}: ${JSON.stringify(schedule[key])}`);
                                }
                                
                                const shiftStart = schedule.start_time;
                                const shiftEnd = schedule.end_time;
                                
                                // Determine which slot this schedule belongs to
                                // We need to be more flexible to match shifts based on approximate times
                                if (shiftStart && (shiftStart.startsWith('07') || shiftStart.startsWith('08'))) {
                                    schedulesByDayAndSlot[date].slot1.push(schedule);
                                } else if (shiftStart && (shiftStart.startsWith('14') || shiftStart.startsWith('13'))) {
                                    schedulesByDayAndSlot[date].slot2.push(schedule);
                                } else if (shiftStart && (shiftStart.startsWith('18') || shiftStart.startsWith('17'))) {
                                    schedulesByDayAndSlot[date].slot3.push(schedule);
                                }
                            });
                            
                            // Get weekdays and weekend dates
                            const weekdayDates = dates.filter(date => {
                                const day = new Date(date).getDay();
                                return day >= 1 && day <= 5; // Monday to Friday
                            });
                            
                            const weekendDates = dates.filter(date => {
                                const day = new Date(date).getDay();
                                return day === 0 || day === 6; // Sunday or Saturday
                            });
                            
                            // Generate weekday rows
                            const weekdayTableBody = printTemplate.find('#print-weekday-body');
                            weekdayTableBody.empty();
                            
                            weekdayDates.forEach(date => {
                                const dayDate = new Date(date);
                                const dayOfWeek = dayDate.toLocaleDateString('en-US', { weekday: 'short' });
                                const frenchDayName = frenchDays[dayOfWeek];
                                const dayFormatted = dayDate.getDate().toString().padStart(2, '0') + '/' + 
                                                     (dayDate.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                                     dayDate.getFullYear();
                                
                                const row = $('<tr>');
                                row.append(`<td style="background-color: #e6e8eb;">${frenchDayName}<br>Le ${dayFormatted}</td>`);
                                
                                // Add cells for each time slot
                                for (let i = 1; i <= 3; i++) {
                                    const cell = $(`<td style="background-color: #e6e8eb;"></td>`);
                                    
                                    if (schedulesByDayAndSlot[date] && schedulesByDayAndSlot[date][`slot${i}`]) {
                                        const employeeNames = schedulesByDayAndSlot[date][`slot${i}`].map(s => {
                                            // Check if employee_name exists to avoid TypeError
                                            // The employee name might be in different properties depending on the API response
                                            let employeeName = "";
                                            
                                            // Look up employee name in the employeeMap
                                            if (s.employee_id && employeeMap[s.employee_id]) {
                                                employeeName = employeeMap[s.employee_id];
                                                console.log("Found name in employeeMap:", employeeName);
                                            } else if (s.employee_name) {
                                                employeeName = s.employee_name;
                                                console.log("Found name in s.employee_name:", employeeName);
                                            } else if (s.name) {
                                                employeeName = s.name;
                                                console.log("Found name in s.name:", employeeName);
                                            } else if (s.employee && s.employee.name) {
                                                employeeName = s.employee.name;
                                                console.log("Found name in s.employee.name:", employeeName);
                                            } else if (s.employee_id) {
                                                // If we have employee_id but no name, try to construct a fallback
                                                employeeName = "Employee " + s.employee_id;
                                                console.log("Using employee_id as fallback:", employeeName);
                                            } else {
                                                // Check if there's any property that might contain the name
                                                for (const key in s) {
                                                    if (typeof s[key] === 'string' && (key.includes('name') || key.includes('employee'))) {
                                                        employeeName = s[key];
                                                        console.log(`Found potential name in s.${key}:`, employeeName);
                                                        break;
                                                    } else if (typeof s[key] === 'object' && s[key] !== null) {
                                                        // Check if this object might contain a name property
                                                        for (const subKey in s[key]) {
                                                            if (typeof s[key][subKey] === 'string' && 
                                                                (subKey.includes('name') || subKey.includes('employee'))) {
                                                                employeeName = s[key][subKey];
                                                                console.log(`Found potential name in s.${key}.${subKey}:`, employeeName);
                                                                break;
                                                            }
                                                        }
                                                        if (employeeName) break;
                                                    }
                                                }
                                                
                                                // If still no name found, use a fallback
                                                if (!employeeName) {
                                                    console.log("No name found in object, using default EMPLOYEE");
                                                    return "EMPLOYEE";
                                                }
                                            }
                                            
                                            // Format the employee name in uppercase as shown in the image
                                            const nameParts = employeeName.toUpperCase().split(' ');
                                            let formattedName = '';
                                            
                                            if (nameParts.length > 1) {
                                                // Format with first name initials if applicable
                                                const lastName = nameParts[0];
                                                const firstNameInitials = nameParts.slice(1).map(part => part.charAt(0) + '.').join('');
                                                formattedName = `${lastName} ${firstNameInitials}`;
                                            } else {
                                                formattedName = employeeName.toUpperCase();
                                            }
                                            
                                            return formattedName;
                                        });
                                        
                                        // Format multiple employees with vertical bars between names when on the same line
                                        // or with line breaks as shown in the image
                                        if (employeeNames.length <= 2) {
                                            cell.html(employeeNames.join('<br>'));
                                        } else {
                                            // For 3 or more names, use vertical bars between some names to match the format
                                            let formattedHtml = '';
                                            for (let j = 0; j < employeeNames.length; j += 2) {
                                                if (j + 1 < employeeNames.length) {
                                                    formattedHtml += employeeNames[j] + ' | ' + employeeNames[j+1] + '<br>';
                                                } else {
                                                    formattedHtml += employeeNames[j];
                                                }
                                            }
                                            cell.html(formattedHtml);
                                        }
                                    }
                                    
                                    row.append(cell);
                                }
                                
                                weekdayTableBody.append(row);
                            });
                            
                            // Generate weekend rows
                            const weekendTableBody = printTemplate.find('#print-weekend-body');
                            weekendTableBody.empty();
                            
                            weekendDates.forEach(date => {
                                const dayDate = new Date(date);
                                const dayOfWeek = dayDate.toLocaleDateString('en-US', { weekday: 'short' });
                                const frenchDayName = frenchDays[dayOfWeek];
                                const dayFormatted = dayDate.getDate().toString().padStart(2, '0') + '/' + 
                                                     (dayDate.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                                     dayDate.getFullYear();
                                
                                const row = $('<tr>');
                                row.append(`<td style="background-color: #e6e8eb;">${frenchDayName}<br>Le ${dayFormatted}</td>`);
                                
                                // Add cells for each time slot
                                for (let i = 1; i <= 3; i++) {
                                    const cell = $(`<td style="background-color: #e6e8eb;"></td>`);
                                    
                                    if (schedulesByDayAndSlot[date] && schedulesByDayAndSlot[date][`slot${i}`]) {
                                        const employeeNames = schedulesByDayAndSlot[date][`slot${i}`].map(s => {
                                            // Check if employee_name exists to avoid TypeError
                                            // The employee name might be in different properties depending on the API response
                                            let employeeName = "";
                                            
                                            // Look up employee name in the employeeMap
                                            if (s.employee_id && employeeMap[s.employee_id]) {
                                                employeeName = employeeMap[s.employee_id];
                                                console.log("Found name in employeeMap:", employeeName);
                                            } else if (s.employee_name) {
                                                employeeName = s.employee_name;
                                                console.log("Found name in s.employee_name:", employeeName);
                                            } else if (s.name) {
                                                employeeName = s.name;
                                                console.log("Found name in s.name:", employeeName);
                                            } else if (s.employee && s.employee.name) {
                                                employeeName = s.employee.name;
                                                console.log("Found name in s.employee.name:", employeeName);
                                            } else if (s.employee_id) {
                                                // If we have employee_id but no name, try to construct a fallback
                                                employeeName = "Employee " + s.employee_id;
                                                console.log("Using employee_id as fallback:", employeeName);
                                            } else {
                                                // Check if there's any property that might contain the name
                                                for (const key in s) {
                                                    if (typeof s[key] === 'string' && (key.includes('name') || key.includes('employee'))) {
                                                        employeeName = s[key];
                                                        console.log(`Found potential name in s.${key}:`, employeeName);
                                                        break;
                                                    } else if (typeof s[key] === 'object' && s[key] !== null) {
                                                        // Check if this object might contain a name property
                                                        for (const subKey in s[key]) {
                                                            if (typeof s[key][subKey] === 'string' && 
                                                                (subKey.includes('name') || subKey.includes('employee'))) {
                                                                employeeName = s[key][subKey];
                                                                console.log(`Found potential name in s.${key}.${subKey}:`, employeeName);
                                                                break;
                                                            }
                                                        }
                                                        if (employeeName) break;
                                                    }
                                                }
                                                
                                                // If still no name found, use a fallback
                                                if (!employeeName) {
                                                    console.log("No name found in object, using default EMPLOYEE");
                                                    return "EMPLOYEE";
                                                }
                                            }
                                            
                                            // Format the employee name in uppercase as shown in the image
                                            const nameParts = employeeName.toUpperCase().split(' ');
                                            let formattedName = '';
                                            
                                            if (nameParts.length > 1) {
                                                // Format with first name initials if applicable
                                                const lastName = nameParts[0];
                                                const firstNameInitials = nameParts.slice(1).map(part => part.charAt(0) + '.').join('');
                                                formattedName = `${lastName} ${firstNameInitials}`;
                                            } else {
                                                formattedName = employeeName.toUpperCase();
                                            }
                                            
                                            return formattedName;
                                        });
                                        
                                        // Format multiple employees with vertical bars between names when on the same line
                                        // or with line breaks as shown in the image
                                        if (employeeNames.length <= 2) {
                                            cell.html(employeeNames.join('<br>'));
                                        } else {
                                            // For 3 or more names, use vertical bars between some names to match the format
                                            let formattedHtml = '';
                                            for (let j = 0; j < employeeNames.length; j += 2) {
                                                if (j + 1 < employeeNames.length) {
                                                    formattedHtml += employeeNames[j] + ' | ' + employeeNames[j+1] + '<br>';
                                                } else {
                                                    formattedHtml += employeeNames[j];
                                                }
                                            }
                                            cell.html(formattedHtml);
                                        }
                                    }
                                    
                                    row.append(cell);
                                }
                                
                                weekendTableBody.append(row);
                            });
                            
                            // Create print window
                            const printWindow = window.open('', '_blank');
                            printWindow.document.write('<html><head><title>Horaire de la Semaine</title>');
                            printWindow.document.write(printStyles);
                            printWindow.document.write('</head><body>');
                            printWindow.document.write(printTemplate.html());
                            printWindow.document.write('</body></html>');
                            
                            printWindow.document.close();
                            printWindow.focus();
                            
                            // Print after a slight delay to ensure styles are loaded
                            setTimeout(function() {
                                printWindow.print();
                                printWindow.close();
                            }, 500);
                        } else {
                            Swal.fire('Error', 'Failed to load schedule data for printing', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load schedule data for printing', 'error');
                    }
                });
            });
        });
    </script>
    
    <!-- Sidebar toggling logic -->
    <script>
    $(document).ready(function () {
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
    });
    </script>
</body>
</html> 
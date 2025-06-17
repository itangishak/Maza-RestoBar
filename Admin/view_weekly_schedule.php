<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit;
}

// Get user privilege
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$stmtPrivilege = $conn->prepare($privilegeQuery);
$stmtPrivilege->bind_param("i", $userId);
$stmtPrivilege->execute();
$privilegeResult = $stmtPrivilege->get_result();

$userPrivilege = '';
if ($privilegeResult && $row = $privilegeResult->fetch_assoc()) {
    $userPrivilege = $row['privilege'];
}
$stmtPrivilege->close();

$isManager = ($userPrivilege === 'Boss' || $userPrivilege === 'Manager');

// Get selected date range
$weekStart = isset($_GET['week_start']) ? $_GET['week_start'] : date('Y-m-d', strtotime('monday this week'));
$weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

// Get selected employee ID
$selectedEmployeeId = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;

// Load all employees for dropdown
$employeeQuery = "SELECT e.employee_id, CONCAT(u.firstname, ' ', u.lastname) AS employee_name
                 FROM employees e
                 JOIN user u ON e.user_id = u.UserId
                 WHERE e.status = 'Active'
                 ORDER BY u.firstname, u.lastname";
$employeeResult = $conn->query($employeeQuery);

$employees = [];
while ($row = $employeeResult->fetch_assoc()) {
    $employees[$row['employee_id']] = $row['employee_name'];
}

// Load all shifts for dropdown
$shiftQuery = "SELECT shift_id, name, start_time, end_time 
              FROM shift_templates
              ORDER BY name";
$shiftResult = $conn->query($shiftQuery);

$shifts = [];
while ($row = $shiftResult->fetch_assoc()) {
    $shifts[$row['shift_id']] = $row['name'] . ' (' . substr($row['start_time'], 0, 5) . ' - ' . substr($row['end_time'], 0, 5) . ')';
}

// Process AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'update_schedule':
                if (!$isManager) {
                    throw new Exception("Insufficient privileges");
                }
                
                $scheduleId = isset($_POST['schedule_id']) ? intval($_POST['schedule_id']) : 0;
                $shiftId = isset($_POST['shift_id']) ? intval($_POST['shift_id']) : 0;
                $status = isset($_POST['status']) ? $_POST['status'] : '';
                
                if ($scheduleId <= 0) {
                    throw new Exception("Invalid schedule ID");
                }
                
                // Update the schedule
                $updateQuery = "UPDATE schedules SET shift_id = ?, status = ? WHERE schedule_id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("isi", $shiftId, $status, $scheduleId);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Schedule updated successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No changes made or record not found']);
                }
                exit;
                
            default:
                echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// Load schedules for the selected week
$whereClause = "WHERE s.work_date BETWEEN ? AND ?";
$params = [$weekStart, $weekEnd];
$types = "ss";

if ($selectedEmployeeId > 0) {
    $whereClause .= " AND s.employee_id = ?";
    $params[] = $selectedEmployeeId;
    $types .= "i";
}

$query = "SELECT 
            s.schedule_id,
            s.employee_id,
            CONCAT(u.firstname, ' ', u.lastname) AS employee_name,
            s.shift_id,
            st.name AS shift_name,
            st.start_time,
            st.end_time,
            s.status,
            s.work_date,
            DAYNAME(s.work_date) AS day_name
          FROM 
            schedules s
            JOIN employees e ON s.employee_id = e.employee_id
            JOIN user u ON e.user_id = u.UserId
            JOIN shift_templates st ON s.shift_id = st.shift_id
          $whereClause
          ORDER BY 
            s.work_date, u.firstname, u.lastname, st.start_time";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$weekSchedule = [];
$days = [];

// Prepare day headers for the table
$currentDay = new DateTime($weekStart);
for ($i = 0; $i < 7; $i++) {
    $dayKey = $currentDay->format('Y-m-d');
    $days[$dayKey] = [
        'name' => $currentDay->format('l'),
        'date' => $currentDay->format('Y-m-d'),
        'short' => $currentDay->format('m/d')
    ];
    $currentDay->modify('+1 day');
}

// Group schedules by employee and day
while ($row = $result->fetch_assoc()) {
    $employeeId = $row['employee_id'];
    $date = $row['work_date'];
    
    if (!isset($weekSchedule[$employeeId])) {
        $weekSchedule[$employeeId] = [
            'name' => $row['employee_name'],
            'days' => []
        ];
    }
    
    $weekSchedule[$employeeId]['days'][$date] = [
        'schedule_id' => $row['schedule_id'],
        'shift_id' => $row['shift_id'],
        'shift_name' => $row['shift_name'],
        'start_time' => substr($row['start_time'], 0, 5),
        'end_time' => substr($row['end_time'], 0, 5),
        'status' => $row['status']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Schedule</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .schedule-cell {
            min-width: 140px;
            padding: 5px;
        }
        .day-off {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .present {
            background-color: #d1e7dd;
        }
        .absent {
            background-color: #f8d7da;
        }
        .ill {
            background-color: #fff3cd;
        }
        .justified {
            background-color: #cfe2ff;
        }
        .shift-info {
            font-size: 0.85em;
        }
        .status-badge {
            font-size: 0.75em;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .edit-controls {
            display: none;
            margin-top: 5px;
        }
        .schedule-cell:hover .edit-controls {
            display: block;
        }
        .nav-week {
            font-size: 1.2em;
            font-weight: bold;
        }
        .form-inline .form-control-sm {
            width: 100px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-3">
            <div class="col">
                <h2>Weekly Schedule</h2>
            </div>
            <?php if ($isManager): ?>
                <div class="col-auto">
                    <a href="manage_weekly_schedule.php" class="btn btn-primary">Generate Schedule</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form method="get" class="form-inline">
                            <div class="form-group mr-3">
                                <label for="week_start" class="mr-2">Week Starting:</label>
                                <input type="date" class="form-control" id="week_start" name="week_start" value="<?php echo $weekStart; ?>">
                            </div>
                            
                            <div class="form-group mr-3">
                                <label for="employee_id" class="mr-2">Employee:</label>
                                <select class="form-control" id="employee_id" name="employee_id">
                                    <option value="0">All Employees</option>
                                    <?php foreach ($employees as $id => $name): ?>
                                        <option value="<?php echo $id; ?>" <?php echo ($selectedEmployeeId == $id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">View</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="?week_start=<?php echo date('Y-m-d', strtotime($weekStart . ' -7 days')); ?>&employee_id=<?php echo $selectedEmployeeId; ?>" class="btn btn-outline-secondary">&laquo; Previous Week</a>
                            
                            <span class="nav-week"><?php echo date('M d', strtotime($weekStart)); ?> - <?php echo date('M d, Y', strtotime($weekEnd)); ?></span>
                            
                            <a href="?week_start=<?php echo date('Y-m-d', strtotime($weekStart . ' +7 days')); ?>&employee_id=<?php echo $selectedEmployeeId; ?>" class="btn btn-outline-secondary">Next Week &raquo;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Employee</th>
                        <?php foreach ($days as $day): ?>
                            <th><?php echo $day['name']; ?><br><?php echo $day['short']; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($weekSchedule)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No schedules found for the selected week.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($weekSchedule as $employeeId => $employee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <?php foreach ($days as $date => $day): ?>
                                    <?php 
                                    $hasSchedule = isset($employee['days'][$date]);
                                    $schedule = $hasSchedule ? $employee['days'][$date] : null;
                                    $status = $hasSchedule ? $schedule['status'] : 'DayOff';
                                    $cellClass = strtolower($status);
                                    ?>
                                    <td class="schedule-cell <?php echo $cellClass; ?>">
                                        <?php if ($hasSchedule): ?>
                                            <div>
                                                <strong><?php echo htmlspecialchars($schedule['shift_name']); ?></strong>
                                                <span class="float-right status-badge badge badge-<?php echo getBadgeClass($status); ?>">
                                                    <?php echo $status; ?>
                                                </span>
                                            </div>
                                            <div class="shift-info">
                                                <?php echo $schedule['start_time']; ?> - <?php echo $schedule['end_time']; ?>
                                            </div>
                                            
                                            <?php if ($isManager): ?>
                                                <div class="edit-controls">
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-schedule-btn" 
                                                            data-schedule-id="<?php echo $schedule['schedule_id']; ?>"
                                                            data-shift-id="<?php echo $schedule['shift_id']; ?>"
                                                            data-status="<?php echo $status; ?>">
                                                        Edit
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="text-center">
                                                <span class="status-badge badge badge-secondary">Day Off</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Edit Schedule Modal -->
    <?php if ($isManager): ?>
    <div class="modal fade" id="editScheduleModal" tabindex="-1" role="dialog" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editScheduleForm">
                        <input type="hidden" id="schedule_id" name="schedule_id" value="">
                        <input type="hidden" name="action" value="update_schedule">
                        
                        <div class="form-group">
                            <label for="shift_id">Shift:</label>
                            <select class="form-control" id="shift_id" name="shift_id">
                                <?php foreach ($shifts as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select class="form-control" id="status" name="status">
                                <option value="Absent">Absent</option>
                                <option value="Present">Present</option>
                                <option value="DayOff">Day Off</option>
                                <option value="Ill">Ill</option>
                                <option value="Justified">Justified</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveScheduleBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <?php if ($isManager): ?>
    <script>
        $(document).ready(function() {
            // Edit schedule button click
            $('.edit-schedule-btn').on('click', function() {
                var scheduleId = $(this).data('schedule-id');
                var shiftId = $(this).data('shift-id');
                var status = $(this).data('status');
                
                $('#schedule_id').val(scheduleId);
                $('#shift_id').val(shiftId);
                $('#status').val(status);
                
                $('#editScheduleModal').modal('show');
            });
            
            // Save schedule button click
            $('#saveScheduleBtn').on('click', function() {
                var formData = $('#editScheduleForm').serialize();
                
                $.ajax({
                    type: 'POST',
                    url: 'view_weekly_schedule.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#editScheduleModal').modal('hide');
                            // Reload page to show changes
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while processing your request.');
                    }
                });
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>

<?php
// Helper function to get badge class based on status
function getBadgeClass($status) {
    switch ($status) {
        case 'Present':
            return 'success';
        case 'Absent':
            return 'danger';
        case 'DayOff':
            return 'secondary';
        case 'Ill':
            return 'warning';
        case 'Justified':
            return 'info';
        default:
            return 'secondary';
    }
}
?> 
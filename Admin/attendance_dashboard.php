<?php
session_start();
require_once 'connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check user's privilege level for showing the historic tab
$showHistoricTab = false;
$userIsBoss = false; // Add a variable to track boss status
$userId = $_SESSION['UserId'];

$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$stmtPrivilege = $conn->prepare($privilegeQuery);
$stmtPrivilege->bind_param("i", $userId);
$stmtPrivilege->execute();
$privilegeResult = $stmtPrivilege->get_result();

if ($privilegeResult && $row = $privilegeResult->fetch_assoc()) {
    $userIsBoss = ($row['privilege'] === 'Boss');
    $showHistoricTab = $userIsBoss;
}
$stmtPrivilege->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Title will be translated -->
    <title data-key="report.attendanceManagement"></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php include_once './header.php'; ?>
    <style>
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
        /* Styles for the tabs */
        .nav-tabs .nav-link {
            color: #495057;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once './navbar.php'; ?>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <!-- Header translated -->
                <h4 data-key="report.attendanceManagement">Attendance Management</h4>
                <!-- Button translated using data-key -->
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAttendanceModal" id="addAttendanceBtn">Add Attendance</button>
            </div>
            <div class="card-body">
                <!-- Tab navigation -->
                <ul class="nav nav-tabs mb-3" id="attendanceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today-tab-pane" 
                                type="button" role="tab" aria-controls="today-tab-pane" aria-selected="true" 
                                data-key="report.todayAttendance">Today's Attendance</button>
                    </li>
                    <?php if ($showHistoricTab): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historic-tab" data-bs-toggle="tab" data-bs-target="#historic-tab-pane" 
                                type="button" role="tab" aria-controls="historic-tab-pane" aria-selected="false"
                                data-key="report.historicAttendance">Historic Attendance</button>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Tab content -->
                <div class="tab-content" id="attendanceTabsContent">
                    <!-- Today's attendance tab -->
                    <div class="tab-pane fade show active" id="today-tab-pane" role="tabpanel" aria-labelledby="today-tab" tabindex="0">
                        <table id="todayAttendanceTable" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th data-key="report.employee">Employee</th>
                                    <th data-key="report.attendanceType">Attendance Type</th>
                                    <th data-key="report.date">Date</th>
                                    <th data-key="report.status">Status</th>
                                    <th data-key="report.clockIn">Clock In</th>
                                    <th data-key="report.clockOut">Clock Out</th>
                                    <th data-key="report.notes">Notes</th>
                                    <th data-key="report.action">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    
                    <?php if ($showHistoricTab): ?>
                    <!-- Historic attendance tab -->
                    <div class="tab-pane fade" id="historic-tab-pane" role="tabpanel" aria-labelledby="historic-tab" tabindex="0">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="dateRangeStart" data-key="report.startDate">Start Date</label>
                                <input type="date" id="dateRangeStart" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="dateRangeEnd" data-key="report.endDate">End Date</label>
                                <input type="date" id="dateRangeEnd" class="form-control">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button id="filterDates" class="btn btn-primary" data-key="report.filter">Filter</button>
                                <button id="resetDates" class="btn btn-secondary ms-2" data-key="report.reset">Reset</button>
                            </div>
                        </div>
                        <table id="historicAttendanceTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                                    <th data-key="report.employee">Employee</th>
                                    <th data-key="report.attendanceType">Attendance Type</th>
                                    <th data-key="report.date">Date</th>
                                    <th data-key="report.status">Status</th>
                                    <th data-key="report.clockIn">Clock In</th>
                                    <th data-key="report.clockOut">Clock Out</th>
                                    <th data-key="report.notes">Notes</th>
                                    <th data-key="report.action">Action</th>
                        </tr>
                    </thead>
                </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Attendance Modal -->
    <div class="modal fade" id="addAttendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addAttendanceForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalAddAttendanceTitle">Add Attendance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="employee_id" data-key="report.employee">Employee</label>
                        <select class="form-control mb-2" id="employee_id" name="employee_id" required>
                            <!-- Options loaded dynamically -->
                        </select>

                        <label for="attendance_type" data-key="report.attendanceType">Attendance Type</label>
                        <select class="form-control mb-2" id="attendance_type" name="attendance_type" required>
                            <option value="Manual" data-key="report.attendanceTypeManual">Manual</option>
                            <option value="QR Code" data-key="report.attendanceTypeQRCode">QR Code</option>
                        </select>

                        <label for="attendance_date" data-key="report.date">Date</label>
                        <input type="date" class="form-control mb-2" id="attendance_date" name="attendance_date" required readonly>

                        <label for="status" data-key="report.status">Status</label>
                        <select class="form-control mb-2" id="status" name="status" required>
                            <option value="Present" data-key="report.statusPresent">Present</option>
                            <option value="Absent" data-key="report.statusAbsent">Absent</option>
                            <option value="DayOff" data-key="report.statusDayOff">Day Off</option>
                            <option value="Ill" data-key="report.statusIll">Ill</option>
                            <option value="Justified" data-key="report.statusJustified">Justified</option>
                        </select>

                        <label for="clock_in_time" data-key="report.clockIn">Clock In</label>
                        <input type="time" class="form-control mb-2" id="clock_in_time" name="clock_in_time" required readonly>

                        <label for="clock_out_time" data-key="report.clockOut">Clock Out</label>
                        <input type="time" class="form-control mb-2" id="clock_out_time" disabled>
                        <small class="text-muted">Clock-out time will be recorded later</small>

                        <label for="notes" data-key="report.notes">Notes</label>
                        <textarea class="form-control mb-2" id="notes" name="notes"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="report.addAttendanceBtn">Add Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="modal fade" id="editAttendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editAttendanceForm">
                    <input type="hidden" id="edit_attendance_id" name="attendance_id">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="report.modalEditAttendanceTitle">Edit Attendance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="edit_employee_id" data-key="report.employee">Employee</label>
                        <select class="form-control mb-2" id="edit_employee_id" name="employee_id" required disabled style="pointer-events: none;"></select>

                        <label for="edit_attendance_type" data-key="report.attendanceType">Attendance Type</label>
                        <select class="form-control mb-2" id="edit_attendance_type" name="attendance_type" required disabled style="pointer-events: none;">
                            <option value="Manual" data-key="report.attendanceTypeManual">Manual</option>
                            <option value="QR Code" data-key="report.attendanceTypeQRCode">QR Code</option>
                        </select>

                        <label for="edit_attendance_date" data-key="report.date">Date</label>
                        <input type="date" class="form-control mb-2" id="edit_attendance_date" name="attendance_date" required readonly disabled>

                        <label for="edit_status" data-key="report.status">Status</label>
                        <select class="form-control mb-2" id="edit_status" name="status" required readonly disabled style="pointer-events: none;">
                            <option value="Present" data-key="report.statusPresent">Present</option>
                            <option value="Absent" data-key="report.statusAbsent">Absent</option>
                            <option value="DayOff" data-key="report.statusDayOff">Day Off</option>
                            <option value="Ill" data-key="report.statusIll">Ill</option>
                            <option value="Justified" data-key="report.statusJustified">Justified</option>
                        </select>

                        <label for="edit_clock_in_time" data-key="report.clockIn">Clock In</label>
                        <input type="time" class="form-control mb-2" id="edit_clock_in_time" name="clock_in_time" required readonly disabled>

                        <label for="edit_clock_out_time" data-key="report.clockOut">Clock Out</label>
                        <input type="time" class="form-control mb-2" id="edit_clock_out_time" name="clock_out_time" readonly disabled>

                        <label for="edit_notes" data-key="report.notes">Notes</label>
                        <textarea class="form-control mb-2" id="edit_notes" name="notes" readonly disabled></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="report.close">Close</button>
                        <button type="submit" class="btn btn-primary" data-key="report.saveChanges">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include_once './footer.php'; ?>

  

    <script>
    // Add this at the start of your JavaScript to expose the PHP privilege value
    const userIsBoss = <?php echo json_encode($userIsBoss); ?>;
    
    $(document).ready(function () {
        // Initialize DataTable for Today's Attendance
        let todayTable;
        try {
            todayTable = $('#todayAttendanceTable').DataTable({
                processing: true,
                ajax: {
                    url: 'fetch_attendance.php',
                    dataSrc: function(json) {
                        // If the response is successful and contains data, return it
                        if (json && json.data && Array.isArray(json.data)) {
                            return json.data;
                        }
                        // Otherwise return an empty array to prevent errors
                        console.warn('No data returned from fetch_attendance.php or incorrect format');
                        return [];
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables Ajax error:', error, thrown);
                        Swal.fire('Error', 'Failed to load attendance data. Please refresh the page.', 'error');
                        return [];
                    }
                },
                columns: [
                    { data: 'name', defaultContent: '' },
                    { data: 'attendance_type', defaultContent: '' },
                    { data: 'attendance_date', defaultContent: '' },
                    { data: 'status', defaultContent: '' },
                    { data: 'clock_in_time', defaultContent: '' },
                    { data: 'clock_out_time', defaultContent: '' },
                    { data: 'notes', defaultContent: '' },
                    {
                        data: null,
                        defaultContent: '',
                        render: function (data, type, row) {
                            if (!row || !row.attendance_id) {
                                return '';
                            }
                            
                            // Create buttons using icons instead of text
                            let buttonsHtml = `<button class="btn btn-sm btn-primary edit-btn" data-id="${row.attendance_id}"><i class="fas fa-edit"></i></button>`;
                            
                            // Only show delete button if user has Boss privilege
                            if (userIsBoss) {
                                buttonsHtml += ` <button class="btn btn-sm btn-danger delete-btn" data-id="${row.attendance_id}"><i class="fas fa-trash-alt"></i></button>`;
                            }
                            
                            return buttonsHtml;
                        }
                    }
                ],
                language: {
                    emptyTable: "No attendance records found for today",
                    zeroRecords: "No matching records found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                }
            });
        } catch (e) {
            console.error('Error initializing Today\'s DataTable:', e);
            Swal.fire('Error', 'Failed to initialize the attendance table. Please refresh the page.', 'error');
        }

        <?php if ($showHistoricTab): ?>
        // Initialize DataTable for Historic Attendance
        let historicTable;
        try {
            historicTable = $('#historicAttendanceTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'fetch_historic_attendance.php',
                    type: 'POST',
                    data: function(d) {
                        d.start_date = $('#dateRangeStart').val();
                        d.end_date = $('#dateRangeEnd').val();
                        return d;
                    },
                    dataSrc: function(json) {
                        if (json && json.data && Array.isArray(json.data)) {
                            return json.data;
                        }
                        console.warn('No data returned from fetch_historic_attendance.php or incorrect format');
                        return [];
                    },
                    error: function(xhr, error, thrown) {
                        console.error('Historic DataTables Ajax error:', error, thrown);
                        Swal.fire('Error', 'Failed to load historic attendance data.', 'error');
                        return [];
                    }
                },
            columns: [
                    { data: 'name', defaultContent: '' },
                    { data: 'attendance_type', defaultContent: '' },
                    { data: 'attendance_date', defaultContent: '' },
                    { data: 'status', defaultContent: '' },
                    { data: 'clock_in_time', defaultContent: '' },
                    { data: 'clock_out_time', defaultContent: '' },
                    { data: 'notes', defaultContent: '' },
                {
                    data: null,
                        defaultContent: '',
                    render: function (data, type, row) {
                            if (!row || !row.attendance_id) {
                                return '';
                            }
                            
                            // Create buttons using icons instead of text
                            let buttonsHtml = `<button class="btn btn-sm btn-primary edit-btn" data-id="${row.attendance_id}"><i class="fas fa-edit"></i></button>`;
                            
                            // Only show delete button if user has Boss privilege
                            if (userIsBoss) {
                                buttonsHtml += ` <button class="btn btn-sm btn-danger delete-btn" data-id="${row.attendance_id}"><i class="fas fa-trash-alt"></i></button>`;
                            }
                            
                            return buttonsHtml;
                        }
                    }
                ],
                order: [[2, 'desc']], // Sort by date descending
                language: {
                    emptyTable: "No attendance records found",
                    zeroRecords: "No matching records found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                }
            });
            
            // Event handlers for date filtering
            $('#filterDates').on('click', function() {
                historicTable.ajax.reload();
            });
            
            $('#resetDates').on('click', function() {
                $('#dateRangeStart').val('');
                $('#dateRangeEnd').val('');
                historicTable.ajax.reload();
            });
            
            // Set default start date to beginning of month
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            $('#dateRangeStart').val(firstDay.toISOString().split('T')[0]);
            $('#dateRangeEnd').val(today.toISOString().split('T')[0]);
        } catch (e) {
            console.error('Error initializing Historic DataTable:', e);
            Swal.fire('Error', 'Failed to initialize the historic attendance table.', 'error');
        }
        <?php endif; ?>

        // Re-run translation when a tab changes
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const translations = JSON.parse(localStorage.getItem('translations') || '{}');
            translatePage(translations);
            
            // Adjust table columns when switching tabs
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        // Re-run translation on every table draw
        $('#todayAttendanceTable, #historicAttendanceTable').on('draw.dt', function () {
          const translations = JSON.parse(localStorage.getItem('translations') || '{}');
          translatePage(translations);
        });

        // Use your provided loadEmployees function to load employee options into dropdown
        function loadEmployees() {
            $.get('fetch_employee.php', function (response) {
                const employeeDropdown = $('#employee_id');
                employeeDropdown.empty(); // Clear existing options

                if (response.status === 'success') {
                    response.data.forEach(employee => {
                        employeeDropdown.append(new Option(employee.employee_name, employee.employee_id));
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        }

        // Function to get current server time
        function getServerDateTime() {
            return $.ajax({
                url: 'get_server_datetime.php',
                type: 'GET',
                dataType: 'json'
            });
        }

        // Call loadEmployees when Add Attendance modal is shown
        $('#addAttendanceModal').on('show.bs.modal', function () {
            loadEmployees();
            
            // Get current date and time from server instead of client
            getServerDateTime().then(function(response) {
                if (response.status === 'success') {
                    // Set the date from server
                    $('#attendance_date').val(response.date);
                    
                    // Set the time from server
                    $('#clock_in_time').val(response.time);
                    
                    // Clear clock-out time as it will be filled later
                    $('#clock_out_time').val('');
                } else {
                    // Fallback to client time if server request fails
                    console.error('Failed to get server time, using client time as fallback');
                    const today = new Date();
                    const formattedDate = today.toISOString().split('T')[0];
                    $('#attendance_date').val(formattedDate);
                    
                    const hours = String(today.getHours()).padStart(2, '0');
                    const minutes = String(today.getMinutes()).padStart(2, '0');
                    const currentTime = `${hours}:${minutes}`;
                    $('#clock_in_time').val(currentTime);
                    
                    // Clear clock-out time
                    $('#clock_out_time').val('');
                }
            }).catch(function(error) {
                console.error('Error getting server time:', error);
                // Use client time as fallback
                const today = new Date();
                const formattedDate = today.toISOString().split('T')[0];
                $('#attendance_date').val(formattedDate);
                
                const hours = String(today.getHours()).padStart(2, '0');
                const minutes = String(today.getMinutes()).padStart(2, '0');
                const currentTime = `${hours}:${minutes}`;
                $('#clock_in_time').val(currentTime);
                
                // Clear clock-out time
                $('#clock_out_time').val('');
            });
        });

        // Add Attendance
        $('#addAttendanceForm').on('submit', function (e) {
            e.preventDefault();
            
            // Enable the clock_out_time field temporarily for serialization
            // but we'll set its value to empty to explicitly indicate it's not set yet
            $('#clock_out_time').prop('disabled', false).val('');
            
            // Serialize form data
            const formData = $(this).serialize();
            
            // Disable the field again
            $('#clock_out_time').prop('disabled', true);
            
            $.ajax({
                url: 'add_attendance.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                        // Reload the tables
                        reloadTables();
                      
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                    Swal.fire('Error', 'Failed to add attendance. Please try again.', 'error');
                }
            });
        });

        // Edit Attendance
        $(document).on('click', '.edit-btn', function () {
            const id = $(this).data('id');
            $.get(`fetch_attendance_by_id.php?id=${id}`, function (response) {
                if (response.status === 'success') {
                    const data = response.data;
                    $('#edit_attendance_id').val(data.attendance_id);
                    $('#edit_attendance_type').val(data.attendance_type);
                    $('#edit_attendance_date').val(data.attendance_date);
                    $('#edit_status').val(data.status);
                    $('#edit_clock_in_time').val(data.clock_in_time);
                    $('#edit_clock_out_time').val(data.clock_out_time);
                    $('#edit_notes').val(data.notes);
                    
                    // Load employees for the Edit modal
                    $.get('fetch_employee.php', function (response) {
                        const dropdown = $('#edit_employee_id');
                        dropdown.empty();
                        if (response.status === 'success') {
                            response.data.forEach(emp => {
                                dropdown.append(new Option(emp.employee_name, emp.employee_id));
                            });
                            $('#edit_employee_id').val(data.employee_id);
                            
                            // Apply privilege-based restrictions to form fields
                            if (!userIsBoss) {
                                // Disable fields that only Boss can edit
                                $('#edit_employee_id').prop('disabled', true);
                                $('#edit_attendance_type').prop('disabled', true);
                                $('#edit_attendance_date').prop('disabled', true);
                                $('#edit_status').prop('disabled', true);
                                $('#edit_clock_in_time').prop('disabled', true);
                                
                                // Add a note for non-Boss users
                                if (!$('#privilege-note').length) {
                                    const noteHtml = '<div id="privilege-note" class="alert alert-warning mt-2">Only users with Boss privilege can edit employee, type, date, status, and clock-in time.</div>';
                                    $('.modal-body').prepend(noteHtml);
                                }
                            } else {
                                // Enable fields for Boss users
                                $('#edit_employee_id').prop('disabled', false);
                                $('#edit_attendance_type').prop('disabled', false);
                                $('#edit_attendance_date').prop('disabled', false);
                                $('#edit_status').prop('disabled', false);
                                $('#edit_clock_in_time').prop('disabled', false);
                                
                                // Remove note if it exists
                                $('#privilege-note').remove();
                            }
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }, 'json');
                    
                    $('#editAttendanceModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        });

        $('#editAttendanceForm').on('submit', function (e) {
            e.preventDefault();
            
            // Re-enable disabled fields before serialization so they get submitted
            const disabledFields = $('#editAttendanceForm').find(':disabled').prop('disabled', false);
            
            // If user isn't a boss, capture original values for restricted fields
            let originalValues = {};
            if (!userIsBoss) {
                originalValues = {
                    employee_id: $('#edit_employee_id').data('original-value'),
                    attendance_type: $('#edit_attendance_type').data('original-value'),
                    attendance_date: $('#edit_attendance_date').data('original-value'),
                    status: $('#edit_status').data('original-value'),
                    clock_in_time: $('#edit_clock_in_time').data('original-value')
                };
            }
            
            // Serialize form data
            const formData = $(this).serialize();
            
            // Re-disable fields that were disabled
            disabledFields.prop('disabled', true);
            
            $.ajax({
                url: 'update_attendance.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                        
                        // Reload both tables
                        reloadTables();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                    Swal.fire('Error', 'Failed to update attendance. Please try again.', 'error');
                }
            });
        });

       
        $('#editAttendanceModal').on('show.bs.modal', function() {
            // Fetch server time and populate clock out field
            $.ajax({
                url: 'get_server_time.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.server_time) {
                        $('#edit_clock_out_time').val(response.server_time);
                    } else {
                        console.error('Failed to fetch server time or time not provided.');
                        // Optionally, set a default or leave blank, or show a small error
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching server time:', error);
                    // Optionally, handle AJAX error
                }
            });

            // Store original values for non-boss users
            if (!userIsBoss) {
                $('#edit_employee_id').data('original-value', $('#edit_employee_id').val());
                $('#edit_attendance_type').data('original-value', $('#edit_attendance_type').val());
                $('#edit_attendance_date').data('original-value', $('#edit_attendance_date').val());
                $('#edit_status').data('original-value', $('#edit_status').val());
                $('#edit_clock_in_time').data('original-value', $('#edit_clock_in_time').val());
            }
        });

        // Delete Attendance
        $(document).on('click', '.delete-btn', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirm Operation',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_attendance.php', { id: id }, function (response) {
                        if (response.status === 'success') {
                            Swal.fire(
                                'Operation Completed',
                                'The attendance record has been successfully deleted.',
                                'success'
                            );
                            // Reload both tables
                            reloadTables();
                        } else {
                            // Display specific error message
                            Swal.fire(
                                'Error',
                                response.message || 'An error occurred while deleting the record.',
                                'error'
                            );
                        }
                    }, 'json')
                    .fail(function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        let errorMsg = 'Failed to delete attendance record.';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.message) {
                                errorMsg = response.message;
                            }
                        } catch (e) {
                            // Use default error message if parsing fails
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    });
                }
            });
        });
        
        // Helper function to reload all tables
        function reloadTables() {
            // Always try to reload today's table
            if ($.fn.DataTable.isDataTable('#todayAttendanceTable')) {
                try {
                    $('#todayAttendanceTable').DataTable().ajax.reload();
                } catch (e) {
                    console.error('Error reloading today\'s table:', e);
                }
            }
            
            // Try to reload historic table if it exists
            if ($.fn.DataTable.isDataTable('#historicAttendanceTable')) {
                try {
                    $('#historicAttendanceTable').DataTable().ajax.reload();
                } catch (e) {
                    console.error('Error reloading historic table:', e);
                }
            }
        }
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

    <!-- Translation Script (with button fix) -->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const lang = localStorage.getItem('userLang') || 'en';
        fetch(`../languages/${lang}.json`)
          .then(response => {
              if (!response.ok) {
                  console.error("Translation file not found:", response.statusText);
                  return {}; // Return empty object to prevent error
              }
              return response.json();
          })
          .then(data => {
              console.log("Translations loaded:", data);
              // Save translations to localStorage for use in DataTables rendering
              localStorage.setItem('translations', JSON.stringify(data || {}));
              translatePage(data || {}); // Provide fallback empty object
              
              // Update Add Attendance button text
              const addAttendanceBtn = document.getElementById('addAttendanceBtn');
              if (addAttendanceBtn) {
                  addAttendanceBtn.textContent = data['report.addAttendance'] || 'Add Attendance';
              }
              
              // Refresh DataTables to update button texts with translations
              if ($.fn.DataTable.isDataTable('#todayAttendanceTable')) {
                  $('#todayAttendanceTable').DataTable().draw();
              }
              if ($.fn.DataTable.isDataTable('#historicAttendanceTable')) {
                  $('#historicAttendanceTable').DataTable().draw();
              }
          })
          .catch(error => {
              console.error("Error fetching translations:", error);
              // Continue with empty translations to prevent breaking the UI
              translatePage({});
          });
      });

      function translatePage(translations) {
        // Ensure translations is an object
        if (!translations || typeof translations !== 'object') {
          translations = {};
        }
        
        document.querySelectorAll('[data-key]').forEach(el => {
          const key = el.getAttribute('data-key');
          
          // Only modify if translation exists
          if (translations[key]) {
            if (el.tagName === 'INPUT') {
              el.value = translations[key];
            } else {
              // Store original text as data attribute if not already stored
              if (!el.getAttribute('data-original-text') && el.textContent.trim()) {
                el.setAttribute('data-original-text', el.textContent.trim());
              }
              el.textContent = translations[key];
            }
          } else {
            // Do nothing if there's no translation - keep the default text
            // This preserves the default English text we've added
          }
        });

        // Also ensure all modal buttons have text
        document.querySelectorAll('.modal button[type="submit"], .modal button[data-bs-dismiss="modal"]').forEach(btn => {
          if (!btn.textContent.trim()) {
            if (btn.getAttribute('data-key') === 'report.close') {
              btn.textContent = 'Close';
            } else if (btn.getAttribute('data-key') === 'report.addAttendanceBtn') {
              btn.textContent = 'Add Attendance';
            } else if (btn.getAttribute('data-key') === 'report.saveChanges') {
              btn.textContent = 'Save Changes';
            }
          }
        });
      }

      function switchLanguage(lang) {
        localStorage.setItem('userLang', lang);
        location.reload();
      }
    </script>

    <!-- Add this script to handle modal button translations -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Make sure these texts are always translated properly
      const confirmDeleteBtn = document.querySelector('.swal2-confirm');
      const cancelBtn = document.querySelector('.swal2-cancel');
      
      if (confirmDeleteBtn) {
        confirmDeleteBtn.textContent = 'Confirm Operation';
      }
      
      if (cancelBtn) {
        cancelBtn.textContent = 'Cancel';
      }
      
      // Apply translation to SweetAlert buttons when they appear
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.addedNodes.length) {
            const confirmBtn = document.querySelector('.swal2-confirm');
            const cancelBtn = document.querySelector('.swal2-cancel');
            
            if (confirmBtn) {
              const translations = JSON.parse(localStorage.getItem('translations') || '{}');
              confirmBtn.textContent = translations['common.confirmDelete'] || 'Confirm Operation';
            }
            
            if (cancelBtn) {
              const translations = JSON.parse(localStorage.getItem('translations') || '{}');
              cancelBtn.textContent = translations['common.cancel'] || 'Cancel';
            }
          }
        });
      });
      
      observer.observe(document.body, { childList: true, subtree: true });
    });
    </script>
    <!-- AJAX code for form submissions -->
    <script>
    $(document).ready(function() {
        // Load employees for the dropdown
        $.ajax({
            url: 'get_employees.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    var employeeSelect = $('#employee_id');
                    var editEmployeeSelect = $('#edit_employee_id');
                    
                    employeeSelect.empty();
                    editEmployeeSelect.empty();
                    
                    employeeSelect.append('<option value="">Select Employee</option>');
                    editEmployeeSelect.append('<option value="">Select Employee</option>');
                    
                    $.each(data.employees, function(index, employee) {
                        employeeSelect.append('<option value="' + employee.employee_id + '">' + employee.name + '</option>');
                        editEmployeeSelect.append('<option value="' + employee.employee_id + '">' + employee.name + '</option>');
                    });
                }
            },
            error: function() {
                console.error('Failed to load employees');
            }
        });
        
        // Flag to prevent duplicate submissions
        let isSubmitting = false;
        
        // Handle Add Attendance form submission
        $('#addAttendanceForm').on('submit', function(e) {
            e.preventDefault();
            
            // Check if already submitting - prevent duplicate submissions
            if(isSubmitting) {
                console.log('Preventing duplicate submission');
                return false;
            }
            
            // Set submitting flag
            isSubmitting = true;
            
            // Disable submit button to prevent double submission
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            
            // Store form reference for closure access
            var form = this;
            
            $.ajax({
                url: 'add_attendance.php',
                type: 'POST',
                data: $(form).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Close the modal first
                        $('#addAttendanceModal').modal('hide');
                        
                        // Clear the form
                        $(form).trigger('reset');
                        
                        // Show success message
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Refresh attendance tables
                            if ($.fn.DataTable.isDataTable('#todayAttendanceTable')) {
                                $('#todayAttendanceTable').DataTable().ajax.reload();
                            }
                            if ($.fn.DataTable.isDataTable('#historicAttendanceTable')) {
                                $('#historicAttendanceTable').DataTable().ajax.reload();
                            }
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    // Reset submission flag
                    isSubmitting = false;
                    
                    // Re-enable submit button
                    submitBtn.prop('disabled', false);
                }
            });
        });
        
        // Reset the submission flag when modal is closed
        $('#addAttendanceModal').on('hidden.bs.modal', function() {
            isSubmitting = false;
            $('#addAttendanceForm').trigger('reset');
        });
        
        // Handle Edit Attendance form submission
        $('#editAttendanceForm').on('submit', function(e) {
            e.preventDefault();
            
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'update_attendance.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                          
                            
                            if ($.fn.DataTable.isDataTable('#todayAttendanceTable')) {
                                $('#todayAttendanceTable').DataTable().ajax.reload();
                            }
                            if ($.fn.DataTable.isDataTable('#historicAttendanceTable')) {
                                $('#historicAttendanceTable').DataTable().ajax.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>

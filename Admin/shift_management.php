<?php
session_start();
require_once 'connection.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="shift.management">Shift Management</title>
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
        .time-input {
            width: 150px;
        }
        .grace-period-input {
            width: 100px;
        }
    </style>
</head>
<body>
    <?php include_once './navbar.php'; ?>
    <?php include_once 'sidebar.php'; ?>

    <main class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 data-key="shift.management">Shift Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal" id="addShiftBtn">
                    <i class="bi bi-plus"></i> <span data-key="shift.addShift">Add Shift</span>
                </button>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="shiftTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th data-key="shift.id">ID</th>
                                <th data-key="shift.name">Name</th>
                                <th data-key="shift.startTime">Start Time</th>
                                <th data-key="shift.endTime">End Time</th>
                                <th data-key="shift.gracePeriod">Grace Period (min)</th>
                                <th data-key="shift.actions">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Shift Modal -->
    <div class="modal fade" id="addShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addShiftForm">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="shift.addShiftTitle">Add New Shift</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="shift_name" class="form-label" data-key="shift.name">Shift Name</label>
                            <input type="text" class="form-control" id="shift_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="start_time" class="form-label" data-key="shift.startTime">Start Time</label>
                            <input type="time" class="form-control time-input" id="start_time" name="start_time" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="end_time" class="form-label" data-key="shift.endTime">End Time</label>
                            <input type="time" class="form-control time-input" id="end_time" name="end_time" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="grace_period" class="form-label" data-key="shift.gracePeriod">Grace Period (minutes)</label>
                            <input type="number" class="form-control grace-period-input" id="grace_period" name="grace_period" 
                                  min="0" max="60" value="0" required>
                            <small class="text-muted" data-key="shift.gracePeriodHelp">Time allowed for late clock-in without penalty</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="shift.cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-key="shift.save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Shift Modal -->
    <div class="modal fade" id="editShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editShiftForm">
                    <input type="hidden" id="edit_shift_id" name="shift_id">
                    <div class="modal-header">
                        <h5 class="modal-title" data-key="shift.editShiftTitle">Edit Shift</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_shift_name" class="form-label" data-key="shift.name">Shift Name</label>
                            <input type="text" class="form-control" id="edit_shift_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_start_time" class="form-label" data-key="shift.startTime">Start Time</label>
                            <input type="time" class="form-control time-input" id="edit_start_time" name="start_time" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_end_time" class="form-label" data-key="shift.endTime">End Time</label>
                            <input type="time" class="form-control time-input" id="edit_end_time" name="end_time" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_grace_period" class="form-label" data-key="shift.gracePeriod">Grace Period (minutes)</label>
                            <input type="number" class="form-control grace-period-input" id="edit_grace_period" name="grace_period" 
                                   min="0" max="60" required>
                            <small class="text-muted" data-key="shift.gracePeriodHelp">Time allowed for late clock-in without penalty</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-key="shift.cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" data-key="shift.update">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once './footer.php'; ?>

    <script>
        $(document).ready(function () {
            // Initialize DataTable for shifts
            let shiftTable = $('#shiftTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'fetch_shifts.php',
                    dataSrc: function(json) {
                        if (json && json.data && Array.isArray(json.data)) {
                            return json.data;
                        }
                        return [];
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables Ajax error:', error, thrown);
                        Swal.fire('Error', 'Failed to load shift data. Please refresh the page.', 'error');
                        return [];
                    }
                },
                columns: [
                    { data: 'shift_id' },
                    { data: 'name' },
                    { 
                        data: 'start_time', 
                        render: function(data) {
                            // Format time for display (e.g., 14:30:00 -> 2:30 PM)
                            return formatTimeForDisplay(data);
                        }
                    },
                    { 
                        data: 'end_time',
                        render: function(data) {
                            return formatTimeForDisplay(data);
                        }
                    },
                    { data: 'grace_period' },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return `
                                <button class="btn btn-sm btn-primary edit-shift-btn" data-id="${row.shift_id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-shift-btn" data-id="${row.shift_id}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            `;
                        }
                    }
                ],
                order: [[0, 'asc']]  // Sort by ID ascending
            });
            
            // Helper function to format time for display
            function formatTimeForDisplay(timeString) {
                if (!timeString) return '';
                
                const [hours, minutes] = timeString.split(':');
                const hour = parseInt(hours, 10);
                const period = hour >= 12 ? 'PM' : 'AM';
                const displayHour = hour % 12 || 12;
                
                return `${displayHour}:${minutes} ${period}`;
            }

            // Add Shift
            $('#addShiftForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'add_shift.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                          
                            shiftTable.ajax.reload();
                            Swal.fire('Success', response.message, 'success');
                            $('#addShiftForm')[0].reset();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to add shift. Please try again.', 'error');
                    }
                });
            });
            
            // Edit Shift - Load Data
            $(document).on('click', '.edit-shift-btn', function() {
                const shiftId = $(this).data('id');
                
                $.ajax({
                    url: 'get_shift.php',
                    type: 'GET',
                    data: { shift_id: shiftId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const shift = response.data;
                            $('#edit_shift_id').val(shift.shift_id);
                            $('#edit_shift_name').val(shift.name);
                            $('#edit_start_time').val(shift.start_time);
                            $('#edit_end_time').val(shift.end_time);
                            $('#edit_grace_period').val(shift.grace_period);
                            
                            $('#editShiftModal').modal('show');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load shift data. Please try again.', 'error');
                    }
                });
            });
            
            // Update Shift
            $('#editShiftForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'update_shift.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                          
                            shiftTable.ajax.reload();
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to update shift. Please try again.', 'error');
                    }
                });
            });
            
            // Delete Shift
            $(document).on('click', '.delete-shift-btn', function() {
                const shiftId = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will delete this shift template. Any schedules using this shift will be affected.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_shift.php',
                            type: 'POST',
                            data: { shift_id: shiftId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    shiftTable.ajax.reload();
                                    Swal.fire('Deleted', response.message, 'success');
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete shift. Please try again.', 'error');
                            }
                        });
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
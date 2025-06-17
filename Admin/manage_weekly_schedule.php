<?php
session_start();
require_once 'connection.php';

// Check if user is logged in with Boss/Manager privileges
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit;
}

// Check user privilege
$userId = $_SESSION['UserId'];
$privilegeQuery = "SELECT privilege FROM user WHERE UserId = ?";
$stmtPrivilege = $conn->prepare($privilegeQuery);
$stmtPrivilege->bind_param("i", $userId);
$stmtPrivilege->execute();
$privilegeResult = $stmtPrivilege->get_result();

$isAuthorized = false;
if ($privilegeResult && $row = $privilegeResult->fetch_assoc()) {
    $isAuthorized = ($row['privilege'] === 'Boss' || $row['privilege'] === 'Manager');
}
$stmtPrivilege->close();

if (!$isAuthorized) {
    header("Location: index.php?error=insufficient_privileges");
    exit;
}

// Process form submission
$message = '';
$messageType = '';
$sourceWeekStart = date('Y-m-d', strtotime('last week monday'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_timetable'])) {
    // AJAX request will be handled by auto_schedule.php
    $message = "Form submitted. Processing...";
    $messageType = "info";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Weekly Schedule</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        #response-container {
            margin-top: 20px;
        }
        .hidden {
            display: none;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Weekly Schedule</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                Generate Weekly Timetable
            </div>
            <div class="card-body">
                <form id="timetableForm">
                    <div class="form-group">
                        <label for="source_week_start">Source Week Start Date (Optional):</label>
                        <input type="date" class="form-control" id="source_week_start" name="source_week_start" 
                               value="<?php echo $sourceWeekStart; ?>">
                        <small class="form-text text-muted">If not selected, last week will be used as source</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="variation_level">Schedule Variation Level:</label>
                        <select class="form-control" id="variation_level" name="variation_level">
                            <option value="1">Low - Minor changes from source week</option>
                            <option value="2" selected>Medium - Moderate changes</option>
                            <option value="3">High - Significant changes</option>
                        </select>
                    </div>
                    
                    <button type="button" id="saveButton" class="btn btn-primary">
                        <span id="spinner" class="spinner-border hidden" role="status"></span>
                        Save Weekly Timetable
                    </button>
                </form>
            </div>
        </div>
        
        <div id="response-container" class="hidden">
            <div class="card">
                <div class="card-header">
                    Results
                </div>
                <div class="card-body" id="response-content">
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#saveButton').on('click', function() {
                // Show spinner and disable button
                $('#spinner').removeClass('hidden');
                $('#saveButton').prop('disabled', true);
                
                // Get form data
                var formData = {
                    source_week_start: $('#source_week_start').val(),
                    variation_level: $('#variation_level').val()
                };
                
                // Send AJAX request
                $.ajax({
                    type: 'POST',
                    url: 'auto_schedule.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        // Display response
                        var html = '';
                        if (response.status === 'success') {
                            html = '<div class="alert alert-success">' + 
                                   '<h5>Success!</h5>' +
                                   '<p>' + response.message + '</p>' +
                                   '<ul>' +
                                   '<li>Current Week: ' + response.current_week_count + ' schedules created</li>' +
                                   '<li>Next Week: ' + response.next_week_count + ' schedules created</li>' +
                                   '<li>Attendance Records: ' + response.attendance_records_created + ' records created</li>' +
                                   '</ul></div>';
                        } else {
                            html = '<div class="alert alert-danger">' +
                                   '<h5>Error</h5>' +
                                   '<p>' + response.message + '</p></div>';
                        }
                        
                        $('#response-content').html(html);
                        $('#response-container').removeClass('hidden');
                    },
                    error: function(xhr, status, error) {
                        $('#response-content').html(
                            '<div class="alert alert-danger">' +
                            '<h5>Error</h5>' +
                            '<p>An error occurred while processing your request.</p>' +
                            '<p>Details: ' + error + '</p></div>'
                        );
                        $('#response-container').removeClass('hidden');
                    },
                    complete: function() {
                        // Hide spinner and enable button
                        $('#spinner').addClass('hidden');
                        $('#saveButton').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html> 
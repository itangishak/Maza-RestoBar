<?php
session_start();
require_once 'connection.php';
// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Feedback</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <?php include_once './header.php'; ?>
  <style>
    .main-container {
      margin-top: 100px;
      margin-left: 70px;
    }
    .card {
      box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    }
    .sidebar.collapsed {
      width: 70px;
      transition: width 0.3s;
    }
    .main-container.full {
      margin-left: 0 !important;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<?php include_once './navbar.php'; ?>
 <!-- ======= Sidebar ======= -->
 <?php include_once 'sidebar.php'; ?>
<!-- End Sidebar -->

<div class="container main-container">
  <div class="card shadow">
    <div class="card-header">
      <h1>Customer Feedback</h1>
    </div>
    <div class="card-body">
      <table id="feedbackTable" class="table table-bordered display" style="width:100%">
        <thead>
          <tr>
            <th>Feedback ID</th>
            <th>Customer Name</th>
            <th>Rating</th>
            <th>Comments</th>
            <th>Submitted At</th>
            <th>Action</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Include Footer -->
<?php include_once './footer.php'; ?>

<script>
$(document).ready(function () {


        // Sidebar toggling logic
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
            const sidebar = document.querySelector('.sidebar');
            const mainContainer = document.querySelector('.main-container');
            const card = document.querySelector('.card');

            // Adjust card size on page load
            if (sidebar && card) {
                if (!sidebar.classList.contains('collapsed')) {
                    card.style.width = '87%';
                    card.style.marginLeft = '20%';
                } else {
                    card.style.width = '100%';
                    card.style.marginLeft = '5%';
                }
            }

            // Toggle sidebar functionality
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

  let table = $('#feedbackTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'fetch_feedback.php',
      type: 'GET'
    },
    order: [[4, "desc"]],
    columns: [
      { data: 'feedback_id' },
      { data: 'customer_name' },
      { 
        data: 'rating',
        render: function(data) {
          return '⭐'.repeat(data) + ' (' + data + ')'; // Display rating as stars
        }
      },
      { data: 'comments' },
      { data: 'created_at' },
      { 
        data: null,
        orderable: false,
        render: function (data, type, row) {
          return `
            <button class="btn btn-danger btn-sm deleteFeedbackBtn" data-id="${row.feedback_id}">
              Delete
            </button>
          `;
        }
      }
    ],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100]
  });

  // Handle Delete Feedback button
  $('#feedbackTable').on('click', '.deleteFeedbackBtn', function () {
    const feedbackId = $(this).data('id');

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to restore this feedback!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: 'delete_feedback.php',
          type: 'POST',
          data: { feedback_id: feedbackId },
          success: function(response) {
            try {
              const res = JSON.parse(response);
              if (res.status === 'success') {
                Swal.fire('Deleted!', res.message, 'success');
                table.ajax.reload();
              } else {
                Swal.fire('Error', res.message, 'error');
              }
            } catch (e) {
              Swal.fire('Error', 'Invalid response from server.', 'error');
            }
          },
          error: function() {
            Swal.fire('Error', 'Failed to delete feedback.', 'error');
          }
        });
      }
    });
  });
});
</script>

</body>
</html>

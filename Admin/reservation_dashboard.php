<?php
session_start();
require_once 'connection.php';

// Optional debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Example check if the user is logged in:
if (!isset($_SESSION['UserId'])) {
    $_SESSION['UserId'] = 1; // For demo
    $_SESSION['privilege'] = 'Administrateur';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="sidebar.reservationManagement">Reservation Management</title>
    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 100px;
            margin-left: 70px;
            
        }

        .card {
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Adjust styling if sidebar toggling is used */
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
    <!-- Navbar (if desired) -->
    <?php  include_once './navbar.php'; ?>

   <!-- ======= Sidebar ======= -->
 <?php include_once 'sidebar.php'; ?>
<!-- End Sidebar -->

    <div class="container main-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 data-key="sidebar.reservationManagement">Reservation Management</h4>
                <button 
                  class="btn btn-primary btn-sm" 
                  data-bs-toggle="modal" 
                  data-bs-target="#addReservationModal"
                  data-key="reservation.addReservationBtn">
                    + Add Reservation
                </button>
            </div>
            <div class="card-body">
                <table id="reservationTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th data-key="reservation.name">Name</th>
                            <th data-key="reservation.email">Email</th>
                            <th data-key="reservation.phone">Phone</th>
                            <th data-key="reservation.date">Date</th>
                            <th data-key="reservation.time">Time</th>
                            <th data-key="reservation.guests">Guests</th>
                            <th data-key="order.status">Status</th>
                            <th data-key="order.action">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Reservation Modal -->
    <div class="modal fade" id="addReservationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
        <form id="addReservationForm">
            <div class="modal-header">
            <h5 class="modal-title" data-key="reservation.addTitle">Add Reservation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label for="customer_name" data-key="reservation.name">Name</label>
                <input 
                type="text" class="form-control mb-2" 
                id="customer_name" name="customer_name" 
                required
                >

                <label for="email" data-key="reservation.email">Email</label>
                <input 
                type="email" class="form-control mb-2" 
                id="email" name="email" 
                required
                >

                <label for="phone" data-key="reservation.phone">Phone</label>
                <input 
                type="text" class="form-control mb-2" 
                id="phone" name="phone" 
                required
                >

                <label for="reservation_date" data-key="reservation.date">Date</label>
                <input 
                type="date" class="form-control mb-2" 
                id="reservation_date" name="reservation_date" 
                required
                >

                <label for="reservation_time" data-key="reservation.time">Time</label>
                <input 
                type="time" class="form-control mb-2" 
                id="reservation_time" name="reservation_time" 
                required
                >

                <label for="guests" data-key="reservation.guests">Number of Guests</label>
                <input 
                type="number" class="form-control mb-2" 
                id="guests" name="guests" 
                min="1" 
                required
                >

                <!-- Hardcode status as "Pending" -->
                <input 
                type="hidden" 
                name="status" 
                value="Pending"
                >
            </div>
            <div class="modal-footer">
            <button 
                type="button" 
                class="btn btn-secondary" 
                data-bs-dismiss="modal"
                data-key="customer.close"
            >
                Close
            </button>
            <button 
                type="submit" 
                class="btn btn-primary"
                data-key="reservation.addReservationBtn"
            >
                Add Reservation
            </button>
            </div>
        </form>
        </div>
    </div>
    </div>


    <!-- Edit Reservation Modal -->
    <div class="modal fade" id="editReservationModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="editReservationForm">
            <input type="hidden" id="edit_reservation_id" name="reservation_id">
            <div class="modal-header">
              <h5 class="modal-title" data-key="reservation.editReservation">Edit Reservation</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label for="edit_customer_name" data-key="reservation.name">Name</label>
                <input 
                  type="text" 
                  class="form-control mb-2" 
                  id="edit_customer_name" 
                  name="customer_name" 
                  required
                >

                <label for="edit_email" data-key="reservation.email">Email</label>
                <input 
                  type="email" 
                  class="form-control mb-2" 
                  id="edit_email" 
                  name="email" 
                  required
                >

                <label for="edit_phone" data-key="reservation.phone">Phone</label>
                <input 
                  type="text" 
                  class="form-control mb-2" 
                  id="edit_phone" 
                  name="phone" 
                  required
                >

                <label for="edit_date" data-key="reservation.date">Date</label>
                <input 
                  type="date" 
                  class="form-control mb-2" 
                  id="edit_date" 
                  name="reservation_date" 
                  required
                >

                <label for="edit_time" data-key="reservation.time">Time</label>
                <input 
                  type="time" 
                  class="form-control mb-2" 
                  id="edit_time" 
                  name="reservation_time" 
                  required
                >

                <label for="edit_guests" data-key="reservation.guests">Guests</label>
                <input 
                  type="number" 
                  class="form-control mb-2" 
                  id="edit_guests" 
                  name="guests" 
                  min="1" 
                  required
                >

                <label for="edit_status" data-key="order.status">Status</label>
                <select 
                  class="form-control mb-2" 
                  id="edit_status" 
                  name="status"
                >
                    <option value="Pending" data-key="order.pending">Pending</option>
                    <option value="Confirmed" data-key="order.confirmed">Confirmed</option>
                    <option value="Cancelled" data-key="order.canceled">Cancelled</option>
                </select>
            </div>
            <div class="modal-footer">
              <button 
                type="button" 
                class="btn btn-secondary" 
                data-bs-dismiss="modal"
                data-key="customer.close"
              >
                Close
              </button>
              <button 
                type="submit" 
                class="btn btn-primary"
                data-key="category.saveChanges"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <!-- Include Footer -->
  <?php include_once './footer.php'; ?>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        let table = $('#reservationTable').DataTable({
            ajax: 'fetch_reservations.php',
            columns: [
                { data: 'customer_name',     "data-key": "reservation.name" },
                { data: 'email',            "data-key": "reservation.email" },
                { data: 'phone',            "data-key": "reservation.phone" },
                { data: 'reservation_date', "data-key": "reservation.date" },
                { data: 'reservation_time', "data-key": "reservation.time" },
                { data: 'guests',           "data-key": "reservation.guests" },
                { data: 'status',           "data-key": "order.status" },
                {
                    data: null,
                    "data-key": "order.action",
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-primary edit-btn" data-id="${row.reservation_id}" data-key="category.edit">
                                ${getTranslation('category.edit')}
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${row.reservation_id}" data-key="category.delete">
                                ${getTranslation('category.delete')}
                            </button>
                        `;
                    }
                }
            ]
        });

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

        // ========= ADD Reservation =========
        $('#addReservationForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'add_reservation.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                        table.ajax.reload();
                        $('#addReservationForm')[0].reset();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire('Error', 'Failed to add reservation.', 'error');
                }
            });
        });

        // ========= EDIT Reservation (open modal) =========
        $('#reservationTable').on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            $.get(`fetch_reservation_by_id.php?id=${id}`, function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    // Populate form
                    $('#edit_reservation_id').val(data.reservation_id);
                    $('#edit_customer_name').val(data.customer_name);
                    $('#edit_email').val(data.email);
                    $('#edit_phone').val(data.phone);
                    $('#edit_date').val(data.reservation_date);
                    $('#edit_time').val(data.reservation_time);
                    $('#edit_guests').val(data.guests);
                    $('#edit_status').val(data.status);
                    $('#editReservationModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }, 'json');
        });

        // ========= UPDATE Reservation =========
        $('#editReservationForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'update_reservation.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                        table.ajax.reload();
                      
                        $('#editReservationForm')[0].reset();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire('Error', 'Failed to update reservation.', 'error');
                }
            });
        });

        // ========= DELETE Reservation =========
        $('#reservationTable').on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_reservation.php', { id }, function(response) {
                        Swal.fire(response.status, response.message, response.status);
                        table.ajax.reload();
                    }, 'json');
                }
            });
        });

        // Translation Logic
        const systemLang = navigator.language || navigator.userLanguage;
        let defaultLang = 'en';
        if (systemLang.startsWith('fr')) {
            defaultLang = 'fr';
        }
        let currentLang = localStorage.getItem('userLang') || defaultLang;
        loadTranslations(currentLang);
        
        function loadTranslations(lang) {
            fetch('../languages/' + lang + '.json')
                .then(response => response.json())
                .then(translations => {
                    localStorage.setItem('translations', JSON.stringify(translations));
                    translatePage(translations);
                })
                .catch(error => console.error('Error loading translations:', error));
        }
        
        function translatePage(translations) {
            document.querySelectorAll('[data-key]').forEach(element => {
                const key = element.getAttribute('data-key');
                if (translations[key]) {
                    element.textContent = translations[key];
                }
            });
            table.draw(); // Redraw table to reflect translations
        }
        
        function getTranslation(key) {
            const translations = JSON.parse(localStorage.getItem('translations')) || {};
            return translations[key] || key;
        }
    });
    </script>
</body>
</html>
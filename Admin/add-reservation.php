<?php
session_start();
require_once 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title data-key="reservation.addTitle">Add Reservation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php include_once './header.php'; ?>
    <style>
        .main-container {
            margin-top: 80px;
            margin-left: 70px;
        }
        .card {
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 500px;
            margin: auto;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php include_once './navbar.php'; ?>
<!-- Sidebar -->
<?php include_once './sidebar.php'; ?>

<div class="container main-container">
    <!-- Add Reservation Form -->
    <div class="card">
        <h4 class="card-title text-center" data-key="reservation.addReservation">Add Reservation</h4>
        <div id="alertMessage"></div> <!-- Placeholder for feedback messages -->
        <form id="addReservationForm">
            <label for="customer_name" data-key="reservation.name">Name</label>
            <input type="text" class="form-control mb-2" id="customer_name" name="customer_name" required>

            <label for="email" data-key="reservation.email">Email</label>
            <input type="email" class="form-control mb-2" id="email" name="email" required>

            <label for="phone" data-key="reservation.phone">Phone</label>
            <input type="text" class="form-control mb-2" id="phone" name="phone" required>

            <label for="reservation_date" data-key="reservation.date">Date</label>
            <input type="date" class="form-control mb-2" id="reservation_date" name="reservation_date" required>

            <label for="reservation_time" data-key="reservation.time">Time</label>
            <input type="time" class="form-control mb-2" id="reservation_time" name="reservation_time" required>

            <label for="guests" data-key="reservation.guests">Number of Guests</label>
            <input type="number" class="form-control mb-2" id="guests" name="guests" min="1" required>

            <button type="submit" class="btn btn-primary mt-3 w-100" data-key="reservation.addReservationBtn">Add Reservation</button>
        </form>
    </div>
</div>

<!-- Footer -->
<?php include_once './footer.php'; ?>

<script>
$(document).ready(function () {
    const lang = localStorage.getItem('userLang') || 'en';

    // Handle Form Submission
    $("#addReservationForm").submit(function (e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&lang=' + encodeURIComponent(lang); // Add language to form data

        $.ajax({
            url: 'add_reservation.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire(getTranslation('reservation.success'), response.message, 'success').then(() => {
                        $('#addReservationForm')[0].reset();
                    });
                } else {
                    Swal.fire(getTranslation('reservation.error'), response.message, 'error');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                Swal.fire(getTranslation('reservation.error'), getTranslation('reservation.failedAddReservation'), 'error');
            }
        });
    });

    // Language Switching Logic
    const systemLang = navigator.language || navigator.userLanguage;
    let defaultLang = 'en';
    if (systemLang.startsWith('fr')) {
        defaultLang = 'fr';
    }

    let currentLang = localStorage.getItem('userLang') || defaultLang;

    loadTranslations(currentLang);

    function loadTranslations(lang) {
        fetch('../languages/' + lang + '.json')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load language file: ' + response.statusText);
                }
                return response.json();
            })
            .then(translations => {
                localStorage.setItem('translations', JSON.stringify(translations));
                translatePage(translations);
            })
            .catch(error => {
                console.error('Error loading translations:', error);
                // Fallback to English
                fetch('../languages/en.json')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Fallback to English failed: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(translations => {
                        localStorage.setItem('translations', JSON.stringify(translations));
                        translatePage(translations);
                    })
                    .catch(fallbackError => console.error('Fallback error:', fallbackError));
            });
    }

    function translatePage(translations) {
        document.querySelectorAll('[data-key]').forEach(element => {
            const key = element.getAttribute('data-key');
            if (translations[key]) {
                if (element.tagName === 'INPUT') {
                    element.value = translations[key]; // Use value for input fields
                } else if (element.tagName === 'BUTTON') {
                    element.textContent = translations[key]; // Use textContent for button text
                } else {
                    element.textContent = translations[key]; // Use textContent for other elements
                }
            }
        });
    }

    function getTranslation(key) {
        const translations = JSON.parse(localStorage.getItem('translations')) || {};
        return translations[key] || (currentLang === 'en' ? 'Default English Text' : 'Texte par défaut en français');
    }

    function switchLanguage(lang) {
        localStorage.setItem('userLang', lang);
        loadTranslations(lang);
    }
});
</script>

</body>
</html>
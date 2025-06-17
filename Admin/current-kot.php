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
    <title data-key="kot.title">Current Kitchen Orders (KOT)</title>

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
            <h1 data-key="kot.currentOrders">Current Kitchen Orders (KOT)</h1>
        </div>
        <div class="card-body">
            <table id="kotTable" class="table table-bordered display" style="width:100%">
                <thead>
                    <tr>
                        <th data-key="kot.kotId">KOT ID</th>
                        <th data-key="kot.orderId">Order ID</th>
                        <th data-key="kot.createdAt">Created At</th>
                        <th data-key="kot.status">Status</th>
                        <th data-key="kot.action">Action</th> <!-- Update status -->
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
    // Initialize DataTable
    const kotTable = $('#kotTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'fetch_current_kot.php',
            type: 'GET'
        },
        order: [[2, "desc"]], // Order by created_at descending
        columns: [
            { data: 'kot_id' },
            { data: 'order_id' },
            { data: 'created_at' },
            { data: 'kot_status' },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary updateKOTBtn" data-id="${row.kot_id}" data-status="${row.kot_status}">
                            ${getTranslation('kot.updateStatus')}
                        </button>
                    `;
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50]
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

    // Handle KOT status update
    $('#kotTable').on('click', '.updateKOTBtn', function() {
        const kotId = $(this).data('id');
        const currentStatus = $(this).data('status');

        Swal.fire({
            title: getTranslation('kot.updateStatusTitle'),
            input: 'select',
            inputOptions: {
                'pending': getTranslation('kot.pending'),
                'cooking': getTranslation('kot.cooking'),
                'ready': getTranslation('kot.ready'),
                'delivered': getTranslation('kot.delivered'),
                'canceled': getTranslation('kot.canceled')
            },
            inputValue: currentStatus,
            showCancelButton: true,
            confirmButtonText: getTranslation('kot.update')
        }).then((result) => {
            if (result.isConfirmed) {
                const newStatus = result.value;
                if (!newStatus) return;

                const lang = localStorage.getItem('userLang') || 'en';
                $.ajax({
                    url: 'update_kot.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { kot_id: kotId, kot_status: newStatus, lang: lang }, // Pass language
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire(getTranslation('kot.success'), response.message, 'success');
                            $('#kotTable').DataTable().ajax.reload();
                        } else {
                            Swal.fire(getTranslation('kot.error'), response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire(getTranslation('kot.error'), getTranslation('kot.failedUpdate'), 'error');
                    }
                });
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
      <!-- Your jQuery logic for sidebars, etc. -->
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
                    card.style.marginLeft = '2%';
                    card.style.marginTop= '5%';
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
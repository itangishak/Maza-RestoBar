<?php
session_start();

// Define session timeout duration (in seconds)
define('SESSION_TIMEOUT', 600); // 600 seconds = 10 minutes

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
    header("Location: login.php");
    exit();
}

// Check for session timeout
if (isset($_SESSION['last_activity'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header("Location: prifil/signout.php?timeout=1");
        exit();
    }
} else {
    // Just in case last_activity is not set
    $_SESSION['last_activity'] = time();
}

// Update last activity time stamp
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php
    session_start();
    require_once './connection.php';
    include_once 'header.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <!-- ======= Header ======= -->
    <?php
    include_once './navbar.php';
    ?>
    <!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <?php

    if($_SESSION['privilege']=='Manager'){
        include_once './sidebarmanager.php';
    }
    else if($_SESSION['privilege']=='Boss'){
        include_once './sidebarboss.php';
    }
    else{
        include_once './sidebaruser.php';
    }
    
    ?>
    <!-- End Sidebar -->

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Tableau de Bord</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Accueil</a></li>
                    <li class="breadcrumb-item active">Tableau de Bord</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
          
        </section>
    </main><!-- Fin #main -->

    <!-- ======= Footer ======= -->
    <?php
    include_once './footer.php';
    ?>

   
    <script>
        
        <!-- Session Timeout Warning and Auto-Logout -->
    
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Redirect to logout page when screen is off or tab is inactive
                window.location.href = 'profil/signout.php?timeout=1';
            }
        });

        // Session timeout warning and auto-logout
        (function() {
            let sessionTimeout = <?php echo SESSION_TIMEOUT * 1000; ?>; // Convert to milliseconds
            let warningTime = 60000; // Warn 1 minute before session expires
            let warningTimer, logoutTimer;

            function startTimers() {
                clearTimeout(warningTimer);
                clearTimeout(logoutTimer);

                warningTimer = setTimeout(function() {
                    alert('Your session will expire in 1 minute due to inactivity.');
                }, sessionTimeout - warningTime);

                logoutTimer = setTimeout(function() {
                    window.location.href = 'profil/signout.php?timeout=1';
                }, sessionTimeout);
            }

            function resetTimers() {
                startTimers();
            }

            // Reset timers on any user interaction
            document.addEventListener('mousemove', resetTimers);
            document.addEventListener('keydown', resetTimers);
            document.addEventListener('click', resetTimers);
            document.addEventListener('scroll', resetTimers);

            startTimers();
        })();
    
    </script>
</body>

</html>

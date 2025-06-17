<?php
// footer.php

// Start session if not started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$customerDisplay = "";

// 1) If the user is logged in via session
if (isset($_SESSION['customer_id'])) {
    // Check if we already have $_SESSION['customer_name']
    if (!empty($_SESSION['customer_name'])) {
        // Use it
        $customerDisplay = htmlspecialchars($_SESSION['customer_name']);
    } else {
        // If session name not set, fetch from DB using their ID
        require_once './Admin/connection.php';
        $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS name 
                                FROM customers 
                                WHERE customer_id = ?");
        $stmt->bind_param("i", $_SESSION['customer_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $customerDisplay = htmlspecialchars($row['name']);
            // Store in session for next time
            $_SESSION['customer_name'] = $customerDisplay;
        } else {
            $customerDisplay = "Customer";
        }
        $stmt->close();
        $conn->close();
    }

    // 2) Also store/update the name in a cookie for 30 days
    setcookie("customer_name", $customerDisplay, time() + (30 * 24 * 60 * 60), "/");

} else {
    // 3) If no session user, check if we have a "customer_name" cookie
    if (isset($_COOKIE['customer_name'])) {
        $customerDisplay = htmlspecialchars($_COOKIE['customer_name']);
    }
}

?>

<div class="container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container py-5">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4 col-md-6">
                <h4 class="section-title ff-secondary text-start text-primary fw-normal" data-key="contact">Contact</h4>
                <p class="mb-2">
                    <i class="fa fa-map-marker-alt me-3"></i>
                    <span data-key="address">Boulevard Melchior Ndadaye, Peace Corner, Bujumbura</span>
                </p>
                <p class="mb-2">
                    <i class="fa fa-phone-alt me-3"></i>+257 69 80 58 98
                </p>
                <p class="mb-2">
                    <i class="fa fa-envelope me-3"></i>barmazaresto@gmail.com
                </p>
                <div class="d-flex pt-2">
                    <a class="btn btn-outline-light btn-social" href="https://www.instagram.com/mazaresto_">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a class="btn btn-outline-light btn-social" href="https://www.facebook.com/">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </div>
            </div>
            <!-- Opening Hours -->
            <div class="col-lg-4 col-md-6">
                <h4 class="section-title ff-secondary text-start text-primary fw-normal" data-key="opening">Opening</h4>
                <h5 class="text-light fw-normal" data-key="monday-saturday">Monday - Saturday</h5>
                <p>06AM - 11PM</p>
                <h5 class="text-light fw-normal" data-key="sunday">Sunday</h5>
                <p>06AM - 05AM</p>
            </div>
            <!-- Newsletter -->
            <div class="col-lg-4 col-md-6">
                <h4 class="section-title ff-secondary text-start text-primary fw-normal" data-key="newsletter">Newsletter</h4>
                <p data-key="newsletter-description">Subscribe to our newsletter and never miss our latest updates, offers, and news.</p>
                <div class="position-relative mx-auto" style="max-width: 400px;">
                    <input class="form-control border-primary w-100 py-3 ps-4 pe-5" type="text" placeholder="Your email">
                    <button type="button" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2" data-key="signup">SignUp</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Bottom -->
    <div class="container">
    <div class="copyright">
        <div class="row justify-content-center">
            <div class="col-md-12 text-center">
                <div class="footer-menu d-flex justify-content-center align-items-center gap-3">
                    <a href="#" data-key="privacy-policy">Privacy Policy</a>
                    <a href="#" data-key="terms-condition">Terms & Condition</a>
                    <a href="./Admin/login.php" data-key="manage">Manage</a>
                    <?php
                    // If a customer name is available, show a welcome message; otherwise, show Login link
                    if ($customerDisplay !== "") {
                        // e.g. "Welcome, John Doe"
                        echo '<span class="text-white ms-2" data-key="welcome">Welcome, ' . htmlspecialchars($customerDisplay) . '</span>';
                    } else {
                        // Show login link
                        echo '<a href="#" data-key="login">Login</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

  <!-- Back to Top -->
  <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top">
    <i class="bi bi-arrow-up"></i>
  </a>
</div>
<!-- JavaScript Libraries -->
<!-- jQuery now in header -->
<script src="./Client/js/main.js"></script>
<script src="Client/lib/bootstrap/bootstrap.bundle.min.js"></script>
<script src="./Client/lib/wow/wow.min.js"></script>
<!-- Other Libraries -->
<script src="./Client/lib/easing/easing.min.js"></script>
<script src="./Client/lib/waypoints/waypoints.min.js"></script>
<script src="./Client/lib/counterup/counterup.min.js"></script>
<script src="./Client/lib/owlcarousel/owl.carousel.min.js"></script>
<!-- Tempus Dominus -->
<script src="./Client/lib/moment/moment.min.js"></script>
<script src="./Client/lib/moment/moment-timezone.min.js"></script>
<script src="./Client/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- SweetAlert2 (for pop-up messages) -->
<script src="Client/lib/sweetalert2/sweetalert2.min.js"></script>

<!-- Lightbox JS -->
<script src="Client/lib/lightbox/lightbox.min.js"></script>

<!-- Bootstrap JS -->
<script 
    src="Client/lib/bootstrap/bootstrap.bundle.min.js">
</script>


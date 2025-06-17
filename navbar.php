<?php
// Get the current page filename (e.g., "index.php", "menu.php")
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="container-xxl position-relative p-0">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 px-lg-5 py-3 py-lg-0">
        <a href="#" class="navbar-brand p-0">
            <h1 class="text-primary m-0">
                <i class="fa fa-utensils me-3"></i>
                <img class="img-fluid" src="./Client/img/LogoMaza.png" alt="Maza Resto-Bar" style="height: 50px;">
            </h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarCollapse">
            <span class="fa fa-bars"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-0 pe-4">
                <a href="index.php" class="nav-item nav-link <?php echo $currentPage === './index.php' ? 'active' : ''; ?>" data-key="home">Home</a>
                <a href="./Client/menu.php" class="nav-item nav-link <?php echo $currentPage === './Client/menu.php' ? 'active' : ''; ?>" data-key="menu">Menu</a>
                <a href="./Client/gallery.php" class="nav-item nav-link <?php echo $currentPage === './Client/gallery.php' ? 'active' : ''; ?>" data-key="gallery">Gallery</a>
                <a href="./Client/reservation.php" class="nav-item nav-link <?php echo $currentPage === './Client/reservation.php' ? 'active' : ''; ?>" data-key="reservation">Reservation</a>
                <a href="./Client/contact.php" class="nav-item nav-link <?php echo $currentPage === './Client/contact.php' ? 'active' : ''; ?>" data-key="contact">Contact</a>
            </div>
            <div class="nav-item position-relative">
                <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#cartModal">
                    <i class="fa fa-shopping-cart fa-2x text-white"></i>
                    <span class="cart-count" id="cart-count">0</span>
                </a>
            </div>
            <!-- Language Switcher Dropdown -->
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="languageDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-globe"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                    <li><a class="dropdown-item language-option" href="#" data-lang="en">
                        <img src="./Client/img/english.png" alt="English" style="width: 20px;"> English
                    </a></li>
                    <li><a class="dropdown-item language-option" href="#" data-lang="fr">
                        <img src="./Client/img/french.png" alt="Français" style="width: 20px;"> Français
                    </a></li>
                </ul>
            </div>
        </div>
    </nav>
</div>
<?php
session_start();
require_once './Admin/connection.php';

/************************************
 * 1) FETCH TOP 5 POPULAR ITEMS
 *    (From order_details + menu_items)
 ************************************/
$sqlPopular = "
SELECT mi.menu_id, mi.name, mi.price, mi.description, mi.image,
       COALESCE(SUM(od.quantity), 0) AS total_orders
FROM menu_items mi
LEFT JOIN order_details od ON mi.menu_id = od.menu_id
WHERE mi.availability = 'available'
GROUP BY mi.menu_id
ORDER BY total_orders DESC
LIMIT 5
";
$resultPopular = $conn->query($sqlPopular);
$popularItems = [];
while ($row = $resultPopular->fetch_assoc()) {
    $popularItems[] = $row;
}

/************************************
 * 2) FETCH CATEGORIES
 *    Then fetch items for each category
 ************************************/
$sqlCategories = "SELECT category_id, name, description FROM menu_categories ORDER BY category_id ASC";
$resCategories = $conn->query($sqlCategories);
$categories = [];
while ($row = $resCategories->fetch_assoc()) {
    $categories[] = $row;
}

// For each category, fetch items that are "available"
$itemsByCategory = [];
foreach ($categories as $cat) {
    $catId = $cat['category_id'];
    $sqlItems = "
        SELECT menu_id, name, price, description, image
        FROM menu_items
        WHERE availability = 'available'
          AND category_id = $catId
        ORDER BY menu_id ASC
    ";
    $resItems = $conn->query($sqlItems);
    $menuItems = [];
    while ($itm = $resItems->fetch_assoc()) {
        $menuItems[] = $itm;
    }
    $itemsByCategory[$catId] = $menuItems;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Maza Resto-Bar</title>
    <meta content="Maza Resto-Bar" name="keywords">
    <meta content="Welcome to Maza Resto-Bar" name="description">
    <!-- Content Security Policy -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; img-src 'self' data:; frame-src 'self' https://www.google.com; connect-src 'self';">

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
        .wow {
            visibility: visible !important;
        }
        /* Cart icon, etc., from your code if needed */
        .cart-count {
            position: absolute;
            top: 8px;
            right: 8px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
    </style>
</head>

<body>
<div class="container-xxl bg-white p-0">
    <!-- =====================================
         NAVBAR & HERO
         (You can adapt from your previous code)
    ====================================== -->
    <div class="container-xxl position-relative p-0">
       <!-- Navbar (unchanged look) -->
    <?php include_once './navbar.php'; ?>
        <!-- Hero Section Start -->
        <div class="container-xxl py-5 bg-dark hero-header mb-5">
            <div class="container my-5 py-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 text-center text-lg-start">
                        <h1 class="display-3 text-white animated slideInLeft" data-key="hero-title">
                            Enjoy Our<br>Delicious Meal
                        </h1>
                        <p class="text-white animated slideInLeft mb-4 pb-2" data-key="hero-description">
                        Experience the perfect blend of flavors, crafted with fresh ingredients and a touch of culinary excellence.
                        </p>
                        <a href="#" class="btn btn-primary py-sm-3 px-sm-5 me-3 animated slideInLeft" data-key="book-table">
                            Book A Table
                        </a>
                    </div>
                    <div class="col-lg-6 text-center text-lg-end overflow-hidden">
                        <img class="img-fluid" src="./Client/img/maze8.png" alt="Delicious Meal">
                    </div>
                </div>
            </div>
        </div>
        <!-- Hero Section End -->
    </div>

    <!-- MOST POPULAR ITEMS START -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h5 class="section-title ff-secondary text-center text-primary fw-normal" data-key="most-popular-items">
                    Most Popular Items
                </h5>
                <h1 class="mb-5" data-key="top-5-best-sellers">Top 5 Best Sellers</h1>
            </div>
            <div class="row g-4">
                <?php foreach ($popularItems as $pitem): ?>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="d-flex align-items-center border p-3 rounded">
                        <img class="flex-shrink-0 img-fluid rounded" 
                             src="<?php echo htmlspecialchars($pitem['image'] ?? 'img/no-image.jpg'); ?>" 
                             alt="" style="width: 80px; height:80px; object-fit: cover;">
                        <div class="w-100 d-flex flex-column text-start ps-4">
                            <h5 class="d-flex justify-content-between border-bottom pb-2">
                                <span><?php echo htmlspecialchars($pitem['name']); ?></span>
                                <span class="text-primary">
                                    <?php echo number_format($pitem['price'], 0) . " BIF"; ?>
                                </span>
                            </h5>
                            <small class="fst-italic">
                                <?php echo htmlspecialchars($pitem['description'] ?? ''); ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- MOST POPULAR ITEMS END -->

    <!-- MENU CATEGORIES START -->
    <div class="container-xxl py-5">
      <div class="container">
        <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h5 class="section-title ff-secondary text-center text-primary fw-normal" data-key="food-menu">
                Food Menu
            </h5>
            <h1 class="mb-5" data-key="our-delicious-offerings">Our Delicious Offerings</h1>
        </div>
        <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
          <!-- Category Tabs -->
          <ul class="nav nav-pills d-inline-flex justify-content-center border-bottom mb-5">
            <?php
            $firstActive = true;
            foreach ($categories as $cat):
                $catId   = $cat['category_id'];
                $catName = $cat['name']; 
                $active  = $firstActive ? 'active' : '';
                $firstActive = false;
            ?>
            <li class="nav-item">
              <a class="d-flex align-items-center text-start mx-3 pb-3 <?php echo $active; ?>" 
                 data-bs-toggle="pill" 
                 href="#tab-<?php echo $catId; ?>">
                  <i class="fa fa-utensils fa-2x text-primary"></i>
                  <div class="ps-3">
                      <small class="text-body" data-key="category-label">Category</small>
                      <h6 class="mt-n1 mb-0">
                        <?php echo htmlspecialchars($catName); ?>
                      </h6>
                  </div>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>

          <div class="tab-content">
            <?php
            $firstActive = true;
            foreach ($categories as $cat):
                $catId = $cat['category_id'];
                $items = $itemsByCategory[$catId] ?? [];
                $tabActive = $firstActive ? 'active' : '';
                $firstActive = false;
            ?>
            <div id="tab-<?php echo $catId; ?>" class="tab-pane fade show p-0 <?php echo $tabActive; ?>">
              <div class="row g-4">
                <?php foreach ($items as $itm): ?>
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.2s">
                  <div class="d-flex align-items-center border p-3 rounded">
                    <img class="flex-shrink-0 img-fluid rounded" 
                         src="<?php echo htmlspecialchars($itm['image'] ?? 'img/no-image.jpg'); ?>" 
                         alt="" style="width: 80px; height:80px; object-fit: cover;">
                    <div class="w-100 d-flex flex-column text-start ps-4">
                      <h5 class="d-flex justify-content-between border-bottom pb-2">
                        <span><?php echo htmlspecialchars($itm['name']); ?></span>
                        <span class="text-primary">
                          <?php echo number_format($itm['price'], 0) . " BIF"; ?>
                        </span>
                      </h5>
                      <small class="fst-italic"><?php echo htmlspecialchars($itm['description'] ?? ''); ?></small>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div> <!-- /.tab-content -->
        </div> <!-- /.tab-class -->
      </div> <!-- /.container -->
    </div>
    <!-- MENU CATEGORIES END -->

      <!-- Footer -->
  <?php include_once './footer.php'; ?>
<!-- Language Switching Script -->
<script>
var translations = {
  'en': {
    'home': 'Home',
    'menu': 'Menu',
    'order': 'Order',
    'gallery': 'Gallery',
    'reservation': 'Reservation',
    'contact': 'Contact',
    'hero-title': 'Enjoy Our<br>Delicious Meal',
    'hero-description': 'Experience the perfect blend of flavors, crafted with fresh ingredients and a touch of culinary excellence.',
    'book-table': 'Book A Table',
    'most-popular-items': 'Most Popular Items',
    'top-5-best-sellers': 'Top 5 Best Sellers',
    'food-menu': 'Food Menu',
    'our-delicious-offerings': 'Our Delicious Offerings',
    'category-label': 'Category',
    'opening': 'Opening',
    'monday-saturday': 'Monday - Saturday',
    'sunday': 'Sunday',
    'welcome': 'Welcome,',
    'newsletter': 'Newsletter',
    'newsletter-description': 'Subscribe to our newsletter and never miss our latest updates, offers, and news.',
    'signup': 'SignUp',
    'privacy-policy': 'Privacy Policy',
    'terms-condition': 'Terms & Condition',
    'login': 'Login',
    'manage': 'Manage',
    'address': 'Boulevard Melchior Ndadaye, Peace Corner, Bujumbura'
  },
  'fr': {
    'home': 'Accueil',
    'menu': 'Menu',
    'order': 'Commander',
    'gallery': 'Gallery',
    'reservation': 'Réservation',
    'contact': 'Contact',
    'welcome': 'Bienvenue,',
    'hero-title': 'Savourez Nos<br>Délicieux Repas',
    'hero-description': 'Découvrez un mélange parfait de saveurs, préparé avec des ingrédients frais et une touche d\'excellence culinaire',
    'book-table': 'Réservez une table',
    'most-popular-items': 'Articles les plus populaires',
    'top-5-best-sellers': 'Top 5 Meilleures Ventes',
    'food-menu': 'Menu',
    'our-delicious-offerings': 'Nos Offres Délicieuses',
    'category-label': 'Catégorie',
    'opening': 'Heures d\'ouverture',
    'monday-saturday': 'Lundi - Samedi',
    'sunday': 'Dimanche',
    'newsletter': 'Bulletin',
    'newsletter-description': 'Abonnez-vous à notre newsletter et ne manquez jamais nos dernières actualités, offres et nouveautés.',
    'signup': 'S\'inscrire',
    'privacy-policy': 'Politique de Confidentialité',
    'terms-condition': 'Termes & Conditions',
    'login': 'Connexion',
    'manage':'Administre',
    'address': 'Boulevard Melchior Ndadaye, Peace Corner, Bujumbura'
  }
};

var currentLang = 'en';
document.querySelectorAll('.language-option').forEach(function (option) {
  option.addEventListener('click', function (event) {
    event.preventDefault();
    var selectedLang = this.getAttribute('data-lang');
    setLanguage(selectedLang);
  });
});

// Function to set language
function setLanguage(lang) {
  currentLang = lang;
  var elements = document.querySelectorAll('[data-key]');
  elements.forEach(function (element) {
    var key = element.getAttribute('data-key');
    if (translations[lang] && translations[lang][key]) {
      element.innerHTML = translations[lang][key];
    }
  });
  localStorage.setItem('selectedLanguage', lang);
}

// Load saved language on page load
window.addEventListener('DOMContentLoaded', function () {
  var savedLang = localStorage.getItem('selectedLanguage') || currentLang;
  setLanguage(savedLang);
});
</script>


</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <?php
    session_start();
    require_once './connection.php';
    // Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  header("Location: login.php");
  exit();
}
    include_once 'header.php';
  ?>
  <meta charset="UTF-8">
  <title data-key="profile.title">Profile</title>
</head>

<body>

  <!-- ======= Header ======= -->
  <?php include_once './navbar.php'; ?>
  <!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <?php include_once 'sidebar.php'; ?>
  <!-- End Sidebar -->

  <?php
    // Typically you'd have: $userId = $_SESSION['UserId']; or similar
    // For demo, you might do $userId = 1 or something:
    // $userId = $_SESSION['UserId'] ?? 1;

    // Fetch user info
    $sql = "SELECT firstname, lastname, email, privilege, image FROM user WHERE UserId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $user = $result->fetch_assoc();
      // Assign user data
      $firstname = $user['firstname'];
      $lastname  = $user['lastname'];
      $email     = $user['email'];
      $privilege = $user['privilege'];
      $profileImage = !empty($user['image']) ? $user['image'] : './assets/img/default-profile.jpg';
    } else {
      // Default if user not found
      $firstname     = 'Unknown';
      $lastname      = '';
      $email         = 'noemail@example.com';
      $privilege     = 'Guest';
      $profileImage  = './assets/img/default-profile.jpg';
    }
    $stmt->close();
  ?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1 data-key="profile.title">Profile</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="dashboard.php" data-key="profile.dashboard">Dashboard</a>
          </li>
          <li class="breadcrumb-item" data-key="profile.user">User</li>
          <li class="breadcrumb-item active" data-key="profile.title">Profile</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section profile">
      <div class="row">
        <!-- Left Column: Profile Card -->
        <div class="col-xl-4">
          <div class="card">
            <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
              <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="rounded-circle">
              <h2><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></h2>
              <h3><?php echo htmlspecialchars($privilege); ?></h3>
            </div>
          </div>
        </div>

        <!-- Right Column: Tabs -->
        <div class="col-xl-8">
          <div class="card">
            <div class="card-body pt-3">

              <!-- Bordered Tabs -->
              <ul class="nav nav-tabs nav-tabs-bordered">
                <li class="nav-item">
                  <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-edit" data-key="profile.settings">
                    Settings
                  </button>
                </li>
                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password" data-key="profile.changePassword">
                    Change Password
                  </button>
                </li>
                <!-- New Tab for Language -->
                <li class="nav-item">
                  <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-language" data-key="profile.languageTab">
                    Language
                  </button>
                </li>
              </ul>

              <div class="tab-content pt-2">

                <!-- 1) Profile Edit Form -->
                <div class="tab-pane fade show active profile-edit pt-3" id="profile-edit">
                  <form id="settings">
                    <div class="input-group" id="success" style="display: none;">
                      <p data-key="profile.successModification">The modification was successfully made.</p>
                    </div>
                    <div class="input-group" id="error" style="display: none;">
                      <p data-key="profile.errorDatabase"></p>
                    </div>

                    <div class="row mb-3">
                      <label for="profileImage" class="col-md-4 col-lg-3 col-form-label" data-key="profile.profileImage">
                        Profile Image
                      </label>
                      <div class="col-md-8 col-lg-9">
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile">
                        <div class="pt-2">
                          <?php if (isset($_GET['success']) && $_GET['success'] == 'removed'): ?>
                            <div class="alert alert-success" role="alert">
                              <p data-key="profile.successImageRemoved">Profile image was successfully removed.</p>
                            </div>
                          <?php endif; ?>

                          <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                              <?php
                                if ($_GET['error'] == 'remove_failed') {
                                  echo '<p data-key="profile.errorImageRemove">Error removing profile image.</p>';
                                } elseif ($_GET['error'] == 'database') {
                                  echo '<p data-key="profile.errorDatabase">Database update error.</p>';
                                } elseif ($_GET['error'] == 'no_image') {
                                  echo '<p data-key="profile.errorNoImage">No profile image found.</p>';
                                }
                              ?>
                            </div>
                          <?php endif; ?>

                          <a href="./profil/upload-profile-image.php" class="btn btn-primary btn-sm" title="Upload new profile image">
                            <i class="bi bi-upload"></i>
                          </a>
                          <a href="./profil/remove-profile-image.php" class="btn btn-danger btn-sm" title="Remove my profile image">
                            <i class="bi bi-trash"></i>
                          </a>
                        </div>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="firstname" class="col-md-4 col-lg-3 col-form-label" data-key="profile.firstName">
                        First Name
                      </label>
                      <div class="col-md-8 col-lg-9">
                        <input name="firstname" type="text" class="form-control" id="firstname"
                               value="<?php echo htmlspecialchars($firstname); ?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="lastname" class="col-md-4 col-lg-3 col-form-label" data-key="profile.lastName">
                        Last Name
                      </label>
                      <div class="col-md-8 col-lg-9">
                        <input name="lastname" type="text" class="form-control" id="lastname"
                               value="<?php echo htmlspecialchars($lastname); ?>">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="email" class="col-md-4 col-lg-3 col-form-label" data-key="profile.email">
                        Email
                      </label>
                      <div class="col-md-8 col-lg-9">
                        <input name="email" type="email" class="form-control" id="email"
                               value="<?php echo htmlspecialchars($email); ?>">
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary" id="submit" data-key="profile.save">Save</button>
                    </div>
                  </form>
                </div><!-- End Profile Edit -->

                <!-- 2) Change Password Form -->
                <div class="tab-pane fade pt-3" id="profile-change-password">
                  <form id="change-password-form">
                    <div class="input-group" id="success1" style="display: none;">
                      <p data-key="profile.successModification">Password successfully changed.</p>
                    </div>
                    <div class="input-group" id="error1" style="display: none;">
                      <p data-key="profile.errorPasswordChange">Failed to change the password. Please try again.</p>
                    </div>

                    <div class="row mb-3">
                      <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label" data-key="profile.currentPassword">
                        Current Password
                      </label>
                      <div class="col-md-8 col-lg-9">
                        <input name="currentPassword" type="password" class="form-control" id="currentPassword">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <label for="newPassword" class="col-md-4 col-lg-3 col-form-label" data-key="profile.newPassword">
                        New Password
                      </label>
                      <div class="col-md-8 col-lg-9">
                        <input name="newPassword" type="password" class="form-control" id="newPassword">
                      </div>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="btn btn-primary" data-key="profile.change">Change</button>
                    </div>
                  </form>
                </div><!-- End Change Password -->

                <!-- 3) Language Tab -->
                <div class="tab-pane fade pt-3" id="profile-language">
                  <div class="row mb-3">
                    <label for="languageSelect" class="col-md-4 col-lg-3 col-form-label" data-key="profile.language">
                      Language
                    </label>
                    <div class="col-md-8 col-lg-9">
                      <select class="form-select" id="languageSelect" onchange="switchLanguage(this.value)">
                        <!-- We'll set default from localStorage in JS below -->
                        <option value="en" data-key="profile.englishOption">English</option>
                        <option value="fr" data-key="profile.frenchOption">Français</option>
                      </select>
                    </div>
                  </div>
                  <!-- Info text if you want -->
                  <p class="small text-muted">
                    <span data-key="profile.languageInfo">Select your preferred interface language.</span>
                  </p>
                </div><!-- End Language Tab -->

              </div><!-- End tab-content -->
            </div><!-- End card-body -->
          </div><!-- End card -->
        </div><!-- End col-xl-8 -->
      </div><!-- End row -->
    </section>
  </main><!-- End #main -->

  <?php include_once './footer.php'; ?>

  <!-- JavaScript for translations (at bottom of <body>) -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const systemLang = navigator.language || navigator.userLanguage;
      let defaultLang = 'en';
      if (systemLang.startsWith('fr')) {
        defaultLang = 'fr';
      }

      let currentLang = localStorage.getItem('userLang') || defaultLang;
      loadTranslations(currentLang);

      // Also auto-select the correct <option> in #languageSelect
      const selectEl = document.getElementById('languageSelect');
      selectEl.value = currentLang;
    });

    function loadTranslations(lang) {
      fetch(`../languages/${lang}.json`)
        .then(response => {
          if (!response.ok) {
            throw new Error(`Cannot fetch translations: ${response.statusText}`);
          }
          return response.json();
        })
        .then(translations => {
          localStorage.setItem('translations', JSON.stringify(translations));
          translatePage(translations);
        })
        .catch(error => {
          console.error('Error loading translations:', error);
          // fallback to English if fails
          fetch('../languages/en.json')
            .then(response => {
              if (!response.ok) {
                throw new Error(`Fallback error: ${response.statusText}`);
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
          if (element.tagName === 'INPUT' || element.tagName === 'BUTTON') {
            element.value = translations[key];
          } else {
            element.textContent = translations[key];
          }
        }
      });
    }

    function switchLanguage(lang) {
      localStorage.setItem('userLang', lang);
      loadTranslations(lang);
    }
  </script>
</body>
</html>

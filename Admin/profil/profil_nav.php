<?php
// Assuming the user is already logged in, use the UserId stored in the session
$userId = $_SESSION['UserId']; // Ensure that the user is authenticated and session contains the user ID

// Fetch user information from the database
$sql = "SELECT firstname, lastname, privilege, image FROM user WHERE UserId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Fetch user data from the database

    // Extract the data into variables for ease of use
    $firstname = $user['firstname'];
    $lastname = $user['lastname'];
    $privilege = $user['privilege'];
    $profileImage = $user['image'] ?: './assets/img/default-profile.jpg'; // Default profile image if none is set
} else {
    // Handle the case where the user is not found
    $firstname = 'Unknown';
    $lastname = '';
    $privilege = 'Guest';
    $profileImage = './assets/img/default-profile.jpg'; // Default profile image if none is set
}

$stmt->close();
?>

<!-- HTML Part -->
<li class="nav-item dropdown pe-3">
    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="rounded-circle">
        <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></span>
    </a><!-- End Profile Image Icon -->

    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
        <li class="dropdown-header">
            <h6><?php echo htmlspecialchars($firstname . ' ' . $lastname); ?></h6>
            <span><?php echo htmlspecialchars($privilege); ?></span>
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>

        <li>
            <a class="dropdown-item d-flex align-items-center" href="accountSettings.php" data-key="navbar.accountSettings">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
            </a>
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>

        <li>
            <a class="dropdown-item d-flex align-items-center" href="./profil/signout.php" data-key="navbar.signOut">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
            </a>
        </li>
    </ul><!-- End Profile Dropdown Items -->
</li>
<?php
require_once '../connection.php';

// Helper function to calculate time difference and return a readable string
function time_difference($date) {
    $now = new DateTime();
    $event_date = new DateTime($date);
    $diff = $now->diff($event_date);

    if ($diff->y > 0) {
        return $diff->y . ' ans';
    } elseif ($diff->m > 0) {
        return $diff->m . ' mois';
    } elseif ($diff->d > 0) {
        return $diff->d . ' jours';
    } elseif ($diff->h > 0) {
        return $diff->h . ' heures';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minutes';
    } else {
        return 'Juste maintenant';
    }
}

// Variables to store the count of notifications
$notifications_count = 0;
$notifications = [];

// 1. Check feed inventory quantity <= 5
$query_feed = "SELECT feed_name, quantity, last_updated FROM feed_inventory WHERE quantity <= 5";
$result_feed = $conn->query($query_feed);
while ($row_feed = $result_feed->fetch_assoc()) {
    $notifications[] = [
        'icon' => 'bi bi-exclamation-circle text-warning',
        'title' => 'Faible Inventaire de Nourriture',
        'message' => 'Le stock de ' . $row_feed['feed_name'] . ' est inférieur à 5 unités.',
        'time' => time_difference($row_feed['last_updated'])
    ];
    $notifications_count++;
}

// 2. Check medical supplies quantity <= 3
$query_medical = "SELECT supply_name, quantity, created_at FROM medical_supplies WHERE quantity <= 3";
$result_medical = $conn->query($query_medical);
while ($row_medical = $result_medical->fetch_assoc()) {
    $notifications[] = [
        'icon' => 'bi bi-exclamation-circle text-warning',
        'title' => 'Faible Stock de Fournitures Médicales',
        'message' => 'Le stock de ' . $row_medical['supply_name'] . ' est inférieur à 3 unités.',
        'time' => time_difference($row_medical['created_at'])
    ];
    $notifications_count++;
}

// 3. Check quarantine duration > 1 month
$query_quarantine = "SELECT tag_number, quarantine_start_date FROM quarantine WHERE status = 'In Quarantine' AND quarantine_start_date <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
$result_quarantine = $conn->query($query_quarantine);
while ($row_quarantine = $result_quarantine->fetch_assoc()) {
    $notifications[] = [
        'icon' => 'bi bi-exclamation-circle text-warning',
        'title' => 'Porc en Quarantaine depuis plus d\'1 mois',
        'message' => 'Le porc avec numéro ' . $row_quarantine['tag_number'] . ' est en quarantaine depuis plus d\'1 mois.',
        'time' => time_difference($row_quarantine['quarantine_start_date'])
    ];
    $notifications_count++;
}

// 4. Check next vaccination due date ≤ 3 days
$query_vaccination = "SELECT tag_number, vaccine_name, next_due_date FROM pig_health_vaccinations WHERE next_due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
$result_vaccination = $conn->query($query_vaccination);
while ($row_vaccination = $result_vaccination->fetch_assoc()) {
    $notifications[] = [
        'icon' => 'bi bi-exclamation-circle text-warning',
        'title' => 'Vaccination à venir',
        'message' => 'Le porc ' . $row_vaccination['tag_number'] . ' a besoin du vaccin ' . $row_vaccination['vaccine_name'] . ' dans 3 jours.',
        'time' => time_difference($row_vaccination['next_due_date'])
    ];
    $notifications_count++;
}

// 5. Check estimated delivery date ≤ 3 days
$query_delivery = "SELECT female_pig_tag, estimated_delivery_date FROM breeding_records WHERE estimated_delivery_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
$result_delivery = $conn->query($query_delivery);
while ($row_delivery = $result_delivery->fetch_assoc()) {
    $notifications[] = [
        'icon' => 'bi bi-exclamation-circle text-warning',
        'title' => 'Livraison de Porcelets',
        'message' => 'Le porc femelle avec numéro ' . $row_delivery['female_pig_tag'] . ' doit accoucher dans 3 jours.',
        'time' => time_difference($row_delivery['estimated_delivery_date'])
    ];
    $notifications_count++;
}

// Notification HTML output
?>
<li class="nav-item dropdown">

    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-bell"></i>
        <span class="badge bg-primary badge-number"><?php echo $notifications_count; ?></span>
    </a><!-- End Notification Icon -->

    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
        <li class="dropdown-header">
            Vous avez <?php echo $notifications_count; ?> nouvelles notifications
            <a href="notifications.php"><span class="badge rounded-pill bg-primary p-2 ms-2">Voir tout</span></a>
        </li>

        <li>
            <hr class="dropdown-divider">
        </li>

        <?php foreach ($notifications as $notification): ?>
        <li class="notification-item">
            <i class="<?php echo $notification['icon']; ?>"></i>
            <div>
                <h4><?php echo $notification['title']; ?></h4>
                <p><?php echo $notification['message']; ?></p>
                <p><?php echo $notification['time']; ?></p>
            </div>
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <?php endforeach; ?>

        <li class="dropdown-footer">
            <a href="notifications.php">Voir toutes les notifications</a>
        </li>

    </ul><!-- End Notification Dropdown Items -->

</li><!-- End Notification Nav -->

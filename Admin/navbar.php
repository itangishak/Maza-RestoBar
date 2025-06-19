<header id="header" class="header fixed-top d-flex align-items-center">
  <div class="d-flex align-items-center justify-content-between">
    <a href="dashboard.php" class="logo d-flex align-items-center">
      <img src="../Client/img/LogoMaza.png" alt="logo" style="width: 100px;">
    </a>
    <i class="bi bi-list toggle-sidebar-btn"></i>
  </div><!-- Fin du Logo -->

  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">
      <!-- Notification Nav -->
      <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
          <i class="bi bi-bell"></i>
          <span class="badge bg-primary badge-number" id="notification-count">0</span>
        </a><!-- Fin icône de notification -->
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications" style="max-height: 400px; overflow-y: auto;">
          <li class="dropdown-header">
            Notifications
            <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">Tout voir</span></a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li class="notification-body" id="notification-body">
            <div class="text-center text-muted">Chargement...</div>
          </li>
        </ul><!-- Fin liste déroulante des notifications -->
      </li><!-- Fin Notification Nav -->

      <!-- Profile Nav -->
      <li class="nav-item dropdown">
        <?php include_once './profil/profil_nav.php'; ?>
      </li><!-- Fin Profile Nav -->
    </ul>
  </nav><!-- Fin barre d’icônes -->
</header>

<!-- jQuery doit être inclus une seule fois. Supprimez les doublons si nécessaire. -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function(){
    // Fonction pour récupérer les notifications dynamiquement
    function fetchNotifications(){
        $.ajax({
            url: 'notification.php',
            type: 'GET',
            dataType: 'json',
            success: function(response){
                // Mettre à jour le compteur de notifications
                $('#notification-count').text(response.total);

                // Construire le HTML des notifications
                let notificationHtml = '';
                if(response.total === 0){
                    notificationHtml = '<div class="text-center text-muted">Aucune nouvelle notification</div>';
                } else {
                    // Commandes en attente
                    if(response.pendingOrders && response.pendingOrders.length > 0){
                        notificationHtml += '<div class="notification-item">';
                        notificationHtml += '<h6>Commandes en attente (' + response.pendingOrders.length + ')</h6>';
                        response.pendingOrders.forEach(function(order){
                            notificationHtml += '<div class="notification-content">';
                            notificationHtml += '<i class="bi bi-exclamation-circle text-warning"></i> ';
                            notificationHtml += 'Commande n°' + order.order_id + ' de ' + order.customer_name + ' est en attente.';
                            notificationHtml += '</div>';
                        });
                        notificationHtml += '</div>';
                        notificationHtml += '<hr class="dropdown-divider">';
                    }
                    // Articles en stock faible
                    if(response.lowStockItems && response.lowStockItems.length > 0){
                        notificationHtml += '<div class="notification-item">';
                        notificationHtml += '<h6>Articles en stock faible (' + response.lowStockItems.length + ')</h6>';
                        response.lowStockItems.forEach(function(item){
                            notificationHtml += '<div class="notification-content">';
                            notificationHtml += '<i class="bi bi-exclamation-circle text-danger"></i> ';
                            notificationHtml += item.item_name + ' est presque épuisé (Stock : ' + item.quantity_in_stock + ').';
                            notificationHtml += '</div>';
                        });
                        notificationHtml += '</div>';
                    }
                }
                $('#notification-body').html(notificationHtml);
            },
            error: function(){
                $('#notification-body').html('<div class="text-center text-danger">Erreur lors du chargement des notifications</div>');
            }
        });
    }

    // Récupération initiale des notifications
    fetchNotifications();
    // Rafraîchissement automatique toutes les 10 secondes
    setInterval(fetchNotifications, 10000);
});
</script>

<style>
.notification-item {
    padding: 10px 15px;
}
.notification-content {
    margin: 5px 0;
    font-size: 14px;
}
.notifications {
    width: 300px;
}
.dropdown-header {
    font-weight: bold;
}
</style>

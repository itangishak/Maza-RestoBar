<?php
  $current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <li class="nav-item">
      <a href="./stock-dashboard.php" class="nav-link <?php echo ($current_page == 'stock-dashboard.php') ? 'active' : 'collapsed'; ?>">
        <i class="bi bi-grid"></i>
        <span class="nav-text">Dashboard</span>
      </a>
    </li>
    <?php
      $stock_pages = ['inventory.php','suppliers.php','purchase_order_dashboard.php','stock-adjustement.php'];
      $is_stock_active = in_array($current_page, $stock_pages);
    ?>
    <li class="nav-item">
      <a href="#" class="nav-link has-dropdown <?php echo $is_stock_active ? 'expanded' : 'collapsed'; ?>">
        <i class="bi bi-box-seam"></i>
        <span class="nav-text" data-key="sidebar.stockManagement">Stock Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="stock-nav" class="nav-content <?php echo $is_stock_active ? 'show' : ''; ?>">
        <li><a href="./inventory.php" class="<?php echo ($current_page == 'inventory.php') ? 'active' : ''; ?>"><i class="bi bi-circle"></i><span class="nav-text" data-key="sidebar.inventory">Inventory</span></a></li>
        <li><a href="./suppliers.php" class="<?php echo ($current_page == 'suppliers.php') ? 'active' : ''; ?>"><i class="bi bi-circle"></i><span class="nav-text" data-key="sidebar.suppliers">Suppliers</span></a></li>
        <li><a href="./stock-adjustement.php" class="<?php echo ($current_page == 'stock-adjustement.php') ? 'active' : ''; ?>"><i class="bi bi-circle"></i><span class="nav-text" data-key="sidebar.stockAdjustement">Stock Adjustement</span></a></li>
        <li><a href="./purchase_order_dashboard.php" class="<?php echo ($current_page == 'purchase_order_dashboard.php') ? 'active' : ''; ?>"><i class="bi bi-circle"></i><span class="nav-text" data-key="sidebar.purchaseOrders">Purchase Orders</span></a></li>
      </ul>
    </li>
    <?php
      $kot_pages = ['current-kot.php', 'kot-history.php'];
      $is_kot_active = in_array($current_page, $kot_pages);
    ?>
    <li class="nav-item">
      <a href="#" class="nav-link has-dropdown <?php echo $is_kot_active ? 'expanded' : 'collapsed'; ?>">
        <i class="bi bi-printer"></i>
        <span class="nav-text" data-key="sidebar.kitchenOrders">Kitchen Orders (KOT)</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="kot-nav" class="nav-content <?php echo $is_kot_active ? 'show' : ''; ?>">
        <li><a href="./current-kot.php" class="<?php echo ($current_page == 'current-kot.php') ? 'active' : ''; ?>"><i class="bi bi-circle"></i><span class="nav-text" data-key="sidebar.currentKOTs">Current KOTs</span></a></li>
        <li><a href="./kot-history.php" class="<?php echo ($current_page == 'kot-history.php') ? 'active' : ''; ?>"><i class="bi bi-circle"></i><span class="nav-text" data-key="sidebar.kotHistory">KOT History</span></a></li>
      </ul>
    </li>
  </ul>
</aside>

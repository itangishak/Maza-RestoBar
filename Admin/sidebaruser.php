<?php
  // Determine the current page
  $current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <!-- Dashboard -->
    <li class="nav-item">
      <a 
        href="./employee-dashboard.php"
        class="nav-link <?php echo ($current_page == 'employee-dashboard.php') ? 'active' : ''; ?>"
      >
        <i class="bi bi-grid"></i>
        <!-- data-key="sidebar.dashboard" -->
        <span class="nav-text" data-key="sidebar.dashboard">Dashboard</span>
      </a>
    </li>
    
    <!-- POS System -->
    <li class="nav-item">
      <a 
        href="./pos.php"
        class="nav-link <?php echo ($current_page == 'pos.php') ? 'active' : ''; ?>"
      >
        <i class="bi bi-check-circle"></i>
        <!-- data-key="sidebar.newSale" -->
        <span class="nav-text" data-key="sidebar.newSale">New Sale</span>
      </a>
    </li>
    
    <!-- Orders Management: Current Orders -->
    <li class="nav-item">
      <a 
        class="nav-link <?php echo ($current_page == 'current-orders.php') ? 'active' : ''; ?>"
        href="./current-orders.php"
      >
        <i class="bi bi-receipt"></i>
        <!-- data-key="sidebar.currentOrders" -->
        <span class="nav-text" data-key="sidebar.currentOrders">Current Orders</span>
      </a>
    </li>
    
    <!-- Kitchen Orders (KOT): Current KOTs -->
    <li class="nav-item">
      <a 
        class="nav-link <?php echo ($current_page == 'current-kot.php') ? 'active' : ''; ?>"
        href="./current-kot.php"
      >
        <i class="bi bi-printer"></i>
        <!-- data-key="sidebar.currentKOTs" -->
        <span class="nav-text" data-key="sidebar.currentKOTs">Current KOTs</span>
      </a>
    </li>


    <!-- Inventory -->
    <li class="nav-item">
      <a class="nav-link" href="inventory.php">
        <i class="bi bi-box"></i>
        <span>Inventory</span>
      </a>
    </li>

    <!-- Employees -->
    <li class="nav-item">
      <a class="nav-link" href="employees.php">
        <i class="bi bi-people"></i>
        <span>Employees</span>
      </a>
    </li>

    <!-- Reports -->
    <li class="nav-item">
      <a class="nav-link" href="reports.php">
        <i class="bi bi-file-earmark-text"></i>
        <span>Reports</span>
      </a>
    </li>

    <!-- Settings -->
    <li class="nav-item">
      <a class="nav-link" href="settings.php">
        <i class="bi bi-gear"></i>
        <span>Settings</span>
      </a>
    </li>
  </ul>
</aside>

<!-- JavaScript for Expand/Collapse Behavior -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const dropdownLinks = document.querySelectorAll(".has-dropdown");

  dropdownLinks.forEach((dropdown) => {
    dropdown.addEventListener("click", function (e) {
      e.preventDefault(); // prevent navigation

      // Close all other submenus
      dropdownLinks.forEach((other) => {
        if (other !== dropdown) {
          other.classList.remove("expanded");
          let otherSubmenu = other.nextElementSibling;
          if (otherSubmenu && otherSubmenu.classList.contains("show")) {
            otherSubmenu.classList.remove("show");
          }
        }
      });

      // Toggle the clicked submenu
      let submenu = this.nextElementSibling;
      if (submenu.classList.contains("show")) {
        submenu.classList.remove("show");
        this.classList.add("collapsed");
        this.classList.remove("expanded");
      } else {
        submenu.classList.add("show");
        this.classList.remove("collapsed");
        this.classList.add("expanded");
      }
    });
  });
});
</script>

<style>
/* Hide submenus by default */
.nav-content {
  display: none;
}

/* Show submenus when "show" is present */
.nav-content.show {
  display: block;
}

/* Expand arrow rotation */
.has-dropdown.expanded i.bi-chevron-down {
  transform: rotate(180deg);
}

/* Active submenu highlight */
.nav-content a.active {
  font-weight: bold;
  color: #007bff;
}
</style>

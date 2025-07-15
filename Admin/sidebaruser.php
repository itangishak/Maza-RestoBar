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
  
  // Fix for Bootstrap dropdowns
  // Make sure Bootstrap is available
  if (typeof bootstrap !== 'undefined') {
    // Initialize all dropdowns on the page
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.forEach(function(dropdownToggleEl) {
      new bootstrap.Dropdown(dropdownToggleEl);
    });
  } else {
    // If Bootstrap is not available yet, wait and try again
    setTimeout(function() {
      if (typeof bootstrap !== 'undefined') {
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        dropdownElementList.forEach(function(dropdownToggleEl) {
          new bootstrap.Dropdown(dropdownToggleEl);
        });
      }
    }, 500); // Wait 500ms and try again
  }
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

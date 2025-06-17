<?php
  // Determine the current page
  $current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <!-- Dashboard -->
    <li class="nav-item">
      <a 
        href="./boss-dashboard.php"
        class="nav-link <?php echo ($current_page == 'boss-dashboard.php') ? 'active' : 'collapsed'; ?>"
      >
        <i class="bi bi-grid"></i>
        <!-- data-key for sidebar.dashboard -->
        <span class="nav-text" data-key="sidebar.dashboard">Dashboard</span>
      </a>
    </li>

    <!-- POS System -->
    <?php
      $pos_pages = ['pos.php', 'sales-history.php', 'buffet_counter.php'];
      $is_pos_active = in_array($current_page, $pos_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#" 
        class="nav-link has-dropdown <?php echo $is_pos_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-cash-register"></i>
        <!-- data-key for sidebar.posSystem -->
        <span class="nav-text" data-key="sidebar.posSystem">POS System</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="pos-nav"
        class="nav-content <?php echo $is_pos_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./pos.php" 
            class="<?php echo ($current_page == 'pos.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.newSale -->
            <span class="nav-text" data-key="sidebar.newSale">New Sale</span>
          </a>
        </li>
        <li>
          <a 
            href="./sales-history.php" 
            class="<?php echo ($current_page == 'sales-history.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.salesHistory -->
            <span class="nav-text" data-key="sidebar.salesHistory">Sales History</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Orders Management -->
    <?php
      $orders_pages = ['current-orders.php', 'order-history.php'];
      $is_orders_active = in_array($current_page, $orders_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_orders_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-receipt"></i>
        <!-- data-key for sidebar.ordersManagement -->
        <span class="nav-text" data-key="sidebar.ordersManagement">Orders Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="orders-nav"
        class="nav-content <?php echo $is_orders_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./current-orders.php" 
            class="<?php echo ($current_page == 'current-orders.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.currentOrders -->
            <span class="nav-text" data-key="sidebar.currentOrders">Current Orders</span>
          </a>
        </li>
        <li>
          <a 
            href="./order-history.php" 
            class="<?php echo ($current_page == 'order-history.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.orderHistory -->
            <span class="nav-text" data-key="sidebar.orderHistory">Order History</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Menu Management -->
    <?php
      $menu_pages = ['menu_dashboard.php', 'menu.php', 'categories.php'];
      $is_menu_active = in_array($current_page, $menu_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_menu_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-card-list"></i>
        <!-- data-key for sidebar.menuManagement -->
        <span class="nav-text" data-key="sidebar.menuManagement">Menu Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="menu-nav" 
        class="nav-content <?php echo $is_menu_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./menu_dashboard.php" 
            class="<?php echo ($current_page == 'menu_dashboard.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.viewMenu -->
            <span class="nav-text" data-key="sidebar.viewMenu">View Menu</span>
          </a>
        </li>
        <li>
          <a 
            href="./add-menu-item.php" 
            class="<?php echo ($current_page == 'add-menu-item.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.addEditItems -->
            <span class="nav-text" data-key="sidebar.addEditItems">Add/Edit Items</span>
          </a>
        </li>
        <li>
          <a 
            href="./categories.php" 
            class="<?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.categories -->
            <span class="nav-text" data-key="sidebar.categories">Categories</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Buffet Management -->
    <?php
      $buffet_pages = ['buffet-reporting.php', 'buffet-settings.php'];
      $is_buffet_active = in_array($current_page, $buffet_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_buffet_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-cup-hot"></i>
        <!-- data-key for sidebar.buffetManagement -->
        <span class="nav-text" data-key="sidebar.buffetManagement">Buffet Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="buffet-nav" 
        class="nav-content <?php echo $is_buffet_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./buffet-reporting.php" 
            class="<?php echo ($current_page == 'buffet-reporting.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.buffetReporting -->
            <span class="nav-text" data-key="sidebar.buffetReporting">Buffet Reporting</span>
          </a>
        </li>
        <li>
          <a 
            href="./buffet-settings.php" 
            class="<?php echo ($current_page == 'buffet-settings.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.buffetSettings -->
            <span class="nav-text" data-key="sidebar.buffetSettings">Buffet Settings</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Reservation Management -->
    <?php
      $reservation_pages = ['reservation_dashboard.php', 'add-reservation.php'];
      $is_reservation_active = in_array($current_page, $reservation_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_reservation_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-calendar-event"></i>
        <!-- data-key for sidebar.reservationManagement -->
        <span class="nav-text" data-key="sidebar.reservationManagement">Reservation Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="reservations-nav" 
        class="nav-content <?php echo $is_reservation_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./reservation_dashboard.php" 
            class="<?php echo ($current_page == 'reservation_dashboard.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.reservationList -->
            <span class="nav-text" data-key="sidebar.reservationList">Reservation List</span>
          </a>
        </li>
        <li>
          <a 
            href="./add-reservation.php" 
            class="<?php echo ($current_page == 'add-reservation.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.addReservation -->
            <span class="nav-text" data-key="sidebar.addReservation">Add Reservation</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Stock Management -->
    <?php
      $stock_pages = ['inventory.php', 'suppliers.php', 'purchase_order_dashboard.php', 'stock-adjustement.php'];
      $is_stock_active = in_array($current_page, $stock_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_stock_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-box-seam"></i>
        <!-- data-key for sidebar.stockManagement -->
        <span class="nav-text" data-key="sidebar.stockManagement">Stock Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="stock-nav" 
        class="nav-content <?php echo $is_stock_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./inventory.php" 
            class="<?php echo ($current_page == 'inventory.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.inventory -->
            <span class="nav-text" data-key="sidebar.inventory">Inventory</span>
          </a>
        </li>
        <li>
          <a 
            href="./suppliers.php" 
            class="<?php echo ($current_page == 'suppliers.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.suppliers -->
            <span class="nav-text" data-key="sidebar.suppliers">Suppliers</span>
          </a>
        </li>
        <li>
          <a 
            href="./stock-adjustement.php" 
            class="<?php echo ($current_page == 'stock-adjustement.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.stockAdjustement -->
            <span class="nav-text" data-key="sidebar.stockAdjustement">Stock Adjustement</span>
          </a>
        </li>
        <li>
          <a 
            href="./purchase_order_dashboard.php" 
            class="<?php echo ($current_page == 'purchase_order_dashboard.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.purchaseOrders -->
            <span class="nav-text" data-key="sidebar.purchaseOrders">Purchase Orders</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Employee Management -->
    <?php
      $employee_pages = ['employees_dashboard.php', 'shift_management.php', 'schedule_management.php', 'attendance_dashboard.php', 'payroll_dashboard.php', 'users_dashboard.php'];
      $is_employee_active = in_array($current_page, $employee_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_employee_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-people"></i>
        <!-- data-key for sidebar.employeeManagement -->
        <span class="nav-text" data-key="sidebar.employeeManagement">Employee Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="employee-nav" 
        class="nav-content <?php echo $is_employee_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./employees_dashboard.php" 
            class="<?php echo ($current_page == 'employees_dashboard.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.employeeList -->
            <span class="nav-text" data-key="sidebar.employeeList">Employee List</span>
          </a>
        </li>
        <li>
          <a 
            href="./shift_management.php" 
            class="<?php echo ($current_page == 'shift_management.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.shifts -->
            <span class="nav-text" data-key="sidebar.shifts">Shifts</span>
          </a>
        </li>
        <li>
          <a 
            href="./schedule_management.php" 
            class="<?php echo ($current_page == 'schedule_management.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.schedules -->
            <span class="nav-text" data-key="sidebar.schedules">Schedules</span>
          </a>
        </li>
        <li>
          <a 
            href="./attendance_dashboard.php" 
            class="<?php echo ($current_page == 'attendance_dashboard.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.attendance -->
            <span class="nav-text" data-key="sidebar.attendance">Attendance</span>
          </a>
        </li>
        <li>
          <a 
            href="./payroll_dashboard.php" 
            class="<?php echo ($current_page == 'payroll_dashboard.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.payroll -->
            <span class="nav-text" data-key="sidebar.payroll">Payroll</span>
          </a>
        </li>
        <li>
          <a 
            href="./users_dashboard.php" 
            class="<?php echo ($current_page == 'users_dashboard.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.user -->
            <span class="nav-text" data-key="sidebar.user">User</span>
          </a>
        </li>
        <li>
          <a 
            href="./employee_loans.php" 
            class="<?php echo ($current_page == 'employee_loans.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-cash-coin"></i>
            <span class="nav-text" data-key="sidebar.employeeLoans">Employee Loans</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Debt Management -->
    <li class="nav-item">
      <a 
        class="nav-link <?php echo ($current_page == 'debts_dashboard.php') ? 'active' : 'collapsed'; ?>" 
        href="./debts_dashboard.php"
      >
        <i class="bi bi-credit-card-2-front"></i>
        <!-- data-key for sidebar.debtManagement -->
        <span class="nav-text" data-key="sidebar.debtManagement">Debt Management</span>
      </a>
    </li>

    <!-- QR Ordering / Web Store -->
    <li class="nav-item">
      <a 
        class="nav-link <?php echo ($current_page == 'qr-ordering.php') ? 'active' : 'collapsed'; ?>" 
        href="#"
      >
        <i class="bi bi-qr-code-scan"></i>
        <!-- data-key for sidebar.qrOrdering -->
        <span class="nav-text" data-key="sidebar.qrOrdering">QR Ordering / Web Store</span>
      </a>
    </li>

    <!-- Financial Management -->
    <?php
      $financial_pages = ['expense-management.php', 'expense-history.php', 'financial-reports.php'];
      $is_financial_active = in_array($current_page, $financial_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_financial_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-cash-coin"></i>
        <span class="nav-text" data-key="sidebar.financialManagement">Financial Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="financial-nav" 
        class="nav-content <?php echo $is_financial_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./expense-management.php" 
            class="<?php echo ($current_page == 'expense-management.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <span class="nav-text" data-key="sidebar.expenses">Add Expenses</span>
          </a>
        </li>
        <li>
          <a 
            href="./expense-history.php" 
            class="<?php echo ($current_page == 'expense-history.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <span class="nav-text" data-key="sidebar.expensesList">Expense History</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Kitchen Orders (KOT) -->
    <?php
      $kot_pages = ['current-kot.php', 'kot-history.php'];
      $is_kot_active = in_array($current_page, $kot_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_kot_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-printer"></i>
        <!-- data-key for sidebar.kitchenOrders -->
        <span class="nav-text" data-key="sidebar.kitchenOrders">Kitchen Orders (KOT)</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="kot-nav" 
        class="nav-content <?php echo $is_kot_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./current-kot.php" 
            class="<?php echo ($current_page == 'current-kot.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.currentKOTs -->
            <span class="nav-text" data-key="sidebar.currentKOTs">Current KOTs</span>
          </a>
        </li>
        <li>
          <a 
            href="./kot-history.php" 
            class="<?php echo ($current_page == 'kot-history.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.kotHistory -->
            <span class="nav-text" data-key="sidebar.kotHistory">KOT History</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Reports and Analytics -->
    <?php
      $reports_pages = ['sales-reports.php', 'inventory-reports.php', 'employee-reports.php'];
      $is_reports_active = in_array($current_page, $reports_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_reports_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-bar-chart"></i>
        <!-- data-key for sidebar.reportsAnalytics -->
        <span class="nav-text" data-key="sidebar.reportsAnalytics">Reports and Analytics</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="reports-nav" 
        class="nav-content <?php echo $is_reports_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./sales-reports.php" 
            class="<?php echo ($current_page == 'sales-reports.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.salesReports -->
            <span class="nav-text" data-key="sidebar.salesReports">Sales Reports</span>
          </a>
        </li>
        <li>
          <a 
            href="./inventory-reports.php" 
            class="<?php echo ($current_page == 'inventory-reports.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.inventoryReports -->
            <span class="nav-text" data-key="sidebar.inventoryReports">Inventory Reports</span>
          </a>
        </li>
        <li>
          <a 
            href="./employee-reports.php" 
            class="<?php echo ($current_page == 'employee-reports.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.employeeReports -->
            <span class="nav-text" data-key="sidebar.employeeReports">Employee Reports</span>
          </a>
        </li>
        <li>
          <a 
            href="./employee_loans.php" 
            class="<?php echo ($current_page == 'employee_loans.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <span class="nav-text" data-key="sidebar.employeeLoans">Employee Loans</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Customer Management (CRM) -->
    <?php
      $crm_pages = ['./customer_dashboard.php', 'feedback.php'];
      $is_crm_active = in_array($current_page, $crm_pages);
    ?>
    <li class="nav-item">
      <a 
        href="#"
        class="nav-link has-dropdown <?php echo $is_crm_active ? 'expanded' : 'collapsed'; ?>"
      >
        <i class="bi bi-person-lines-fill"></i>
        <!-- data-key for sidebar.customerManagement -->
        <span class="nav-text" data-key="sidebar.customerManagement">Customer Management</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul 
        id="crm-nav" 
        class="nav-content <?php echo $is_crm_active ? 'show' : ''; ?>"
      >
        <li>
          <a 
            href="./customer_dashboard.php" 
            class="<?php echo ($current_page == './customer_dashboard.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.customers -->
            <span class="nav-text" data-key="sidebar.customers">Customers</span>
          </a>
        </li>
        <li>
          <a 
            href="./feedback.php" 
            class="<?php echo ($current_page == 'feedback.php') ? 'active' : ''; ?>"
          >
            <i class="bi bi-circle"></i>
            <!-- data-key for sidebar.feedback -->
            <span class="nav-text" data-key="sidebar.feedback">Feedback</span>
          </a>
        </li>
      </ul>
    </li>

  </ul>
</aside>

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

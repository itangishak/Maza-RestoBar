<?php
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['UserId'])) {
  header("Location: login.php");
  exit();
}

// Get purchase order ID from URL
$purchase_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$purchase_id) {
  header("Location: purchase_order_dashboard.php");
  exit();
}

// Fetch purchase order details
$query = "SELECT * FROM purchase_orders WHERE purchase_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $purchase_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
  header("Location: purchase_order_dashboard.php?error=not_found");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Purchase Order</title>
  <?php include_once './header.php'; ?>
  <style>
    .main-container {
      margin-top: 100px;
      margin-left: 70px;
    }
    .card {
      box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>
  <?php include_once './navbar.php'; ?>
  <?php include_once 'sidebar.php'; ?>

  <div class="container main-container">
    <div class="card">
      <div class="card-header">
        <h4>Edit Purchase Order #<?php echo $purchase_id; ?></h4>
      </div>
      <div class="card-body">
        <form id="editOrderForm">
          <input type="hidden" name="purchase_id" value="<?php echo $purchase_id; ?>">
          
          <div class="form-group">
            <label>Product Names</label>
            <input type="text" class="form-control" name="product_names" 
                   value="<?php echo htmlspecialchars($order['product_names'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Order Date</label>
            <input type="date" class="form-control" name="order_date" 
                   value="<?php echo htmlspecialchars($order['order_date'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label>Payment Method</label>
            <select class="form-control" name="payment_method">
              <option value="cash" <?php echo ($order['payment_method'] ?? '') === 'cash' ? 'selected' : ''; ?>>Cash</option>
              <option value="credit" <?php echo ($order['payment_method'] ?? '') === 'credit' ? 'selected' : ''; ?>>Credit</option>
              <option value="bank" <?php echo ($order['payment_method'] ?? '') === 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Total Amount</label>
            <input type="number" step="0.01" class="form-control" name="total" 
                   value="<?php echo htmlspecialchars($order['total'] ?? ''); ?>">
          </div>
          
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="purchase_order_dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>

  <?php include_once './footer.php'; ?>

  <script>
    $(document).ready(function() {
      $('#editOrderForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
          url: 'update_purchase_order.php',
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function(response) {
            if (response.status === 'success') {
              Swal.fire('Success', response.message, 'success')
                .then(() => window.location.href = 'purchase_order_dashboard.php');
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          },
          error: function() {
            Swal.fire('Error', 'An error occurred while updating the order', 'error');
          }
        });
      });
    });
  </script>
</body>
</html>

<?php
session_start();
require_once 'connection.php';
require_once __DIR__ . '/../includes/auth.php';

require_privilege(['Stock']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stock Dashboard</title>
  <?php include_once 'header.php'; ?>
</head>
<body>
  <?php include_once 'navbar.php'; ?>
  <?php include_once 'sidebar.php'; ?>
  <main id="main" class="main">
    <div class="pagetitle"><h1>Stock Dashboard</h1></div>
    <section class="section dashboard">
      <p>Welcome to the stock management dashboard.</p>
    </section>
  </main>
</body>
</html>

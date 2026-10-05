<?php require_once __DIR__ . '/auth.php'; require_admin();
$pendingAppts = (int)db()->query("SELECT COUNT(*) FROM appointments WHERE status='PENDING'")->fetchColumn(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CJM Beauty &amp; Cosmetic Admin</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<nav>
  <strong>CJM Beauty &amp; Cosmetic Admin</strong>
  <a href="index.php">Products</a>
  <a href="gallery.php">Gallery</a>
  <a href="orders.php">Orders</a>
  <a href="appointments.php">Appointments<?= $pendingAppts ? " ($pendingAppts new)" : "" ?></a>
  <a href="services.php">Services</a>
  <a href="../index.php" target="_blank">View shop</a>
  <a href="logout.php">Logout</a>
</nav>
<main>

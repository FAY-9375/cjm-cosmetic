<?php require 'layout.php'; page_header('Home');
$cats = db()->query("SELECT category, COUNT(*) n FROM products GROUP BY category ORDER BY category")->fetchAll();
$new = db()->query("SELECT * FROM products ORDER BY id DESC LIMIT 8")->fetchAll();
$deals = db()->query("SELECT * FROM products WHERE old_price > price ORDER BY (old_price - price) / old_price DESC LIMIT 4")->fetchAll();
?>
<section class="hero">
  <h1>Glow with confidence</h1>
  <p>Premium cosmetics, skincare and beauty services in one place.</p>
  <a class="btn" href="shop.php">Shop now</a> <a class="btn dark" href="booking.php">Book an appointment</a>
</section>
<main class="wrap">
  <h2 class="sec">Shop by category</h2>
  <div class="tiles"><?php foreach ($cats as $c): ?><a class="tile" href="shop.php?cat=<?= urlencode($c['category']) ?>"><?= e($c['category']) ?></a><?php endforeach; ?></div>

  <?php if ($deals): ?><h2 class="sec">Top deals</h2><div class="grid"><?php foreach ($deals as $p) card($p); ?></div><?php endif; ?>

  <h2 class="sec">New arrivals</h2>
  <div class="grid"><?php foreach ($new as $p) card($p); ?></div>

  <div class="promo"><h2>Book your beauty session</h2><p>Facials, makeup, hair styling and more. Choose your date and time online.</p><a class="btn" href="booking.php">Book now</a></div>
</main>
<?php page_footer();

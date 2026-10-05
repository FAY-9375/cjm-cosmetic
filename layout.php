<?php
require_once __DIR__ . '/config.php';

function e(mixed $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function norm_phone(string $p): string {
    $p = preg_replace('/\D/', '', $p);
    if (preg_match('/^0([71]\d{8})$/', $p, $m) || preg_match('/^([71]\d{8})$/', $p, $m)) $p = '254' . $m[1];
    return $p;
}
function cart_json(array $p): string {
    return e(json_encode(['id' => (int)$p['id'], 'name' => $p['name'], 'price' => (float)$p['price'], 'image' => $p['image']]));
}
function card(array $p): void {
    $disc = ($p['old_price'] && $p['old_price'] > $p['price']) ? round(100 - $p['price'] / $p['old_price'] * 100) : 0; ?>
    <div class="card">
      <a href="product.php?id=<?= $p['id'] ?>">
        <?php if ($disc): ?><span class="badge">-<?= $disc ?>%</span><?php endif; ?>
        <?php if ($p['image']): ?><img class="pimg" src="uploads/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
        <?php else: ?><div class="thumb"><?= e(strtoupper(substr($p['name'], 0, 1))) ?></div><?php endif; ?>
        <h3><?= e($p['name']) ?></h3>
      </a>
      <p class="price">KES <?= number_format($p['price']) ?><?php if ($disc): ?><del>KES <?= number_format($p['old_price']) ?></del><?php endif; ?></p>
      <button onclick="addToCart(<?= cart_json($p) ?>)">Add to cart</button>
    </div>
<?php }
function page_header(string $title): void {
    $cats = db()->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | CJM Beauty and Cosmetic</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top">Free consultation with every appointment | Pay securely with M-Pesa</div>
<header class="main">
  <div class="bar">
    <a class="logo" href="index.php">CJM Beauty &amp; Cosmetic</a>
    <form class="search" action="shop.php"><input name="q" placeholder="Search products, brands and categories" value="<?= e($_GET['q'] ?? '') ?>"><button>Search</button></form>
    <div class="links"><a href="booking.php">Book Appointment</a><a href="track.php">Track Order</a><a href="cart.php">Cart <span class="cart-count">0</span></a></div>
  </div>
  <nav class="catnav"><a href="shop.php">All Products</a>
    <?php foreach ($cats as $c): ?><a href="shop.php?cat=<?= urlencode($c) ?>"><?= e($c) ?></a><?php endforeach; ?>
    <a href="booking.php">Services</a><a href="contact.php">Contact</a></nav>
</header>
<?php }
function page_footer() { ?>
<footer>
  <p><a href="index.php">Home</a><a href="shop.php">Shop</a><a href="booking.php">Book Appointment</a><a href="track.php">Track Order</a><a href="contact.php">Contact</a></p>
  <p>&copy; <?= date('Y') ?> CJM Beauty and Cosmetic. All rights reserved.</p>
</footer>
<div id="toast"></div>
<script src="script.js"></script>
</body>
</html>
<?php }

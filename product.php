<?php require 'layout.php';
$s = db()->prepare("SELECT * FROM products WHERE id = ?"); $s->execute([(int)($_GET['id'] ?? 0)]); $p = $s->fetch();
if (!$p) { http_response_code(404); page_header('Not found'); echo '<main class="wrap"><p>Product not found.</p></main>'; page_footer(); exit; }
$r = db()->prepare("SELECT * FROM products WHERE category = ? AND id <> ? LIMIT 4"); $r->execute([$p['category'], $p['id']]); $related = $r->fetchAll();
page_header($p['name']);
$disc = ($p['old_price'] && $p['old_price'] > $p['price']) ? round(100 - $p['price'] / $p['old_price'] * 100) : 0;
?>
<main class="wrap">
  <div class="detail">
    <?php if ($p['image']): ?><img class="pimg" src="uploads/<?= e($p['image']) ?>" alt=""><?php else: ?><div class="thumb"><?= e(strtoupper(substr($p['name'], 0, 1))) ?></div><?php endif; ?>
    <div>
      <p><a href="shop.php?cat=<?= urlencode($p['category']) ?>"><?= e($p['category']) ?></a></p>
      <h1><?= e($p['name']) ?></h1>
      <p class="price big">KES <?= number_format($p['price']) ?><?php if ($disc): ?><del>KES <?= number_format($p['old_price']) ?></del> <span class="badge" style="position:static">-<?= $disc ?>%</span><?php endif; ?></p>
      <p><?= nl2br(e($p['description'])) ?></p>
      <label>Quantity <input type="number" id="qty" value="1" min="1" max="20" style="width:90px"></label>
      <button onclick="addToCart(<?= cart_json($p) ?>, Math.max(1, +document.getElementById('qty').value))">Add to cart</button>
      <button class="btn dark" onclick="addToCart(<?= cart_json($p) ?>, Math.max(1, +document.getElementById('qty').value)); location='cart.php'">Buy now</button>
    </div>
  </div>
  <?php if ($related): ?><h2 class="sec">You may also like</h2><div class="grid"><?php foreach ($related as $x) card($x); ?></div><?php endif; ?>
</main>
<?php page_footer();

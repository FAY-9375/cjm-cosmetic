<?php require 'layout.php';
$q = trim($_GET['q'] ?? ''); $cat = trim($_GET['cat'] ?? ''); $sort = $_GET['sort'] ?? 'new';
$where = []; $args = [];
if ($q !== '') { $where[] = "(name LIKE ? OR category LIKE ? OR description LIKE ?)"; array_push($args, "%$q%", "%$q%", "%$q%"); }
if ($cat !== '') { $where[] = "category = ?"; $args[] = $cat; }
$order = ['new' => 'id DESC', 'low' => 'price ASC', 'high' => 'price DESC'][$sort] ?? 'id DESC';
$st = db()->prepare("SELECT * FROM products" . ($where ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY $order");
$st->execute($args); $products = $st->fetchAll();
$cats = db()->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
page_header($cat ?: 'Shop');
?>
<main class="wrap shop">
  <aside class="side"><strong>Categories</strong>
    <a href="shop.php" class="<?= $cat === '' ? 'on' : '' ?>">All</a>
    <?php foreach ($cats as $c): ?><a href="shop.php?cat=<?= urlencode($c) ?>" class="<?= $c === $cat ? 'on' : '' ?>"><?= e($c) ?></a><?php endforeach; ?>
  </aside>
  <section>
    <form method="get" style="display:flex;justify-content:space-between;align-items:center">
      <strong><?= count($products) ?> product(s)<?= $q ? ' for "' . e($q) . '"' : '' ?></strong>
      <input type="hidden" name="q" value="<?= e($q) ?>"><input type="hidden" name="cat" value="<?= e($cat) ?>">
      <select name="sort" style="width:auto" onchange="this.form.submit()">
        <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Newest</option>
        <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Price: low to high</option>
        <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: high to low</option>
      </select>
    </form>
    <div class="grid"><?php foreach ($products as $p) card($p); ?></div>
    <?php if (!$products): ?><p>No products found.</p><?php endif; ?>
  </section>
</main>
<?php page_footer();

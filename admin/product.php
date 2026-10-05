<?php require '_header.php';
$id = (int)($_GET['id'] ?? 0);
$p = ['name' => '', 'category' => '', 'description' => '', 'price' => '', 'old_price' => '', 'image' => ''];
if ($id) {
    $s = db()->prepare("SELECT * FROM products WHERE id = ?"); $s->execute([$id]);
    $p = $s->fetch() ?: die('Product not found');
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $p['name'] = trim($_POST['name']); $p['category'] = trim($_POST['category']); $p['price'] = $_POST['price']; $p['description'] = trim($_POST['description'] ?? ''); $p['old_price'] = $_POST['old_price'] ?? '';
        $old = is_numeric($p['old_price']) && $p['old_price'] > $p['price'] ? $p['old_price'] : null;
    try {
        if ($p['name'] === '' || !is_numeric($p['price']) || $p['price'] < 1) throw new Exception('Enter a name and a price of at least 1.');
        $image = $_POST['image_existing'] ?? '';            // picked from the gallery
        if (!empty($_FILES['image_file']['name'])) $image = save_upload($_FILES['image_file']); // or uploaded from device
        if ($image !== '') {                                 // must exist in the gallery
            $c = db()->prepare("SELECT COUNT(*) FROM media WHERE filename = ?"); $c->execute([$image]);
            if (!$c->fetchColumn()) throw new Exception('Invalid image.');
        }
        $image = $image === '' ? null : $image;
        if ($id) db()->prepare("UPDATE products SET name=?, category=?, description=?, price=?, old_price=?, image=? WHERE id=?")->execute([$p['name'], $p['category'] ?: 'General', $p['description'], $p['price'], $old, $image, $id]);
        else db()->prepare("INSERT INTO products (name, category, description, price, old_price, image) VALUES (?,?,?,?,?,?)")->execute([$p['name'], $p['category'] ?: 'General', $p['description'], $p['price'], $old, $image]);
        header('Location: index.php'); exit;
    } catch (Exception $ex) { $err = $ex->getMessage(); }
}
$media = db()->query("SELECT filename FROM media ORDER BY id DESC")->fetchAll(PDO::FETCH_COLUMN);
?>
<h2><?= $id ? 'Edit product' : 'Add product' ?></h2>
<?php if ($err): ?><p class="notice"><?= e($err) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="box">
  <input type="hidden" name="csrf" value="<?= csrf() ?>">
  <label>Name <input name="name" value="<?= e($p['name']) ?>" required></label>
  <label>Category <input name="category" value="<?= e($p['category']) ?>" placeholder="e.g. Makeup, Skincare, Hair"></label>
  <label>Price (KES) <input name="price" type="number" step="1" min="1" value="<?= e($p['price']) ?>" required></label>
  <label>Old price (KES, optional - shows a discount badge) <input name="old_price" type="number" step="1" min="0" value="<?= e($p['old_price']) ?>"></label>
  <label>Description <textarea name="description" rows="4" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px"><?= e($p['description']) ?></textarea></label>

  <label>Upload a new image (opens your phone or computer gallery)
    <input type="file" name="image_file" id="file" accept="image/*">
  </label>
  <img id="preview" class="thumb big" hidden>

  <p><strong>Or pick from the gallery</strong></p>
  <div class="picker">
    <label class="pick none"><input type="radio" name="image_existing" value="" <?= !$p['image'] ? 'checked' : '' ?>><span>No image</span></label>
    <?php foreach ($media as $m): ?>
      <label class="pick"><input type="radio" name="image_existing" value="<?= e($m) ?>" <?= $p['image'] === $m ? 'checked' : '' ?>><img src="../uploads/<?= e($m) ?>"></label>
    <?php endforeach; ?>
  </div>
  <button>Save product</button> <a class="btn light" href="index.php">Cancel</a>
</form>
<script>
document.getElementById('file').onchange = e => {
  const f = e.target.files[0], img = document.getElementById('preview');
  if (f) { img.src = URL.createObjectURL(f); img.hidden = false; }
};
</script>
</main></body></html>

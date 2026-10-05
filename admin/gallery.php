<?php require '_header.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['delete'])) {
        $f = basename($_POST['delete']);
        db()->prepare("UPDATE products SET image = NULL WHERE image = ?")->execute([$f]);
        db()->prepare("DELETE FROM media WHERE filename = ?")->execute([$f]);
        @unlink(UPLOAD_DIR . $f);
        $msg = 'Image deleted.';
    } elseif (!empty($_FILES['images']['name'][0])) {
        $ok = 0; $errs = [];
        foreach ($_FILES['images']['name'] as $i => $n) {
            $file = ['error' => $_FILES['images']['error'][$i], 'size' => $_FILES['images']['size'][$i],
                     'tmp_name' => $_FILES['images']['tmp_name'][$i]];
            try { save_upload($file); $ok++; } catch (Exception $ex) { $errs[] = $n . ': ' . $ex->getMessage(); }
        }
        $msg = "$ok image(s) uploaded. " . implode(' ', $errs);
    }
}
$media = db()->query("SELECT filename FROM media ORDER BY id DESC")->fetchAll(PDO::FETCH_COLUMN);
?>
<h2>Gallery</h2>
<?php if ($msg): ?><p class="notice"><?= e($msg) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="box">
  <input type="hidden" name="csrf" value="<?= csrf() ?>">
  <label>Upload images (you can select several) <input type="file" name="images[]" accept="image/*" multiple required></label>
  <button>Upload</button>
</form>
<div class="picker">
  <?php foreach ($media as $m): ?>
  <form method="post" class="pick" onsubmit="return confirm('Delete this image? Products using it will lose their image.')">
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <img src="../uploads/<?= e($m) ?>">
    <button class="danger" name="delete" value="<?= e($m) ?>">Delete</button>
  </form>
  <?php endforeach; ?>
  <?php if (!$media): ?><p>No images yet.</p><?php endif; ?>
</div>
</main></body></html>

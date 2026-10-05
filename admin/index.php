<?php require '_header.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try { db()->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$_POST['delete']]); }
    catch (PDOException $ex) { $err = 'This product is part of an order and cannot be deleted.'; }
}
$products = db()->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>
<h2>Products <a class="btn" href="product.php">+ Add product</a></h2>
<?php if ($err): ?><p class="notice"><?= e($err) ?></p><?php endif; ?>
<div class="scroll"><table>
  <tr><th>Image</th><th>Name</th><th>Category</th><th>Price (KES)</th><th></th></tr>
  <?php foreach ($products as $p): ?>
  <tr>
    <td><?= $p['image'] ? '<img class="thumb" src="../uploads/' . e($p['image']) . '">' : '-' ?></td>
    <td><?= e($p['name']) ?></td>
    <td><?= e($p['category']) ?></td>
    <td><?= number_format($p['price']) ?></td>
    <td class="actions">
      <a class="btn" href="product.php?id=<?= $p['id'] ?>">Edit</a>
      <form method="post" onsubmit="return confirm('Delete this product?')">
        <input type="hidden" name="csrf" value="<?= csrf() ?>">
        <button class="danger" name="delete" value="<?= $p['id'] ?>">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
</main></body></html>

<?php require '_header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['delete'])) db()->prepare("DELETE FROM services WHERE id = ?")->execute([(int)$_POST['delete']]);
    elseif (trim($_POST['name'] ?? '') !== '' && is_numeric($_POST['price'] ?? ''))
        db()->prepare("INSERT INTO services (name, price, duration_min) VALUES (?,?,?)")->execute([trim($_POST['name']), $_POST['price'], max(15, (int)$_POST['duration'])]);
    header('Location: services.php'); exit;
}
$rows = db()->query("SELECT * FROM services ORDER BY name")->fetchAll();
?>
<h2>Services</h2>
<form method="post" class="box">
  <input type="hidden" name="csrf" value="<?= csrf() ?>">
  <label>Service name <input name="name" required></label>
  <label>Price (KES) <input name="price" type="number" min="0" required></label>
  <label>Duration (minutes) <input name="duration" type="number" min="15" value="60" required></label>
  <button>Add service</button>
</form>
<div class="scroll"><table>
  <tr><th>Service</th><th>Price (KES)</th><th>Duration</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
  <tr><td><?= e($r['name']) ?></td><td><?= number_format($r['price']) ?></td><td><?= $r['duration_min'] ?> min</td>
    <td><form method="post" onsubmit="return confirm('Delete this service?')"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button class="danger" name="delete" value="<?= $r['id'] ?>">Delete</button></form></td></tr>
  <?php endforeach; ?>
</table></div>
</main></body></html>

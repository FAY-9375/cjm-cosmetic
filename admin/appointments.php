<?php require '_header.php';
$allowed = ['PENDING', 'CONFIRMED', 'COMPLETED', 'CANCELLED'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (in_array($_POST['status'] ?? '', $allowed, true))
        db()->prepare("UPDATE appointments SET status = ? WHERE id = ?")->execute([$_POST['status'], (int)$_POST['id']]);
    header('Location: appointments.php?status=' . urlencode($_GET['status'] ?? '')); exit;
}
$f = in_array($_GET['status'] ?? '', $allowed, true) ? $_GET['status'] : '';
$st = db()->prepare("SELECT * FROM appointments" . ($f ? " WHERE status = ?" : "") . " ORDER BY (status = 'PENDING') DESC, appt_date ASC, appt_time ASC LIMIT 300");
$st->execute($f ? [$f] : []); $rows = $st->fetchAll();
?>
<h2>Appointments</h2>
<p><a class="btn <?= $f ? 'light' : '' ?>" href="appointments.php">All</a>
<?php foreach ($allowed as $a): ?><a class="btn <?= $f === $a ? '' : 'light' ?>" href="appointments.php?status=<?= $a ?>"><?= ucfirst(strtolower($a)) ?></a> <?php endforeach; ?></p>
<div class="scroll"><table>
  <tr><th>Ref</th><th>Date and time</th><th>Service</th><th>Customer</th><th>Notes</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($rows as $a): ?>
  <tr>
    <td>#<?= $a['id'] ?></td>
    <td><?= date('D, d M Y', strtotime($a['appt_date'])) ?><br><strong><?= substr($a['appt_time'], 0, 5) ?></strong></td>
    <td><?= e($a['service_name']) ?></td>
    <td><?= e($a['customer_name']) ?><br><a href="tel:+<?= e($a['phone']) ?>"><?= e($a['phone']) ?></a> |
        <a href="https://wa.me/<?= e($a['phone']) ?>" target="_blank">WhatsApp</a><?= $a['email'] ? '<br>' . e($a['email']) : '' ?></td>
    <td><?= e($a['notes']) ?></td>
    <td><span class="tag <?= strtolower($a['status']) ?>"><?= $a['status'] ?></span></td>
    <td><form method="post" class="actions">
      <input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="id" value="<?= $a['id'] ?>">
      <button name="status" value="CONFIRMED">Confirm</button>
      <button name="status" value="COMPLETED">Done</button>
      <button class="danger" name="status" value="CANCELLED">Cancel</button>
    </form></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7">No appointments yet.</td></tr><?php endif; ?>
</table></div>
</main></body></html>

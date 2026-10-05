<?php require 'layout.php';
$services = db()->query("SELECT * FROM services ORDER BY name")->fetchAll();
$slots = ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
$err = ''; $done = null; $v = ['name'=>'','phone'=>'','email'=>'','service'=>'','date'=>'','time'=>'','notes'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) $v[$k] = trim($_POST[$k] ?? '');
    $phone = norm_phone($v['phone']);
    $s = db()->prepare("SELECT * FROM services WHERE id = ?"); $s->execute([(int)$v['service']]); $svc = $s->fetch();
    $d = DateTime::createFromFormat('Y-m-d', $v['date']);
    if (!$v['name'] || !$svc) $err = 'Enter your name and choose a service.';
    elseif (!preg_match('/^254[71]\d{8}$/', $phone)) $err = 'Enter a valid phone number.';
    elseif (!$d || $d->format('Y-m-d') !== $v['date'] || $v['date'] < date('Y-m-d')) $err = 'Choose a valid date (today or later).';
    elseif (!in_array($v['time'], $slots, true)) $err = 'Choose a time.';
    elseif ($v['date'] === date('Y-m-d') && $v['time'] <= date('H:i')) $err = 'That time has already passed today.';
    elseif ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $err = 'Enter a valid email or leave it blank.';
    else {
        $c = db()->prepare("SELECT COUNT(*) FROM appointments WHERE appt_date = ? AND appt_time = ? AND status <> 'CANCELLED'");
        $c->execute([$v['date'], $v['time'] . ':00']);
        if ($c->fetchColumn()) $err = 'That time is already booked. Please pick another.';
        else {
            db()->prepare("INSERT INTO appointments (customer_name, phone, email, service_id, service_name, appt_date, appt_time, notes) VALUES (?,?,?,?,?,?,?,?)")
              ->execute([$v['name'], $phone, $v['email'] ?: null, $svc['id'], $svc['name'], $v['date'], $v['time'] . ':00', $v['notes'] ?: null]);
            $done = ['id' => db()->lastInsertId(), 'svc' => $svc['name'], 'date' => $v['date'], 'time' => $v['time']];
        }
    }
}
page_header('Book appointment');
?>
<main class="wrap two"><section class="box">
  <h2>Book an appointment</h2>
  <?php if ($done): ?>
    <p class="notice ok">Booking received! Reference <strong>#<?= $done['id'] ?></strong>: <?= e($done['svc']) ?> on <?= date('D, d M Y', strtotime($done['date'])) ?> at <?= e($done['time']) ?>. We will confirm shortly.</p>
  <?php else: ?>
  <?php if ($err): ?><p class="notice"><?= e($err) ?></p><?php endif; ?>
  <form method="post">
    <input name="name" placeholder="Full name" value="<?= e($v['name']) ?>" required>
    <input name="phone" placeholder="Phone e.g. 0712345678" value="<?= e($v['phone']) ?>" required>
    <input name="email" type="email" placeholder="Email (optional)" value="<?= e($v['email']) ?>">
    <select name="service" required><option value="">Choose a service</option>
      <?php foreach ($services as $s): ?><option value="<?= $s['id'] ?>" <?= $v['service'] == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> - KES <?= number_format($s['price']) ?> (<?= $s['duration_min'] ?> min)</option><?php endforeach; ?>
    </select>
    <input name="date" id="date" type="date" min="<?= date('Y-m-d') ?>" value="<?= e($v['date']) ?>" required>
    <select name="time" id="time" required><option value="">Choose a time</option>
      <?php foreach ($slots as $t): ?><option value="<?= $t ?>" <?= $v['time'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
    </select>
    <textarea name="notes" rows="3" placeholder="Notes (optional)"><?= e($v['notes']) ?></textarea>
    <button>Book appointment</button>
  </form>
  <?php endif; ?>
</section>
<aside class="box"><h2>Opening hours</h2><p>Mon - Sat: 9:00 - 18:00<br>Last booking: 17:00</p><p>Payment for services is made at the salon.</p></aside>
</main>
<?php page_footer(); ?>
<script>
async function loadSlots() {
  const d = document.getElementById('date').value; if (!d) return;
  const taken = await (await fetch('slots.php?date=' + d)).json();
  document.querySelectorAll('#time option').forEach(o => { if (!o.value) return; o.disabled = taken.includes(o.value); o.textContent = o.value + (o.disabled ? ' (booked)' : ''); });
}
document.getElementById('date').addEventListener('change', loadSlots); loadSlots();
</script>

<?php require 'layout.php'; page_header('Track order');
$o = null; $items = []; $err = '';
if (isset($_GET['id'])) {
    $s = db()->prepare("SELECT * FROM orders WHERE id = ? AND phone = ?"); $s->execute([(int)$_GET['id'], norm_phone($_GET['phone'] ?? '')]); $o = $s->fetch();
    if ($o) { $i = db()->prepare("SELECT p.name, oi.qty, oi.price FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?"); $i->execute([$o['id']]); $items = $i->fetchAll(); }
    else $err = 'No order found. Check the order number and phone number.';
}
?>
<main class="wrap"><h2 class="sec">Track your order</h2>
  <form class="box" method="get" style="max-width:420px">
    <input name="id" type="number" placeholder="Order number" value="<?= e($_GET['id'] ?? '') ?>" required>
    <input name="phone" placeholder="Phone used at checkout" value="<?= e($_GET['phone'] ?? '') ?>" required>
    <button>Track</button>
  </form>
  <?php if ($err): ?><p class="notice"><?= e($err) ?></p><?php endif; ?>
  <?php if ($o): ?><div class="box" style="margin-top:16px">
    <p><strong>Order #<?= $o['id'] ?></strong> placed <?= e($o['created_at']) ?></p>
    <p>Payment status: <strong><?= e($o['status']) ?></strong> <?= $o['mpesa_receipt'] ? '(Receipt ' . e($o['mpesa_receipt']) . ')' : '' ?></p>
    <p>Delivery to: <?= e($o['address']) ?></p>
    <table><?php foreach ($items as $it): ?><tr><td><?= e($it['name']) ?> x<?= $it['qty'] ?></td><td>KES <?= number_format($it['price'] * $it['qty']) ?></td></tr><?php endforeach; ?></table>
    <p class="total">Total: KES <?= number_format($o['total']) ?></p></div><?php endif; ?>
</main>
<?php page_footer();

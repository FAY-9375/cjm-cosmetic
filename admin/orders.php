<?php require '_header.php';

// Bulk: all failed, plus pending orders older than 15 minutes
$bulkWhere = "(LOWER(status) = 'failed' OR (LOWER(status) = 'pending' AND created_at < NOW() - INTERVAL 15 MINUTE))";
// Single: any failed or pending order
$oneWhere = "LOWER(status) IN ('failed','pending')";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_orders'])) {
    check_csrf();
    $pdo = db();
    $pdo->beginTransaction();
    try {
        if (!empty($_POST['order_id'])) {
            $id = (int)$_POST['order_id'];
            $pdo->prepare("DELETE FROM order_items WHERE order_id = ? AND order_id IN (SELECT id FROM orders WHERE $oneWhere)")->execute([$id]);
            $pdo->prepare("DELETE FROM orders WHERE id = ? AND $oneWhere")->execute([$id]);
        } else {
            $pdo->exec("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE $bulkWhere)");
            $pdo->exec("DELETE FROM orders WHERE $bulkWhere");
        }
        $pdo->commit();
    } catch (Exception $ex) {
        $pdo->rollBack();
        die('Could not delete: ' . e($ex->getMessage()));
    }
    header('Location: orders.php');
    exit;
}

$orders = db()->query("SELECT * FROM orders ORDER BY id DESC LIMIT 200")->fetchAll();
?>
<h2>Orders</h2>

<form method="post" onsubmit="return confirm('Delete all failed orders and pending orders older than 15 minutes?')" style="margin-bottom:12px">
  <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
  <button name="delete_orders" value="1">Delete failed &amp; old pending orders</button>
</form>

<div class="scroll"><table>
  <tr><th>#</th><th>Customer</th><th>Phone</th><th>Address</th><th>Total (KES)</th><th>Status</th><th>M-Pesa receipt</th><th>Date</th><th></th></tr>
  <?php foreach ($orders as $o): ?>
  <tr>
    <td><?= $o['id'] ?></td><td><?= e($o['customer_name']) ?></td><td><?= e($o['phone']) ?></td><td><?= e($o['address']) ?></td>
    <td><?= number_format($o['total']) ?></td><td><span class="tag <?= strtolower($o['status']) ?>"><?= e($o['status']) ?></span></td>
    <td><?= e($o['mpesa_receipt']) ?></td><td><?= e($o['created_at']) ?></td>
    <td>
      <?php if (in_array(strtolower($o['status']), ['failed', 'pending'])): ?>
      <form method="post" onsubmit="return confirm('Delete this <?= e(strtolower($o['status'])) ?> order?')">
        <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
        <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
        <button name="delete_orders" value="1">Delete</button>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
</main></body></html>
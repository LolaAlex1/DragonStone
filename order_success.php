<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: ' . url('index.php')); exit; }

$pdo = db();
$stmt = $pdo->prepare("SELECT o.*, u.name AS user_name, u.email AS user_email
                         FROM orders o
                         LEFT JOIN users u ON u.id = o.user_id
                        WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  set_flash('Order not found.', 'info');
  header('Location: ' . url('index.php')); exit;
}

$items = $pdo->prepare(
  "SELECT oi.*, p.name
     FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?"
);
$items->execute([$id]);
$rows = $items->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="h4 mb-3">✅ Order Placed</h1>
<p>Thank you! Your order <strong>#<?= (int)$order['id'] ?></strong> has been placed.</p>

<div class="card mb-3">
  <div class="card-body">
    <div><strong>Status:</strong> <?= esc($order['status']) ?></div>
    <div><strong>Total:</strong> $<?= price($order['total']) ?></div>
    <div><strong>Date:</strong> <?= esc($order['created_at']) ?></div>
  </div>
</div>

<div class="card">
  <div class="card-header">Items</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th>Product</th>
            <th style="width:120px;">Qty</th>
            <th style="width:120px;">Price</th>
            <th style="width:140px;">Subtotal</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= esc($r['name'] ?? 'Product #'.$r['product_id']) ?></td>
            <td><?= (int)$r['qty'] ?></td>
            <td>$<?= price($r['price']) ?></td>
            <td>$<?= price($r['price'] * $r['qty']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<p class="mt-3">
  <a class="btn btn-primary" href="<?= url('catalog.php') ?>">Continue shopping</a>
</p>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
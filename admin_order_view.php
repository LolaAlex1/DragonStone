<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/header.php';

if (!is_logged_in() || (current_user()['role'] ?? '') !== 'admin') {
  set_flash('Admins only.', 'info');
  header('Location: ' . url('auth_login.php'));
  exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  set_flash('Invalid order id', 'info');
  header('Location: ' . url('admin_orders.php'));
  exit;
}

/* Fetch order header */
$stmt = db()->prepare("
  SELECT o.*, u.name AS user_name, u.email AS user_email
  FROM orders o
  LEFT JOIN users u ON u.id = o.user_id
  WHERE o.id = ?
  LIMIT 1
");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  set_flash('Order not found', 'info');
  header('Location: ' . url('admin_orders.php'));
  exit;
}

/* Fetch line items */
$itemsStmt = db()->prepare("
  SELECT oi.qty, oi.price, p.name, p.slug
  FROM order_items oi
  JOIN products p ON p.id = oi.product_id
  WHERE oi.order_id = ?
  ORDER BY oi.id ASC
");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

/* Compute total from items for cross-check display */
$computedTotal = 0.00;
foreach ($items as $it) {
  $computedTotal += ((int)$it['qty']) * ((float)$it['price']);
}

/* Helper for status badge color */
function _status_badge_class(string $s): string {
  return [
    'pending'   => 'secondary',
    'paid'      => 'primary',
    'shipped'   => 'warning',
    'completed' => 'success',
    'cancelled' => 'danger',
  ][$s] ?? 'secondary';
}
?>
<style>
  .ds-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px;
  }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Order #<?= (int)$order['id'] ?></h1>
  <div class="d-flex gap-2">
    <a href="<?= url('admin_orders.php') ?>"
       class="btn btn-secondary"
       style="background:#374151; border:none;">
      ← Back to Orders
    </a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="ds-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="me-3">
          <div class="mb-1">
            <strong>Status:</strong>
            <span class="badge bg-<?= _status_badge_class($order['status']) ?>">
              <?= esc($order['status']) ?>
            </span>
          </div>
          <div class="mb-1"><strong>Date:</strong> <?= esc($order['created_at']) ?></div>
          <div class="mb-1">
            <strong>Customer:</strong>
            <?= esc($order['user_name'] ?: 'Guest') ?>
            (<?= esc($order['user_email'] ?: '-') ?>)
          </div>
          <div class="mb-1">
            <strong>Stored Total:</strong> $<?= price($order['total']) ?>
          </div>
        </div>
      </div>

      <!-- Status update form -->
      <form method="post" action="<?= url('admin_order_update.php') ?>" class="row g-2 align-items-end">
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <div class="col-8">
          <label class="form-label"><strong>Update Status</strong></label>
          <select name="status" class="form-select">
            <?php
              $opts = ['pending','paid','shipped','completed','cancelled'];
              foreach ($opts as $opt):
            ?>
              <option value="<?= $opt ?>" <?= $order['status'] === $opt ? 'selected' : '' ?>>
                <?= ucfirst($opt) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-4">
          <label class="form-label d-block">&nbsp;</label>
          <button class="btn btn-primary w-100">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="ds-card">
  <h2 class="h5 mb-3">Items</h2>
  <table class="table table-bordered align-middle mb-3">
    <thead class="table-light">
      <tr>
        <th>Product</th>
        <th class="text-end">Qty</th>
        <th class="text-end">Price</th>
        <th class="text-end">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= esc($it['name']) ?></td>
          <td class="text-end"><?= (int)$it['qty'] ?></td>
          <td class="text-end">$<?= price($it['price']) ?></td>
          <td class="text-end">$<?= price(((int)$it['qty']) * ((float)$it['price'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3" class="text-end">Stored Total:</th>
        <th class="text-end">$<?= price($order['total']) ?></th>
      </tr>
      <tr>
        <th colspan="3" class="text-end">Computed Total:</th>
        <th class="text-end">$<?= price($computedTotal) ?></th>
      </tr>
    </tfoot>
  </table>

  <div class="d-flex justify-content-end">
    <a href="<?= url('admin_orders.php') ?>" class="btn btn-outline-secondary">← Back to Orders</a>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
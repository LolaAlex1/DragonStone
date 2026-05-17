<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

ensure_session();

// Must be logged in
if (!is_logged_in()) {
  set_flash('Please log in to checkout.', 'info');
  header('Location: ' . url('auth_login.php'));
  exit;
}

// Pull cart
$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
  set_flash('Your cart is empty.');
  header('Location: ' . url('cart.php'));
  exit;
}

// Load products for ids in cart
$ids = array_map('intval', array_keys($cart));
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$pdo = db();
$stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build a map id->row and compute totals
$map = [];
foreach ($items as $row) $map[$row['id']] = $row;

$total = 0.0;
$lineItems = [];
foreach ($cart as $pid => $qty) {
  if (!isset($map[$pid])) continue; // product removed
  $p = $map[$pid];
  $qty = max(1, (int)$qty);
  $lineTotal = $qty * (float)$p['price'];
  $total += $lineTotal;
  $lineItems[] = [
    'id'    => $p['id'],
    'name'  => $p['name'],
    'price' => (float)$p['price'],
    'qty'   => $qty,
    'stock' => (int)$p['stock'],
  ];
}
?>
<h1 class="h4 mb-3">Checkout</h1>

<div class="card mb-3">
  <div class="card-header">Order Summary</div>
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
        <?php foreach ($lineItems as $li): ?>
          <tr>
            <td><?= esc($li['name']) ?></td>
            <td><?= (int)$li['qty'] ?> <?php if ($li['qty'] > $li['stock']): ?>
                <span class="badge bg-danger">exceeds stock (<?= (int)$li['stock'] ?>)</span>
              <?php endif; ?>
            </td>
            <td>$<?= price($li['price']) ?></td>
            <td>$<?= price($li['qty'] * $li['price']) ?></td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="3" class="text-end fw-bold">Total</td>
          <td class="fw-bold">$<?= price($total) ?></td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<form method="post" action="<?= url('place_order.php') ?>">
  <!-- if you wanted shipping info later, add fields here -->
  <button class="btn btn-success">Place Order</button>
  <a class="btn btn-secondary" href="<?= url('cart.php') ?>">Back to Cart</a>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
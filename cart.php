<?php
// Handle POST BEFORE any HTML output
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

ensure_session();
$cart =& cart_ref();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $qtys = $_POST['qty'] ?? [];
  foreach ($qtys as $id => $q) {
    $id = (string)$id;
    $q = max(0, (int)$q);
    if (isset($cart[$id])) {
      if ($q === 0) {
        unset($cart[$id]);
      } else {
        $cart[$id]['qty'] = $q;
      }
    }
  }
  // Redirect after POST (PRG pattern) – now this works because no output yet
  header('Location: ' . url('cart.php'));
  exit;
}

// Only now render the page
require_once __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Your Cart</h1>

<?php if (!$cart): ?>
  <div class="alert alert-info">
    Your cart is empty. <a href="<?= url('catalog.php') ?>">Browse catalog</a>.
  </div>
<?php else: ?>
  <form method="post">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Product</th>
            <th style="width:120px">Qty</th>
            <th style="width:140px">Price</th>
            <th style="width:140px">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cart as $item): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= esc($item['image_url']) ?>" width="60" class="rounded border" alt="">
                  <?php endif; ?>
                  <div><?= esc($item['name']) ?></div>
                </div>
              </td>
              <td>
                <input
                  type="number"
                  name="qty[<?= (int)$item['id'] ?>]"
                  value="<?= (int)$item['qty'] ?>"
                  min="0"
                  class="form-control"
                  style="width:90px"
                >
              </td>
              <td>$<?= price($item['price']) ?></td>
              <td>$<?= price($item['price'] * $item['qty']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">Total</th>
            <th>$<?= price(cart_total()) ?></th>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="d-flex gap-2">
  <button class="btn btn-outline-primary" type="submit" name="action" value="update">Update Cart</button>
  <a class="btn btn-success" href="<?= url('checkout.php') ?>">Checkout</a>
</div>
  </form>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
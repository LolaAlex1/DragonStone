<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';   // ✅ ADD THIS
ensure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . url('checkout.php'));
  exit;
}

if (!is_logged_in()) {
  set_flash('Please log in to place an order.', 'info');
  header('Location: ' . url('auth_login.php'));
  exit;
}

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
  set_flash('Your cart is empty.');
  header('Location: ' . url('cart.php'));
  exit;
}

$pdo = db();
$pdo->beginTransaction();

try {
  // Load products locked FOR UPDATE to prevent race conditions
  $ids = array_map('intval', array_keys($cart));
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
  $stmt->execute($ids);
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $map = [];
  foreach ($products as $p) $map[$p['id']] = $p;

  // Build totals and validate stock
  $total = 0.0;
  $lines = [];
  foreach ($cart as $pid => $qty) {
    if (!isset($map[$pid])) continue;
    $qty = max(1, (int)$qty);
    $row = $map[$pid];
    if ($qty > (int)$row['stock']) {
      throw new Exception("Insufficient stock for {$row['name']} (have {$row['stock']}, requested $qty)");
    }
    $price = (float)$row['price']; // snapshot now
    $total += $price * $qty;
    $lines[] = ['product_id' => (int)$pid, 'qty' => $qty, 'price' => $price];
  }

  if (!$lines) {
    throw new Exception('No valid items in cart.');
  }

  // Create order
  $userId = current_user()['id'] ?? null; // can be NULL if guest later
  $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'paid')");
  $stmt->execute([$userId, $total]);
  $orderId = (int)$pdo->lastInsertId();

  // Insert order items + reduce stock
  $ins = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
  $upd = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
  foreach ($lines as $li) {
    $ins->execute([$orderId, $li['product_id'], $li['qty'], $li['price']]);
    $upd->execute([$li['qty'], $li['product_id']]);
  }

  $pdo->commit();

  // Clear cart
  unset($_SESSION['cart']);

  set_flash('Order placed successfully!');
  header('Location: ' . url('order_success.php?id=' . $orderId));
  exit;

} catch (Exception $e) {
  $pdo->rollBack();
  set_flash('Order failed: ' . $e->getMessage(), 'info');
  header('Location: ' . url('checkout.php'));
  exit;
}
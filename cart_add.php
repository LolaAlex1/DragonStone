<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
if ($id <= 0) { header('Location: ' . url('catalog.php')); exit; }

// fetch product (only needed fields)
$stmt = db()->prepare('SELECT id,name,price,image_url FROM products WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$p = $stmt->fetch();

if ($p) {
  $cart =& cart_ref();
  $key = (string)$p['id'];
  if (!isset($cart[$key])) {
    $cart[$key] = [
      'id' => $p['id'],
      'name' => $p['name'],
      'price' => (float)$p['price'],
      'qty' => 0,
      'image_url' => $p['image_url'] ?? ''
    ];
  }
  $cart[$key]['qty'] += $qty;
}
header('Location: ' . url('cart.php'));
exit;
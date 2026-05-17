<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$user = current_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
  header('Location: ' . url('auth_login.php'));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . url('admin_orders.php'));
  exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);
$status   = trim($_POST['status'] ?? '');

$allowed = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
if (!$order_id || !in_array($status, $allowed, true)) {
  set_flash('Invalid order update.', 'info');
  header('Location: ' . url('admin_orders.php'));
  exit;
}

$stmt = db()->prepare("UPDATE orders SET status = ? WHERE id = ?");
$ok = $stmt->execute([$status, $order_id]);

set_flash($ok ? 'Order status updated.' : 'Failed to update order.');
header('Location: ' . url('admin_order_view.php?id=' . $order_id));
exit;
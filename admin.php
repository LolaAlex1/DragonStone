<?php
require_once __DIR__ . '/includes/header.php';

if (!is_logged_in() || current_user()['role'] !== 'admin') {
    header('Location: ' . url('index.php'));
    exit;
}
?>

<h1>Admin Dashboard</h1>
<p>Welcome, <?= htmlspecialchars(current_user()['name']) ?>!</p>

<ul>
  <li><a href="<?= url('admin_products.php') ?>">Manage Products</a></li>
  <li><a href="<?= url('admin_users.php') ?>">Manage Users</a></li>
  <li><a href="<?= url('admin_orders.php') ?>">View Orders</a></li>
</ul>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
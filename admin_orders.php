<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/header.php';

if (!is_logged_in() || current_user()['role'] !== 'admin') {
  set_flash('Admins only.','info');
  header('Location: ' . url('auth_login.php'));
  exit;
}

// fetch orders newest first
$sql = "SELECT o.id, o.created_at, o.status, o.total,
               u.name AS user_name, u.email AS user_email
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        ORDER BY o.id DESC";
$orders = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="h3 mb-3">Orders</h1>

<?php if (!$orders): ?>
  <div class="alert alert-info">No orders yet.</div>
<?php else: ?>
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Status</th>
        <th class="text-end">Total</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= (int)$o['id'] ?></td>
          <td><?= htmlspecialchars($o['created_at']) ?></td>
          <td>
            <?= htmlspecialchars($o['user_name'] ?: 'Guest') ?><br>
            <small class="text-muted"><?= htmlspecialchars($o['user_email'] ?: '-') ?></small>
          </td>
          <td><?= htmlspecialchars($o['status']) ?></td>
          <td class="text-end">$<?= number_format($o['total'], 2) ?></td>
          <td>
  <a href="admin_order_view.php?id=<?= $o['id'] ?>" 
     class="btn btn-sm btn-primary" 
     style="background:#2563eb; border:none; padding:6px 12px; font-size:0.9rem; border-radius:5px;">
     👁 View
  </a>
</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
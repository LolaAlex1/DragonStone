<?php
// /Applications/MAMP/htdocs/DRAGSTONE/admin_user_view.php
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

$userId = (int)($_GET['id'] ?? 0);
if ($userId <= 0) {
  set_flash('Invalid user id.', 'info');
  header('Location: ' . url('admin_users.php'));
  exit;
}

// Fetch user
$uStmt = db()->prepare("SELECT id, name, email, role, created_at FROM users WHERE id=? LIMIT 1");
$uStmt->execute([$userId]);
$user = $uStmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
  set_flash('User not found.', 'info');
  header('Location: ' . url('admin_users.php'));
  exit;
}

// Fetch orders for user
$oStmt = db()->prepare("
  SELECT id, created_at, status, total
  FROM orders
  WHERE user_id = ?
  ORDER BY id DESC
");
$oStmt->execute([$userId]);
$orders = $oStmt->fetchAll(PDO::FETCH_ASSOC);

// Summary
$orderCount = count($orders);
$totalSpent = 0.0;
foreach ($orders as $o) $totalSpent += (float)$o['total'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">👤 User #<?= (int)$user['id'] ?> — <?= esc($user['name']) ?></h1>
  <a href="<?= url('admin_users.php') ?>" class="btn btn-secondary">← Back to Users</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Profile</div>
      <div class="card-body">
        <div><strong>Name:</strong> <?= esc($user['name']) ?></div>
        <div><strong>Email:</strong> <?= esc($user['email']) ?></div>
        <div><strong>Role:</strong> <span class="badge bg-<?= $user['role']==='admin'?'success':'secondary' ?>">
          <?= esc($user['role']) ?></span></div>
        <div><strong>Joined:</strong> <?= esc($user['created_at']) ?></div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Order Summary</div>
      <div class="card-body">
        <div><strong>Total Orders:</strong> <?= (int)$orderCount ?></div>
        <div><strong>Total Spent:</strong> $<?= number_format($totalSpent, 2) ?></div>
      </div>
    </div>
  </div>
</div>

<h2 class="h5 mb-3">Orders</h2>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th style="width:90px">ID</th>
        <th>Date</th>
        <th style="width:160px">Status</th>
        <th style="width:140px">Total</th>
        <th style="width:140px" class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td>#<?= (int)$o['id'] ?></td>
          <td><?= esc($o['created_at']) ?></td>
          <td>
            <span class="badge
              <?php
                echo match($o['status']) {
                  'paid' => 'bg-success',
                  'pending' => 'bg-warning text-dark',
                  'shipped' => 'bg-info text-dark',
                  'completed' => 'bg-primary',
                  'cancelled' => 'bg-danger',
                  default => 'bg-secondary'
                };
              ?>">
              <?= esc($o['status']) ?>
            </span>
          </td>
          <td>$<?= number_format($o['total'], 2) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary"
               href="<?= url('admin_order_view.php?id='.(int)$o['id']) ?>">View</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$orders): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No orders for this user.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
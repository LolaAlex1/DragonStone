<?php
// /Applications/MAMP/htdocs/DRAGSTONE/admin_users.php
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

// Optional search by name/email
$q = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT id, name, email, role, created_at FROM users";
if ($q !== '') {
  $sql .= " WHERE name LIKE ? OR email LIKE ?";
  $like = '%' . $q . '%';
  $params = [$like, $like];
}
$sql .= " ORDER BY id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count current admins (used by UI + delete safety)
$adminCount = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$me = current_user();
?>
<div class="d-flex align-items-center mb-3">
  <h1 class="h4 mb-0">👥 Manage Users</h1>
</div>

<form class="row g-2 mb-3" method="get" action="<?= url('admin_users.php') ?>" style="max-width:560px">
  <div class="col">
    <input class="form-control" name="q" placeholder="Search name or email…" value="<?= esc($q) ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-primary">Search</button>
    <a class="btn btn-secondary" href="<?= url('admin_users.php') ?>">Reset</a>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead class="table-dark">
      <tr>
        <th style="width:80px">ID</th>
        <th style="min-width:220px">Name</th>
        <th style="min-width:280px">Email</th>
        <th style="width:160px">Role</th>
        <th style="width:180px">Joined</th>
        <th style="width:260px" class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <?php
          $isSelf   = (int)$u['id'] === (int)$me['id'];
          $isAdmin  = $u['role'] === 'admin';
          $canDelete = !$isSelf && (!$isAdmin || $adminCount > 1); // don’t delete self, don’t delete last admin
        ?>
        <tr>
          <td>#<?= (int)$u['id'] ?></td>
          <td><?= esc($u['name']) ?></td>
          <td><?= esc($u['email']) ?></td>
          <td>
            <form class="d-flex gap-2" method="post" action="<?= url('admin_user_update.php') ?>">
              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <select name="role" class="form-select form-select-sm" <?= $isSelf ? 'disabled' : '' ?>>
                <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>customer</option>
                <option value="admin"    <?= $u['role'] === 'admin'    ? 'selected' : '' ?>>admin</option>
              </select>
              <button class="btn btn-sm btn-outline-primary" <?= $isSelf ? 'disabled' : '' ?>>Save</button>
            </form>
          </td>
          <td><?= esc($u['created_at']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary"
               href="<?= url('admin_user_view.php?id='.(int)$u['id']) ?>">View</a>

            <form method="post" action="<?= url('admin_user_delete.php') ?>"
                  onsubmit="return confirm('Delete this user? This cannot be undone.');"
                  class="d-inline">
              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" <?= !$canDelete ? 'disabled' : '' ?>>Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$users): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">No users found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
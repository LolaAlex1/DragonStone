<?php
// /Applications/MAMP/htdocs/DRAGSTONE/admin_user_delete.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . url('admin_users.php'));
  exit;
}

if (!is_logged_in() || (current_user()['role'] ?? '') !== 'admin') {
  set_flash('Admins only.', 'info');
  header('Location: ' . url('auth_login.php'));
  exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
if ($userId <= 0) {
  set_flash('Invalid request.', 'info');
  header('Location: ' . url('admin_users.php'));
  exit;
}

$me = current_user();
if ($userId === (int)$me['id']) {
  set_flash("You can't delete yourself.", 'info');
  header('Location: ' . url('admin_users.php'));
  exit;
}

// Cannot delete last admin
$row = db()->prepare("SELECT role FROM users WHERE id=?");
$row->execute([$userId]);
$role = $row->fetchColumn();

if ($role === false) {
  set_flash('User not found.', 'info');
  header('Location: ' . url('admin_users.php'));
  exit;
}

if ($role === 'admin') {
  $adminCount = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
  if ($adminCount <= 1) {
    set_flash('Cannot delete the last admin.', 'info');
    header('Location: ' . url('admin_users.php'));
    exit;
  }
}

// Optional: also delete or anonymize their orders, depending on your policy
$del = db()->prepare("DELETE FROM users WHERE id=?");
$del->execute([$userId]);

set_flash('User deleted.');
header('Location: ' . url('admin_users.php'));
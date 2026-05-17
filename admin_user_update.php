<?php
// /Applications/MAMP/htdocs/DRAGSTONE/admin_user_update.php
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
$role   = trim($_POST['role'] ?? '');

$allowed = ['customer', 'admin'];
if ($userId <= 0 || !in_array($role, $allowed, true)) {
  set_flash('Invalid request.', 'info');
  header('Location: ' . url('admin_users.php'));
  exit;
}

$me = current_user();
if ($userId === (int)$me['id']) {
  set_flash("You can't change your own role.", 'info');
  header('Location: ' . url('admin_users.php'));
  exit;
}

// If demoting an admin, ensure at least one admin remains
$old = db()->prepare("SELECT role FROM users WHERE id=?");
$old->execute([$userId]);
$oldRole = $old->fetchColumn();

if ($oldRole === false) {
  set_flash('User not found.', 'info');
  header('Location: ' . url('admin_users.php'));
  exit;
}

if ($oldRole === 'admin' && $role !== 'admin') {
  $adminCount = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
  if ($adminCount <= 1) {
    set_flash('Cannot demote the last admin.', 'info');
    header('Location: ' . url('admin_users.php'));
    exit;
  }
}

$upd = db()->prepare("UPDATE users SET role=? WHERE id=?");
$upd->execute([$role, $userId]);

set_flash('Role updated.');
header('Location: ' . url('admin_users.php'));
<?php
// TEMPORARY HELPER - DELETE AFTER SUCCESS
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
  $pdo   = db();
  $email = 'alex.admin@dragonstone.com';
  $name  = 'Alex Admin';
  $plain = 'Admin@2024!';  // login password

  $hash = password_hash($plain, PASSWORD_DEFAULT);

  $pdo->beginTransaction();

  // upsert-ish
  $sel = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
  $sel->execute([$email]);
  if ($u = $sel->fetch()) {
    $upd = $pdo->prepare("UPDATE users SET name=?, password_hash=?, role='admin' WHERE id=?");
    $upd->execute([$name, $hash, $u['id']]);
    $msg = "Updated existing user to admin.";
  } else {
    $ins = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'admin')");
    $ins->execute([$name, $email, $hash]);
    $msg = "Inserted new admin user.";
  }

  $pdo->commit();
  echo "✅ {$msg}<br>Email: <b>{$email}</b><br>Password: <b>{$plain}</b>";
} catch (Throwable $e) {
  if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
  echo "❌ " . htmlspecialchars($e->getMessage());
}
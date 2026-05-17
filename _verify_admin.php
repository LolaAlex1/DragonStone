<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$pdo = db();
$stmt = $pdo->prepare("SELECT id, email, password_hash, role FROM users WHERE email = ?");
$stmt->execute(['admin@dragonstone.local']);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) { die("No such user"); }

echo "<pre>";
echo "Email: {$u['email']}\n";
echo "Role: {$u['role']}\n";
echo "Hash length: " . strlen($u['password_hash']) . "\n";
echo "Verify(admin123): " . (password_verify('admin123', $u['password_hash']) ? 'TRUE' : 'FALSE') . "\n";
echo "</pre>";
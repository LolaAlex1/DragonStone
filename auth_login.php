<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/session.php';

$errors = [];
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($email === '' || $pass === '') {
    $errors[] = 'Email and password are required.';
  } else {
    $stmt = db()->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($pass, $user['password_hash'])) {
      session_start();
      $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']];
      header('Location: ' . url('index.php'));
      exit;
    } else {
      $errors[] = 'Invalid credentials.';
    }
  }
}
?>
<h1 class="h3 mb-3">Login</h1>
<?php if ($errors): ?>
  <div class="alert alert-danger"><?php echo esc(implode('<br>', $errors)); ?></div>
<?php endif; ?>

<form method="post" style="max-width:420px">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input name="email" type="email" class="form-control" value="<?= esc($email) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input name="password" type="password" class="form-control" required>
  </div>
  <button class="btn btn-primary">Login</button>
  <a class="btn btn-link" href="<?= url('auth_register.php') ?>">Create account</a>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
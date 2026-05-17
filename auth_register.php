<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/session.php';

$errors = [];
$name  = trim($_POST['name']  ?? '');
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password']  ?? '';
$pass2 = $_POST['password2'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($name === '' || $email === '' || $pass === '' || $pass2 === '') {
    $errors[] = 'All fields are required.';
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format.';
  }
  if ($pass !== $pass2) {
    $errors[] = 'Passwords do not match.';
  }

  if (!$errors) {
    // check duplicate email
    $stmt = db()->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      $errors[] = 'Email already registered.';
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $ins = db()->prepare('INSERT INTO users(name,email,password_hash,role) VALUES (?,?,?, "customer")');
      $ins->execute([$name, $email, $hash]);

      // auto-login then redirect
      ensure_session();
      $_SESSION['user'] = ['name'=>$name,'email'=>$email,'role'=>'customer'];

      // redirect to catalog (change to index.php if you build a homepage)
      header('Location: ' . url('catalog.php'));
      exit;
    }
  }
}

// Only render HTML after all PHP logic above (so redirects work)
require_once __DIR__ . '/includes/header.php';
?>
<h1 class="h3 mb-3">Create an account</h1>
<?php if ($errors): ?>
  <div class="alert alert-danger"><?= esc(implode('<br>', $errors)) ?></div>
<?php endif; ?>

<form method="post" action="<?= esc($_SERVER['PHP_SELF']) ?>" class="row g-3" style="max-width:520px">
  <div class="col-12">
    <label class="form-label">Name</label>
    <input name="name" class="form-control" value="<?= esc($name) ?>" required>
  </div>
  <div class="col-12">
    <label class="form-label">Email</label>
    <input name="email" type="email" class="form-control" value="<?= esc($email) ?>" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Password</label>
    <input name="password" type="password" class="form-control" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Confirm Password</label>
    <input name="password2" type="password" class="form-control" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Register</button>
    <a class="btn btn-link" href="<?= url('auth_login.php') ?>">I already have an account</a>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
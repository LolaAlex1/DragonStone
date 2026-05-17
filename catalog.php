<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/session.php';

$cats = db()->query("SELECT id, name, slug FROM categories ORDER BY name")->fetchAll();
?>
<h1 class="h3 mb-4">Shop by Category</h1>

<?php if (empty($cats)): ?>
  <div class="alert alert-warning">No categories yet.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($cats as $c): ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <a class="text-decoration-none" href="<?= url('products.php?c=' . urlencode($c['slug'])) ?>">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h2 class="h5 mb-1"><?= esc($c['name']) ?></h2>
              <div class="text-muted small"><?= esc($c['slug']) ?></div>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

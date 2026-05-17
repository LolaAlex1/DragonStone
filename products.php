<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

$slug = $_GET['c'] ?? '';
if ($slug === '') { header('Location: ' . url('catalog.php')); exit; }

$stmt = db()->prepare("
  SELECT p.*, c.name AS category_name
  FROM products p
  JOIN categories c ON c.id = p.category_id
  WHERE c.slug = ?
  ORDER BY p.name
");
$stmt->execute([$slug]);
$rows = $stmt->fetchAll();

$categoryName = $rows[0]['category_name'] ?? '';
?>
<a href="<?= url('catalog.php') ?>" class="btn btn-link mb-3">&larr; All categories</a>
<h1 class="h3 mb-3"><?= esc($categoryName ?: 'Products') ?></h1>

<?php if (empty($rows)): ?>
  <div class="alert alert-info">No products found in this category.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($rows as $p): ?>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="card h-100">
          <?php if (!empty($p['image_url'])): ?>
            <img src="<?= esc($p['image_url']) ?>" class="card-img-top" alt="">
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h2 class="h5 mb-1"><?= esc($p['name']) ?></h2>
            <?php if (!is_null($p['carbon_score'])): ?>
              <div class="text-muted small mb-2">Carbon score: <?= (int)$p['carbon_score'] ?></div>
            <?php endif; ?>
            <div class="mt-auto d-flex align-items-center justify-content-between">
              <div class="fw-semibold">$<?= price($p['price']) ?></div>
              <a href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>" class="btn btn-primary btn-sm">View</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

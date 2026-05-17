<?php
// admin_products.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php'; // header shows nav + flashes

// ---- Admin guard ----
$me = current_user();
if (!$me || ($me['role'] ?? 'customer') !== 'admin') {
  set_flash('info', 'Admins only.');
  header('Location: ' . url('index.php'));
  exit;
}

/* ========= Helpers ========= */
function slugify(string $s): string {
  $s = strtolower(trim($s));
  $s = preg_replace('~[^a-z0-9]+~', '-', $s);
  $s = trim($s, '-');
  return $s ?: ('product-' . bin2hex(random_bytes(3)));
}

function fetch_categories(): array {
  $st = db()->query('SELECT id, name FROM categories ORDER BY name');
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

function fetch_product(int $id): ?array {
  $st = db()->prepare('SELECT * FROM products WHERE id=? LIMIT 1');
  $st->execute([$id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  return $row ?: null;
}

function fetch_products(): array {
  $sql = 'SELECT p.*, c.name AS category_name
          FROM products p
          LEFT JOIN categories c ON c.id = p.category_id
          ORDER BY p.id DESC';
  return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/* ========= Handle actions ========= */
$errors = [];
$editing = null;
$cats = fetch_categories();

if (isset($_GET['delete'])) {
  // ---- Delete product ----
  $id = (int)$_GET['delete'];
  $p = fetch_product($id);
  if ($p) {
    // remove local image file if we own it
    if (!empty($p['image_url']) && is_local_upload_path($p['image_url'])) {
      @unlink(local_upload_fs_path($p['image_url']));
    }
    $del = db()->prepare('DELETE FROM products WHERE id=?');
    $del->execute([$id]);
    set_flash('ok', 'Product deleted.');
  }
  header('Location: ' . url('admin_products.php'));
  exit;
}

if (isset($_GET['edit'])) {
  $editing = fetch_product((int)$_GET['edit']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // We support both create and edit through a hidden field 'pid'
  $pid = (int)($_POST['pid'] ?? 0);

  $name        = trim($_POST['name'] ?? '');
  $category_id = (int)($_POST['category_id'] ?? 0);
  $price       = (float)($_POST['price'] ?? 0);
  $stock       = (int)($_POST['stock'] ?? 0);
  $image_url   = trim($_POST['image_url'] ?? ''); // stay as-is unless file provided

  if ($name === '' || $category_id <= 0 || $price < 0) {
    $errors[] = 'Please fill name, category and price correctly.';
  }

  // prefer uploaded file over the typed URL
  try {
    $uploaded = save_uploaded_image($_FILES['image_file'] ?? null);
    if ($uploaded) {
      $image_url = $uploaded; // e.g. "uploads/xxx.jpg"
    }
  } catch (Exception $e) {
    $errors[] = 'Image upload error: ' . $e->getMessage();
  }

  if (!$errors) {
    if ($pid > 0) {
      // ---- Update existing ----
      $current = fetch_product($pid);
      if (!$current) {
        $errors[] = 'Product not found.';
      } else {
        // If a new upload replaced an old local file, delete the old file
        if (!empty($uploaded) && !empty($current['image_url']) && is_local_upload_path($current['image_url'])) {
          @unlink(local_upload_fs_path($current['image_url']));
        }
        $slug = $current['slug'] ?: slugify($name);
        $stmt = db()->prepare('UPDATE products
          SET name=?, slug=?, category_id=?, price=?, stock=?, image_url=?
          WHERE id=?');
        $stmt->execute([$name, $slug, $category_id, $price, $stock, $image_url, $pid]);
        set_flash('ok', 'Product updated.');
        header('Location: ' . url('admin_products.php?edit=' . $pid));
        exit;
      }
    } else {
      // ---- Create new ----
      $slug = slugify($name);
      $ins = db()->prepare('INSERT INTO products (name, slug, category_id, price, stock, image_url)
                           VALUES (?,?,?,?,?,?)');
      $ins->execute([$name, $slug, $category_id, $price, $stock, $image_url]);
      set_flash('ok', 'Product created.');
      header('Location: ' . url('admin_products.php'));
      exit;
    }
  }
}

// For display
$rows = fetch_products();
?>

<style>
  .admin-card { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; }
  .admin-card .card-header{ background:#f9fafb; font-weight:600; }
  .btn-primary { background:#2563eb; border:none; }
  .btn-danger { background:#dc2626; border:none; }
  .btn-secondary { background:#374151; border:none; }
  .form-text { color:#6b7280; }
  .thumb { width:56px; height:56px; object-fit:cover; border-radius:6px; border:1px solid #e5e7eb; }
</style>

<h1 class="h3 mb-4">🛠 Manage Products</h1>

<div class="row g-4">
  <!-- Create / Edit form -->
  <div class="col-lg-5">
    <div class="admin-card">
      <div class="card-header p-3">
        <?= $editing ? 'Edit Product' : 'Add New Product' ?>
      </div>
      <div class="card-body p-3">
        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <?= esc(implode('<br>', $errors)) ?>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="pid" value="<?= $editing ? (int)$editing['id'] : 0 ?>">

          <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" required
                   value="<?= esc($editing['name'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
              <option value="">-- Select --</option>
              <?php foreach ($cats as $c): ?>
                <option value="<?= (int)$c['id'] ?>"
                  <?= isset($editing['category_id']) && $editing['category_id']==$c['id'] ? 'selected' : '' ?>>
                  <?= esc($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Price (USD)</label>
            <input name="price" type="number" step="0.01" min="0" class="form-control" required
                   value="<?= esc($editing['price'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input name="stock" type="number" min="0" class="form-control"
                   value="<?= esc($editing['stock'] ?? '0') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Image URL (optional)</label>
            <input name="image_url" class="form-control"
                   value="<?= esc($editing['image_url'] ?? '') ?>">
            <div class="form-text">You can paste an external URL here, or upload a file below (which will override this).</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Upload Image (optional)</label>
            <input type="file" name="image_file" accept="image/*" class="form-control">
            <div class="form-text">Allowed: JPG, PNG, WEBP, GIF (max 3MB).</div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Create Product' ?></button>
            <?php if ($editing): ?>
              <a href="<?= url('admin_products.php') ?>" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- List -->
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="card-header p-3">All Products</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $p): ?>
              <?php
                $img = $p['image_url'] ?? '';
                if ($img && !preg_match('~^https?://~i', $img)) {
                  $img = url($img); // local upload → full URL
                }
              ?>
              <tr>
                <td>#<?= (int)$p['id'] ?></td>
                <td>
                  <?php if (!empty($img)): ?>
                    <img src="<?= esc($img) ?>" class="thumb" alt="<?= esc($p['name']) ?>">
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td><?= esc($p['name']) ?></td>
                <td><?= esc($p['category_name'] ?? '-') ?></td>
                <td>$<?= price($p['price']) ?></td>
                <td><?= (int)$p['stock'] ?></td>
                <td>
                  <a class="btn btn-sm btn-primary" href="<?= url('admin_products.php?edit=' . (int)$p['id']) ?>">Edit</a>
                  <a class="btn btn-sm btn-danger"
                     href="<?= url('admin_products.php?delete=' . (int)$p['id']) ?>"
                     onclick="return confirm('Delete this product?');">Delete</a>
                  <a class="btn btn-sm btn-secondary"
                     href="<?= url('product.php?id=' . (int)$p['id']) ?>" target="_blank">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
              <tr><td colspan="7" class="text-center text-muted p-4">No products yet.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
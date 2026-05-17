<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="bg-dark text-light py-5">
  <div class="container text-center">
    <h1 class="display-5 fw-bold">Dragonstone: Sustainable Essentials for Everyday Life</h1>
    <p class="lead mt-3">Clean, modern, nature-inspired products. Transparent sourcing. Real impact.</p>
    <div class="mt-4">
      <a href="<?= url('catalog.php') ?>" class="btn btn-success btn-lg me-2">Shop Catalog</a>
      <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="py-5">
  <div class="container">
    <h2 class="h3 mb-4">Shop by Category</h2>
    <div class="row g-3">
      <?php
      $cats = [
        ['Cleaning & Household','cleaning-household'],
        ['Kitchen & Dining','kitchen-dining'],
        ['Home Décor & Living','home-decor'],
        ['Bathroom & Personal Care','bathroom-personal'],
        ['Lifestyle & Wellness','lifestyle-wellness'],
        ['Kids & Pets','kids-pets'],
        ['Outdoor & Garden','outdoor-garden'],
      ];
      foreach ($cats as [$label,$slug]): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <a class="text-decoration-none" href="<?= url('products.php?category=' . urlencode($slug)) ?>">
            <div class="card h-100 shadow-sm">
              <div class="card-body d-flex align-items-center justify-content-center">
                <span class="fw-semibold text-center"><?= htmlspecialchars($label) ?></span>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section id="features" class="py-5 bg-light">
  <div class="container">
    <h2 class="h3 mb-4">Why Dragonstone?</h2>
    <div class="row g-3">
      <div class="col-md-6 col-lg-3">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5">Carbon Footprint</h3>
            <p class="mb-2">See the environmental impact of each product.</p>
            <a class="btn btn-sm btn-outline-secondary disabled">Coming soon</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5">Subscriptions</h3>
            <p class="mb-2">Auto-deliver essentials like cleaning supplies.</p>
            <a class="btn btn-sm btn-outline-secondary disabled">Coming soon</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5">EcoPoints</h3>
            <p class="mb-2">Earn rewards for purchases & community actions.</p>
            <a class="btn btn-sm btn-outline-secondary disabled">Coming soon</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card h-100">
          <div class="card-body">
            <h3 class="h5">Community Hub</h3>
            <p class="mb-2">Share tips, DIY projects, and challenges.</p>
            <a class="btn btn-sm btn-outline-secondary disabled">Coming soon</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED -->
<section class="py-5">
  <div class="container">
    <h2 class="h3 mb-4">Featured Products</h2>
    <div class="row g-3">
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <img src="https://images.unsplash.com/photo-1595433707802-6b2626ef1c86?q=80&w=1200&auto=format&fit=crop" class="card-img-top" alt="">
          <div class="card-body">
            <h3 class="h5">Bamboo Cutting Board</h3>
            <p class="mb-3 text-muted">Durable, sustainable, and naturally antimicrobial.</p>
            <a href="<?= url('product.php?slug=bamboo-cutting-board') ?>" class="btn btn-outline-primary">View</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <img src="https://images.unsplash.com/photo-1520975916090-3105956dac38?q=80&w=1200&auto=format&fit=crop" class="card-img-top" alt="">
          <div class="card-body">
            <h3 class="h5">Refillable Shampoo Bottle (500ml)</h3>
            <p class="mb-3 text-muted">Less plastic, same clean. Bring it back for refills.</p>
            <a href="<?= url('product.php?slug=refillable-shampoo-bottle-500ml') ?>" class="btn btn-outline-primary">View</a>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <img src="https://images.unsplash.com/photo-1503602642458-232111445657?q=80&w=1200&auto=format&fit=crop" class="card-img-top" alt="">
          <div class="card-body">
            <h3 class="h5">Solar-Powered Garden Light</h3>
            <p class="mb-3 text-muted">Bright paths, zero wiring, zero emissions.</p>
            <a href="<?= url('product.php?slug=solar-powered-garden-light') ?>" class="btn btn-outline-primary">View</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- NEWSLETTER -->
<section class="py-5 bg-dark text-light">
  <div class="container">
    <div class="row align-items-center g-3">
      <div class="col-lg-6">
        <h2 class="h3 mb-2">Stay in the loop</h2>
        <p class="mb-0">Get new product drops, tips & challenges in your inbox.</p>
      </div>
      <div class="col-lg-6">
        <form class="d-flex gap-2">
          <input type="email" class="form-control" placeholder="you@example.com" required>
          <button class="btn btn-success">Subscribe</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
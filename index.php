<?php
/**
 * ClassicCart - Home Page
 */
$page_title = 'ClassicCart - Shop Smart. Shop Simple.';
require_once 'includes/auth.php';

$categories = get_categories(true);
$featured = db()->query(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.status = 1
     ORDER BY (p.rating * p.stock) DESC
     LIMIT 8'
)->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg">
        <div class="container hero-inner">
            <div class="hero-content">
                <h1>Shop Smart. Shop Simple.</h1>
                <p class="subtitle">Discover quality products at affordable prices.</p>
                <div class="btn-holder">
                    <a href="shop.php" class="btn btn-white btn-lg">Shop Now</a>
                    <a href="shop.php#categories" class="btn btn-outline btn-lg">Explore Categories</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section" id="categories">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Shop by Category</h2>
            <a href="shop.php" class="section-link">View all products &rarr;</a>
        </div>
        <?php if (!empty($categories)) { ?>
            <div class="category-grid">
                <?php foreach ($categories as $cat) { ?>
                    <a href="shop.php?category=<?php echo (int)$cat['id']; ?>" class="category-card">
                        <div class="cat-img">
                            <img src="<?php echo e(category_image($cat['image'])); ?>" alt="<?php echo e($cat['name']); ?>" loading="lazy">
                        </div>
                        <div class="cat-name"><?php echo e($cat['name']); ?></div>
                        <div class="cat-count"><?php echo (int)$cat['product_count']; ?> products</div>
                    </a>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="text-muted">No categories available yet.</p>
        <?php } ?>
    </div>
</section>

<!-- Featured Products -->
<section class="section" style="background:#fff; border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Products</h2>
            <a href="shop.php" class="section-link">View all &rarr;</a>
        </div>
        <?php if (!empty($featured)) { ?>
            <div class="product-grid">
                <?php foreach ($featured as $product) { product_card($product); } ?>
            </div>
        <?php } else { ?>
            <p class="text-muted">Products coming soon.</p>
        <?php } ?>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section">
    <div class="container">
        <div class="section-header text-center" style="justify-content:center;">
            <h2 class="section-title">Why Choose Us</h2>
        </div>
        <div class="why-grid">
            <div class="why-card">
                <div class="why-icon">&#128274;</div>
                <h4>Secure Payment</h4>
                <p>Your payments and personal details are always safe and encrypted.</p>
            </div>
            <div class="why-card">
                <div class="why-icon">&#128666;</div>
                <h4>Fast Delivery</h4>
                <p>Quick and reliable doorstep delivery across the country.</p>
            </div>
            <div class="why-card">
                <div class="why-icon">&#11088;</div>
                <h4>Quality Products</h4>
                <p>We hand-pick quality products from trusted brands.</p>
            </div>
            <div class="why-card">
                <div class="why-icon">&#8635;</div>
                <h4>Easy Returns</h4>
                <p>Simple and hassle-free returns within the return window.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
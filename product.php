<?php
/**
 * ClassicCart - Product Details Page
 */
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$product = get_product($id, true);

if (!$product) {
    include 'includes/header.php';
    ?>
    <section class="page-header">
        <div class="container"><h1>Product Not Found</h1></div>
    </section>
    <div class="container">
        <div class="empty-state card">
            <div class="empty-icon">&#128533;</div>
            <h3>Oops! Product not found.</h3>
            <p>The product you are looking for may have been removed.</p>
            <a href="shop.php" class="btn btn-primary">Back to Shop</a>
        </div>
    </div>
    <?php
    include 'includes/footer.php';
    exit;
}

$page_title = $product['name'] . ' - ClassicCart';

/* Mark wishlist state */
$inWishlist = false;
$user = current_user();
if ($user) {
    $stmt = db()->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ? LIMIT 1');
    $stmt->execute(array($user['id'], $product['id']));
    $inWishlist = (bool)$stmt->fetch();
}

/* Related products */
$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p JOIN categories c ON c.id = p.category_id
     WHERE p.category_id = ? AND p.id != ? AND p.status = 1
     ORDER BY RAND() LIMIT 4'
);
$stmt->execute(array($product['category_id'], $product['id']));
$related = $stmt->fetchAll();

$price      = product_price($product);
$percent    = discount_percent($product['price'], $product['discount_price']);
$outOfStock = ((int)$product['stock'] <= 0);

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="index.php">Home</a> &raquo; <a href="shop.php">Shop</a> &raquo; <a href="shop.php?category=<?php echo (int)$product['category_id']; ?>"><?php echo e($product['category_name']); ?></a> &raquo; <?php echo e($product['name']); ?></div>
    </div>
</section>

<div class="container">
    <div class="card" style="padding:28px;">
        <div class="product-detail">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="<?php echo e(product_image($product['image'])); ?>" alt="<?php echo e($product['name']); ?>">
                </div>
            </div>

            <div class="product-info">
                <div class="product-category"><?php echo e($product['category_name']); ?></div>
                <h1><?php echo e($product['name']); ?></h1>
                <div class="info-meta">
                    <span>Product ID: #<?php echo (int)$product['id']; ?></span>
                </div>
                <div class="info-rating">
                    <?php echo product_rating($product); ?>
                    <span class="text-muted" style="font-size:13px;">(<?php echo number_format((float)$product['rating'], 1); ?> rating)</span>
                </div>

                <div class="info-price">
                    <span class="now"><?php echo money($price); ?></span>
                    <?php if ($percent > 0) { ?>
                        <span class="was"><del><?php echo money($product['price']); ?></del></span>
                        <span class="save">Save <?php echo $percent; ?>%</span>
                    <?php } ?>
                </div>

                <div class="stock-indicator <?php echo $outOfStock ? 'out-stock' : (((int)$product['stock'] <= 10) ? 'low-stock' : 'in-stock'); ?>">
                    <span class="dot"></span>
                    <span class="text">
                        <?php if ($outOfStock) { ?>
                            Out of Stock
                        <?php } elseif ((int)$product['stock'] <= 10) { ?>
                            Only <?php echo (int)$product['stock']; ?> left in stock
                        <?php } else { ?>
                            In Stock (<?php echo (int)$product['stock']; ?> available)
                        <?php } ?>
                    </span>
                </div>

                <?php if (!$outOfStock) { ?>
                    <div class="product-actions-row">
                        <div class="qty-selector">
                            <button type="button" class="qty-minus">&minus;</button>
                            <input type="text" value="1" max="<?php echo (int)$product['stock']; ?>" readonly>
                            <button type="button" class="qty-plus">+</button>
                        </div>
                        <button type="button" class="btn btn-accent btn-lg add-to-cart" data-id="<?php echo (int)$product['id']; ?>">
                            &#128722; Add to Cart
                        </button>
                        <button type="button" class="btn btn-wishlist btn-lg wishlist-btn <?php echo $inWishlist ? 'active' : ''; ?>"
                            data-id="<?php echo (int)$product['id']; ?>"
                            title="<?php echo $inWishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">&#9829;</button>
                    </div>
                <?php } else { ?>
                    <div class="product-actions-row">
                        <button type="button" class="btn btn-accent btn-lg" disabled>&#128722; Add to Cart</button>
                        <button type="button" class="btn btn-wishlist btn-lg wishlist-btn <?php echo $inWishlist ? 'active' : ''; ?>"
                            data-id="<?php echo (int)$product['id']; ?>">&#9829;</button>
                    </div>
                <?php } ?>

                <div class="product-desc">
                    <h3>Product Description</h3>
                    <p><?php echo nl2br(e($product['description'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($related)) { ?>
        <section class="section" style="padding-bottom:0;">
            <div class="section-header">
                <h2 class="section-title">Related Products</h2>
            </div>
            <div class="product-grid">
                <?php foreach ($related as $product) { product_card($product); } ?>
            </div>
        </section>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
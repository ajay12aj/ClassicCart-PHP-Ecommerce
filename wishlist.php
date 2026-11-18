<?php
/**
 * ClassicCart - Wishlist
 * Handles AJAX actions: add, remove, move_to_cart.
 */
$page_title = 'My Wishlist - ClassicCart';
require_once 'includes/auth.php';

$user = current_user();

/* ---------------- AJAX actions ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] == 1) {
    if (!$user) {
        json_out(array('error' => 'Please login to continue.', 'login' => true));
    }

    $action    = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $p = get_product($productId, true);

    switch ($action) {
        case 'add':
            if (!$p) {
                json_out(array('error' => 'Product not found.'));
            }
            $stmt = db()->prepare('INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)');
            $stmt->execute(array($user['id'], $productId));
            json_out(array(
                'success' => true,
                'message' => 'Added to wishlist.',
                'wishlist_count' => wishlist_count(),
            ));

        case 'remove':
            db()->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')->execute(array($user['id'], $productId));
            json_out(array(
                'success' => true,
                'message' => 'Removed from wishlist.',
                'removed' => true,
                'wishlist_count' => wishlist_count(),
            ));

        case 'move_to_cart':
            if (!$p) {
                json_out(array('error' => 'Product not found.'));
            }
            if ((int)$p['stock'] <= 0) {
                json_out(array('error' => 'This product is currently out of stock.'));
            }
            $stmt = db()->prepare('SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?');
            $stmt->execute(array($user['id'], $productId));
            $row = $stmt->fetch();
            $newQty = $row ? ($row['quantity'] + 1) : 1;
            if ($newQty > (int)$p['stock']) {
                $newQty = (int)$p['stock'];
            }
            if ($row) {
                db()->prepare('UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?')
                    ->execute(array($newQty, $user['id'], $productId));
            } else {
                db()->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)')
                    ->execute(array($user['id'], $productId, $newQty));
            }
            db()->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')->execute(array($user['id'], $productId));
            json_out(array(
                'success' => true,
                'message' => 'Moved to cart.',
                'cart_count' => cart_count(),
                'wishlist_count' => wishlist_count(),
            ));

        default:
            json_out(array('error' => 'Invalid request.'));
    }
}

/* ---------------- Display wishlist ---------------- */
if (!$user) {
    flash_set('warning', 'Please login to view your wishlist.');
    remember_redirect();
    header('Location: login.php');
    exit;
}

$stmt = db()->prepare(
    'SELECT w.id AS wish_id, w.created_at AS w_created, p.*, c.name AS category_name
     FROM wishlist w
     JOIN products p ON p.id = w.product_id
     JOIN categories c ON c.id = p.category_id
     WHERE w.user_id = ?
     ORDER BY w.created_at DESC'
);
$stmt->execute(array($user['id']));
$wishlistItems = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>My Wishlist</h1>
        <div class="breadcrumb"><a href="index.php">Home</a> &raquo; Wishlist</div>
    </div>
</section>

<div class="container">
    <?php if (empty($wishlistItems)) { ?>
        <div class="empty-state card">
            <div class="empty-icon">&#9825;</div>
            <h3>Your wishlist is empty</h3>
            <p>Save products you love and find them here anytime.</p>
            <a href="shop.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php } else { ?>
        <div class="product-grid">
            <?php foreach ($wishlistItems as $item) { ?>
                <div class="product-card wishlist-item">
                    <a class="product-img-link" href="product.php?id=<?php echo (int)$item['id']; ?>">
                        <div class="product-img">
                            <img src="<?php echo e(product_image($item['image'])); ?>" alt="<?php echo e($item['name']); ?>" loading="lazy">
                        </div>
                    </a>
                    <?php if ((int)$item['stock'] <= 0) { ?>
                        <span class="stock-badge out">Out of Stock</span>
                    <?php } ?>
                    <span class="discount-badge" style="right:10px; left:auto;">&#9829;</span>
                    <div class="product-body">
                        <div class="product-category"><?php echo e($item['category_name']); ?></div>
                        <h3 class="product-name">
                            <a href="product.php?id=<?php echo (int)$item['id']; ?>"><?php echo e($item['name']); ?></a>
                        </h3>
                        <?php echo product_rating($item); ?>
                        <div class="product-price">
                            <span class="price"><?php echo money(product_price($item)); ?></span>
                            <?php if (discount_percent($item['price'], $item['discount_price']) > 0) { ?>
                                <del class="old-price"><?php echo money($item['price']); ?></del>
                            <?php } ?>
                        </div>
                        <div class="product-actions">
                            <button type="button" class="btn btn-primary btn-sm move-to-cart flex-fill"
                                data-id="<?php echo (int)$item['id']; ?>"
                                <?php echo (int)$item['stock'] <= 0 ? 'disabled' : ''; ?>>
                                Move to Cart
                            </button>
                            <button type="button" class="btn btn-wishlist btn-sm active wishlist-btn"
                                data-id="<?php echo (int)$item['id']; ?>" title="Remove from wishlist">&#9829;</button>
                            <a class="btn btn-outline btn-sm" href="product.php?id=<?php echo (int)$item['id']; ?>">Details</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
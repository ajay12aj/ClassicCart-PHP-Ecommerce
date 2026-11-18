<?php
/**
 * ClassicCart - Global Helper Functions
 */

require_once __DIR__ . '/../config/database.php';

/* ---------------------------------------------------------------------
   Session helpers
--------------------------------------------------------------------- */
function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/** Set a flash message that is shown once on the next page load. */
function flash_set(string $type, string $message): void
{
    start_session();
    $_SESSION['flash'][$type][] = $message;
}

/** Get and clear all pending flash messages. */
function flash_get(): array
{
    start_session();
    $messages = $_SESSION['flash'] ?? array();
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------------------------------------------------------------------
   Output helpers
--------------------------------------------------------------------- */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function is_ajax(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_out(array $data): void
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/* ---------------------------------------------------------------------
   Currency & formatting
--------------------------------------------------------------------- */
function money($amount): string
{
    return 'Rs. ' . number_format((float)$amount, 2);
}

function format_date(string $datetime): string
{
    return date('d M Y, h:i A', strtotime($datetime));
}

function discount_percent($price, $discountPrice): int
{
    if ($discountPrice > 0 && (float)$price > 0 && (float)$price > (float)$discountPrice) {
        return (int)round(((float)$price - (float)$discountPrice) / (float)$price * 100);
    }
    return 0;
}

/* ---------------------------------------------------------------------
   CSRF protection
--------------------------------------------------------------------- */
function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool
{
    start_session();
    $token = $_POST['csrf_token'] ?? '';
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* ---------------------------------------------------------------------
   E-commerce helpers
--------------------------------------------------------------------- */
function base_url(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $dir = rtrim($dir, '/');
    if (strpos($dir, '/admin') !== false) {
        $dir = substr($dir, 0, strpos($dir, '/admin'));
    }
    return $dir;
}

function product_image(string $image): string
{
    if ($image === '' || $image === null) {
        return base_url() . '/uploads/products/placeholder.jpg';
    }
    return base_url() . '/uploads/products/' . rawurlencode($image);
}

function product_price(array $p): float
{
    return ($p['discount_price'] > 0) ? (float)$p['discount_price'] : (float)$p['price'];
}

function active_price(array $p): float
{
    return product_price($p);
}

/** Get total number of items in the logged in user's cart. */
function cart_count(): int
{
    $user = current_user();
    if (!$user) {
        return 0;
    }
    $stmt = db()->prepare('SELECT COALESCE(SUM(quantity),0) AS total FROM cart WHERE user_id = ?');
    $stmt->execute(array($user['id']));
    return (int)$stmt->fetch()['total'];
}

function wishlist_count(): int
{
    $user = current_user();
    if (!$user) {
        return 0;
    }
    $stmt = db()->prepare('SELECT COUNT(*) AS total FROM wishlist WHERE user_id = ?');
    $stmt->execute(array($user['id']));
    return (int)$stmt->fetch()['total'];
}

function get_categories(bool $activeOnly = true): array
{
    $sql = 'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 1) AS product_count
            FROM categories c';
    if ($activeOnly) {
        $sql .= ' WHERE c.status = 1';
    }
    $sql .= ' ORDER BY c.name';
    return db()->query($sql)->fetchAll();
}

/** Returns cart rows joined with product info for the current user. */
function get_cart_items(): array
{
    $user = current_user();
    if (!$user) {
        return array();
    }
    $stmt = db()->prepare(
        'SELECT c.id AS cart_id, c.quantity, p.*, cat.name AS category_name
         FROM cart c
         JOIN products p ON p.id = c.product_id
         JOIN categories cat ON cat.id = p.category_id
         WHERE c.user_id = ?
         ORDER BY c.created_at DESC'
    );
    $stmt->execute(array($user['id']));
    return $stmt->fetchAll();
}

function cart_totals(): array
{
    $items    = get_cart_items();
    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += product_price($item) * (int)$item['quantity'];
    }
    $shipping = shipping_fee($subtotal);
    return array(
        'items'    => $items,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total'    => $subtotal + $shipping,
    );
}

function shipping_fee(float $subtotal): float
{
    if ($subtotal <= 0) {
        return 0;
    }
    return ($subtotal >= 999) ? 0.0 : 50.0;
}

function order_status_classes(string $status): string
{
    $map = array(
        'Pending'    => 'badge-warning',
        'Confirmed'  => 'badge-info',
        'Processing' => 'badge-primary',
        'Shipped'    => 'badge-secondary',
        'Delivered'  => 'badge-success',
        'Cancelled'  => 'badge-danger',
    );
    return $map[$status] ?? 'badge-secondary';
}

/* ---------------------------------------------------------------------
   Rating display
--------------------------------------------------------------------- */
function stars(float $rating): string
{
    $rating = max(0, min(5, (float)$rating));
    $html   = '<span class="rating-stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $html .= '<span class="star full">&#9733;</span>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<span class="star half">&#9733;</span>';
        } else {
            $html .= '<span class="star empty">&#9733;</span>';
        }
    }
    $html .= '</span>';
    return $html;
}

function product_rating(array $p): string
{
    return stars((float)$p['rating']);
}

/* ---------------------------------------------------------------------
   Product card (reusable)
--------------------------------------------------------------------- */
function product_card(array $p): void
{
    $price      = product_price($p);
    $percent    = discount_percent($p['price'], $p['discount_price']);
    $image      = product_image($p['image']);
    $lowStock   = ((int)$p['stock'] > 0 && (int)$p['stock'] <= 5);
    $outOfStock = ((int)$p['stock'] <= 0);
    ?>
    <div class="product-card">
        <a class="product-img-link" href="product.php?id=<?php echo (int)$p['id']; ?>">
            <div class="product-img">
                <img src="<?php echo e($image); ?>" alt="<?php echo e($p['name']); ?>" loading="lazy">
            </div>
        </a>
        <?php if ($percent > 0) { ?>
            <span class="discount-badge">-<?php echo (int)$percent; ?>%</span>
        <?php } ?>
        <?php if ($outOfStock) { ?>
            <span class="stock-badge out">Out of Stock</span>
        <?php } elseif ($lowStock) { ?>
            <span class="stock-badge low">Only <?php echo (int)$p['stock']; ?> left</span>
        <?php } ?>
        <div class="product-body">
            <div class="product-category"><?php echo e($p['category_name'] ?? ''); ?></div>
            <h3 class="product-name">
                <a href="product.php?id=<?php echo (int)$p['id']; ?>"><?php echo e($p['name']); ?></a>
            </h3>
            <?php echo product_rating($p); ?>
            <div class="product-price">
                <span class="price"><?php echo money($price); ?></span>
                <?php if ($percent > 0) { ?>
                    <del class="old-price"><?php echo money($p['price']); ?></del>
                <?php } ?>
            </div>
            <div class="product-actions">
                <button type="button" class="btn btn-primary btn-sm add-to-cart flex-fill"
                    data-id="<?php echo (int)$p['id']; ?>"
                    <?php echo $outOfStock ? 'disabled' : ''; ?>>
                    Add to Cart
                </button>
                <button type="button" class="btn btn-wishlist btn-sm wishlist-btn" data-id="<?php echo (int)$p['id']; ?>"
                    title="Add to wishlist">&#9829;</button>
                <a class="btn btn-outline btn-sm" href="product.php?id=<?php echo (int)$p['id']; ?>">Details</a>
            </div>
        </div>
    </div>
    <?php
}

/** Get product by id including category name (active products only when requested). */
function get_product(int $id, bool $activeOnly = true): ?array
{
    $sql = 'SELECT p.*, c.name AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.id = ?';
    if ($activeOnly) {
        $sql .= ' AND p.status = 1';
    }
    $stmt = db()->prepare($sql);
    $stmt->execute(array($id));
    $product = $stmt->fetch();
    return $product ?: null;
}

/* ---------------------------------------------------------------------
   File upload
--------------------------------------------------------------------- */
/* Absolute server path to the product image folder (works from /admin too). */
define('UPLOAD_DIR_ABS', __DIR__ . '/../uploads/products/');
const UPLOAD_DIR = 'uploads/products/';

define('CATEGORY_UPLOAD_DIR_ABS', __DIR__ . '/../uploads/categories/');
const CATEGORY_UPLOAD_DIR = 'uploads/categories/';

/** Build the public URL for a category image. */
function category_image(?string $image): string
{
    if ($image === '' || $image === null) {
        return base_url() . '/uploads/categories/placeholder.jpg';
    }
    return base_url() . '/uploads/categories/' . rawurlencode($image);
}

/**
 * Validate and store an uploaded image into a target directory.
 * Returns array with 'error' => bool, 'file' => stored filename, 'message'.
 */
function upload_image_to(array $file, string $targetAbs, string $prefix = 'img'): array
{
    $allowedTypes = array('image/jpeg', 'image/png', 'image/webp');
    $allowedExt   = array('jpg', 'jpeg', 'png', 'webp');
    $maxSize      = 2 * 1024 * 1024; /* 2 MB */

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('error' => true, 'file' => '', 'message' => 'Image upload failed. Please try again.');
    }
    if ($file['size'] > $maxSize) {
        return array('error' => true, 'file' => '', 'message' => 'Image is too large. Maximum allowed size is 2MB.');
    }

    $type = $file['type'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($type, $allowedTypes) || !in_array($ext, $allowedExt)) {
        return array('error' => true, 'file' => '', 'message' => 'Only JPG, PNG and WEBP image files are allowed.');
    }

    $filename = $prefix . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target   = $targetAbs . $filename;

    if (!is_dir($targetAbs)) {
        mkdir($targetAbs, 0777, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return array('error' => true, 'file' => '', 'message' => 'Could not save the image file.');
    }

    return array('error' => false, 'file' => $filename, 'message' => '');
}

/**
 * Validate and store an uploaded product image.
 * Returns array with 'error' => bool, 'file' => stored filename, 'message'.
 */
function upload_product_image(array $file): array
{
    return upload_image_to($file, UPLOAD_DIR_ABS, 'p');
}

/** Validate and store an uploaded category image. */
function upload_category_image(array $file): array
{
    return upload_image_to($file, CATEGORY_UPLOAD_DIR_ABS, 'c');
}

/** Safely remove a category image (keeps bundled sample placeholders intact). */
function delete_category_image(?string $filename): void
{
    if ($filename === '' || $filename === null) {
        return;
    }
    /* Do not delete bundled sample images. */
    if ((bool)preg_match('/^c\d+\.jpg$/', $filename)) {
        return;
    }
    $file = CATEGORY_UPLOAD_DIR_ABS . $filename;
    if (is_file($file)) {
        @unlink($file);
    }
}

/** Safely remove an old product image (keeps sample placeholders intact). */
function delete_product_image(?string $filename): void
{
    if ($filename === '' || $filename === null) {
        return;
    }
    /* Do not delete bundled sample images. */
    if ((bool)preg_match('/^p\d+\.jpg$/', $filename)) {
        return;
    }
    $file = UPLOAD_DIR_ABS . $filename;
    if (is_file($file)) {
        @unlink($file);
    }
}
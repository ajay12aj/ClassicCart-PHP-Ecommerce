<?php
/**
 * ClassicCart - Admin Delete Product (POST only)
 */
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    flash_set('error', 'Invalid request.');
    header('Location: products.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$product = get_product($id, false);

if (!$product) {
    flash_set('error', 'Product not found.');
    header('Location: products.php');
    exit;
}

try {
    db()->prepare('DELETE FROM products WHERE id = ?')->execute(array($id));
    delete_product_image($product['image']);
    flash_set('success', 'Product deleted successfully.');
} catch (Exception $ex) {
    flash_set('error', 'Could not delete this product. It may be referenced in existing orders.');
}

header('Location: products.php');
exit;
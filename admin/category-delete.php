<?php
/**
 * ClassicCart - Admin Delete Category (POST only)
 * Deletion is blocked when the category still has products.
 */
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    flash_set('error', 'Invalid request.');
    header('Location: categories.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute(array($id));
$category = $stmt->fetch();

if (!$category) {
    flash_set('error', 'Category not found.');
    header('Location: categories.php');
    exit;
}

$stmt = db()->prepare('SELECT COUNT(*) AS c FROM products WHERE category_id = ?');
$stmt->execute(array($id));
$productCount = (int)$stmt->fetch()['c'];

if ($productCount > 0) {
    flash_set('error', 'This category has ' . $productCount . ' product(s). Move or delete the products first.');
    header('Location: categories.php');
    exit;
}

try {
    db()->prepare('DELETE FROM categories WHERE id = ?')->execute(array($id));
    delete_category_image($category['image']);
    flash_set('success', 'Category deleted successfully.');
} catch (Exception $ex) {
    flash_set('error', 'Could not delete this category.');
}

header('Location: categories.php');
exit;
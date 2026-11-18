<?php
/**
 * ClassicCart - Admin Update Order Status (POST only)
 */
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    flash_set('error', 'Invalid request.');
    header('Location: orders.php');
    exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
$status  = $_POST['status'] ?? '';
$allowed = array('Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled');

if (!in_array($status, $allowed)) {
    flash_set('error', 'Invalid order status.');
    header('Location: order-details.php?id=' . $orderId);
    exit;
}

$stmt = db()->prepare('SELECT id FROM orders WHERE id = ?');
$stmt->execute(array($orderId));
if (!$stmt->fetch()) {
    flash_set('error', 'Order not found.');
    header('Location: orders.php');
    exit;
}

db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute(array($status, $orderId));
flash_set('success', 'Order status updated to "' . $status . '".');
header('Location: order-details.php?id=' . $orderId);
exit;
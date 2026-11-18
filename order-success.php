<?php
/**
 * ClassicCart - Order Success Page
 */
require_once 'includes/auth.php';
require_login();

$orderId = (int)($_GET['order'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute(array($orderId, current_user()['id']));
$order = $stmt->fetch();

if (!$order) {
    flash_set('error', 'Order not found.');
    header('Location: orders.php');
    exit;
}

$page_title = 'Order Successful - ClassicCart';
include 'includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1>Order Confirmation</h1></div>
</section>

<div class="container">
    <div class="success-box">
        <div class="success-icon">&#10004;</div>
        <h2>Order Placed Successfully!</h2>
        <p class="lead">Thank you for shopping with ClassicCart. Your order has been received.</p>

        <div class="card order-summary-card">
            <div class="os-row"><span class="label">Order Number</span><span class="value">#<?php echo e($order['order_number']); ?></span></div>
            <div class="os-row"><span class="label">Customer Name</span><span class="value"><?php echo e($order['name']); ?></span></div>
            <div class="os-row"><span class="label">Total Amount</span><span class="value"><?php echo money($order['total']); ?></span></div>
            <div class="os-row"><span class="label">Payment Method</span><span class="value"><?php echo e($order['payment_method']); ?></span></div>
            <div class="os-row"><span class="label">Order Date</span><span class="value"><?php echo format_date($order['created_at']); ?></span></div>
            <div class="os-row"><span class="label">Order Status</span><span class="value"><span class="badge badge-warning"><?php echo e($order['status']); ?></span></span></div>
        </div>

        <div class="flex" style="justify-content:center;">
            <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
            <a href="orders.php" class="btn btn-primary">View My Orders</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
/**
 * ClassicCart - Order Details (customer view)
 */
require_once 'includes/auth.php';
require_login();

$orderId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute(array($orderId, current_user()['id']));
$order = $stmt->fetch();

if (!$order) {
    flash_set('error', 'Order not found.');
    header('Location: orders.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt->execute(array($order['id']));
$items = $stmt->fetchAll();

$page_title = 'Order Details - ClassicCart';
include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Order Details</h1>
        <div class="breadcrumb"><a href="index.php">Home</a> &raquo; <a href="orders.php">My Orders</a> &raquo; #<?php echo e($order['order_number']); ?></div>
    </div>
</section>

<div class="container">
    <div class="card" style="padding:0; overflow:hidden; margin-bottom:22px;">
        <div class="card-header" style="padding:18px 22px;">
            <h3>Order #<?php echo e($order['order_number']); ?></h3>
            <span class="badge <?php echo order_status_classes($order['status']); ?>"><?php echo e($order['status']); ?></span>
        </div>
        <div style="padding:6px 22px 18px;">
            <div class="table-responsive">
                <table class="table" style="margin:0; border:none;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item) { ?>
                            <tr>
                                <td><?php echo e($item['product_name']); ?></td>
                                <td><?php echo money($item['price']); ?></td>
                                <td><?php echo (int)$item['quantity']; ?></td>
                                <td><?php echo money($item['subtotal']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <h3 style="margin-bottom:14px;">Shipping Address</h3>
            <p style="margin-bottom:4px;"><b><?php echo e($order['name']); ?></b></p>
            <p style="margin-bottom:4px;"><?php echo nl2br(e($order['address'])); ?></p>
            <p style="margin-bottom:4px;"><?php echo e($order['city']) . ', ' . e($order['state']) . ' - ' . e($order['pincode']); ?></p>
            <p>Phone: <?php echo e($order['phone']); ?></p>
        </div>

        <div class="card">
            <h3 style="margin-bottom:14px;">Payment Summary</h3>
            <div class="summary-row"><span>Subtotal</span> <b><?php echo money($order['subtotal']); ?></b></div>
            <div class="summary-row"><span>Shipping</span> <b><?php echo $order['shipping'] == 0 ? '<span class="free">FREE</span>' : money($order['shipping']); ?></b></div>
            <div class="summary-row total"><span>Grand Total</span> <span><?php echo money($order['total']); ?></span></div>
            <div class="summary-row" style="padding-top:6px;"><span>Payment Method</span> <b><?php echo e($order['payment_method']); ?></b></div>
            <div class="summary-row"><span>Order Date</span> <b><?php echo format_date($order['created_at']); ?></b></div>
        </div>
    </div>

    <div class="mt-4">
        <a href="orders.php" class="btn btn-outline">&larr; Back to My Orders</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
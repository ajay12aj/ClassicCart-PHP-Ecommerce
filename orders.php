<?php
/**
 * ClassicCart - My Orders Page
 */
$page_title = 'My Orders - ClassicCart';
require_once 'includes/auth.php';
require_login();

$user = current_user();
$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute(array($user['id']));
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>My Orders</h1>
        <div class="breadcrumb"><a href="index.php">Home</a> &raquo; My Orders</div>
    </div>
</section>

<div class="container">
    <?php if (empty($orders)) { ?>
        <div class="empty-state card">
            <div class="empty-icon">&#128196;</div>
            <h3>No orders yet</h3>
            <p>When you place an order it will appear here.</p>
            <a href="shop.php" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php } else { ?>
        <div class="card" style="padding:0; overflow:hidden;">
            <div class="table-responsive">
                <table class="table" style="margin:0; border:none;">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order) { ?>
                            <tr>
                                <td>#<?php echo e($order['order_number']); ?></td>
                                <td><?php echo format_date($order['created_at']); ?></td>
                                <td><?php echo money($order['total']); ?></td>
                                <td><?php echo e($order['payment_method']); ?></td>
                                <td><span class="badge <?php echo order_status_classes($order['status']); ?>"><?php echo e($order['status']); ?></span></td>
                                <td><a href="order-details.php?id=<?php echo (int)$order['id']; ?>" class="btn btn-outline btn-sm">View Details</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
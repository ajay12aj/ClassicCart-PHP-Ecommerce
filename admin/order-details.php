<?php
/**
 * ClassicCart - Admin Order Details
 */
$page_title = 'Order Details';
$page       = 'orders';
require_once __DIR__ . '/includes/header.php';

$oid = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare(
    'SELECT o.*, u.name AS username, u.email AS useremail, u.phone AS userphone
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     WHERE o.id = ?'
);
$stmt->execute(array($oid));
$order = $stmt->fetch();

if (!$order) {
    flash_set('error', 'Order not found.');
    header('Location: orders.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt->execute(array($order['id']));
$items = $stmt->fetchAll();

$statuses = array('Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled');
?>

<div class="flex-between mb-3">
    <div>
        <h3 style="margin-bottom:2px;">Order #<?php echo e($order['order_number']); ?></h3>
        <span class="badge <?php echo order_status_classes($order['status']); ?>"><?php echo e($order['status']); ?></span>
    </div>
    <a href="orders.php" class="btn btn-outline btn-sm">&larr; Back to Orders</a>
</div>

<div class="card">
    <div class="card-header"><h3>Ordered Items</h3></div>
    <div class="table-responsive">
        <table class="table">
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
                        <td style="font-weight:600;"><?php echo e($item['product_name']); ?></td>
                        <td><?php echo money($item['price']); ?></td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td><?php echo money($item['subtotal']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:600;">Subtotal</td>
                    <td><?php echo money($order['subtotal']); ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:600;">Shipping</td>
                    <td><?php echo $order['shipping'] == 0 ? 'FREE' : money($order['shipping']); ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:700;">Grand Total</td>
                    <td style="font-weight:700;"><?php echo money($order['total']); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    <div class="card">
        <div class="card-header"><h3>Customer Information</h3></div>
        <div class="order-detail-box">
            <div class="od-row"><span class="label">Name</span><span><?php echo e($order['name']); ?></span></div>
            <div class="od-row"><span class="label">Email</span><span><?php echo e($order['email']); ?></span></div>
            <div class="od-row"><span class="label">Phone</span><span><?php echo e($order['phone']); ?></span></div>
            <?php if ($order['user_id']) { ?>
                <div class="od-row">
                    <span class="label">Account</span>
                    <span><a href="users.php?search=<?php echo e($order['useremail']); ?>">View User Account</a></span>
                </div>
            <?php } else { ?>
                <div class="od-row"><span class="label">Account</span><span class="text-muted">Deleted user</span></div>
            <?php } ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Shipping Address</h3></div>
        <div class="order-detail-box">
            <div class="od-row"><span class="label">Address</span><span><?php echo nl2br(e($order['address'])); ?></span></div>
            <div class="od-row"><span class="label">City</span><span><?php echo e($order['city']); ?></span></div>
            <div class="od-row"><span class="label">State</span><span><?php echo e($order['state']); ?></span></div>
            <div class="od-row"><span class="label">Pincode</span><span><?php echo e($order['pincode']); ?></span></div>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <h3>Update Order Status</h3>
        <span class="text-muted">Placed on <?php echo format_date($order['created_at']); ?> &middot; <?php echo e($order['payment_method']); ?></span>
    </div>
    <div class="card-body">
        <form method="post" action="order-status.php" class="flex" style="gap:10px;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
            <select name="status" class="form-control" style="width:200px;">
                <?php foreach ($statuses as $s) { ?>
                    <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="btn btn-primary">Update Status</button>
        </form>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .grid-2 { grid-template-columns: 1fr !important; }
    }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
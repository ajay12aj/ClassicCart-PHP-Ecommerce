<?php
/**
 * ClassicCart - Admin Dashboard
 */
$page_title = 'Dashboard';
$page       = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$db = db();

$counts = array(
    'products'  => (int)$db->query('SELECT COUNT(*) AS c FROM products')->fetch()['c'],
    'categories'=> (int)$db->query('SELECT COUNT(*) AS c FROM categories')->fetch()['c'],
    'users'     => (int)$db->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'],
    'orders'    => (int)$db->query('SELECT COUNT(*) AS c FROM orders')->fetch()['c'],
    'pending'   => (int)$db->query("SELECT COUNT(*) AS c FROM orders WHERE status = 'Pending'")->fetch()['c'],
    'delivered' => (int)$db->query("SELECT COUNT(*) AS c FROM orders WHERE status = 'Delivered'")->fetch()['c'],
    'revenue'   => (float)$db->query("SELECT COALESCE(SUM(total),0) AS c FROM orders WHERE status != 'Cancelled'")->fetch()['c'],
);

$recentOrders = $db->query(
    'SELECT o.*, u.name AS customer_name
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC
     LIMIT 8'
)->fetchAll();
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">&#128230;</div>
        <div>
            <div class="stat-value"><?php echo $counts['products']; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">&#128193;</div>
        <div>
            <div class="stat-value"><?php echo $counts['categories']; ?></div>
            <div class="stat-label">Total Categories</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">&#128101;</div>
        <div>
            <div class="stat-value"><?php echo $counts['users']; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal">&#128196;</div>
        <div>
            <div class="stat-value"><?php echo $counts['orders']; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">&#9203;</div>
        <div>
            <div class="stat-value"><?php echo $counts['pending']; ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">&#10004;</div>
        <div>
            <div class="stat-value"><?php echo $counts['delivered']; ?></div>
            <div class="stat-label">Delivered Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">&#8377;</div>
        <div>
            <div class="stat-value"><?php echo money($counts['revenue']); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">&#9888;</div>
        <div>
            <div class="stat-value"><?php echo $counts['products']; ?>/<?php echo $counts['categories']; ?></div>
            <div class="stat-label">Products per Category</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Orders</h3>
        <a href="orders.php" class="btn btn-outline btn-sm">View All Orders</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)) { ?>
                    <tr><td colspan="6" class="text-center text-muted">No orders yet.</td></tr>
                <?php } ?>
                <?php foreach ($recentOrders as $order) { ?>
                    <tr>
                        <td>#<?php echo e($order['order_number']); ?></td>
                        <td><?php echo e($order['name']); ?></td>
                        <td><?php echo format_date($order['created_at']); ?></td>
                        <td><?php echo money($order['total']); ?></td>
                        <td><span class="badge <?php echo order_status_classes($order['status']); ?>"><?php echo e($order['status']); ?></span></td>
                        <td><a href="order-details.php?id=<?php echo (int)$order['id']; ?>" class="btn btn-info btn-sm">View</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
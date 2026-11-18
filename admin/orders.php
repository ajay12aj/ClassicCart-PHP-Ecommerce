<?php
/**
 * ClassicCart - Admin Orders List
 */
$page_title = 'Orders';
$page       = 'orders';
require_once __DIR__ . '/includes/header.php';

$statusFilter = $_GET['status'] ?? '';
$itemsPerPage = 10;
$pageNo       = max(1, (int)($_GET['page'] ?? 1));

$where  = '1 = 1';
$params = array();
if (in_array($statusFilter, array('Pending','Confirmed','Processing','Shipped','Delivered','Cancelled'))) {
    $where = 'o.status = ?';
    $params[] = $statusFilter;
}

$countStmt = db()->prepare("SELECT COUNT(*) AS c FROM orders o WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];
$totalPages = max(1, (int)ceil($total / $itemsPerPage));
if ($pageNo > $totalPages) { $pageNo = $totalPages; }
$offset = ($pageNo - 1) * $itemsPerPage;

$stmt = db()->prepare(
    "SELECT o.*, u.name AS customer_name, u.email AS customer_email
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     WHERE $where
     ORDER BY o.created_at DESC
     LIMIT $itemsPerPage OFFSET $offset"
);
$stmt->execute($params);
$orders = $stmt->fetchAll();

function oqstring(array $extra = array()): string
{
    $qs = array('status' => $_GET['status'] ?? '', 'page' => '');
    foreach ($extra as $k => $v) { $qs[$k] = $v; }
    return '?' . http_build_query(array_filter($qs, function ($v) { return $v !== ''; }));
}

$statuses = array('Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled');
?>

<div class="card">
    <div class="card-header">
        <h3>All Orders (<?php echo $total; ?>)</h3>
        <form method="get" action="orders.php" class="flex" style="gap:8px;">
            <select name="status" class="form-control" style="width:170px;">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $s) { ?>
                    <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)) { ?>
                    <tr><td colspan="7" class="text-center text-muted">No orders found.</td></tr>
                <?php } ?>
                <?php foreach ($orders as $order) { ?>
                    <tr>
                        <td>#<?php echo e($order['order_number']); ?></td>
                        <td><?php echo e($order['name']); ?></td>
                        <td><?php echo format_date($order['created_at']); ?></td>
                        <td><?php echo money($order['total']); ?></td>
                        <td><?php echo e($order['payment_method']); ?></td>
                        <td><span class="badge <?php echo order_status_classes($order['status']); ?>"><?php echo e($order['status']); ?></span></td>
                        <td><a href="order-details.php?id=<?php echo (int)$order['id']; ?>" class="btn btn-info btn-sm">View</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1) { ?>
        <div style="padding:14px 20px; border-top:1px solid var(--border); display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
            <?php if ($pageNo > 1) { ?><a href="<?php echo oqstring(array('page' => $pageNo - 1)); ?>" class="btn btn-outline btn-sm">&laquo; Prev</a><?php } ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <?php if ($i === $pageNo) { ?>
                    <span class="btn btn-primary btn-sm" disabled><?php echo $i; ?></span>
                <?php } else { ?>
                    <a href="<?php echo oqstring(array('page' => $i)); ?>" class="btn btn-outline btn-sm"><?php echo $i; ?></a>
                <?php } ?>
            <?php } ?>
            <?php if ($pageNo < $totalPages) { ?><a href="<?php echo oqstring(array('page' => $pageNo + 1)); ?>" class="btn btn-outline btn-sm">Next &raquo;</a><?php } ?>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
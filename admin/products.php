<?php
/**
 * ClassicCart - Admin Products List
 */
$page_title = 'Products';
$page       = 'products';
require_once __DIR__ . '/includes/header.php';

$search     = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$itemsPerPage = 10;
$pageNo    = max(1, (int)($_GET['page'] ?? 1));

$where  = array('1 = 1');
$params = array();
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.id = ?)';
    $params[] = "%$search%";
    $params[] = (int)$search;
}
if ($statusFilter === 'active' || $statusFilter === 'inactive') {
    $where[] = 'p.status = ?';
    $params[] = $statusFilter === 'active' ? 1 : 0;
}
$whereClause = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) AS c FROM products p WHERE $whereClause");
$countStmt->execute($params);
$total = (int)$countStmt->fetch()['c'];
$totalPages = max(1, (int)ceil($total / $itemsPerPage));
if ($pageNo > $totalPages) { $pageNo = $totalPages; }
$offset = ($pageNo - 1) * $itemsPerPage;

$stmt = db()->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE $whereClause
     ORDER BY p.id DESC
     LIMIT $itemsPerPage OFFSET $offset"
);
$stmt->execute($params);
$products = $stmt->fetchAll();

function qstring(array $extra = array()): string
{
    $qs = array('search' => $_GET['search'] ?? '', 'status' => $_GET['status'] ?? '', 'page' => '');
    foreach ($extra as $k => $v) { $qs[$k] = $v; }
    return '?' . http_build_query(array_filter($qs, function ($v) { return $v !== ''; }));
}
?>

<div class="flex-between mb-3">
    <p class="text-muted">Showing <?php echo $total; ?> products</p>
    <a href="product-add.php" class="btn btn-primary">+ Add New Product</a>
</div>

<div class="card">
    <div class="card-header">
        <form method="get" action="products.php" class="flex" style="gap:8px;">
            <input type="text" name="search" class="form-control" style="width:260px;" placeholder="Search by name or ID..." value="<?php echo e($search); ?>">
            <select name="status" class="form-control" style="width:150px;">
                <option value="">All Status</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="products.php" class="btn btn-outline">Clear</a>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)) { ?>
                    <tr><td colspan="8" class="text-center text-muted">No matching products found.</td></tr>
                <?php } ?>
                <?php foreach ($products as $p) { ?>
                    <tr>
                        <td><?php echo (int)$p['id']; ?></td>
                        <td><img src="<?php echo e(product_image($p['image'])); ?>" class="thumbnail" alt=""></td>
                        <td style="font-weight:600;"><?php echo e($p['name']); ?></td>
                        <td><?php echo e($p['category_name'] ?? 'N/A'); ?></td>
                        <td>
                            <?php echo money(active_price($p)); ?>
                            <?php if ((float)$p['discount_price'] > 0) { ?>
                                <div style="font-size:12px; color:var(--muted);"><del><?php echo money($p['price']); ?></del></div>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ((int)$p['stock'] <= 0) { ?>
                                <span class="badge badge-danger">Out of stock</span>
                            <?php } else { ?>
                                <span class="badge badge-success"><?php echo (int)$p['stock']; ?></span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php echo (int)$p['status'] === 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="product-edit.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="../product.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-info btn-sm" target="_blank">View</a>
                                <form method="post" action="product-delete.php" data-confirm-form="Are you sure you want to delete this product? This cannot be undone.">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1) { ?>
        <div style="padding:14px 20px; border-top:1px solid var(--border); display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
            <?php if ($pageNo > 1) { ?><a href="<?php echo qstring(array('page' => $pageNo - 1)); ?>" class="btn btn-outline btn-sm">&laquo; Prev</a><?php } ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <?php if ($i === $pageNo) { ?>
                    <span class="btn btn-primary btn-sm" disabled><?php echo $i; ?></span>
                <?php } else { ?>
                    <a href="<?php echo qstring(array('page' => $i)); ?>" class="btn btn-outline btn-sm"><?php echo $i; ?></a>
                <?php } ?>
            <?php } ?>
            <?php if ($pageNo < $totalPages) { ?><a href="<?php echo qstring(array('page' => $pageNo + 1)); ?>" class="btn btn-outline btn-sm">Next &raquo;</a><?php } ?>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
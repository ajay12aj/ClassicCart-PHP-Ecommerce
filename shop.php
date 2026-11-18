<?php
/**
 * ClassicCart - Shop Page (product listing with search, filters, sorting, pagination)
 */
$page_title = 'Shop - ClassicCart';
require_once 'includes/auth.php';

$itemsPerPage = 9;
$page    = max(1, (int)($_GET['page'] ?? 1));
$search  = trim($_GET['search'] ?? '');
$catId   = (int)($_GET['category'] ?? 0);
$min     = (float)($_GET['min'] ?? 0);
$max     = (float)($_GET['max'] ?? 0);
$sort    = $_GET['sort'] ?? 'newest';

/* Build WHERE clause with prepared params */
$where  = array('1 = 1');
$params = array();

if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    array_push($params, "%$search%", "%$search%", "%$search%");
}
if ($catId > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $catId;
}
if ($min > 0) {
    $where[] = '(CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END) >= ?';
    $params[] = $min;
}
if ($max > 0) {
    $where[] = '(CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END) <= ?';
    $params[] = $max;
}

$whereClause = implode(' AND ', $where);

$orderBy = 'p.created_at DESC';
switch ($sort) {
    case 'price_low':
        $orderBy = '(CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END) ASC';
        break;
    case 'price_high':
        $orderBy = '(CASE WHEN p.discount_price > 0 THEN p.discount_price ELSE p.price END) DESC';
        break;
    case 'name_az':
        $orderBy = 'p.name ASC';
        break;
    default:
        $orderBy = 'p.created_at DESC';
}

$countStmt = db()->prepare("SELECT COUNT(*) AS total FROM products p JOIN categories c ON c.id = p.category_id WHERE $whereClause AND p.status = 1");
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetch()['total'];
$totalPages    = max(1, (int)ceil($totalProducts / $itemsPerPage));
if ($page > $totalPages) { $page = $totalPages; }
$offset = ($page - 1) * $itemsPerPage;

$stmt = db()->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE $whereClause AND p.status = 1
     ORDER BY $orderBy
     LIMIT $itemsPerPage OFFSET $offset"
);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = get_categories(true);

/* Build pagination query strings */
function buildQuery(array $overrides = array()): array
{
    $qs = array(
        'search'   => $_GET['search']   ?? '',
        'category' => $_GET['category'] ?? '',
        'min'      => $_GET['min']      ?? '',
        'max'      => $_GET['max']      ?? '',
        'sort'     => $_GET['sort']     ?? 'newest',
    );
    foreach ($overrides as $key => $value) {
        $qs[$key] = $value;
    }
    return array_filter($qs, function ($v) { return $v !== ''; });
}

function hurl(array $overrides = array()): string
{
    $qs = buildQuery($overrides);
    return 'shop.php' . ($qs ? '?' . http_build_query($qs, '', '&') : '');
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Shop Products</h1>
        <div class="breadcrumb"><a href="index.php">Home</a> &raquo; Shop</div>
    </div>
</section>

<div class="container">
    <div class="shop-layout">
        <!-- Sidebar filters -->
        <aside class="shop-sidebar">
            <div class="filter-box">
                <h4>Categories</h4>
                <ul class="filter-list">
                    <li><a href="<?php echo hurl(array('category' => '', 'page' => '')); ?>" class="<?php echo $catId === 0 ? 'active' : ''; ?>">All Categories</a></li>
                    <?php foreach ($categories as $cat) { ?>
                        <li>
                            <a href="<?php echo hurl(array('category' => $cat['id'], 'page' => '')); ?>" class="<?php echo $catId === (int)$cat['id'] ? 'active' : ''; ?>">
                                <?php echo e($cat['name']); ?>
                                <span class="count"><?php echo (int)$cat['product_count']; ?></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="filter-box">
                <h4>Price Range</h4>
                <form method="get" action="shop.php">
                    <input type="hidden" name="search" value="<?php echo e($search); ?>">
                    <input type="hidden" name="category" value="<?php echo (int)$catId; ?>">
                    <input type="hidden" name="sort" value="<?php echo e($sort); ?>">
                    <div class="form-row">
                        <div class="form-group" style="margin-bottom:10px;">
                            <input type="number" class="form-control" name="min" placeholder="Min" min="0" value="<?php echo $min > 0 ? (int)$min : ''; ?>">
                        </div>
                        <div class="form-group" style="margin-bottom:10px;">
                            <input type="number" class="form-control" name="max" placeholder="Max" min="0" value="<?php echo $max > 0 ? (int)$max : ''; ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">Apply Price</button>
                    <?php if ($min > 0 || $max > 0) { ?>
                        <a href="<?php echo hurl(array('min' => '', 'max' => '', 'page' => '')); ?>" class="btn btn-outline btn-sm btn-block mt-2">Clear Price</a>
                    <?php } ?>
                </form>
            </div>
        </aside>

        <!-- Product listing -->
        <section>
            <div class="shop-topbar">
                <div class="result-count">Showing <?php echo $totalProducts; ?> product<?php echo $totalProducts === 1 ? '' : 's'; ?></div>
                <form class="sort-form" method="get" action="shop.php">
                    <input type="hidden" name="search" value="<?php echo e($search); ?>">
                    <input type="hidden" name="category" value="<?php echo (int)$catId; ?>">
                    <input type="hidden" name="min" value="<?php echo $min > 0 ? (int)$min : ''; ?>">
                    <input type="hidden" name="max" value="<?php echo $max > 0 ? (int)$max : ''; ?>">
                    <label for="sortSelect" class="text-muted" style="font-size:13px;">Sort by:</label>
                    <select name="sort" id="sortSelect" class="form-control">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Latest</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_az" <?php echo $sort === 'name_az' ? 'selected' : ''; ?>>Name: A to Z</option>
                    </select>
                </form>
            </div>

            <?php if (!empty($products)) { ?>
                <div class="product-grid">
                    <?php foreach ($products as $product) { product_card($product); } ?>
                </div>
            <?php } else { ?>
                <div class="empty-state card">
                    <div class="empty-icon">&#128269;</div>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter criteria.</p>
                    <a href="shop.php" class="btn btn-primary">Clear Filters</a>
                </div>
            <?php } ?>

            <?php if ($totalPages > 1) { ?>
                <div class="pagination">
                    <?php if ($page > 1) { ?>
                        <a href="<?php echo hurl(array('page' => $page - 1)); ?>">&laquo; Prev</a>
                    <?php } ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <?php if ($i === $page) { ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php } else { ?>
                            <a href="<?php echo hurl(array('page' => $i)); ?>"><?php echo $i; ?></a>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($page < $totalPages) { ?>
                        <a href="<?php echo hurl(array('page' => $page + 1)); ?>">Next &raquo;</a>
                    <?php } ?>
                </div>
            <?php } ?>
        </section>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
/**
 * ClassicCart - Admin Categories List
 */
$page_title = 'Categories';
$page       = 'categories';
require_once __DIR__ . '/includes/header.php';

$categories = db()->query(
    'SELECT c.*,
            (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c
     ORDER BY c.id DESC'
)->fetchAll();
?>

<div class="flex-between mb-3">
    <p class="text-muted">Manage product categories</p>
    <a href="category-add.php" class="btn btn-primary">+ Add New Category</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)) { ?>
                    <tr><td colspan="8" class="text-center text-muted">No categories yet.</td></tr>
                <?php } ?>
                <?php foreach ($categories as $cat) { ?>
                    <tr>
                        <td><?php echo (int)$cat['id']; ?></td>
                        <td>
                            <?php if (!empty($cat['image'])) { ?>
                                <img src="<?php echo e(category_image($cat['image'])); ?>" class="thumbnail" alt="<?php echo e($cat['name']); ?>">
                            <?php } else { ?>
                                <span class="badge badge-secondary">No image</span>
                            <?php } ?>
                        </td>
                        <td style="font-weight:600;"><?php echo e($cat['name']); ?></td>
                        <td style="max-width:300px;"><?php echo e($cat['description']); ?></td>
                        <td><?php echo (int)$cat['product_count']; ?></td>
                        <td>
                            <?php echo (int)$cat['status'] === 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'; ?>
                        </td>
                        <td><?php echo format_date($cat['created_at']); ?></td>
                        <td>
                            <div class="actions">
                                <a href="category-edit.php?id=<?php echo (int)$cat['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                <form method="post" action="category-delete.php" data-confirm-form="Are you sure you want to delete this category? Categories with products cannot be deleted.">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" <?php echo (int)$cat['product_count'] > 0 ? 'disabled title="Category has products"' : ''; ?>>Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
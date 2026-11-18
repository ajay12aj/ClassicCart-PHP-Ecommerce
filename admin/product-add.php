<?php
/**
 * ClassicCart - Admin Add Product
 */
$page_title = 'Add Product';
$page       = 'products';
require_once __DIR__ . '/includes/header.php';

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$input = array('name' => '', 'category_id' => '', 'description' => '', 'price' => '', 'discount_price' => '', 'stock' => '', 'rating' => '4.0', 'status' => 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid form submission. Please try again.');
        header('Location: product-add.php');
        exit;
    }

    foreach ($input as $key => $value) {
        if (isset($_POST[$key])) {
            $input[$key] = trim((string)$_POST[$key]);
        }
    }

    $errors = array();
    $catId  = (int)$input['category_id'];
    if ($input['name'] === '' || strlen($input['name']) < 2) {
        $errors[] = 'Product name is required.';
    }
    $catStmt = db()->prepare('SELECT id FROM categories WHERE id = ?');
    $catStmt->execute(array($catId));
    if (!$catStmt->fetch()) {
        $errors[] = 'Please select a valid category.';
    }
    if ($input['price'] === '' || (float)$input['price'] <= 0) {
        $errors[] = 'Please enter a valid price.';
    }
    if ($input['discount_price'] !== '' && (float)$input['discount_price'] >= (float)$input['price']) {
        $errors[] = 'Discount price must be lower than the original price.';
    }
    if ($input['stock'] === '' || (int)$input['stock'] < 0) {
        $errors[] = 'Please enter a valid stock quantity.';
    }

    /* Image upload validation */
    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $img = upload_product_image($_FILES['image']);
        if ($img['error']) {
            $errors[] = $img['message'];
        } else {
            $imageName = $img['file'];
        }
    } elseif ($input['name'] !== '') {
        $errors[] = 'Please choose a product image.';
    }

    if (empty($errors)) {
        try {
            db()->prepare(
                'INSERT INTO products (category_id, name, description, price, discount_price, stock, image, rating, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute(array(
                $catId,
                $input['name'],
                $input['description'],
                (float)$input['price'],
                $input['discount_price'] !== '' ? (float)$input['discount_price'] : null,
                (int)$input['stock'],
                $imageName,
                (float)$input['rating'],
                $input['status'] ? 1 : 0,
            ));
            flash_set('success', 'Product added successfully.');
            header('Location: products.php');
            exit;
        } catch (Exception $ex) {
            flash_set('error', 'Something went wrong. Please try again.');
        }
    } else {
        foreach ($errors as $err) {
            flash_set('error', $err);
        }
    }
}

?>

<div class="card">
    <div class="card-header">
        <h3>Add New Product</h3>
        <a href="products.php" class="btn btn-outline btn-sm">&larr; Back to Products</a>
    </div>
    <div class="card-body">
        <form method="post" action="product-add.php" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($input['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat) { ?>
                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo (int)$input['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"><?php echo e($input['description']); ?></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" step="0.01" min="0" id="price" name="price" class="form-control" value="<?php echo e($input['price']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="discount_price">Discount Price</label>
                    <input type="number" step="0.01" min="0" id="discount_price" name="discount_price" class="form-control" value="<?php echo e($input['discount_price']); ?>">
                    <div class="form-hint">Leave empty if there is no discount.</div>
                </div>
                <div class="form-group">
                    <label for="stock">Stock Quantity *</label>
                    <input type="number" min="0" id="stock" name="stock" class="form-control" value="<?php echo e($input['stock']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="rating">Rating (0 - 5)</label>
                    <input type="number" step="0.1" min="0" max="5" id="rating" name="rating" class="form-control" value="<?php echo e($input['rating']); ?>">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="image">Product Image * (JPG, PNG, WEBP - max 2MB)</label>
                    <input type="file" id="image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="1" <?php echo (int)$input['status'] === 1 ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo (int)$input['status'] === 0 ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Product</button>
                <a href="products.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
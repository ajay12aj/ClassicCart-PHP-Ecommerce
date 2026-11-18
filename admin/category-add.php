<?php
/**
 * ClassicCart - Admin Add Category
 */
$page_title = 'Add Category';
$page       = 'categories';
require_once __DIR__ . '/includes/header.php';

$input = array('name' => '', 'image' => '', 'description' => '', 'status' => 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid form submission. Please try again.');
        header('Location: category-add.php');
        exit;
    }

    $input['name']        = trim($_POST['name'] ?? '');
    $input['description'] = trim($_POST['description'] ?? '');
    $input['status']      = isset($_POST['status']) ? 1 : 0;

    $errors = array();
    if ($input['name'] === '' || strlen($input['name']) < 2) {
        $errors[] = 'Category name is required.';
    }

    /* Optional image upload */
    if (!empty($_FILES['image']['name'])) {
        $img = upload_category_image($_FILES['image']);
        if ($img['error']) {
            $errors[] = $img['message'];
        } else {
            $input['image'] = $img['file'];
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM categories WHERE name = ?');
        $stmt->execute(array($input['name']));
        if ($stmt->fetch()) {
            $errors[] = 'A category with this name already exists.';
        }
    }

    if (empty($errors)) {
        try {
            db()->prepare('INSERT INTO categories (name, image, description, status) VALUES (?, ?, ?, ?)')
                ->execute(array($input['name'], $input['image'] !== '' ? $input['image'] : null, $input['description'], $input['status']));
            flash_set('success', 'Category added successfully.');
            header('Location: categories.php');
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
        <h3>Add New Category</h3>
        <a href="categories.php" class="btn btn-outline btn-sm">&larr; Back to Categories</a>
    </div>
    <div class="card-body">
        <form method="post" action="category-add.php" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($input['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="image">Category Image (JPG, PNG, WEBP - max 2MB)</label>
                    <input type="file" id="image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-hint">Optional. Shown on the homepage category cards.</div>
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"><?php echo e($input['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label class="flex" style="gap:8px; width:fit-content;">
                    <input type="checkbox" name="status" value="1" <?php echo $input['status'] ? 'checked' : ''; ?> style="width:16px;height:16px;">
                    Active
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Category</button>
                <a href="categories.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
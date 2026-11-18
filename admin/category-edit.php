<?php
/**
 * ClassicCart - Admin Edit Category
 */
$page_title = 'Edit Category';
$page       = 'categories';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
$stmt->execute(array($id));
$category = $stmt->fetch();

if (!$category) {
    flash_set('error', 'Category not found.');
    header('Location: categories.php');
    exit;
}

$input = array(
    'name'        => $category['name'],
    'image'       => $category['image'],
    'description' => $category['description'],
    'status'      => $category['status'],
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid form submission. Please try again.');
        header('Location: category-edit.php?id=' . $id);
        exit;
    }

    $input['name']        = trim($_POST['name'] ?? '');
    $input['description'] = trim($_POST['description'] ?? '');
    $input['status']      = isset($_POST['status']) ? 1 : 0;

    $errors = array();
    if ($input['name'] === '' || strlen($input['name']) < 2) {
        $errors[] = 'Category name is required.';
    }

    $oldImage = $category['image'];

    /* Optional new image (replaces the old one) */
    if (!empty($_FILES['image']['name'])) {
        $img = upload_category_image($_FILES['image']);
        if ($img['error']) {
            $errors[] = $img['message'];
        } else {
            $input['image'] = $img['file'];
        }
    }

    /* Remove current image if requested */
    $removeImage = isset($_POST['remove_image']) ? true : false;

    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM categories WHERE name = ? AND id != ?');
        $stmt->execute(array($input['name'], $id));
        if ($stmt->fetch()) {
            $errors[] = 'A category with this name already exists.';
        }
    }

    if (empty($errors)) {
        try {
            $newImage = $removeImage ? null : $input['image'];
            db()->prepare('UPDATE categories SET name = ?, image = ?, description = ?, status = ? WHERE id = ?')
                ->execute(array($input['name'], $newImage, $input['description'], $input['status'], $id));

            if ($newImage !== $oldImage) {
                delete_category_image($oldImage);
            }

            flash_set('success', 'Category updated successfully.');
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
        <h3>Edit Category #<?php echo (int)$id; ?></h3>
        <a href="categories.php" class="btn btn-outline btn-sm">&larr; Back to Categories</a>
    </div>
    <div class="card-body">
        <form method="post" action="category-edit.php?id=<?php echo (int)$id; ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($input['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="image">Category Image (JPG, PNG, WEBP - max 2MB)</label>
                    <?php if (!empty($category['image'])) { ?>
                        <div class="category-image-preview">
                            <img src="<?php echo e(category_image($category['image'])); ?>" alt="<?php echo e($category['name']); ?>">
                            <label class="flex" style="gap:6px; width:fit-content; margin-top:8px;">
                                <input type="checkbox" name="remove_image" value="1" style="width:16px;height:16px;">
                                Remove current image
                            </label>
                        </div>
                    <?php } ?>
                    <input type="file" id="image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-hint">Leave empty to keep the current image. Uploading a new one replaces it.</div>
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
                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="categories.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
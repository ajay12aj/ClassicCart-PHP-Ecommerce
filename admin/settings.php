<?php
/**
 * ClassicCart - Admin Settings (manage admin account)
 */
$page_title = 'Settings';
$page       = 'settings';
require_once __DIR__ . '/includes/header.php';

$admin = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid form submission. Please try again.');
        header('Location: settings.php');
        exit;
    }

    $form = $_POST['form'] ?? '';
    $errors = array();

    if ($form === 'profile') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (strlen($name) < 3) {
            $errors[] = 'Name must be at least 3 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email.';
        }

        if (empty($errors)) {
            $stmt = db()->prepare('SELECT id FROM admins WHERE email = ? AND id != ?');
            $stmt->execute(array($email, $admin['id']));
            if ($stmt->fetch()) {
                $errors[] = 'Another admin already uses this email.';
            } else {
                db()->prepare('UPDATE admins SET name = ?, email = ? WHERE id = ?')->execute(array($name, $email, $admin['id']));
                flash_set('success', 'Profile updated successfully.');
                header('Location: settings.php');
                exit;
            }
        }
    }

    if ($form === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $admin['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
        if (strlen($new) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            db()->prepare('UPDATE admins SET password = ? WHERE id = ?')
                ->execute(array(password_hash($new, PASSWORD_BCRYPT), $admin['id']));
            flash_set('success', 'Password changed successfully.');
            header('Location: settings.php');
            exit;
        }
    }

    foreach ($errors as $err) {
        flash_set('error', $err);
    }
    header('Location: settings.php');
    exit;
}
?>

<div class="card">
    <div class="card-header"><h3>Admin Profile</h3></div>
    <div class="card-body" style="max-width:560px;">
        <form method="post" action="settings.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="form" value="profile">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e($admin['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo e($admin['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Change Password</h3></div>
    <div class="card-body" style="max-width:560px;">
        <form method="post" action="settings.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="form" value="password">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-control" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="6" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
/**
 * ClassicCart - Admin Login
 */
require_once __DIR__ . '/../includes/auth.php';

/* Already logged in? */
if (current_admin()) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid form submission. Please try again.');
        header('Location: login.php');
        exit;
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        flash_set('error', 'Please enter your email and password.');
        header('Location: login.php');
        exit;
    }

    $stmt = db()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute(array($email));
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        flash_set('error', 'Invalid login details.');
        header('Location: login.php');
        exit;
    }

    if ((int)$admin['status'] !== 1) {
        flash_set('error', 'This admin account has been deactivated.');
        header('Location: login.php');
        exit;
    }

    start_session();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int)$admin['id'];

    flash_set('success', 'Welcome back, ' . $admin['name'] . '!');
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ClassicCart</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-login-body">
    <a href="../index.php" class="btn btn-outline btn-sm backHome" style="background:#fff;">&#8592; Back to Store</a>

    <div class="admin-login-card">
        <div class="login-logo">Classic<span>Cart</span></div>
        <h2>Admin Login</h2>
        <p class="login-sub">Enter your credentials to access the admin panel.</p>

        <?php $flashMsgs = flash_get(); ?>
        <?php if (!empty($flashMsgs)) { ?>
            <div class="flash-container">
                <?php foreach ($flashMsgs as $type => $messages) { ?>
                    <?php foreach ($messages as $msg) { ?>
                        <div class="flash flash-<?php echo e($type === 'error' ? 'danger' : $type); ?>">
                            <span><?php echo e($msg); ?></span>
                            <button type="button" class="flash-close" aria-label="Close">&times;</button>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>

        <form method="post" action="login.php">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Login to Admin Panel</button>
        </form>
        <div class="back-link">
            <small class="text-muted">Default: admin@classiccart.com / Admin@123</small>
        </div>
    </div>

<script src="../assets/vendor/jquery.min.js"></script>
<script>
$(function () {
    $('.flash-close').on('click', function () { $(this).closest('.flash').fadeOut(200); });
});
</script>
</body>
</html>
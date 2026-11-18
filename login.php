<?php
/**
 * ClassicCart - User Login
 */
$page_title = 'Login - ClassicCart';
require_once 'includes/auth.php';

if (current_user()) {
    header('Location: index.php');
    exit;
}

remember_redirect();

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

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute(array($email));
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        flash_set('error', 'Invalid login details. Please check your email and password.');
        header('Location: login.php');
        exit;
    }

    if ((int)$user['status'] !== 1) {
        flash_set('error', 'Your account has been deactivated. Please contact support.');
        header('Location: login.php');
        exit;
    }

    start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];

    flash_set('success', 'Welcome back, ' . $user['name'] . '!');
    header('Location: ' . pop_redirect());
    exit;
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1>Login</h1></div>
</section>

<div class="container">
    <div class="auth-wrapper">
        <div class="card auth-card">
            <h2>Welcome Back</h2>
            <p class="auth-subtitle">Login to continue shopping with ClassicCart.</p>
            <form method="post" action="login.php" id="loginForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect" value="<?php echo e($_SESSION['redirect_after_login'] ?? ''); ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Login</button>
            </form>
            <div class="auth-footer">New to ClassicCart? <a href="register.php">Create an account</a></div>
            <div class="auth-footer" style="margin-top:6px;">
                <small class="text-muted">Demo user: user@demo.com / User@123</small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
/**
 * ClassicCart - User Registration
 */
$page_title = 'Create Account - ClassicCart';
require_once 'includes/auth.php';

/* Already logged in? */
if (current_user()) {
    header('Location: index.php');
    exit;
}

$errors = array();
$input = array('name' => '', 'email' => '', 'phone' => '', 'password' => '', 'confirm' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid form submission. Please try again.');
        header('Location: register.php');
        exit;
    }

    $input['name']     = trim($_POST['name'] ?? '');
    $input['email']    = trim($_POST['email'] ?? '');
    $input['phone']    = trim($_POST['phone'] ?? '');
    $input['password'] = $_POST['password'] ?? '';
    $input['confirm']  = $_POST['confirm_password'] ?? '';

    if (strlen($input['name']) < 3) {
        $errors[] = 'Name must be at least 3 characters.';
    }
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^[0-9]{10}$/', $input['phone'])) {
        $errors[] = 'Please enter a valid 10 digit phone number.';
    }
    if (strlen($input['password']) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($input['password'] !== $input['confirm']) {
        $errors[] = 'Passwords do not match.';
    }

    /* Duplicate email check */
    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute(array($input['email']));
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists. Please login.';
        }
    }

    if (empty($errors)) {
        db()->prepare('INSERT INTO users (name, email, phone, password, status) VALUES (?, ?, ?, ?, 1)')
            ->execute(array($input['name'], $input['email'], $input['phone'], password_hash($input['password'], PASSWORD_BCRYPT)));

        $userId = (int)db()->lastInsertId();
        start_session();
        $_SESSION['user_id'] = $userId;

        flash_set('success', 'Account created successfully. Welcome to ClassicCart!');
        header('Location: index.php');
        exit;
    }

    foreach ($errors as $err) {
        flash_set('error', $err);
    }
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container"><h1>Create Account</h1></div>
</section>

<div class="container">
    <div class="auth-wrapper">
        <div class="card auth-card">
            <h2>Create an Account</h2>
            <p class="auth-subtitle">Join ClassicCart to shop smart and simple.</p>
            <form method="post" action="register.php" id="registerForm">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo e($input['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo e($input['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo e($input['phone']); ?>" maxlength="10" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="6" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Register</button>
            </form>
            <div class="auth-footer">Already have an account? <a href="login.php">Login here</a></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
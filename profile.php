<?php
/**
 * ClassicCart - User Profile
 */
$page_title = 'My Profile - ClassicCart';
require_once 'includes/auth.php';
require_login();

$user = current_user();
$tab  = $_GET['tab'] ?? 'details';
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid form submission. Please try again.');
        header('Location: profile.php');
        exit;
    }

    $form = $_POST['form'] ?? '';

    if ($form === 'details') {
        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $state   = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');

        if (strlen($name) < 3) {
            $errors[] = 'Name must be at least 3 characters.';
        }
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            $errors[] = 'Please enter a valid 10 digit phone number.';
        }
        if ($pincode !== '' && !preg_match('/^[0-9]{5,6}$/', $pincode)) {
            $errors[] = 'Please enter a valid pincode.';
        }

        if (empty($errors)) {
            db()->prepare(
                'UPDATE users SET name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?'
            )->execute(array($name, $phone, $address, $city, $state, $pincode, $user['id']));
            flash_set('success', 'Profile updated successfully.');
            header('Location: profile.php?tab=details');
            exit;
        }
    }

    if ($form === 'password') {
        $current     = $_POST['current_password'] ?? '';
        $new         = $_POST['new_password'] ?? '';
        $confirm     = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
        if (strlen($new) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            db()->prepare('UPDATE users SET password = ? WHERE id = ?')
                ->execute(array(password_hash($new, PASSWORD_BCRYPT), $user['id']));
            flash_set('success', 'Password changed successfully.');
            header('Location: profile.php?tab=password');
            exit;
        }
    }

    foreach ($errors as $err) {
        flash_set('error', $err);
    }
    header('Location: profile.php?tab=' . $tab);
    exit;
}

include 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>My Profile</h1>
        <div class="breadcrumb"><a href="index.php">Home</a> &raquo; Profile</div>
    </div>
</section>

<div class="container">
    <div class="profile-layout" style="max-width:760px; margin:0 auto;">
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="display:flex; border-bottom:1px solid var(--border); background:var(--light-bg); overflow-x:auto;">
                <a href="profile.php?tab=details" class="profile-tab <?php echo $tab === 'details' ? 'active' : ''; ?>">Profile Details</a>
                <a href="profile.php?tab=password" class="profile-tab <?php echo $tab === 'password' ? 'active' : ''; ?>">Change Password</a>
                <a href="orders.php" class="profile-tab">My Orders</a>
                <a href="logout.php" class="profile-tab">Logout</a>
            </div>

            <?php if ($tab === 'details') { ?>
                <div style="padding:24px;">
                    <h3 style="margin-bottom:16px;">Account Information</h3>
                    <div class="info-block" style="background:var(--light-bg); border:1px solid var(--border); border-radius:var(--radius-sm); padding:14px 18px; margin-bottom:22px; display:flex; gap:26px; flex-wrap:wrap;">
                        <div><small class="text-muted">Name</small><div style="font-weight:600;"><?php echo e($user['name']); ?></div></div>
                        <div><small class="text-muted">Email</small><div style="font-weight:600;"><?php echo e($user['email']); ?></div></div>
                        <div><small class="text-muted">Phone</small><div style="font-weight:600;"><?php echo e($user['phone']); ?></div></div>
                        <div><small class="text-muted">Member Since</small><div style="font-weight:600;"><?php echo format_date($user['created_at']); ?></div></div>
                    </div>

                    <h3 style="margin-bottom:16px;">Edit Profile</h3>
                    <form method="post" action="profile.php">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="form" value="details">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo e($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email (cannot be changed)</label>
                            <input type="email" id="email" class="form-control" value="<?php echo e($user['email']); ?>" readonly disabled>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo e($user['phone']); ?>" maxlength="10" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control"><?php echo e($user['address']); ?></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" class="form-control" value="<?php echo e($user['city']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" class="form-control" value="<?php echo e($user['state']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pincode">Pincode</label>
                            <input type="text" id="pincode" name="pincode" class="form-control" value="<?php echo e($user['pincode']); ?>" maxlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            <?php } else { ?>
                <div style="padding:24px;">
                    <h3 style="margin-bottom:16px;">Change Password</h3>
                    <form method="post" action="profile.php">
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
            <?php } ?>
        </div>
    </div>
</div>

<style>
    .profile-tab {
        padding: 14px 20px;
        font-weight: 600;
        font-size: 14px;
        color: var(--muted);
        border-bottom: 3px solid transparent;
        white-space: nowrap;
    }
    .profile-tab:hover { color: var(--primary); }
    .profile-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
</style>

<?php include 'includes/footer.php'; ?>
<?php
/**
 * ClassicCart - Admin User Management
 */
$page_title = 'Users';
$page       = 'users';
require_once __DIR__ . '/includes/header.php';

/* Handle actions: toggle_status, delete */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($action === 'toggle_status') {
        $stmt = db()->prepare('SELECT status FROM users WHERE id = ?');
        $stmt->execute(array($userId));
        $user = $stmt->fetch();
        if ($user) {
            $newStatus = (int)$user['status'] === 1 ? 0 : 1;
            db()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute(array($newStatus, $userId));
            flash_set('success', $newStatus === 1 ? 'User activated.' : 'User deactivated.');
        }
        header('Location: users.php');
        exit;
    }

    if ($action === 'delete') {
        $stmt = db()->prepare('SELECT id FROM users WHERE id = ?');
        $stmt->execute(array($userId));
        if ($stmt->fetch()) {
            try {
                db()->prepare('DELETE FROM users WHERE id = ?')->execute(array($userId));
                flash_set('success', 'User deleted successfully.');
            } catch (Exception $ex) {
                flash_set('error', 'Could not delete this user.');
            }
        }
        header('Location: users.php');
        exit;
    }
}

$search = trim($_GET['search'] ?? '');
$where  = '1 = 1';
$params = array();
if ($search !== '') {
    $where = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    array_push($params, "%$search%", "%$search%", "%$search%");
}

$stmt = db()->prepare(
    "SELECT u.*,
            (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count
     FROM users u
     WHERE $where
     ORDER BY u.created_at DESC"
);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="flex-between mb-3">
    <p class="text-muted"><?php echo count($users); ?> registered customer(s)</p>
    <form method="get" action="users.php" class="flex" style="gap:8px;">
        <input type="text" name="search" class="form-control" style="width:240px;" placeholder="Search by name, email, phone..." value="<?php echo e($search); ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search !== '') { ?><a href="users.php" class="btn btn-outline">Clear</a><?php } ?>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)) { ?>
                    <tr><td colspan="8" class="text-center text-muted">No users found.</td></tr>
                <?php } ?>
                <?php foreach ($users as $user) { ?>
                    <tr>
                        <td><?php echo (int)$user['id']; ?></td>
                        <td style="font-weight:600;"><?php echo e($user['name']); ?></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo e($user['phone']); ?></td>
                        <td><?php echo (int)$user['order_count']; ?></td>
                        <td><?php echo format_date($user['created_at']); ?></td>
                        <td>
                            <?php echo (int)$user['status'] === 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <form method="post" action="users.php" data-confirm-form="Are you sure you want to <?php echo (int)$user['status'] === 1 ? 'deactivate' : 'activate'; ?> this user?">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        <?php echo (int)$user['status'] === 1 ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                                <form method="post" action="users.php" data-confirm-form="Delete this user? Their cart and wishlist will also be removed. Orders will be kept.">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
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
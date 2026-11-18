<?php
/**
 * ClassicCart - Authentication helpers
 */

require_once __DIR__ . '/functions.php';

/** Returns the currently logged in customer record, or null. */
function current_user(): ?array
{
    start_session();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute(array((int)$_SESSION['user_id']));
    $user = $stmt->fetch();
    if ($user && (int)$user['status'] === 1) {
        return $user;
    }
    unset($_SESSION['user_id']);
    return null;
}

/** Redirect visitor to login page unless already logged in. */
function require_login(): void
{
    start_session();
    if (!current_user()) {
        $redirect = $_SERVER['REQUEST_URI'];
        flash_set('warning', 'Please login to continue.');
        header('Location: login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

/** Save the "redirect back" target from the query string into the session. */
function remember_redirect(): void
{
    start_session();
    if (!empty($_GET['redirect']) && strpos($_GET['redirect'], 'http') !== 0) {
        $_SESSION['redirect_after_login'] = $_GET['redirect'];
    }
}

function pop_redirect(): string
{
    start_session();
    $target = $_SESSION['redirect_after_login'] ?? '';
    unset($_SESSION['redirect_after_login']);
    if ($target !== '' && strpos($target, 'http') !== 0) {
        return $target;
    }
    return 'index.php';
}

/** Returns the currently logged in admin record, or null. */
function current_admin(): ?array
{
    start_session();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
    $stmt->execute(array((int)$_SESSION['admin_id']));
    $admin = $stmt->fetch();
    if ($admin && (int)$admin['status'] === 1) {
        return $admin;
    }
    unset($_SESSION['admin_id']);
    return null;
}

/** Redirect to admin login unless the admin is authenticated. */
function require_admin(): void
{
    start_session();
    if (!current_admin()) {
        flash_set('warning', 'Please login to continue.');
        header('Location: login.php');
        exit;
    }
}

function is_admin_logged_in(): bool
{
    return current_admin() !== null;
}
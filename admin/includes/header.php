<?php
/**
 * Admin shared header. Requires an authenticated admin.
 * Set $page_title and $page (menu key) before including.
 */
if (!isset($page_title)) {
    $page_title = 'Admin Panel - ClassicCart';
}
if (!isset($page)) {
    $page = 'dashboard';
}
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
start_session();
$admin     = current_admin();
$flashMsgs = flash_get();
$adminInitial = strtoupper(substr($admin['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?></title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="admin-wrapper">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="flex">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">&#9776;</button>
                <span class="topbar-title"><?php echo e($page_title); ?></span>
            </div>
            <div class="topbar-right">
                <a href="../index.php" class="btn btn-outline btn-sm">&#128279; View Store</a>
                <div class="admin-avatar">
                    <span class="avatar-circle"><?php echo e($adminInitial); ?></span>
                    <div>
                        <div class="admin-name"><?php echo e($admin['name']); ?></div>
                        <div class="admin-role">Administrator</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content">
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
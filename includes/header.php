<?php
/**
 * Front-end shared header: <head>, navbar and flash messages.
 * Set $page_title before including this file.
 */
if (!isset($page_title)) {
    $page_title = 'ClassicCart - Shop Smart. Shop Simple.';
}
require_once __DIR__ . '/auth.php';
$current_user = current_user();
$flashMsgs    = flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title); ?></title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>

<?php if (!empty($flashMsgs)) { ?>
    <div class="flash-container">
        <?php foreach ($flashMsgs as $type => $messages) { ?>
            <?php foreach ($messages as $msg) { ?>
                <div class="flash flash-<?php echo e($type); ?>">
                    <span><?php echo e($msg); ?></span>
                    <button type="button" class="flash-close" aria-label="Close">&times;</button>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
<?php } ?>

<main class="page-main">
<?php
/**
 * ClassicCart - Admin Logout
 */
require_once __DIR__ . '/../includes/auth.php';

start_session();
unset($_SESSION['admin_id']);
session_regenerate_id(true);

flash_set('success', 'You have been logged out successfully.');
header('Location: login.php');
exit;
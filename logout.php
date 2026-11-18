<?php
/**
 * ClassicCart - User Logout
 */
require_once 'includes/auth.php';

start_session();
unset($_SESSION['user_id']);
session_regenerate_id(true);

flash_set('success', 'You have been logged out successfully.');
header('Location: index.php');
exit;
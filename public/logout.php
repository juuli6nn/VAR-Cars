<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['authenticated']);
unset($_SESSION['is_admin']);
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['user_email']);
unset($_SESSION['cart']);
unset($_SESSION['order_details']);

// quick confirmation toast on the homepage
$_SESSION['flash']      = 'You have been logged out successfully.';
$_SESSION['flash_type'] = 'success';

header('Location: index.php');
exit;

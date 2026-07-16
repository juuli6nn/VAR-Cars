<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['authenticated']) || empty($_SESSION['is_admin'])) {
    $_SESSION['flash']      = 'Admin access only. Please log in.';
    $_SESSION['flash_type'] = 'error';
    header('Location: /VAR-Cars/public/login.php');
    exit;
}

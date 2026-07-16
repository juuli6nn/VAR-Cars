<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: store.php');
    exit;
}

// pag nag add to cart na di user, punta/redirect sa signup
if (empty($_SESSION['authenticated'])) {
    $_SESSION['flash']      = 'Please log in or sign up to add cars to your cart.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: login.php');
    exit;
}

if (!empty($_SESSION['is_admin'])) {
    $_SESSION['flash']      = 'Admin accounts cannot add cars to a cart.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: store.php');
    exit;
}

$vehicleId = intval($_POST['vehicle_id'] ?? 0);

// the form tells us where to go back to, but don't trust it blindly -
// strip it down to just a filename.php (+ query string) so nobody can
// redirect users off-site
$redirect = 'store.php';
if (isset($_POST['redirect'])) {
    $rawRedirect = $_POST['redirect'];
    $filename    = basename(parse_url($rawRedirect, PHP_URL_PATH));
    if (preg_match('/^[a-zA-Z0-9_\-]+\.php$/', $filename)) {
        $redirect = $filename;
        $qs = parse_url($rawRedirect, PHP_URL_QUERY);
        if ($qs) {
            $redirect .= '?' . $qs;
        }
    }
}

if ($vehicleId > 0) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    if (!in_array($vehicleId, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $vehicleId;

        require_once '../includes/db.php';
        $userId = intval($_SESSION['user_id']);
        $sql    = "INSERT IGNORE INTO cart_items (user_id, vehicle_id) VALUES (?, ?)";
        $stmt   = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $vehicleId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['flash']      = 'Car added to your cart!';
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash']      = 'That car is already in your cart.';
        $_SESSION['flash_type'] = 'warning';
    }
} else {
    $_SESSION['flash']      = 'Invalid car selection.';
    $_SESSION['flash_type'] = 'error';
}

header('Location: ' . $redirect);
exit;

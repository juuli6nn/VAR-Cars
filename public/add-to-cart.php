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

    require_once '../includes/db.php';

    // block sold-out cars even if the form was bypassed
    $stmt = mysqli_prepare($conn, "SELECT stock FROM vehicles WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $vehicleId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row || (int)$row['stock'] <= 0) {
        $_SESSION['flash']      = 'Sorry, that car is sold out.';
        $_SESSION['flash_type'] = 'warning';
        header('Location: ' . $redirect);
        exit;
    }

    // how many of this model are already in the cart?
    $inCart = 0;
    foreach ($_SESSION['cart'] as $cid) {
        if ((int)$cid == $vehicleId) {
            $inCart++;
        }
    }

    if ($inCart >= (int)$row['stock']) {
        $_SESSION['flash']      = 'Only ' . (int)$row['stock'] . ' in stock — they are all in your cart already.';
        $_SESSION['flash_type'] = 'warning';
    } else {
        $_SESSION['cart'][] = $vehicleId;

        $userId = intval($_SESSION['user_id']);
        $sql    = "INSERT INTO cart_items (user_id, vehicle_id, qty) VALUES (?, ?, 1)
                   ON DUPLICATE KEY UPDATE qty = qty + 1";
        $stmt   = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $vehicleId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['flash']      = $inCart > 0
            ? 'Added another one — you now have ' . ($inCart + 1) . ' in your cart.'
            : 'Car added to your cart!';
        $_SESSION['flash_type'] = 'success';
    }
} else {
    $_SESSION['flash']      = 'Invalid car selection.';
    $_SESSION['flash_type'] = 'error';
}

header('Location: ' . $redirect);
exit;

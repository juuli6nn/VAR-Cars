<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: cart.php');
    exit;
}

$vehicleId = intval($_POST['vehicle_id'] ?? 0);
$mode      = ($_POST['mode'] ?? 'all') === 'one' ? 'one' : 'all';

if ($vehicleId > 0 && isset($_SESSION['cart'])) {
    $newCart    = array();
    $removedOne = false;
    foreach ($_SESSION['cart'] as $id) {
        if ($id == $vehicleId && ($mode === 'all' || !$removedOne)) {
            $removedOne = true;      // 'one' mode: drop only the first match
            continue;
        }
        $newCart[] = $id;
    }
    $_SESSION['cart'] = $newCart;

    if (!empty($_SESSION['user_id'])) {
        require_once '../includes/db.php';
        $userId = intval($_SESSION['user_id']);

        if ($mode === 'one') {
            // drop one unit; delete the row when qty hits zero
            $sql  = "UPDATE cart_items SET qty = qty - 1 WHERE user_id = ? AND vehicle_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $vehicleId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $sql  = "DELETE FROM cart_items WHERE user_id = ? AND vehicle_id = ? AND qty <= 0";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $vehicleId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $sql  = "DELETE FROM cart_items WHERE user_id = ? AND vehicle_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $vehicleId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $_SESSION['flash']      = $mode === 'one' ? 'One unit removed from your cart.' : 'Car removed from your cart.';
    $_SESSION['flash_type'] = 'success';
}

header('Location: cart.php');
exit;

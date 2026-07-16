<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: cart.php');
    exit;
}

$vehicleId = intval($_POST['vehicle_id'] ?? 0);

if ($vehicleId > 0 && isset($_SESSION['cart'])) {
    $newCart = array();
    foreach ($_SESSION['cart'] as $id) {
        if ($id != $vehicleId) {
            $newCart[] = $id;
        }
    }
    $_SESSION['cart'] = $newCart;

    if (!empty($_SESSION['user_id'])) {
        require_once '../includes/db.php';
        $userId = intval($_SESSION['user_id']);
        $sql    = "DELETE FROM cart_items WHERE user_id = ? AND vehicle_id = ?";
        $stmt   = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $userId, $vehicleId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $_SESSION['flash']      = 'Car removed from your cart.';
    $_SESSION['flash_type'] = 'success';
}

header('Location: cart.php');
exit;

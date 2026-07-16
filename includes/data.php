<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

if (empty($_SESSION['authenticated']) && isset($_SESSION['cart'])) {
    unset($_SESSION['cart'], $_SESSION['order_details']);
}

define('ADMIN_EMAIL',    'admin@varcars.com');
define('ADMIN_PASS_MD5', md5('123'));

$ALL_CARS = array();
$result = mysqli_query($conn, "SELECT * FROM vehicles ORDER BY id");
while ($row = mysqli_fetch_assoc($result)) {
    $row['id']    = (int)$row['id'];
    $row['year']  = (int)$row['year'];
    $row['price'] = (float)$row['price'];
    $ALL_CARS[] = $row;
}

$BRANDS = array(
    'Mercedes-Benz',
    'BMW',
    'Porsche',
    'Audi',
    'Lamborghini',
    'Ferrari',
    'Bentley',
    'Rolls-Royce',
    'Aston Martin',
);

function find_car($id, $cars) {
    foreach ($cars as $car) {
        if ($car['id'] == $id) {
            return $car;
        }
    }
    return null;
}

function filter_by_brand($cars, $brand) {
    if (!$brand) {
        return $cars;
    }
    $result = array();
    foreach ($cars as $car) {
        if ($car['make'] == $brand) {
            $result[] = $car;
        }
    }
    return $result;
}

function fmt_price($price) {
    return '₱' . number_format($price, 2);
}

function log_activity($conn, $action, $details = '') {
    $actor = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'guest';
    $role  = !empty($_SESSION['is_admin'])      ? 'admin'
           : (!empty($_SESSION['authenticated']) ? 'user' : 'guest');

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO activity_log (actor, role, action, details) VALUES (?, ?, ?, ?)"
    );
    if (!$stmt) {
        return;
    }
    mysqli_stmt_bind_param($stmt, 'ssss', $actor, $role, $action, $details);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

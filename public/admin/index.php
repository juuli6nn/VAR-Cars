<?php
require_once '../../includes/admin-guard.php';
require_once '../../includes/data.php';

$result      = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders");
$totalOrders = (int) mysqli_fetch_assoc($result)['cnt'];

$result      = mysqli_query($conn, "SELECT COALESCE(SUM(total), 0) AS rev FROM orders");
$totalSales  = (float) mysqli_fetch_assoc($result)['rev'];

$result      = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM users");
$totalUsers  = (int) mysqli_fetch_assoc($result)['cnt'];

$result = mysqli_query($conn,
    "SELECT o.id, o.buyer_name AS buyer, o.email, o.total, o.item_count AS count,
            o.pay_method AS method, o.created_at AS date,
            GROUP_CONCAT(CONCAT(oi.make, ' ', oi.model) ORDER BY oi.id SEPARATOR '||') AS vehicles
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     GROUP BY o.id
     ORDER BY o.created_at DESC
     LIMIT 5"
);
$recentOrders = array();
while ($row = mysqli_fetch_assoc($result)) {
    $recentOrders[] = $row;
}

$pageTitle = 'Dashboard';
$adminPage = 'dashboard';

require_once '../../includes/admin-header.php';
?>

<!-- Stat cards -->
<div class="admin-stats">
    <div class="admin-stat-card">
        <p class="admin-stat-card__label">Total Orders</p>
        <p class="admin-stat-card__value"><?= $totalOrders ?></p>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-card__label">Total Revenue</p>
        <p class="admin-stat-card__value"><?= fmt_price($totalSales) ?></p>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-card__label">Cars Listed</p>
        <p class="admin-stat-card__value"><?= count($ALL_CARS) ?></p>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-card__label">Registered Users</p>
        <p class="admin-stat-card__value"><?= $totalUsers ?></p>
    </div>
</div>

<!-- Quick actions -->
<div class="admin-actions" style="margin-bottom:2rem;">
    <a class="admin-btn admin-btn--primary" href="orders.php">View Orders</a>
    <a class="admin-btn admin-btn--ghost"   href="cars.php">View Catalogue</a>
    <a class="admin-btn admin-btn--ghost"   href="/VAR-Cars/public/index.php">View Site</a>
</div>

<!-- Recent orders -->
<div class="admin-page-head">
    <div>
        <h1>Recent Orders</h1>
        <p>Last <?= count($recentOrders) ?> of <?= $totalOrders ?> total</p>
    </div>
    <a class="admin-btn admin-btn--ghost" href="orders.php">See all &rarr;</a>
</div>

<?php if (empty($recentOrders)): ?>
<div style="background:#1B2B34;border:1px solid #3C4A56;border-radius:12px;padding:3rem;text-align:center;color:#555;">
    No orders yet. They will appear here after customers complete checkout.
</div>
<?php else: ?>
<table class="admin-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Buyer</th>
            <th>Email</th>
            <th>Items</th>
            <th>Method</th>
            <th>Date</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recentOrders as $order): ?>
        <tr>
            <td style="color:#555;">#<?= (int)$order['id'] ?></td>
            <td style="color:#fff;font-weight:500;"><?= htmlspecialchars($order['buyer'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)$order['count'] ?> vehicle<?= $order['count'] != 1 ? 's' : '' ?></td>
            <td><?= htmlspecialchars(ucfirst($order['method']), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($order['date'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-right" style="color:#fff;font-weight:600;"><?= fmt_price($order['total']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once '../../includes/admin-footer.php'; ?>

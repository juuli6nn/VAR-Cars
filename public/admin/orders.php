<?php
require_once '../../includes/admin-guard.php';
require_once '../../includes/data.php';

$result = mysqli_query($conn,
    "SELECT o.id, o.buyer_name AS buyer, o.email, o.total, o.item_count AS count,
            o.pay_method AS method, o.created_at AS date,
            GROUP_CONCAT(CONCAT(oi.make, ' ', oi.model) ORDER BY oi.id SEPARATOR '||') AS vehicles
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     GROUP BY o.id
     ORDER BY o.created_at DESC"
);
$allOrders = array();
while ($row = mysqli_fetch_assoc($result)) {
    $allOrders[] = $row;
}

$pageTitle = 'Orders';
$adminPage = 'orders';

require_once '../../includes/admin-header.php';
?>

<div class="admin-page-head">
    <div>
        <h1>All Orders</h1>
        <p><?= count($allOrders) ?> order<?= count($allOrders) != 1 ? 's' : '' ?> placed</p>
    </div>
</div>

<?php if (empty($allOrders)): ?>
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
            <th>Vehicles</th>
            <th>Method</th>
            <th>Date</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($allOrders as $order): ?>
        <tr>
            <td style="color:#555;">#<?= (int)$order['id'] ?></td>
            <td style="color:#fff;font-weight:500;"><?= htmlspecialchars($order['buyer'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?php
                $vehicles = $order['vehicles'] ? explode('||', $order['vehicles']) : array();
                foreach ($vehicles as $v):
                ?>
                <span style="display:block;font-size:0.78rem;color:#888;">
                    <?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <?php endforeach; ?>
            </td>
            <td><?= htmlspecialchars(ucfirst($order['method']), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($order['date'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-right" style="color:#fff;font-weight:600;"><?= fmt_price($order['total']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once '../../includes/admin-footer.php'; ?>

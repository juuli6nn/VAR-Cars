<?php
require_once '../../includes/admin-guard.php';
require_once '../../includes/data.php';

$pageTitle = 'Catalogue';
$adminPage = 'cars';

require_once '../../includes/admin-header.php';
?>

<div class="admin-page-head">
    <div>
        <h1>Car Catalogue</h1>
        <p><?= count($ALL_CARS) ?> vehicles listed across <?= count($BRANDS) ?> brands</p>
    </div>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>Photo</th>
            <th>Vehicle</th>
            <th>Brand</th>
            <th>Type</th>
            <th>Year</th>
            <th>Engine</th>
            <th>Fuel</th>
            <th class="text-right">Price</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ALL_CARS as $car): ?>
        <tr>
            <td>
                <div style="
                    width:72px;aspect-ratio:4/3;border-radius:6px;flex-shrink:0;
                    background:url('/VAR-Cars/public/assets/images/<?= rawurlencode($car['img']) ?>') center/cover #1B2B34;
                    border:1px solid #3C4A56;
                "></div>
            </td>
            <td>
                <p style="font-weight:600;color:#ffffff;margin:0 0 2px;">
                    <?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p style="font-size:0.75rem;color:#555;margin:0;">
                    <?= htmlspecialchars($car['transmission'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </td>
            <td><?= htmlspecialchars($car['make'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($car['type'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int)$car['year'] ?></td>
            <td style="font-size:0.78rem;color:#888;"><?= htmlspecialchars($car['engine'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($car['fuel'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-right" style="color:#fff;font-weight:600;"><?= fmt_price($car['price']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../../includes/admin-footer.php'; ?>

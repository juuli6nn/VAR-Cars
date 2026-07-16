<?php
require_once '../../includes/admin-guard.php';
require_once '../../includes/data.php';

// ── Inventory report ──────────────────────────────────────────
// remaining stock per car + how many units have actually been sold
$inventory = array();
$result = mysqli_query($conn,
    "SELECT v.id, v.make, v.model, v.price, v.stock,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.vehicle_id = v.id) AS sold
     FROM vehicles v
     ORDER BY v.stock ASC, v.make");
while ($row = mysqli_fetch_assoc($result)) {
    $inventory[] = $row;
}

$totalInStock = 0;
$soldOutCount = 0;
foreach ($inventory as $inv) {
    $totalInStock += (int)$inv['stock'];
    if ((int)$inv['stock'] <= 0) {
        $soldOutCount++;
    }
}

// ── Audit log ─────────────────────────────────────────────────
// distinct actions for the filter dropdown
$actionTypes = array();
$result = mysqli_query($conn, "SELECT DISTINCT action FROM activity_log ORDER BY action");
while ($row = mysqli_fetch_assoc($result)) {
    $actionTypes[] = $row['action'];
}

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

if ($filter != '') {
    $stmt = mysqli_prepare($conn,
        "SELECT actor, role, action, details, created_at
         FROM activity_log WHERE action = ? ORDER BY created_at DESC LIMIT 200");
    mysqli_stmt_bind_param($stmt, 's', $filter);
    mysqli_stmt_execute($stmt);
    $logResult = mysqli_stmt_get_result($stmt);
} else {
    $logResult = mysqli_query($conn,
        "SELECT actor, role, action, details, created_at
         FROM activity_log ORDER BY created_at DESC LIMIT 200");
}

$logRows = array();
while ($row = mysqli_fetch_assoc($logResult)) {
    $logRows[] = $row;
}

$pageTitle = 'Reports';
$adminPage = 'reports';

require_once '../../includes/admin-header.php';
?>

<!-- Inventory summary -->
<div class="admin-stats">
    <div class="admin-stat-card">
        <p class="admin-stat-card__label">Models Listed</p>
        <p class="admin-stat-card__value"><?= count($inventory) ?></p>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-card__label">Units In Stock</p>
        <p class="admin-stat-card__value"><?= $totalInStock ?></p>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-card__label">Sold-Out Models</p>
        <p class="admin-stat-card__value"><?= $soldOutCount ?></p>
    </div>
    <div class="admin-stat-card">
        <p class="admin-stat-card__label">Logged Activities</p>
        <p class="admin-stat-card__value"><?= count($logRows) ?><?= count($logRows) == 200 ? '+' : '' ?></p>
    </div>
</div>

<!-- Inventory report -->
<div class="admin-page-head">
    <div>
        <h1>Inventory Report</h1>
        <p>Remaining stock and units sold per vehicle</p>
    </div>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>Vehicle</th>
            <th class="text-right">Price</th>
            <th class="text-right">Units Sold</th>
            <th class="text-right">Remaining Stock</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($inventory as $inv): ?>
        <tr>
            <td style="color:#fff;font-weight:500;"><?= htmlspecialchars($inv['make'] . ' ' . $inv['model'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-right"><?= fmt_price($inv['price']) ?></td>
            <td class="text-right"><?= (int)$inv['sold'] ?></td>
            <td class="text-right" style="color:#fff;font-weight:600;"><?= (int)$inv['stock'] ?></td>
            <td>
                <?php if ((int)$inv['stock'] <= 0): ?>
                    <span style="color:#e74c3c;font-weight:600;">Sold out</span>
                <?php elseif ((int)$inv['stock'] <= 2): ?>
                    <span style="color:#e67e22;font-weight:600;">Low stock</span>
                <?php else: ?>
                    <span style="color:#2ecc71;">In stock</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Audit log -->
<div class="admin-page-head" style="margin-top:2.5rem;">
    <div>
        <h1>Audit Log</h1>
        <p>Recent activity by everyone logged in to the system (latest 200)</p>
    </div>
    <form method="GET" action="reports.php">
        <select name="filter" class="form-select" onchange="this.form.submit()" style="min-width:180px;">
            <option value="">All actions</option>
            <?php foreach ($actionTypes as $act): ?>
            <option value="<?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>" <?= $filter == $act ? 'selected' : '' ?>>
                <?= htmlspecialchars($act, ENT_QUOTES, 'UTF-8') ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (empty($logRows)): ?>
<div style="background:#1B2B34;border:1px solid #3C4A56;border-radius:12px;padding:3rem;text-align:center;color:#555;">
    No activity recorded yet<?= $filter ? ' for this action' : '' ?>.
</div>
<?php else: ?>
<table class="admin-table">
    <thead>
        <tr>
            <th>When</th>
            <th>Actor</th>
            <th>Role</th>
            <th>Action</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logRows as $log): ?>
        <tr>
            <td style="color:#888;font-size:0.8rem;white-space:nowrap;"><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="color:#fff;"><?= htmlspecialchars($log['actor'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars(ucfirst($log['role']), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></td>
            <td style="color:#aaa;"><?= htmlspecialchars($log['details'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once '../../includes/admin-footer.php'; ?>

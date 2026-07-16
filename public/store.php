<?php
require_once '../includes/data.php';

$activeBrand = '';
if (isset($_GET['brand']) && in_array($_GET['brand'], $BRANDS)) {
    $activeBrand = $_GET['brand'];
}

$cars = filter_by_brand($ALL_CARS, $activeBrand);

if ($activeBrand) {
    $pageTitle = $activeBrand . ' Cars';
    $pageDesc  = 'Browse ' . $activeBrand . ' vehicles at VAR Cars.';
} else {
    $pageTitle = 'Browse All Cars';
    $pageDesc  = 'Browse all vehicles at VAR Cars.';
}
$activePage = 'store';

require_once '../includes/header.php';
?>

<main>

<div class="page-hero">
    <div class="page-hero__inner">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb__sep" aria-hidden="true">&rsaquo;</span>
            <span>Browse Cars</span>
            <?php if ($activeBrand): ?>
            <span class="breadcrumb__sep" aria-hidden="true">&rsaquo;</span>
            <span><?= htmlspecialchars($activeBrand, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </nav>

        <?php if ($activeBrand): ?>
        <h1><?= htmlspecialchars($activeBrand, ENT_QUOTES, 'UTF-8') ?></h1>
        <?php else: ?>
        <h1>All Vehicles</h1>
        <?php endif; ?>

        <p><?= count($cars) ?> vehicle<?= count($cars) != 1 ? 's' : '' ?> available<?= $activeBrand ? ' from this brand' : '' ?></p>
    </div>
</div>

<section class="section container">

    <!-- Brand filter tabs -->
    <div class="filter-tabs">
        <a href="store.php" class="filter-tab <?= ($activeBrand == '') ? 'active' : '' ?>">
            All <span class="text-muted">(<?= count($ALL_CARS) ?>)</span>
        </a>
        <?php foreach ($BRANDS as $brand):
            $brandCars = filter_by_brand($ALL_CARS, $brand);
        ?>
        <a href="store.php?brand=<?= urlencode($brand) ?>"
           class="filter-tab <?= ($activeBrand == $brand) ? 'active' : '' ?>">
            <?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?>
            <span class="text-muted">(<?= count($brandCars) ?>)</span>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($cars)): ?>
    <div class="empty-state">
        <h3>No vehicles found</h3>
        <p>Try a different brand.</p>
        <a class="button button--primary" href="store.php">View all cars</a>
    </div>
    <?php else: ?>
    <div class="car-grid">
        <?php foreach ($cars as $car): ?>
        <article class="car-card">
            <div class="car-card__thumb"
                 style="background-image:url('/VAR-Cars/public/assets/images/<?= rawurlencode($car['img']) ?>');"
                 role="img"
                 aria-label="<?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="car-card__body">
                <div class="car-card__meta">
                    <span class="car-card__year"><?= (int)$car['year'] ?></span>
                </div>
                <p class="car-card__name"><?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="car-card__type">
                    <?= htmlspecialchars($car['type'], ENT_QUOTES, 'UTF-8') ?>
                    &middot; <?= htmlspecialchars($car['transmission'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <p class="car-card__price"><?= fmt_price($car['price']) ?></p>
            </div>

            <div class="car-card__actions">
                <?php if (empty($_SESSION['is_admin'])): ?>
                <form method="POST" action="add-to-cart.php" style="width:100%;">
                    <input type="hidden" name="vehicle_id" value="<?= (int)$car['id'] ?>">
                    <input type="hidden" name="redirect"
                           value="store.php<?= $activeBrand ? '?brand=' . urlencode($activeBrand) : '' ?>">
                    <button type="submit" class="button button--primary button--sm button--full">Add to cart</button>
                </form>
                <?php else: ?>
                <span class="button button--ghost button--sm button--full" style="cursor:default;">Admin view</span>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</section>
</main>

<?php require_once '../includes/footer.php'; ?>

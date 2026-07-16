<?php
require_once '../includes/data.php';

$cartIds   = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$cartItems = array();   // grouped by vehicle id, each entry has 'car' + 'qty'
$cartTotal = 0;
$cartUnits = 0;

foreach ($cartIds as $id) {
    $id  = (int)$id;
    $car = find_car($id, $ALL_CARS);
    if ($car != null) {
        if (!isset($cartItems[$id])) {
            $cartItems[$id] = array('car' => $car, 'qty' => 0);
        }
        $cartItems[$id]['qty']++;
        $cartTotal += $car['price'];
        $cartUnits++;
    }
}

$pageTitle  = 'Your Cart';
$pageDesc   = 'Review the vehicles in your VAR Cars shopping cart.';
$activePage = 'cart';

require_once '../includes/header.php';
?>

<main>
<div class="section container">

    <!-- Page heading -->
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--text-2xl);">Your Cart</h1>
            <p class="section-head__sub">
                <?= $cartUnits ?> vehicle<?= $cartUnits != 1 ? 's' : '' ?> selected
            </p>
        </div>
        <a class="button button--ghost" href="store.php">&larr; Keep browsing</a>
    </div>

    <?php if (empty($cartItems)): ?>
    <!-- Empty state -->
    <div class="empty-state">
        <h3>Your cart is empty</h3>
        <p>You haven't added any cars yet. Head to the store to get started.</p>
        <a class="button button--primary" href="store.php">Browse all cars</a>
    </div>

    <?php else: ?>
    <!-- Cart layout: table + summary sidebar -->
    <div class="cart-layout">

        <!-- Left: cart table -->
        <div>
            <table class="cart-table" aria-label="Cart items">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th style="text-align:center;">Qty</th>
                        <th class="text-right">Price</th>
                        <th><span class="sr-only">Remove</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): $car = $item['car']; $qty = $item['qty']; ?>
                    <tr>
                        <!-- Thumbnail + name -->
                        <td>
                            <div style="display:flex;align-items:center;gap:var(--space-md);">
                                <div class="cart-thumb"
                                     style="background-image:url('/VAR-Cars/public/assets/images/<?= rawurlencode($car['img']) ?>');"
                                     role="img"
                                     aria-label="<?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div>
                                    <p class="cart-item__name">
                                        <?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <p class="cart-item__meta">
                                        <?= (int)$car['year'] ?>
                                        &middot; <?= htmlspecialchars($car['type'], ENT_QUOTES, 'UTF-8') ?>
                                        &middot; <?= htmlspecialchars($car['transmission'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                            </div>
                        </td>

                        <!-- Quantity controls -->
                        <td>
                            <div style="display:flex;align-items:center;justify-content:center;gap:var(--space-sm);">
                                <form method="POST" action="remove-from-cart.php">
                                    <input type="hidden" name="vehicle_id" value="<?= (int)$car['id'] ?>">
                                    <input type="hidden" name="mode" value="one">
                                    <button type="submit" class="button button--ghost button--sm"
                                            aria-label="Remove one" title="Remove one">&minus;</button>
                                </form>
                                <span style="min-width:1.5rem;text-align:center;font-weight:600;"><?= $qty ?></span>
                                <?php if ($qty < (int)$car['stock']): ?>
                                <form method="POST" action="add-to-cart.php">
                                    <input type="hidden" name="vehicle_id" value="<?= (int)$car['id'] ?>">
                                    <input type="hidden" name="redirect" value="cart.php">
                                    <button type="submit" class="button button--ghost button--sm"
                                            aria-label="Add one more" title="Add one more">+</button>
                                </form>
                                <?php else: ?>
                                <button type="button" class="button button--ghost button--sm" disabled
                                        title="No more in stock">+</button>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Price -->
                        <td class="cart-item__price text-right">
                            <?= fmt_price($car['price'] * $qty) ?>
                            <?php if ($qty > 1): ?>
                            <div class="text-muted text-sm"><?= $qty ?> &times; <?= fmt_price($car['price']) ?></div>
                            <?php endif; ?>
                        </td>

                        <!-- Remove -->
                        <td>
                            <form method="POST" action="remove-from-cart.php">
                                <input type="hidden" name="vehicle_id" value="<?= (int)$car['id'] ?>">
                                <input type="hidden" name="mode" value="all">
                                <button type="submit" class="button button--danger button--sm">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Right: order summary -->
        <aside class="summary-card" aria-label="Order summary">
            <h3>Order Summary</h3>

            <?php foreach ($cartItems as $item): $car = $item['car']; $qty = $item['qty']; ?>
            <div class="summary-line">
                <span><?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?><?= $qty > 1 ? ' &times;' . $qty : '' ?></span>
                <span><?= fmt_price($car['price'] * $qty) ?></span>
            </div>
            <?php endforeach; ?>

            <div class="summary-total">
                <span>Total</span>
                <span><?= fmt_price($cartTotal) ?></span>
            </div>

            <a class="button button--primary button--full" href="checkout.php"
               style="margin-top:var(--space-lg);">
                Proceed to Checkout &rarr;
            </a>

            <p class="text-muted text-sm" style="margin-top:var(--space-md);text-align:center;">
                No payment is processed &mdash; this is a demo.
            </p>
        </aside>

    </div>
    <?php endif; ?>

</div>
</main>

<?php require_once '../includes/footer.php'; ?>

<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['last_order'])) {
    header('Location: store.php');
    exit;
}

$order = $_SESSION['last_order'];

$buyerName  = htmlspecialchars(isset($order['buyer'])  ? $order['buyer']  : 'Customer', ENT_QUOTES, 'UTF-8');
$buyerEmail = htmlspecialchars(isset($order['email'])  ? $order['email']  : '',          ENT_QUOTES, 'UTF-8');
$orderTotal = number_format((float)(isset($order['total'])  ? $order['total']  : 0), 2);
$itemCount  = (int)(isset($order['count'])  ? $order['count']  : 0);
$payMethod  = htmlspecialchars(ucfirst(isset($order['method']) ? $order['method'] : 'card'), ENT_QUOTES, 'UTF-8');

unset($_SESSION['last_order']);

$pageTitle  = 'Order Confirmed';
$pageDesc   = 'Your VAR Cars order has been confirmed.';
$activePage = '';

require_once '../includes/header.php';
?>

<main>
<div class="success-wrap">
    <div class="success-card">

        <!-- Check icon -->
        <div class="success-card__icon" aria-hidden="true">&#10003;</div>

        <!-- Heading -->
        <h1>Order Confirmed!</h1>
        <p>
            Thank you, <strong><?= $buyerName ?></strong>!<br>
            Your order has been received and is being processed.
        </p>

        <!-- Receipt -->
        <div style="
            background: var(--c-card);
            border: 1px solid var(--c-border);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            margin-bottom: var(--space-xl);
            text-align: left;
        ">
            <h3 style="margin-bottom:var(--space-md);font-size:var(--text-base);">
                Order Receipt
            </h3>

            <div class="summary-line">
                <span class="text-muted">Buyer</span>
                <span><?= $buyerName ?></span>
            </div>
            <div class="summary-line">
                <span class="text-muted">Email</span>
                <span><?= $buyerEmail ?></span>
            </div>
            <div class="summary-line">
                <span class="text-muted">Vehicles ordered</span>
                <span><?= $itemCount ?> vehicle<?= $itemCount != 1 ? 's' : '' ?></span>
            </div>
            <div class="summary-line">
                <span class="text-muted">Payment method</span>
                <span><?= $payMethod ?></span>
            </div>

            <div class="summary-total">
                <span>Total Paid</span>
                <span>&#8369;<?= $orderTotal ?></span>
            </div>
        </div>

        <!-- Educational notice -->
        <p style="font-size:var(--text-xs);color:var(--c-silver);margin-bottom:var(--space-xl);">
            Educational purposes only.
        </p>

        <!-- Actions -->
        <div style="display:flex;gap:var(--space-md);justify-content:center;flex-wrap:wrap;">
            <a class="button button--primary" href="store.php">Browse More Cars</a>
            <a class="button button--ghost" href="index.php">Go to Home</a>
        </div>

    </div>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>

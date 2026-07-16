<?php

require_once '../includes/data.php';

$cartIds      = isset($_SESSION['cart'])          ? $_SESSION['cart']          : array();
$orderDetails = isset($_SESSION['order_details']) ? $_SESSION['order_details'] : array();

if (empty($cartIds) || empty($orderDetails)) {
    $_SESSION['flash']      = 'Please complete your details first.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: checkout.php');
    exit;
}

$cartItems = array();
$cartTotal = 0;
foreach ($cartIds as $id) {
    $car = find_car((int)$id, $ALL_CARS);
    if ($car != null) {
        $cartItems[] = $car;
        $cartTotal  += $car['price'];
    }
}

$errors = array();
$values = array(
    'pay_method'  => 'credit',
    'card_name'   => strtoupper($orderDetails['full_name']),
    'card_number' => '4111 1111 1111 1111',
    'card_expiry' => '12/28',
    'card_cvv'    => '123',
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $values['pay_method']  = isset($_POST['pay_method'])  ? $_POST['pay_method']          : 'credit';
    $values['card_name']   = trim(isset($_POST['card_name'])   ? $_POST['card_name']   : '');
    $values['card_number'] = trim(isset($_POST['card_number']) ? $_POST['card_number'] : '');
    $values['card_expiry'] = trim(isset($_POST['card_expiry']) ? $_POST['card_expiry'] : '');
    $values['card_cvv']    = trim(isset($_POST['card_cvv'])    ? $_POST['card_cvv']    : '');

    if ($values['pay_method'] == 'credit' || $values['pay_method'] == 'debit') {

        if ($values['card_name'] == '') {
            $errors['card_name'] = 'Name on card is required.';
        }

        $cardDigits = preg_replace('/\s+/', '', $values['card_number']);
        if ($cardDigits == '') {
            $errors['card_number'] = 'Card number is required.';
        } elseif (!preg_match('/^\d{13,19}$/', $cardDigits)) {
            $errors['card_number'] = 'Enter a valid card number (13-19 digits).';
        }

        if ($values['card_expiry'] == '') {
            $errors['card_expiry'] = 'Expiry date is required.';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $values['card_expiry'])) {
            $errors['card_expiry'] = 'Use MM/YY format (e.g. 08/27).';
        }

        if ($values['card_cvv'] == '') {
            $errors['card_cvv'] = 'CVV is required.';
        } elseif (!preg_match('/^\d{3,4}$/', $values['card_cvv'])) {
            $errors['card_cvv'] = 'CVV must be 3 or 4 digits.';
        }
    }

    // make sure nothing in the cart sold out while the user was checking out
    $needed = array();               // vehicle_id => units requested
    foreach ($cartItems as $item) {
        $vid = (int)$item['id'];
        $needed[$vid] = isset($needed[$vid]) ? $needed[$vid] + 1 : 1;
    }
    $soldOut = array();
    foreach ($needed as $vid => $qty) {
        $stmt = mysqli_prepare($conn, "SELECT make, model, stock FROM vehicles WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $vid);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if (!$row || (int)$row['stock'] < $qty) {
            $soldOut[] = $row ? ($row['make'] . ' ' . $row['model']) : ('Vehicle #' . $vid);
        }
    }
    if (!empty($soldOut)) {
        $errors['stock'] = 'Sorry, these are no longer available: ' . implode(', ', $soldOut) . '. Please remove them from your cart.';
    }

    if (empty($errors)) {

        $itemCount = count($cartItems);
        $sql  = "INSERT INTO orders (buyer_name, email, total, item_count, pay_method) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssdis',
            $orderDetails['full_name'],
            $orderDetails['email'],
            $cartTotal,
            $itemCount,
            $values['pay_method']
        );
        mysqli_stmt_execute($stmt);
        $orderId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);


        $sql  = "INSERT INTO order_items (order_id, vehicle_id, make, model, price) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        foreach ($cartItems as $item) {
            mysqli_stmt_bind_param($stmt, 'iissd',
                $orderId,
                $item['id'],
                $item['make'],
                $item['model'],
                $item['price']
            );
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);

        // draw down stock, one unit per purchased vehicle (never below 0)
        $sql  = "UPDATE vehicles SET stock = GREATEST(stock - 1, 0) WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        foreach ($cartItems as $item) {
            $vid = (int)$item['id'];
            mysqli_stmt_bind_param($stmt, 'i', $vid);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);

        log_activity($conn, 'order_placed', 'Order #' . $orderId . ' — ' . fmt_price($cartTotal));

        unset($_SESSION['cart'], $_SESSION['order_details']);
        if (!empty($_SESSION['user_id'])) {
            $userId = intval($_SESSION['user_id']);
            $sql    = "DELETE FROM cart_items WHERE user_id = ?";
            $stmt   = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $_SESSION['last_order'] = array(
            'id'     => $orderId,
            'buyer'  => $orderDetails['full_name'],
            'email'  => $orderDetails['email'],
            'total'  => $cartTotal,
            'count'  => $itemCount,
            'method' => $values['pay_method'],
        );

        header('Location: order-success.php');
        exit;
    }
}

$pageTitle  = 'Payment';
$pageDesc   = 'Complete your VAR Cars purchase — enter your payment details.';
$activePage = 'store';

require_once '../includes/header.php';
?>

<main>
<div class="section container">

    <!-- Page heading -->
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--text-2xl);">Payment</h1>
            <p class="section-head__sub">Step 2 of 2 &mdash; Payment details</p>
        </div>
        <a class="button button--ghost" href="checkout.php">&larr; Back to details</a>
    </div>

    <div class="checkout-layout">

        <!-- Left: payment form -->
        <form class="checkout-form" method="POST" action="payment.php" novalidate id="payment-form">

            <?php if (isset($errors['stock'])): ?>
            <div class="flash flash--error" style="margin-bottom:var(--space-md);">
                <?= htmlspecialchars($errors['stock'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>

            <!-- Payment method selector -->
            <div class="checkout-form__section">
                <h3>Payment Method</h3>
                <div class="payment-methods">
                    <label class="payment-method <?= ($values['pay_method'] == 'credit') ? 'selected' : '' ?>">
                        <input type="radio" name="pay_method" value="credit"
                               <?= ($values['pay_method'] == 'credit') ? 'checked' : '' ?>
                               style="position:absolute;opacity:0;">
                        Credit Card
                    </label>
                    <label class="payment-method <?= ($values['pay_method'] == 'debit') ? 'selected' : '' ?>">
                        <input type="radio" name="pay_method" value="debit"
                               <?= ($values['pay_method'] == 'debit') ? 'checked' : '' ?>
                               style="position:absolute;opacity:0;">
                        Debit Card
                    </label>
                    <label class="payment-method <?= ($values['pay_method'] == 'bank') ? 'selected' : '' ?>">
                        <input type="radio" name="pay_method" value="bank"
                               <?= ($values['pay_method'] == 'bank') ? 'checked' : '' ?>
                               style="position:absolute;opacity:0;">
                        Bank Transfer
                    </label>
                </div>
            </div>

            <!-- Card fields (hidden for bank transfer) -->
            <div class="checkout-form__section" id="card-fields">
                <h3>Card Details</h3>

                <div style="display:flex;flex-direction:column;gap:var(--space-md);">

                    <!-- Name on card -->
                    <div class="form-group">
                        <label class="form-label" for="card_name">
                            Name on Card <span>*</span>
                        </label>
                        <input
                            class="form-input <?= isset($errors['card_name']) ? 'error' : '' ?>"
                            type="text"
                            id="card_name"
                            name="card_name"
                            value="<?= htmlspecialchars($values['card_name'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="As it appears on the card"
                            autocomplete="cc-name">
                        <?php if (isset($errors['card_name'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['card_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Card number -->
                    <div class="form-group">
                        <label class="form-label" for="card_number">
                            Card Number <span>*</span>
                        </label>
                        <input
                            class="form-input <?= isset($errors['card_number']) ? 'error' : '' ?>"
                            type="text"
                            id="card_number"
                            name="card_number"
                            value="<?= htmlspecialchars($values['card_number'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="1234 5678 9012 3456"
                            maxlength="19"
                            autocomplete="cc-number">
                        <?php if (isset($errors['card_number'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['card_number'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Expiry and CVV side by side -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="card_expiry">
                                Expiry Date <span>*</span>
                            </label>
                            <input
                                class="form-input <?= isset($errors['card_expiry']) ? 'error' : '' ?>"
                                type="text"
                                id="card_expiry"
                                name="card_expiry"
                                value="<?= htmlspecialchars($values['card_expiry'], ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="MM/YY"
                                maxlength="5"
                                autocomplete="cc-exp">
                            <?php if (isset($errors['card_expiry'])): ?>
                                <span class="form-error"><?= htmlspecialchars($errors['card_expiry'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="card_cvv">
                                CVV <span>*</span>
                            </label>
                            <input
                                class="form-input <?= isset($errors['card_cvv']) ? 'error' : '' ?>"
                                type="text"
                                id="card_cvv"
                                name="card_cvv"
                                value="<?= htmlspecialchars($values['card_cvv'], ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="&bull;&bull;&bull;"
                                maxlength="4"
                                autocomplete="cc-csc">
                            <?php if (isset($errors['card_cvv'])): ?>
                                <span class="form-error"><?= htmlspecialchars($errors['card_cvv'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Bank transfer details (shown when bank is selected) -->
            <div class="checkout-form__section" id="bank-fields" style="display:none;">
                <h3>Bank Transfer Details</h3>
                <div style="background:var(--c-nav);border:1px solid var(--c-border);border-radius:var(--radius-md);padding:var(--space-lg);">
                    <p style="color:var(--c-silver);font-size:var(--text-sm);line-height:1.8;">
                        <strong style="color:var(--c-white);">Name:</strong> <?= htmlspecialchars($orderDetails['full_name'], ENT_QUOTES, 'UTF-8') ?><br>
                        <strong style="color:var(--c-white);">Email:</strong> <?= htmlspecialchars($orderDetails['email'], ENT_QUOTES, 'UTF-8') ?><br>
                        <strong style="color:var(--c-white);">Contact:</strong> <?= htmlspecialchars($orderDetails['contact'], ENT_QUOTES, 'UTF-8') ?><br>
                        <strong style="color:var(--c-white);">Address:</strong> <?= htmlspecialchars($orderDetails['address'] . ', ' . $orderDetails['city'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <!-- Confirm button -->
            <button type="submit" class="button button--primary button--full">
                &#10003; Confirm Order &mdash; <?= fmt_price($cartTotal) ?>
            </button>

            <p class="text-muted text-sm" style="text-align:center;">
                Educational purposes only.
            </p>

        </form>

        <!-- Right: order summary -->
        <aside class="summary-card" aria-label="Order summary">
            <h3>Order Summary</h3>

            <?php
            // group duplicates so "2 × same model" shows as one line
            $grouped = array();
            foreach ($cartItems as $car) {
                $cid = (int)$car['id'];
                if (!isset($grouped[$cid])) {
                    $grouped[$cid] = array('car' => $car, 'qty' => 0);
                }
                $grouped[$cid]['qty']++;
            }
            foreach ($grouped as $g): $car = $g['car']; $qty = $g['qty']; ?>
            <div class="summary-line">
                <span><?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?><?= $qty > 1 ? ' &times;' . $qty : '' ?></span>
                <span><?= fmt_price($car['price'] * $qty) ?></span>
            </div>
            <?php endforeach; ?>

            <div class="summary-total">
                <span>Total</span>
                <span><?= fmt_price($cartTotal) ?></span>
            </div>
        </aside>

    </div>

</div>
</main>

<!-- Payment method toggle -->
<script>
(function () {
    var radios      = document.querySelectorAll('input[name="pay_method"]');
    var cardFields  = document.getElementById('card-fields');
    var bankFields  = document.getElementById('bank-fields');
    var methods     = document.querySelectorAll('.payment-method');

    function updateMethod() {
        var selected = '';
        for (var i = 0; i < radios.length; i++) {
            if (radios[i].checked) selected = radios[i].value;
            methods[i].classList.toggle('selected', radios[i].checked);
        }
        if (selected === 'bank') {
            cardFields.style.display = 'none';
            bankFields.style.display = 'block';
        } else {
            cardFields.style.display = 'block';
            bankFields.style.display = 'none';
        }
    }

    for (var i = 0; i < radios.length; i++) {
        radios[i].addEventListener('change', updateMethod);
    }
    for (var i = 0; i < methods.length; i++) {
        (function(idx) {
            methods[idx].addEventListener('click', function () {
                radios[idx].checked = true;
                updateMethod();
            });
        })(i);
    }

    updateMethod();
})();
</script>

<?php require_once '../includes/footer.php'; ?>

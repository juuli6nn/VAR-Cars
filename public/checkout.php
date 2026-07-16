<?php
require_once '../includes/data.php';

$cartIds = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
if (empty($cartIds)) {
    $_SESSION['flash']      = 'Your cart is empty. Add a car first.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: store.php');
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
    'full_name' => '',
    'email'     => '',
    'address'   => '',
    'city'      => '',
    'contact'   => '+63 ',
    'notes'     => '',
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    foreach ($values as $key => $val) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    // same validation style as register.php
    if ($values['full_name'] == '') {
        $errors['full_name'] = 'Full name is required.';
    }

    if ($values['email'] == '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $values['email'])) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($values['address'] == '') {
        $errors['address'] = 'Street address is required.';
    }

    if ($values['city'] == '') {
        $errors['city'] = 'City / municipality is required.';
    }

    $contactStripped = trim($values['contact']);
    if ($contactStripped == '' || $contactStripped == '+63' || $contactStripped == '+63 ') {
        $errors['contact'] = 'Contact number is required.';
    } elseif (!preg_match('/^[\d\s\+\-\(\)]{10,20}$/', $contactStripped)) {
        $errors['contact'] = 'Enter a valid contact number (e.g. +63 912 345 6789).';
    }

    if (empty($errors)) {
        $_SESSION['order_details'] = $values;
        header('Location: payment.php');
        exit;
    }
}

$pageTitle  = 'Checkout';
$pageDesc   = 'Complete your VAR Cars order — enter your contact and delivery details.';
$activePage = 'store';

require_once '../includes/header.php';
?>

<main>
<div class="section container">

    <!-- Page heading -->
    <div class="section-head">
        <div>
            <h1 style="font-size:var(--text-2xl);">Checkout</h1>
            <p class="section-head__sub">Step 1 of 2 &mdash; Your details</p>
        </div>
        <a class="button button--ghost" href="cart.php">&larr; Back to cart</a>
    </div>

    <div class="checkout-layout">

        <!-- Left: details form -->
        <form class="checkout-form" method="POST" action="checkout.php" novalidate id="checkout-form">

            <!-- Personal Information -->
            <div class="checkout-form__section">
                <h3>Personal Information</h3>

                <div style="display:flex;flex-direction:column;gap:var(--space-md);">

                    <!-- Full name -->
                    <div class="form-group">
                        <label class="form-label" for="full_name">
                            Full Name <span>*</span>
                        </label>
                        <input
                            class="form-input <?= isset($errors['full_name']) ? 'error' : '' ?>"
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="<?= htmlspecialchars($values['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="e.g. Juan dela Cruz"
                            autocomplete="name"
                            required>
                        <?php if (isset($errors['full_name'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label" for="email">
                            Email Address <span>*</span>
                        </label>
                        <input
                            class="form-input <?= isset($errors['email']) ? 'error' : '' ?>"
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Contact number -->
                    <div class="form-group">
                        <label class="form-label" for="contact">
                            Contact Number <span>*</span>
                        </label>
                        <input
                            class="form-input <?= isset($errors['contact']) ? 'error' : '' ?>"
                            type="tel"
                            id="contact"
                            name="contact"
                            value="<?= htmlspecialchars($values['contact'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="+63 9XX XXX XXXX"
                            autocomplete="tel"
                            required>
                        <?php if (isset($errors['contact'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['contact'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- Delivery Address -->
            <div class="checkout-form__section">
                <h3>Delivery Address</h3>

                <div style="display:flex;flex-direction:column;gap:var(--space-md);">

                    <!-- Street address -->
                    <div class="form-group">
                        <label class="form-label" for="address">
                            Street Address <span>*</span>
                        </label>
                        <input
                            class="form-input <?= isset($errors['address']) ? 'error' : '' ?>"
                            type="text"
                            id="address"
                            name="address"
                            value="<?= htmlspecialchars($values['address'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="House no., Street, Barangay"
                            autocomplete="street-address"
                            required>
                        <?php if (isset($errors['address'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['address'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- City -->
                    <div class="form-group">
                        <label class="form-label" for="city">
                            City / Municipality <span>*</span>
                        </label>
                        <input
                            class="form-input <?= isset($errors['city']) ? 'error' : '' ?>"
                            type="text"
                            id="city"
                            name="city"
                            value="<?= htmlspecialchars($values['city'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="e.g. Quezon City"
                            autocomplete="address-level2"
                            required>
                        <?php if (isset($errors['city'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['city'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Notes (optional) -->
                    <div class="form-group">
                        <label class="form-label" for="notes">
                            Order Notes <em style="font-weight:400;color:var(--c-silver);">(optional)</em>
                        </label>
                        <textarea
                            class="form-textarea"
                            id="notes"
                            name="notes"
                            placeholder="Special requests, preferred pickup time, etc."
                            rows="3"><?= htmlspecialchars($values['notes'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="button button--primary button--full">
                Continue to Payment &rarr;
            </button>

        </form>

        <!-- Right: order summary -->
        <aside class="summary-card" aria-label="Order summary">
            <h3>Order Summary</h3>

            <?php foreach ($cartItems as $car): ?>
            <div class="summary-line">
                <span><?= htmlspecialchars($car['make'] . ' ' . $car['model'], ENT_QUOTES, 'UTF-8') ?></span>
                <span><?= fmt_price($car['price']) ?></span>
            </div>
            <?php endforeach; ?>

            <div class="summary-total">
                <span>Total</span>
                <span><?= fmt_price($cartTotal) ?></span>
            </div>

            <p class="text-muted text-sm" style="margin-top:var(--space-md);text-align:center;">
                No real payment is processed.<br>This is an educational demo.
            </p>
        </aside>

    </div>

</div>
</main>

<!-- Client-side validation -->
<script>
(function () {
    var form = document.getElementById('checkout-form');

    form.addEventListener('submit', function (e) {
        var valid = true;

        // Remove previous JS errors
        var oldErrors = form.querySelectorAll('.js-error');
        for (var i = 0; i < oldErrors.length; i++) {
            oldErrors[i].remove();
        }
        var oldInvalid = form.querySelectorAll('.js-invalid');
        for (var i = 0; i < oldInvalid.length; i++) {
            oldInvalid[i].classList.remove('error', 'js-invalid');
        }

        function showError(inputId, msg) {
            var input = document.getElementById(inputId);
            if (!input) return;
            input.classList.add('error', 'js-invalid');
            var span       = document.createElement('span');
            span.className   = 'form-error js-error';
            span.textContent = msg;
            input.parentNode.appendChild(span);
            valid = false;
        }

        var fullName = document.getElementById('full_name').value.trim();
        var email    = document.getElementById('email').value.trim();
        var address  = document.getElementById('address').value.trim();
        var city     = document.getElementById('city').value.trim();
        var contact  = document.getElementById('contact').value.trim();

        if (!fullName) showError('full_name', 'Full name is required.');
        if (!email)    showError('email', 'Email address is required.');
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
                       showError('email', 'Enter a valid email address.');
        if (!address)  showError('address', 'Street address is required.');
        if (!city)     showError('city', 'City is required.');
        if (!contact)  showError('contact', 'Contact number is required.');

        if (!valid) e.preventDefault();
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>

<?php

require_once '../includes/data.php';

if (!empty($_SESSION['authenticated'])) {
    header('Location: index.php');
    exit;
}

$errors  = array();
$success = false;
$values  = array(
    'full_name' => '',
    'email'     => '',
    'address'   => '',
    'contact'   => '+63 ',
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    foreach ($values as $key => $val) {
        $values[$key] = trim(isset($_POST[$key]) ? $_POST[$key] : '');
    }
    $password        = isset($_POST['password'])         ? $_POST['password']         : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // name check
    if ($values['full_name'] == '') {
        $errors['full_name'] = 'Full name is required.';
    } elseif (strlen($values['full_name']) < 2) {
        $errors['full_name'] = 'Please enter your complete name.';
    }

    // email check 
    if ($values['email'] == '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $values['email'])) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // password rules: 8+ chars, needs an uppercase and a number
    if ($password == '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number.';
    }

    // password check
    if ($confirmPassword == '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password != $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if ($values['address'] == '') {
        $errors['address'] = 'Complete address is required.';
    }

    $contactStripped = trim($values['contact']);
    if ($contactStripped == '' || $contactStripped == '+63' || $contactStripped == '+63 ') {
        $errors['contact'] = 'Contact number is required.';
    } elseif (!preg_match('/^[\d\s\+\-\(\)]{10,20}$/', $contactStripped)) {
        $errors['contact'] = 'Enter a valid contact number (e.g. +63 912 345 6789).';
    }

    if (empty($errors)) {
        // token for the email verification link
        $token = md5(uniqid(mt_rand(), true));

        $hashedPassword = md5($password);
        $sql  = "INSERT INTO users (full_name, email, password, address, contact, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, 0, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssss',
            $values['full_name'],
            $values['email'],
            $hashedPassword,
            $values['address'],
            $values['contact'],
            $token
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            log_activity($conn, 'register', $values['email']);

            // mail   verify link
            require_once '../includes/mailer.php';
            $mailSent = send_verification_email($values['email'], $values['full_name'], $token);

            $success = true;
            $successMailSent = $mailSent;
            foreach ($values as $key => $val) {
                $values[$key] = $key == 'contact' ? '+63 ' : '';
            }
        } else {
            $errorCode = mysqli_stmt_errno($stmt);
            mysqli_stmt_close($stmt);
            if ($errorCode == 1062) {
                $errors['email'] = 'That email is already registered.';
            } else {
                $errors['general'] = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle  = 'Create Account';
$pageDesc   = 'Register for a VAR Cars account.';
$activePage = 'register';

require_once '../includes/header.php';
?>

<style>
body {
    background-image: linear-gradient(rgba(8,14,20,0.42), rgba(8,14,20,0.48)),
                      url('/VAR-Cars/public/assets/images/Porsche%20Taycan%204S%20Black%20Edition%20desktop%202_.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}
</style>

<main>
<div class="auth-wrap" style="background:none;">
    <div class="auth-card auth-card--glass" style="max-width:36rem;">

        <?php if ($success): ?>

        <!-- Success screen — replaces the form -->
        <div style="text-align:center;padding:var(--space-lg) 0;">
            <div style="font-size:3rem;margin-bottom:var(--space-md);">&#9993;</div>
            <p class="auth-card__title">Check your email!</p>
            <p style="color:var(--c-silver);font-size:var(--text-sm);line-height:1.7;margin-bottom:var(--space-xl);">
                Your account has been created.<br>
                We sent a verification link to <strong style="color:var(--c-white);"><?= htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : '', ENT_QUOTES, 'UTF-8') ?></strong>.<br>
                Click the link in that email to activate your account before logging in.
            </p>
            <?php if (empty($successMailSent)): ?>
            <p style="color:var(--color-warning);font-size:var(--text-xs);margin-bottom:var(--space-lg);">
                &#9888; The verification email could not be sent. Please contact support.
            </p>
            <?php endif; ?>
            <a href="login.php" class="button button--primary button--full">Go to Log In</a>
            <p style="margin-top:var(--space-md);font-size:var(--text-xs);color:var(--c-silver);">
                Didn't receive it? Check your spam folder.
            </p>
        </div>

        <?php else: ?>

        <p class="auth-card__title" style="text-align:center;">Create an Account</p>
        <p class="auth-card__sub" style="text-align:center;">Sign up to your VAR Cars account.</p>

        <!-- General error (e.g. registration failed) -->
        <?php if (isset($errors['general'])): ?>
        <div class="flash flash--error" style="margin-bottom:var(--space-lg);">
            <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <!-- Registration form -->
        <form
            class="auth-card__form"
            method="POST"
            action="register.php"
            novalidate
            id="register-form">

            <!-- Full name -->
            <div class="form-group">
                <label class="form-label" for="full_name">
                    Complete Name <span>*</span>
                </label>
                <input
                    class="form-input <?= isset($errors['full_name']) ? 'error' : '' ?>"
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="<?= htmlspecialchars($values['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Juan Andres dela Cruz"
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

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">
                    Password <span>*</span>
                </label>
                <input
                    class="form-input <?= isset($errors['password']) ? 'error' : '' ?>"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Min. 8 characters"
                    autocomplete="new-password"
                    required>
                <?php if (isset($errors['password'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>

                <!-- Password strength bar -->
                <div id="strength-bar-wrap" style="margin-top:var(--space-xs);display:none;">
                    <div style="height:4px;background:var(--c-border);border-radius:4px;overflow:hidden;">
                        <div id="strength-bar" style="height:100%;width:0%;transition:width 0.3s,background 0.3s;border-radius:4px;"></div>
                    </div>
                    <span id="strength-label" style="font-size:var(--text-xs);color:var(--c-silver);"></span>
                </div>

                <span class="form-hint">At least 8 characters, one uppercase letter, one number.</span>
            </div>

            <!-- Confirm password -->
            <div class="form-group">
                <label class="form-label" for="confirm_password">
                    Confirm Password <span>*</span>
                </label>
                <input
                    class="form-input <?= isset($errors['confirm_password']) ? 'error' : '' ?>"
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Re-enter your password"
                    autocomplete="new-password"
                    required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <!-- Complete address -->
            <div class="form-group">
                <label class="form-label" for="address">
                    Complete Address <span>*</span>
                </label>
                <textarea
                    class="form-textarea <?= isset($errors['address']) ? 'error' : '' ?>"
                    id="address"
                    name="address"
                    placeholder="House no., Street, Barangay, City, Province"
                    rows="2"
                    autocomplete="street-address"
                    required><?= htmlspecialchars($values['address'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (isset($errors['address'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['address'], ENT_QUOTES, 'UTF-8') ?></span>
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
                    placeholder="+63 912 345 6789"
                    autocomplete="tel"
                    required>
                <?php if (isset($errors['contact'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['contact'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <!-- Submit -->
            <button type="submit" class="button button--primary button--full" style="margin-top:var(--space-sm);">
                Create Account
            </button>

        </form>

        <p style="text-align:center;margin-top:var(--space-lg);font-size:var(--text-sm);color:var(--c-silver);">
            Already have an account? <a href="login.php" style="color:var(--c-white);font-weight:600;">Log in</a>
        </p>

        <?php endif; ?>

    </div>
</div>
</main>

<!-- Client-side validation + password strength meter -->
<script>
(function () {
    var form         = document.getElementById('register-form');
    var pwInput      = document.getElementById('password');
    var confirmInput = document.getElementById('confirm_password');
    var strengthBar  = document.getElementById('strength-bar');
    var strengthWrap = document.getElementById('strength-bar-wrap');
    var strengthLbl  = document.getElementById('strength-label');

    // Password strength meter
    function getStrength(pw) {
        var score = 0;
        if (pw.length >= 8)               score++;
        if (pw.length >= 12)              score++;
        if (/[A-Z]/.test(pw))            score++;
        if (/[0-9]/.test(pw))            score++;
        if (/[^A-Za-z0-9]/.test(pw))    score++;
        return score;
    }

    pwInput.addEventListener('input', function () {
        var pw = this.value;
        if (!pw) {
            strengthWrap.style.display = 'none';
            return;
        }
        strengthWrap.style.display = 'block';

        var score  = getStrength(pw);
        var pct    = (score / 5) * 100;
        var colors = ['#c95a5a', '#c95a5a', '#c9a84c', '#6ab08a', '#4caf82'];
        var labels = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'];

        strengthBar.style.width      = pct + '%';
        strengthBar.style.background = colors[score - 1] || '#c95a5a';
        strengthLbl.textContent      = labels[score - 1] || '';
    });

    // Client-side validation on submit
    form.addEventListener('submit', function (e) {
        var valid = true;

        var oldErrors = form.querySelectorAll('.js-error');
        for (var i = 0; i < oldErrors.length; i++) oldErrors[i].remove();
        var oldInvalid = form.querySelectorAll('.js-invalid');
        for (var i = 0; i < oldInvalid.length; i++) oldInvalid[i].classList.remove('error', 'js-invalid');

        function showError(inputId, msg) {
            var input = document.getElementById(inputId);
            if (!input) return;
            input.classList.add('error', 'js-invalid');
            var span       = document.createElement('span');
            span.className   = 'form-error js-error';
            span.textContent = msg;
            input.insertAdjacentElement('afterend', span);
            valid = false;
        }

        var fullName = document.getElementById('full_name').value.trim();
        var email    = document.getElementById('email').value.trim();
        var pw       = pwInput.value;
        var cpw      = confirmInput.value;
        var address  = document.getElementById('address').value.trim();
        var contact  = document.getElementById('contact').value.trim();

        if (!fullName)      showError('full_name', 'Full name is required.');
        if (!email)         showError('email', 'Email is required.');
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
                            showError('email', 'Enter a valid email address.');
        if (!pw)            showError('password', 'Password is required.');
        else if (pw.length < 8)
                            showError('password', 'Password must be at least 8 characters.');
        if (!cpw)           showError('confirm_password', 'Please confirm your password.');
        else if (pw !== cpw) showError('confirm_password', 'Passwords do not match.');
        if (!address)       showError('address', 'Complete address is required.');
        if (!contact)       showError('contact', 'Contact number is required.');

        if (!valid) e.preventDefault();
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>

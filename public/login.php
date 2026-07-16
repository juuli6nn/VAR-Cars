<?php

require_once '../includes/data.php';

if (!empty($_SESSION['authenticated'])) {
    header('Location: index.php');
    exit;
}

$errors = array();
$values = array('email' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $values['email'] = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password        = isset($_POST['password'])   ? $_POST['password'] : '';

    // basic checks before touching the db
    if ($values['email'] == '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $values['email'])) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password == '') {
        $errors['password'] = 'Password is required.';
    }

    if (empty($errors)) {

        if ($values['email'] == ADMIN_EMAIL && md5($password) == ADMIN_PASS_MD5) {
            $_SESSION['authenticated'] = true;
            $_SESSION['is_admin']      = true;
            $_SESSION['user_email']    = ADMIN_EMAIL;
            $_SESSION['username']      = 'Admin';

            $_SESSION['flash']         = 'Welcome, Admin!';
            $_SESSION['flash_type']    = 'success';

            log_activity($conn, 'login', 'superadmin');
            header('Location: admin/index.php');
            exit;
        }

        $sql  = "SELECT id, full_name, email, password, is_verified, is_admin FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $values['email']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user == null || $user['password'] != md5($password)) {
            $errors['general'] = 'Incorrect email or password. Please try again.';
        } elseif (!$user['is_verified']) {
            $errors['general'] = 'Please verify your email address before logging in. Check your inbox for the verification link.';
        } else {
            $_SESSION['authenticated'] = true;
            $_SESSION['is_admin']      = (bool)$user['is_admin'];
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_email']    = $user['email'];
            $_SESSION['username']      = $user['full_name'];

            log_activity($conn, 'login');

            $_SESSION['cart'] = array();
            $sql  = "SELECT vehicle_id, qty FROM cart_items WHERE user_id = ? ORDER BY added_at";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $user['id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $qty = max(1, (int)$row['qty']);
                for ($i = 0; $i < $qty; $i++) {
                    $_SESSION['cart'][] = (int)$row['vehicle_id'];
                }
            }
            mysqli_stmt_close($stmt);

            $_SESSION['flash']         = 'Welcome back, ' . $user['full_name'] . '!';
            $_SESSION['flash_type']    = 'success';

            // if they got bounced here from checkout, send them back there
            $redirect = isset($_SESSION['login_redirect']) ? $_SESSION['login_redirect'] : 'index.php';
            unset($_SESSION['login_redirect']);
            header('Location: ' . $redirect);
            exit;
        }
    }
}

$pageTitle  = 'Log In';
$pageDesc   = 'Log in to your VAR Cars account.';
$activePage = 'login';

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
    <div class="auth-card auth-card--glass">

        <p class="auth-card__title" style="text-align:center;">Welcome Back</p>
        <p class="auth-card__sub" style="text-align:center;">Log in to your VAR Cars account.</p>

        <!-- General error (wrong credentials) -->
        <?php if (isset($errors['general'])): ?>
        <div class="flash flash--error" style="margin-bottom:var(--space-lg);">
            <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <!-- Login form -->
        <form
            class="auth-card__form"
            method="POST"
            action="login.php"
            novalidate
            id="login-form">

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
                    autofocus
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
                <div style="position:relative;">
                    <input
                        class="form-input <?= isset($errors['password']) ? 'error' : '' ?>"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Your password"
                        autocomplete="current-password"
                        required
                        style="padding-right:3rem;">
                    <button
                        type="button"
                        id="toggle-pw"
                        aria-label="Show password"
                        style="
                            position:absolute;right:var(--space-sm);top:50%;
                            transform:translateY(-50%);
                            background:none;border:none;cursor:pointer;
                            color:var(--c-silver);font-size:var(--text-sm);
                            padding:var(--space-2xs);
                        ">Show</button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                class="button button--primary button--full"
                style="margin-top:var(--space-sm);">
                Log In
            </button>

        </form>

        <div class="auth-card__footer">
            Don't have an account?
            <a href="register.php" style="color:var(--c-white);font-weight:600;">Register here</a>
        </div>

    </div>
</div>
</main>

<!-- Show/hide password + client-side validation -->
<script>
(function () {
    var pwInput   = document.getElementById('password');
    var toggleBtn = document.getElementById('toggle-pw');

    toggleBtn.addEventListener('click', function () {
        var isHidden = pwInput.type === 'password';
        pwInput.type       = isHidden ? 'text' : 'password';
        this.textContent   = isHidden ? 'Hide' : 'Show';
        this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });

    var form = document.getElementById('login-form');

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
            input.closest('.form-group').appendChild(span);
            valid = false;
        }

        var email = document.getElementById('email').value.trim();
        var pw    = pwInput.value;

        if (!email)
            showError('email', 'Email is required.');
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
            showError('email', 'Enter a valid email address.');

        if (!pw)
            showError('password', 'Password is required.');

        if (!valid) e.preventDefault();
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>

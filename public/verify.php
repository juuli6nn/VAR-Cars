<?php
require_once '../includes/data.php';

$error   = '';
$success = false;

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($token == '') {
    $error = 'Invalid verification link.';
} else {
    $sql  = "SELECT id, full_name FROM users WHERE verification_token = ? AND is_verified = 0";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user   = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user == null) {
        $error = 'This link is invalid or has already been used.';
    } else {
        $sql  = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $user['id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $success = true;
        $_SESSION['flash']      = 'Email verified! You can now log in.';
        $_SESSION['flash_type'] = 'success';
        header('Location: login.php');
        exit;
    }
}

$pageTitle  = 'Verify Email';
$activePage = '';
require_once '../includes/header.php';
?>

<main>
<div class="auth-wrap">
    <div class="auth-card" style="text-align:center;">
        <?php if ($error): ?>
            <p style="font-size:2rem;margin-bottom:var(--space-md);">&#10007;</p>
            <p class="auth-card__title">Verification Failed</p>
            <p class="auth-card__sub"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <a href="register.php" class="button button--ghost" style="margin-top:var(--space-lg);">Back to Sign Up</a>
        <?php endif; ?>
    </div>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>

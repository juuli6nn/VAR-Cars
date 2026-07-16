<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_cartCount = count($_SESSION['cart'] ?? array());

$_flash     = isset($_SESSION['flash'])      ? $_SESSION['flash']      : null;
$_flashType = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
unset($_SESSION['flash'], $_SESSION['flash_type']);

$_loggedIn = !empty($_SESSION['authenticated']);
$_username = htmlspecialchars(isset($_SESSION['username']) ? $_SESSION['username'] : '', ENT_QUOTES, 'UTF-8');

if (!isset($pageTitle))  $pageTitle  = 'VAR Cars';
if (!isset($pageDesc))   $pageDesc   = 'Browse quality vehicles at VAR Cars — your trusted car marketplace.';
if (!isset($activePage)) $activePage = '';

$pageTitle = htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');
$pageDesc  = htmlspecialchars($pageDesc,  ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDesc ?>">
    <title>VAR Cars</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" href="/VAR-Cars/public/assets/images/VAR Logo.svg" type="image/svg+xml">

    <!-- Styles -->
    <link rel="stylesheet" href="/VAR-Cars/public/assets/css/styles.css">
</head>
<body>

<!-- Sticky navigation -->
<header class="site-header">
    <nav class="site-nav" id="site-nav" aria-label="Primary navigation">

        <!-- Left: brand logo -->
        <a class="brand" href="/VAR-Cars/public/index.php" aria-label="VAR Cars home">
            <img src="/VAR-Cars/public/assets/images/VAR Logo.svg"
                 alt="VAR Cars"
                 class="brand__logo-img">
        </a>

        <!-- Center: page links -->
        <ul class="site-nav__links">
            <li><a href="/VAR-Cars/public/index.php" class="<?= ($activePage == 'home')  ? 'active' : '' ?>">Home</a></li>
            <li><a href="/VAR-Cars/public/store.php" class="<?= ($activePage == 'store') ? 'active' : '' ?>">Browse Cars</a></li>
            <li><a href="/VAR-Cars/public/about.php" class="<?= ($activePage == 'about') ? 'active' : '' ?>">About</a></li>
        </ul>

        <!-- Right: cart + sign up -->
        <div class="site-nav__actions">
            <?php if (empty($_SESSION['is_admin'])): ?>
            <a class="site-nav__cart" href="/VAR-Cars/public/cart.php" aria-label="Shopping cart (<?= $_cartCount ?> items)">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9"  cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <?php if ($_cartCount > 0): ?>
                    <span class="cart-count"><?= $_cartCount ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <?php if ($_loggedIn): ?>
                <?php if (!empty($_SESSION['is_admin'])): ?>
                    <a href="/VAR-Cars/public/admin/index.php" class="button--signup">Admin Panel</a>
                <?php endif; ?>
                <a href="/VAR-Cars/public/logout.php" class="button--signup">Log Out</a>
            <?php else: ?>
                <a href="/VAR-Cars/public/login.php" class="button--signup">Sign In</a>
            <?php endif; ?>
        </div>

        <!-- Mobile hamburger -->
        <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="site-nav" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

    </nav>
</header>

<?php if ($_flash): ?>
<div class="flash-wrap" id="flash-toast" role="status">
    <p class="flash flash--<?= htmlspecialchars($_flashType, ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($_flash, ENT_QUOTES, 'UTF-8') ?>
    </p>
</div>
<script>setTimeout(function(){ var t=document.getElementById('flash-toast'); if(t) t.remove(); }, 4000);</script>
<?php endif; ?>

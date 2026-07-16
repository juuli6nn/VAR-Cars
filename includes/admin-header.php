<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_flash     = isset($_SESSION['flash'])      ? $_SESSION['flash']      : null;
$_flashType = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
unset($_SESSION['flash'], $_SESSION['flash_type']);

if (!isset($pageTitle))   $pageTitle   = 'Admin';
if (!isset($adminPage))   $adminPage   = '';

$pageTitle = htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | VAR Cars Admin</title>
    <link rel="icon" href="/VAR-Cars/public/assets/images/VAR Logo.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/VAR-Cars/public/assets/css/styles.css">
    <link rel="stylesheet" href="/VAR-Cars/public/assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">

        <div class="admin-sidebar__brand">
            <img src="/VAR-Cars/public/assets/images/VAR Logo.svg" alt="VAR Cars" class="admin-sidebar__logo">
        </div>

        <nav class="admin-sidebar__nav">
            <p class="admin-sidebar__section-label">Main</p>
            <a href="/VAR-Cars/public/admin/index.php"
               class="admin-sidebar__link <?= ($adminPage == 'dashboard') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <a href="/VAR-Cars/public/admin/orders.php"
               class="admin-sidebar__link <?= ($adminPage == 'orders') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Orders
            </a>
            <a href="/VAR-Cars/public/admin/cars.php"
               class="admin-sidebar__link <?= ($adminPage == 'cars') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Catalogue
            </a>
            <a href="/VAR-Cars/public/admin/users.php"
               class="admin-sidebar__link <?= ($adminPage == 'users') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Users
            </a>
            <a href="/VAR-Cars/public/admin/reports.php"
               class="admin-sidebar__link <?= ($adminPage == 'reports') ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Reports
            </a>

            <p class="admin-sidebar__section-label" style="margin-top:var(--space-lg);">Site</p>
            <a href="/VAR-Cars/public/index.php" class="admin-sidebar__link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                View Site
            </a>
            <a href="/VAR-Cars/public/logout.php" class="admin-sidebar__link admin-sidebar__link--danger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Log Out
            </a>
        </nav>

    </aside>

    <!-- Main content -->
    <div class="admin-main">

        <!-- Top bar -->
        <header class="admin-topbar">
            <p class="admin-topbar__title"><?= $pageTitle ?></p>
            <div class="admin-topbar__user">
                <div class="admin-topbar__avatar">A</div>
                <span>Admin</span>
            </div>
        </header>

        <?php if ($_flash): ?>
        <div class="flash-wrap" id="flash-toast" role="status">
            <p class="flash flash--<?= htmlspecialchars($_flashType, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($_flash, ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
        <script>setTimeout(function(){ var t=document.getElementById('flash-toast'); if(t) t.remove(); }, 4000);</script>
        <?php endif; ?>

        <div class="admin-content">

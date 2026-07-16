<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle  = 'About Us';
$pageDesc   = 'Learn about VAR Cars and the Anthrowpic team behind this project.';
$activePage = 'about';

require_once '../includes/header.php';
?>

<main>

<div class="page-hero">
    <div class="page-hero__inner">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb__sep" aria-hidden="true">&rsaquo;</span>
            <span>About</span>
        </nav>
        <h1>About VAR Cars</h1>
        <p>A final project by the Anthrowpic group.</p>
    </div>
</div>

<div class="section container">

    <!-- Project intro -->
    <div class="about-intro">
        <div>
            <h2>What is VAR Cars?</h2>
            <p style="color:var(--c-silver);margin-top:var(--space-md);line-height:1.8;">
                VAR Cars is an online car dealership website built as a final project for our
                <strong style="color:var(--c-white);">Applications Development and Emerging Technologies</strong> course.
                The site demonstrates a complete e-commerce flow
            </p>
            <p style="color:var(--c-silver);margin-top:var(--space-md);line-height:1.8;">
                Visitors can browse vehicles from the world's most prestigious brands,
                register an account, add cars to their cart, and go through a full checkout and payment flow.
            </p>
        </div>
        <div class="about-visual" style="background:#ffffff;padding:var(--space-xl);">
            <img src="/VAR-Cars/public/assets/images/VAR Logo.svg"
                 alt="VAR Cars logo"
                 style="width:100%;height:100%;object-fit:contain;">
        </div>
    </div>

    <!-- Team -->
    <div style="margin-top:var(--space-3xl);">
        <h2 style="margin-bottom:var(--space-xl);">Meet the Team</h2>

        <div class="team-split">

            <!-- Photo container -->
            <div class="team-photo">
                <img src="/VAR-Cars/public/assets/images/PHOTO ME.jpg" alt="Arianne Julian Cruz">
            </div>

            <!-- Details container -->
            <div class="team-details">
                <p class="team-details__label">Anthrowpic</p>
                <p class="team-details__name">Arianne Julian Cruz</p>
                <p class="team-details__role">Founder &amp; Developer</p>
                <p class="team-details__bio">
                    Sole developer behind VAR Cars and the mind behind Anthrowpic.
                </p>

                <div class="team-details__links">
                    <a class="team-link team-link--linkedin"
                       href="https://www.linkedin.com/in/juuli6nn/"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="Julian Cruz on LinkedIn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.55V9h3.57v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.22.79 24 1.77 24h20.45c.98 0 1.78-.78 1.78-1.73V1.73C24 .77 23.2 0 22.22 0z"/>
                        </svg>
                        <span>LinkedIn</span>
                    </a>
                    <a class="team-link team-link--github"
                       href="https://github.com/juuli6nn"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="Julian Cruz on GitHub">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.7-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2 0-.4-.5-1.6.2-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.6.2 2.8.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3z"/>
                        </svg>
                        <span>GitHub</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
</main>

<?php require_once '../includes/footer.php'; ?>

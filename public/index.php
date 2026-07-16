<?php
require_once '../includes/data.php';

$pageTitle  = 'VAR Cars';
$pageDesc   = 'Browse Budget, Mid-Range and Luxury vehicles at VAR Cars — an Anthrowpic educational project.';
$activePage = 'home';


require_once '../includes/header.php';
?>

<main>

<!-- Hero section -->
<section class="hero" aria-labelledby="hero-title">
    <div class="hero__content">
        <h1 id="hero-title">Drive something<br>extraordinary.</h1>
        <p class="hero__description">
            From entry-level to ultra-luxury. Browse our curated catalogue
            of the world's finest vehicles and find your perfect match.
        </p>
        <div class="hero__actions">
            <a class="button button--primary" href="store.php">Browse all cars</a>
            <a class="button button--ghost" href="store.php">Browse brands</a>
        </div>
    </div>
</section>

<?php
$brands = array(
    array('name' => 'Mercedes-Benz', 'img' => 'T1 MERC.avif'),
    array('name' => 'BMW',           'img' => 'T2 BMW.jpg'),
    array('name' => 'Porsche',       'img' => 'T3 PORSCHE.avif'),
    array('name' => 'Audi',          'img' => 'T4 AUDI.jpg'),
    array('name' => 'Lamborghini',   'img' => 'T5 LAMBO.webp'),
    array('name' => 'Ferrari',       'img' => 'T6 FERRARI.jpg'),
    array('name' => 'Bentley',       'img' => 'T7 BENTLEY.jpg'),
    array('name' => 'Rolls-Royce',   'img' => 'T8 ROLLS ROYCE.jpg'),
    array('name' => 'Aston Martin',  'img' => 'T9 ASTON MARTIN.webp'),
);
?>

<!-- Brands -->
<section class="section container" aria-labelledby="brands-title">
    <div class="section-head">
        <div>
            <h2 id="brands-title">Brands</h2>
            <p class="section-head__sub">The most prestigious automotive names, in one place.</p>
        </div>
        <a class="button button--ghost" href="store.php">View all cars &rarr;</a>
    </div>

    <div class="brand-grid">
        <?php foreach ($brands as $brand): ?>
        <a class="brand-card" href="store.php?brand=<?= urlencode($brand['name']) ?>"
           style="background-image: url('/VAR-Cars/public/assets/images/<?= rawurlencode($brand['img']) ?>');">
            <span class="brand-card__name"><?= htmlspecialchars($brand['name'], ENT_QUOTES, 'UTF-8') ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- About teaser -->
<section class="section container" aria-labelledby="about-teaser-title" style="border-top:1px solid var(--c-border);">
    <div class="about-intro" style="margin-bottom:0;">
        <div>
            <h2 id="about-teaser-title">Built by Anthrowpic.</h2>
            <p style="color:var(--c-silver);margin-top:var(--space-md);line-height:1.8;">
                VAR Cars is an <strong style="color:var(--c-white);">Applications Development and Emerging Technologies</strong><br>
                final project showcasing a complete e-commerce flow &mdash; user registration, product catalogue, shopping cart,
                checkout.
            </p>
            <a class="button button--ghost" href="about.php" style="margin-top:var(--space-xl);">Meet the team &rarr;</a>
        </div>
        <div class="about-visual" style="background:#ffffff;padding:var(--space-xl);">
            <img src="/VAR-Cars/public/assets/images/ANTHROWPIC SVG LOGO.svg"
                 alt="Anthrowpic logo"
                 style="width:100%;height:100%;object-fit:contain;">
        </div>
    </div>
</section>

</main>

<?php require_once '../includes/footer.php'; ?>

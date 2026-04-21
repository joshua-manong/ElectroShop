<?php
$pageTitle = 'About Us';
require_once 'includes/config.php';
require_once 'includes/header.php';
?>
<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">About ElectroShop</h1>
        <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="index.php">Home</a></li><li class="breadcrumb-item active">About</li></ol></nav>
    </div>
</div>

<section class="section-spacing">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="section-label">Our Story</div>
                <h2 class="section-title">The Philippines' Trusted Tech Store</h2>
                <p style="color:var(--text-secondary);line-height:1.8;margin-bottom:20px;">Founded in 2020, ElectroShop has grown to become one of the most trusted electronics retailers in the Philippines. We believe everyone deserves access to the latest technology at fair prices.</p>
                <p style="color:var(--text-secondary);line-height:1.8;margin-bottom:28px;">From smartphones to laptops, gaming gear to home electronics — we carry authentic products from the world's leading technology brands, backed by genuine warranties and world-class customer support.</p>
                <div class="row g-3">
                    <?php foreach ([['10K+','Products'],['50K+','Happy Customers'],['9','Product Categories'],['99%','Customer Satisfaction']] as $stat): ?>
                    <div class="col-6">
                        <div style="background:var(--black-card);border:1px solid var(--black-border);border-radius:var(--radius-md);padding:20px;text-align:center;">
                            <div style="font-family:var(--font-display);font-size:1.8rem;font-weight:900;color:var(--blue-primary);"><?= $stat[0] ?></div>
                            <div style="font-size:.85rem;color:var(--text-secondary);"><?= $stat[1] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div style="background:var(--gradient-card);border:1px solid var(--black-border);border-radius:var(--radius-lg);padding:40px;">
                    <h3 style="font-family:var(--font-display);color:var(--white-pure);margin-bottom:20px;">Our Mission</h3>
                    <p style="color:var(--text-secondary);line-height:1.8;">To make cutting-edge technology accessible to every Filipino — with honest pricing, authentic products, and exceptional service that puts customers first.</p>
                    <hr style="border-color:var(--black-border);margin:24px 0;">
                    <h3 style="font-family:var(--font-display);color:var(--white-pure);margin-bottom:20px;">Our Vision</h3>
                    <p style="color:var(--text-secondary);line-height:1.8;">To be the leading technology marketplace in Southeast Asia, connecting millions of customers with the best tech products and creating a seamless digital shopping experience.</p>
                    <hr style="border-color:var(--black-border);margin:24px 0;">
                    <h3 style="font-family:var(--font-display);color:var(--white-pure);margin-bottom:20px;">Our Values</h3>
                    <ul style="color:var(--text-secondary);line-height:2;list-style:none;padding:0;">
                        <?php foreach (['Integrity & Transparency','Customer First Always','Innovation & Progress','Quality Guaranteed','Community & Care'] as $v): ?>
                        <li><i class="bi bi-check-circle-fill text-blue me-2"></i><?= $v ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

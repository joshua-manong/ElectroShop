<?php
$pageTitle = 'My Wishlist';
require_once 'includes/config.php';
if (!isLoggedIn()) redirect(SITE_URL . '/login.php');

$items = $pdo->prepare("SELECT w.*,p.product_name,p.price,p.original_price,p.product_image,p.brand,p.rating,p.review_count FROM wishlist w JOIN products p ON w.product_id=p.product_id WHERE w.user_id=? ORDER BY w.added_at DESC");
$items->execute([$_SESSION['user_id']]);
$wishlistItems = $items->fetchAll();
require_once 'includes/header.php';
?>
<div class="page-banner"><div class="container"><h1 class="page-banner-title">My Wishlist</h1></div></div>
<div class="container section-spacing" style="padding-top:40px;">
<?php if(empty($wishlistItems)): ?>
<div class="text-center py-5">
    <i class="bi bi-heart" style="font-size:4rem;color:var(--text-secondary);display:block;margin-bottom:16px;"></i>
    <h4 style="color:var(--white-pure);">Your wishlist is empty</h4>
    <p style="color:var(--text-secondary);">Save products you love to your wishlist!</p>
    <a href="shop.php" class="btn-primary-custom mt-2"><i class="bi bi-shop"></i> Browse Products</a>
</div>
<?php else: ?>
<div class="row g-4">
<?php foreach ($wishlistItems as $p): ?>
<div class="col-6 col-md-4 col-lg-3">
    <div class="product-card">
        <div class="product-card-img-wrap">
            <img src="<?= $p['product_image'] ?>" class="product-card-img" alt="<?= htmlspecialchars($p['product_name']) ?>">
            <div class="product-card-actions">
                <button class="action-btn" onclick="toggleWishlist(<?= $p['product_id'] ?>,this)" style="color:#FF3366;"><i class="bi bi-heart-fill"></i></button>
            </div>
        </div>
        <div class="product-card-body">
            <div class="product-brand"><?= htmlspecialchars($p['brand']) ?></div>
            <div class="product-title"><a href="product.php?id=<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></a></div>
            <div class="product-price-row">
                <span class="product-price"><?= formatPrice($p['price']) ?></span>
                <button class="add-cart-btn" onclick="addToCart(<?= $p['product_id'] ?>)"><i class="bi bi-cart-plus"></i></button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>

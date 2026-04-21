<?php
require_once 'includes/config.php';

$productId = (int)($_GET['id'] ?? 0);
if (!$productId) redirect(SITE_URL . '/shop.php');

// Fetch product
$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) redirect(SITE_URL . '/shop.php');

$pageTitle = $product['product_name'];

// Fetch additional images
$imgStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
$imgStmt->execute([$productId]);
$extraImages = $imgStmt->fetchAll();

// Fetch reviews
$revStmt = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.review_date DESC");
$revStmt->execute([$productId]);
$reviews = $revStmt->fetchAll();

// Related products
$relStmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND product_id != ? LIMIT 4");
$relStmt->execute([$product['category_id'], $productId]);
$related = $relStmt->fetchAll();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) redirect(SITE_URL . '/login.php');
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating >= 1 && $rating <= 5 && $comment) {
        $revInsert = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)");
        $revInsert->execute([$_SESSION['user_id'], $productId, $rating, $comment]);
        // Update product average rating
        $avgStmt = $pdo->prepare("UPDATE products SET rating = (SELECT AVG(rating) FROM reviews WHERE product_id = ?), review_count = (SELECT COUNT(*) FROM reviews WHERE product_id = ?) WHERE product_id = ?");
        $avgStmt->execute([$productId, $productId, $productId]);
        redirect("product.php?id=$productId#reviews");
    }
}

require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="page-banner" style="padding:30px 0;">
    <div class="container">
        <nav><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
            <li class="breadcrumb-item"><a href="shop.php?category=<?= $product['category_id'] ?>"><?= $product['category_name'] ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['product_name']) ?></li>
        </ol></nav>
    </div>
</div>

<div class="container section-spacing" style="padding-top:40px;">
    <div class="row g-5">
        <!-- LEFT: Gallery -->
        <div class="col-lg-5">
            <div class="product-gallery-main mb-3">
                <img id="mainProductImage" src="<?= $product['product_image'] ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" onerror="this.src='https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=500'">
            </div>
            <?php if (!empty($extraImages)): ?>
            <div class="gallery-thumbs">
                <div class="gallery-thumb active" onclick="switchGalleryImage('<?= $product['product_image'] ?>', this)">
                    <img src="<?= $product['product_image'] ?>" alt="Main">
                </div>
                <?php foreach ($extraImages as $img): ?>
                <div class="gallery-thumb" onclick="switchGalleryImage('<?= $img['image_url'] ?>', this)">
                    <img src="<?= $img['image_url'] ?>" alt="Gallery">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Details -->
        <div class="col-lg-7">
            <div class="product-brand mb-2"><?= htmlspecialchars($product['brand']) ?> &bull; <?= htmlspecialchars($product['category_name']) ?></div>
            <h1 class="product-detail-title mb-3"><?= htmlspecialchars($product['product_name']) ?></h1>

            <!-- Rating -->
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="stars fs-5"><?php for($i=1;$i<=5;$i++) echo '<i class="bi bi-star'.($i<=round($product['rating'])?'-fill':'').'"></i>'; ?></div>
                <span style="color:var(--text-secondary);font-size:.9rem;"><?= number_format($product['rating'],1) ?> (<?= $product['review_count'] ?> reviews)</span>
                <span style="color:<?= $product['stock'] > 0 ? '#00D4AA' : '#FF3366' ?>;font-size:.85rem;font-weight:600;">
                    <i class="bi bi-<?= $product['stock'] > 0 ? 'check-circle' : 'x-circle' ?>-fill"></i>
                    <?= $product['stock'] > 0 ? "In Stock ({$product['stock']} available)" : 'Out of Stock' ?>
                </span>
            </div>

            <!-- Price -->
            <div class="d-flex align-items-baseline gap-3 mb-4">
                <div class="product-detail-price"><?= formatPrice($product['price']) ?></div>
                <?php if ($product['original_price']): ?>
                <span style="color:var(--text-secondary);text-decoration:line-through;font-size:1.1rem;"><?= formatPrice($product['original_price']) ?></span>
                <?php $disc = round(100 - ($product['price']/$product['original_price']*100)); ?>
                <span style="background:#FF3366;color:white;padding:3px 10px;border-radius:50px;font-size:.8rem;font-weight:700;">-<?= $disc ?>% OFF</span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <p style="color:var(--text-secondary);line-height:1.8;margin-bottom:28px;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <!-- Quantity + Add to Cart -->
            <?php if ($product['stock'] > 0): ?>
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="qty-control">
                    <button class="qty-btn" onclick="changeQty(document.getElementById('qty'), -1)">−</button>
                    <input type="number" id="qty" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button class="qty-btn" onclick="changeQty(document.getElementById('qty'), 1)">+</button>
                </div>
                <button class="btn-primary-custom flex-grow-1 justify-content-center" onclick="addToCart(<?= $product['product_id'] ?>, parseInt(document.getElementById('qty').value))">
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
                <button class="action-btn" onclick="toggleWishlist(<?= $product['product_id'] ?>, this)" title="Add to Wishlist" style="width:50px;height:50px;font-size:1.2rem;">
                    <i class="bi bi-heart"></i>
                </button>
            </div>
            <?php else: ?>
            <div class="alert" style="background:rgba(255,51,102,.1);border:1px solid #FF3366;color:#FF3366;border-radius:var(--radius-sm);">
                <i class="bi bi-exclamation-circle me-2"></i> This product is currently out of stock.
            </div>
            <?php endif; ?>

            <!-- Delivery Info -->
            <div style="background:var(--black-surface);border:1px solid var(--black-border);border-radius:var(--radius-md);padding:16px;">
                <div class="d-flex gap-4 flex-wrap">
                    <div class="d-flex align-items-center gap-2 text-blue"><i class="bi bi-truck"></i><span style="font-size:.85rem;color:var(--text-secondary);">Free Delivery (₱2,000+)</span></div>
                    <div class="d-flex align-items-center gap-2"><i class="bi bi-shield-check text-blue"></i><span style="font-size:.85rem;color:var(--text-secondary);">Authentic Guaranteed</span></div>
                    <div class="d-flex align-items-center gap-2"><i class="bi bi-arrow-repeat text-blue"></i><span style="font-size:.85rem;color:var(--text-secondary);">30-Day Returns</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SPECIFICATIONS -->
    <div class="row mt-5">
        <div class="col-lg-8">
            <h3 style="font-family:var(--font-display);color:var(--white-pure);margin-bottom:20px;">Specifications</h3>
            <div class="spec-table">
                <table class="table mb-0">
                    <tbody>
                        <tr><td>Brand</td><td><?= htmlspecialchars($product['brand']) ?></td></tr>
                        <tr><td>Category</td><td><?= htmlspecialchars($product['category_name']) ?></td></tr>
                        <tr><td>Price</td><td><?= formatPrice($product['price']) ?></td></tr>
                        <tr><td>Stock</td><td><?= $product['stock'] ?> units</td></tr>
                        <tr><td>Rating</td><td><?= number_format($product['rating'],1) ?>/5.0 (<?= $product['review_count'] ?> reviews)</td></tr>
                        <tr><td>SKU</td><td>ES-<?= str_pad($product['product_id'], 5, '0', STR_PAD_LEFT) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- REVIEWS -->
    <div class="mt-5" id="reviews">
        <h3 style="font-family:var(--font-display);color:var(--white-pure);margin-bottom:28px;">Customer Reviews</h3>

        <?php if (isLoggedIn()): ?>
        <!-- Review Form -->
        <div style="background:var(--black-card);border:1px solid var(--black-border);border-radius:var(--radius-lg);padding:24px;margin-bottom:32px;">
            <h5 style="color:var(--white-pure);margin-bottom:16px;">Write a Review</h5>
            <form method="POST">
                <input type="hidden" name="submit_review" value="1">
                <div class="mb-3">
                    <label class="form-label">Your Rating</label>
                    <div class="star-container d-flex gap-1 mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star rating-star" data-value="<?= $i ?>" style="font-size:1.5rem;cursor:pointer;color:var(--text-secondary);transition:var(--transition);" onclick="setRating(<?= $i ?>)"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="rating_input" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Your Review</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience with this product..." required></textarea>
                </div>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-send"></i> Submit Review</button>
            </form>
        </div>
        <?php else: ?>
        <div class="alert mb-4" style="background:var(--blue-glow);border:1px solid rgba(0,102,255,.3);color:var(--blue-light);border-radius:var(--radius-md);">
            <a href="login.php" style="color:var(--blue-light);font-weight:600;"><i class="bi bi-person me-2"></i>Login to write a review</a>
        </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <?php if (empty($reviews)): ?>
        <div class="text-center py-4" style="color:var(--text-secondary);">
            <i class="bi bi-chat-dots fs-2 d-block mb-2"></i>
            No reviews yet. Be the first to review this product!
        </div>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($reviews as $rev): ?>
            <div class="col-12">
                <div style="background:var(--black-card);border:1px solid var(--black-border);border-radius:var(--radius-md);padding:20px;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div style="width:40px;height:40px;background:var(--gradient-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:white;">
                            <?= strtoupper(substr($rev['full_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:700;color:var(--white-pure);"><?= htmlspecialchars($rev['full_name']) ?></div>
                            <div class="stars"><?php for($i=1;$i<=5;$i++) echo '<i class="bi bi-star'.($i<=$rev['rating']?'-fill':'').'"></i>'; ?></div>
                        </div>
                        <div class="ms-auto" style="font-size:.8rem;color:var(--text-secondary);"><?= date('M d, Y', strtotime($rev['review_date'])) ?></div>
                    </div>
                    <p style="color:var(--text-secondary);margin:0;"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- RELATED PRODUCTS -->
    <?php if (!empty($related)): ?>
    <div class="mt-5">
        <h3 style="font-family:var(--font-display);color:var(--white-pure);margin-bottom:28px;">Related Products</h3>
        <div class="row g-4">
            <?php foreach ($related as $rp): ?>
            <div class="col-6 col-md-3">
                <div class="product-card">
                    <div class="product-card-img-wrap">
                        <img src="<?= $rp['product_image'] ?>" alt="<?= htmlspecialchars($rp['product_name']) ?>" class="product-card-img" loading="lazy">
                    </div>
                    <div class="product-card-body">
                        <div class="product-brand"><?= htmlspecialchars($rp['brand']) ?></div>
                        <div class="product-title"><a href="product.php?id=<?= $rp['product_id'] ?>"><?= htmlspecialchars($rp['product_name']) ?></a></div>
                        <div class="product-price-row">
                            <span class="product-price"><?= formatPrice($rp['price']) ?></span>
                            <button class="add-cart-btn" onclick="addToCart(<?= $rp['product_id'] ?>)"><i class="bi bi-cart-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function setRating(val) {
    document.getElementById('rating_input').value = val;
    document.querySelectorAll('.rating-star').forEach((s, i) => {
        s.className = 'bi ' + (i < val ? 'bi-star-fill' : 'bi-star') + ' rating-star';
        s.style.color = i < val ? '#FFB800' : 'var(--text-secondary)';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>

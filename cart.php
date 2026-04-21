<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/config.php';

if (!isLoggedIn()) redirect(SITE_URL . '/login.php?redirect=cart.php');

// Fetch cart items
$cartStmt = $pdo->prepare("SELECT c.*, p.product_name, p.price, p.product_image, p.brand, p.stock FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
$cartStmt->execute([$_SESSION['user_id']]);
$cartItems = $cartStmt->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) $subtotal += $item['price'] * $item['quantity'];
$shipping = $subtotal >= 2000 ? 0 : 150;
$total = $subtotal + $shipping;

require_once 'includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Shopping Cart</h1>
        <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="index.php">Home</a></li><li class="breadcrumb-item active">Cart</li></ol></nav>
    </div>
</div>

<div class="container section-spacing" style="padding-top:40px;">
    <?php if (empty($cartItems)): ?>
    <div class="text-center py-5">
        <i class="bi bi-cart-x" style="font-size:5rem;color:var(--text-secondary);display:block;margin-bottom:20px;"></i>
        <h3 style="color:var(--white-pure);">Your cart is empty</h3>
        <p style="color:var(--text-secondary);margin-bottom:28px;">Looks like you haven't added anything to your cart yet.</p>
        <a href="shop.php" class="btn-primary-custom"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="cart-table">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="d-none d-md-table-cell">Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= $item['product_image'] ?>" class="cart-item-image" alt="<?= htmlspecialchars($item['product_name']) ?>" onerror="this.src='https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=200'">
                                    <div>
                                        <div style="font-weight:600;font-size:.9rem;color:var(--white-pure);"><?= htmlspecialchars($item['product_name']) ?></div>
                                        <div style="font-size:.75rem;color:var(--text-secondary);"><?= htmlspecialchars($item['brand']) ?></div>
                                        <div class="d-md-none mt-1" style="color:var(--blue-primary);font-weight:700;"><?= formatPrice($item['price']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell" style="color:var(--blue-primary);font-weight:700;"><?= formatPrice($item['price']) ?></td>
                            <td>
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="updateCartItem(<?= $item['cart_id'] ?>, <?= max(1, $item['quantity'] - 1) ?>)">−</button>
                                    <span class="qty-input"><?= $item['quantity'] ?></span>
                                    <button class="qty-btn" onclick="updateCartItem(<?= $item['cart_id'] ?>, <?= min($item['stock'], $item['quantity'] + 1) ?>)">+</button>
                                </div>
                            </td>
                            <td style="font-weight:700;color:var(--white-pure);"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                            <td>
                                <button class="action-btn" onclick="removeCartItem(<?= $item['cart_id'] ?>)" title="Remove" style="color:#FF3366;">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="shop.php" class="btn-outline-custom"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
                <button onclick="location.reload()" class="btn-outline-custom"><i class="bi bi-arrow-clockwise"></i> Update Cart</button>
            </div>
        </div>

        <!-- Cart Summary -->
        <div class="col-lg-4">
            <div class="cart-summary-box">
                <h5 style="font-family:var(--font-display);font-weight:700;color:var(--white-pure);margin-bottom:20px;">Order Summary</h5>
                <div class="d-flex justify-content-between mb-3">
                    <span style="color:var(--text-secondary);">Subtotal</span>
                    <span style="color:var(--white-pure);font-weight:600;"><?= formatPrice($subtotal) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span style="color:var(--text-secondary);">Shipping</span>
                    <span style="color:<?= $shipping === 0 ? '#00D4AA' : 'var(--white-pure)' ?>;font-weight:600;">
                        <?= $shipping === 0 ? '<i class="bi bi-check-circle me-1"></i>FREE' : formatPrice($shipping) ?>
                    </span>
                </div>
                <?php if ($shipping > 0): ?>
                <div class="mb-3 p-2" style="background:var(--blue-glow);border-radius:var(--radius-sm);font-size:.8rem;color:var(--blue-light);">
                    <i class="bi bi-truck me-1"></i> Add <?= formatPrice(2000 - $subtotal) ?> more for FREE shipping!
                </div>
                <?php endif; ?>
                <hr style="border-color:var(--black-border);">
                <div class="d-flex justify-content-between mb-4">
                    <span style="font-weight:700;font-size:1.1rem;color:var(--white-pure);">Total</span>
                    <span style="font-family:var(--font-display);font-size:1.3rem;font-weight:700;color:var(--blue-primary);"><?= formatPrice($total) ?></span>
                </div>
                <a href="checkout.php" class="btn-primary-custom w-100 justify-content-center" style="border-radius:var(--radius-sm);padding:14px;">
                    <i class="bi bi-lock-fill"></i> Proceed to Checkout
                </a>

                <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
                    <span class="pay-badge"><i class="bi bi-cash"></i> COD</span>
                    <span class="pay-badge"><i class="bi bi-phone"></i> GCash</span>
                    <span class="pay-badge"><i class="bi bi-credit-card"></i> Credit Card</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

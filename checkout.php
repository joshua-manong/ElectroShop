<?php
$pageTitle = 'Checkout';
require_once 'includes/config.php';

if (!isLoggedIn()) redirect(SITE_URL . '/login.php?redirect=checkout.php');

// Fetch cart items
$cartStmt = $pdo->prepare("SELECT c.*, p.product_name, p.price, p.product_image, p.brand, p.stock FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
$cartStmt->execute([$_SESSION['user_id']]);
$cartItems = $cartStmt->fetchAll();

if (empty($cartItems)) redirect(SITE_URL . '/cart.php');

$subtotal = 0;
foreach ($cartItems as $item) $subtotal += $item['price'] * $item['quantity'];
$shipping = $subtotal >= 2000 ? 0 : 150;
$total = $subtotal + $shipping;

// Fetch user info
$userStmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

$error = '';
$success = '';

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $fullName      = trim($_POST['full_name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $shippingAddr  = trim($_POST['shipping_address'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? '';

    if (!$fullName || !$email || !$shippingAddr || !$paymentMethod) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $pdo->beginTransaction();
            // Insert order
            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, payment_method, order_status, shipping_address) VALUES (?, ?, ?, 'pending', ?)");
            $orderStmt->execute([$_SESSION['user_id'], $total, $paymentMethod, $shippingAddr]);
            $orderId = $pdo->lastInsertId();
            // Insert order items
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $itemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
                // Decrease stock
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?")->execute([$item['quantity'], $item['product_id']]);
            }
            // Clear cart
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
            $pdo->commit();
            redirect(SITE_URL . "/order-success.php?order_id=$orderId");
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'An error occurred. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Checkout</h1>
        <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="index.php">Home</a></li><li class="breadcrumb-item"><a href="cart.php">Cart</a></li><li class="breadcrumb-item active">Checkout</li></ol></nav>
    </div>
</div>

<div class="container section-spacing" style="padding-top:40px;">
    <?php if ($error): ?>
    <div class="alert mb-4" style="background:rgba(255,51,102,.1);border:1px solid #ff3366;color:#ff6688;border-radius:var(--radius-md);">
        <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php">
        <div class="row g-4">
            <!-- LEFT -->
            <div class="col-lg-8">
                <!-- Customer Info -->
                <div class="checkout-card">
                    <div class="checkout-section-title"><i class="bi bi-person-fill"></i> Customer Information</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="checkout-card">
                    <div class="checkout-section-title"><i class="bi bi-geo-alt-fill"></i> Shipping Address</div>
                    <div class="mb-3">
                        <label class="form-label">Full Delivery Address *</label>
                        <textarea name="shipping_address" class="form-control" rows="3" placeholder="House/Unit No., Street, Barangay, City, Province, ZIP Code" required><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="checkout-card">
                    <div class="checkout-section-title"><i class="bi bi-credit-card-fill"></i> Payment Method</div>
                    <input type="hidden" name="payment_method" id="payment_method" value="cash_on_delivery">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="payment-option selected" data-method="cash_on_delivery" onclick="selectPayment('cash_on_delivery')">
                                <i class="bi bi-cash-coin"></i>
                                <div>
                                    <div class="payment-option-label">Cash on Delivery (COD)</div>
                                    <div class="payment-option-desc">Pay with cash when your order arrives</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="payment-option" data-method="gcash" onclick="selectPayment('gcash')">
                                <i class="bi bi-phone-fill"></i>
                                <div>
                                    <div class="payment-option-label">GCash</div>
                                    <div class="payment-option-desc">Pay via GCash mobile wallet</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="payment-option" data-method="credit_card" onclick="selectPayment('credit_card')">
                                <i class="bi bi-credit-card-2-front-fill"></i>
                                <div>
                                    <div class="payment-option-label">Credit / Debit Card</div>
                                    <div class="payment-option-desc">Visa, Mastercard, JCB accepted</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="col-lg-4">
                <div class="cart-summary-box">
                    <h5 style="font-family:var(--font-display);font-weight:700;color:var(--white-pure);margin-bottom:20px;">Order Summary</h5>
                    <div style="max-height:300px;overflow-y:auto;">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="<?= $item['product_image'] ?>" style="width:50px;height:50px;border-radius:var(--radius-sm);object-fit:cover;" onerror="this.src='https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=200'">
                            <div class="flex-grow-1">
                                <div style="font-size:.85rem;font-weight:600;color:var(--white-pure);line-height:1.2;"><?= htmlspecialchars(substr($item['product_name'], 0, 30)) ?><?= strlen($item['product_name']) > 30 ? '...' : '' ?></div>
                                <div style="font-size:.75rem;color:var(--text-secondary);">x<?= $item['quantity'] ?></div>
                            </div>
                            <div style="font-weight:700;color:var(--blue-primary);white-space:nowrap;"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <hr style="border-color:var(--black-border);">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-secondary);">Subtotal</span>
                        <span style="color:var(--white-pure);font-weight:600;"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span style="color:var(--text-secondary);">Shipping</span>
                        <span style="color:<?= $shipping===0?'#00D4AA':'var(--white-pure)' ?>;font-weight:600;"><?= $shipping===0 ? 'FREE' : formatPrice($shipping) ?></span>
                    </div>
                    <hr style="border-color:var(--black-border);">
                    <div class="d-flex justify-content-between mb-4">
                        <span style="font-weight:700;font-size:1.1rem;color:var(--white-pure);">Total</span>
                        <span style="font-family:var(--font-display);font-size:1.3rem;font-weight:700;color:var(--blue-primary);"><?= formatPrice($total) ?></span>
                    </div>
                    <button type="submit" name="place_order" class="btn-primary-custom w-100 justify-content-center" style="border-radius:var(--radius-sm);padding:14px;">
                        <i class="bi bi-bag-check-fill"></i> Place Order
                    </button>
                    <div class="text-center mt-3" style="font-size:.75rem;color:var(--text-secondary);">
                        <i class="bi bi-shield-lock me-1"></i> Your order is secured and protected
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>

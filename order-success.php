<?php
// order-success.php
$pageTitle = 'Order Placed!';
require_once 'includes/config.php';
if (!isLoggedIn()) redirect(SITE_URL . '/login.php');
$orderId = (int)($_GET['order_id'] ?? 0);
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$orderStmt->execute([$orderId, $_SESSION['user_id']]);
$order = $orderStmt->fetch();
if (!$order) redirect(SITE_URL . '/index.php');
require_once 'includes/header.php';
?>
<div class="container" style="padding: 100px 20px; text-align:center;">
    <div style="background:var(--black-card);border:1px solid var(--black-border);border-radius:var(--radius-lg);padding:60px 40px;max-width:600px;margin:0 auto;">
        <div style="width:80px;height:80px;background:rgba(0,212,170,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:2.5rem;color:#00D4AA;">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h2 style="font-family:var(--font-display);color:var(--white-pure);margin-bottom:12px;">Order Placed Successfully!</h2>
        <p style="color:var(--text-secondary);margin-bottom:8px;">Thank you for shopping at ElectroShop!</p>
        <p style="color:var(--text-secondary);margin-bottom:32px;">Order #<strong style="color:var(--blue-primary);"><?= str_pad($orderId, 6, '0', STR_PAD_LEFT) ?></strong> has been confirmed.</p>
        <div style="background:var(--black-surface);border-radius:var(--radius-md);padding:20px;margin-bottom:32px;text-align:left;">
            <div class="d-flex justify-content-between mb-2"><span style="color:var(--text-secondary);">Total Amount</span><span style="color:var(--blue-primary);font-weight:700;font-family:var(--font-display);"><?= formatPrice($order['total_price']) ?></span></div>
            <div class="d-flex justify-content-between mb-2"><span style="color:var(--text-secondary);">Payment</span><span style="color:var(--white-pure);"><?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></span></div>
            <div class="d-flex justify-content-between"><span style="color:var(--text-secondary);">Status</span><span class="status-badge status-pending">Pending</span></div>
        </div>
        <div class="d-flex gap-3 justify-content-center">
            <a href="index.php" class="btn-outline-custom"><i class="bi bi-house"></i> Home</a>
            <a href="shop.php" class="btn-primary-custom"><i class="bi bi-bag"></i> Continue Shopping</a>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

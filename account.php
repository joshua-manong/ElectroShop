<?php
$pageTitle = 'My Account';
require_once 'includes/config.php';
if (!isLoggedIn()) redirect(SITE_URL . '/login.php');

$user = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$_SESSION['user_id']]);
$user = $user->fetch();

$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY order_date DESC LIMIT 10");
$orders->execute([$_SESSION['user_id']]);
$orders = $orders->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $pdo->prepare("UPDATE users SET full_name=?,phone=?,address=? WHERE user_id=?")->execute([$fullName,$phone,$address,$_SESSION['user_id']]);
    $_SESSION['full_name'] = $fullName;
    $message = 'Profile updated successfully!';
}

require_once 'includes/header.php';
?>
<div class="page-banner"><div class="container"><h1 class="page-banner-title">My Account</h1></div></div>
<div class="container section-spacing" style="padding-top:40px;">
<?php if($message): ?><div class="alert mb-4" style="background:rgba(0,212,170,.1);border:1px solid #00d4aa;color:#00d4aa;border-radius:var(--radius-md);"><?= $message ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div style="background:var(--black-card);border:1px solid var(--black-border);border-radius:var(--radius-lg);padding:28px;margin-bottom:20px;">
            <div class="text-center mb-3">
                <div style="width:72px;height:72px;background:var(--gradient-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:900;color:white;margin:0 auto 12px;"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
                <h5 style="color:var(--white-pure);"><?= htmlspecialchars($user['full_name']) ?></h5>
                <span class="status-badge status-completed"><?= ucfirst($user['role']) ?></span>
            </div>
            <hr style="border-color:var(--black-border);">
            <form method="POST">
                <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>"></div>
                <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>"></div>
                <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea></div>
                <button type="submit" class="btn-primary-custom w-100 justify-content-center"><i class="bi bi-save"></i> Save Changes</button>
            </form>
        </div>
        <div class="d-flex gap-2">
            <a href="wishlist.php" class="btn-outline-custom flex-grow-1 justify-content-center"><i class="bi bi-heart"></i> Wishlist</a>
            <a href="logout.php" class="btn btn-sm" style="background:rgba(255,51,102,.1);border:1px solid #FF3366;color:#FF3366;border-radius:var(--radius-sm);padding:10px 16px;"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-lg-8">
        <div style="background:var(--black-card);border:1px solid var(--black-border);border-radius:var(--radius-lg);overflow:hidden;">
            <div style="padding:20px;border-bottom:1px solid var(--black-border);font-weight:700;color:var(--white-pure);"><i class="bi bi-bag me-2 text-blue"></i>Order History</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Order #</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td style="color:var(--blue-primary);font-weight:700;">#<?= str_pad($o['order_id'],5,'0',STR_PAD_LEFT) ?></td>
                        <td style="font-weight:700;"><?= formatPrice($o['total_price']) ?></td>
                        <td style="font-size:.85rem;color:var(--text-secondary);"><?= ucwords(str_replace('_',' ',$o['payment_method'])) ?></td>
                        <td><span class="status-badge status-<?= $o['order_status'] ?>"><?= ucfirst($o['order_status']) ?></span></td>
                        <td style="font-size:.8rem;color:var(--text-secondary);"><?= date('M d, Y',strtotime($o['order_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($orders)): ?><tr><td colspan="5" class="text-center py-4" style="color:var(--text-secondary);">No orders yet</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'includes/config.php';

if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$error = '';
$success = '';
$activeTab = 'login';

// ============ REGISTRATION ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $activeTab = 'register';
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');

    if (!$full_name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email exists
        $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            $insertStmt->execute([$full_name, $email, $hashed, $phone, $address]);
            $success = 'Account created successfully! You can now login.';
            $activeTab = 'login';
        }
    }
}

// ============ LOGIN ============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];

            if ($user['role'] === 'admin') {
                redirect(SITE_URL . '/admin/dashboard.php');
            } else {
                redirect(SITE_URL . '/index.php');
            }
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register | ElectroShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="auth-header">
            <a href="<?= SITE_URL ?>/index.php" class="logo d-inline-flex mb-3">
                <div class="logo-icon me-2">
                    <svg width="32" height="32" viewBox="0 0 36 36" fill="none">
                        <rect width="36" height="36" rx="8" fill="rgba(255,255,255,0.2)"/>
                        <path d="M8 18H14M22 18H28M18 8V14M18 22V28" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="18" cy="18" r="4" fill="white"/>
                        <path d="M12 12L15 15M21 21L24 24M12 24L15 21M21 15L24 12" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <span style="font-family:var(--font-display);font-size:1.3rem;font-weight:900;color:white;">Electro<span style="opacity:.8;">Shop</span></span>
            </a>
            <p style="color:rgba(255,255,255,0.8);font-size:.9rem;margin:0;">Your trusted electronics store in the Philippines</p>
        </div>

        <!-- Body -->
        <div class="auth-body">
            <!-- Tabs -->
            <div class="auth-tabs">
                <div class="auth-tab <?= $activeTab === 'login' ? 'active' : '' ?>" data-tab="login" onclick="switchAuthTab('login')">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                </div>
                <div class="auth-tab <?= $activeTab === 'register' ? 'active' : '' ?>" data-tab="register" onclick="switchAuthTab('register')">
                    <i class="bi bi-person-plus me-2"></i> Register
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" style="background:rgba(255,51,102,.1);border:1px solid #ff3366;color:#ff6688;">
                <i class="bi bi-exclamation-circle-fill"></i> <?= $error ?>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center gap-2" style="background:rgba(0,212,170,.1);border:1px solid #00d4aa;color:#00d4aa;">
                <i class="bi bi-check-circle-fill"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <!-- LOGIN FORM -->
            <div id="loginForm" class="auth-form" style="display: <?= $activeTab === 'login' ? 'block' : 'none' ?>">
                <form method="POST" action="login.php">
                    <input type="hidden" name="action" value="login">
                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:var(--black-surface);border-color:var(--black-border);color:var(--blue-primary);">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" name="email" class="form-control" placeholder="your@email.com" required value="<?= isset($_POST['email']) && $_POST['action'] === 'login' ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:var(--black-surface);border-color:var(--black-border);color:var(--blue-primary);">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
                            <button type="button" class="input-group-text" style="background:var(--black-surface);border-color:var(--black-border);color:var(--text-secondary);cursor:pointer;" onclick="togglePassword('loginPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary-custom w-100 justify-content-center mb-3" style="border-radius:var(--radius-sm);">
                        <i class="bi bi-box-arrow-in-right"></i> Login to Account
                    </button>
                    <div class="text-center">
                        <small style="color:var(--text-secondary);"></small>
                    </div>
                </form>
            </div>

            <!-- REGISTER FORM -->
            <div id="registerForm" class="auth-form" style="display: <?= $activeTab === 'register' ? 'block' : 'none' ?>">
                <form method="POST" action="login.php">
                    <input type="hidden" name="action" value="register">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Juan Dela Cruz" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" id="regPassword" class="form-control" placeholder="Min. 6 characters" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="09XX XXX XXXX">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Street, Barangay, City, Province"></textarea>
                    </div>
                    <button type="submit" class="btn-primary-custom w-100 justify-content-center" style="border-radius:var(--radius-sm);">
                        <i class="bi bi-person-plus"></i> Create Account
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center pb-4">
            <a href="<?= SITE_URL ?>/index.php" style="color:var(--text-secondary);font-size:.85rem;">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function switchAuthTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(f => f.style.display = 'none');
    document.querySelector(`.auth-tab[data-tab="${tab}"]`).classList.add('active');
    document.getElementById(tab + 'Form').style.display = 'block';
}
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { input.type = 'password'; icon.className = 'bi bi-eye'; }
}
</script>
</body>
</html>

<?php
$pageTitle = 'Contact Us';
require_once 'includes/config.php';

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In production, you'd send an email here
    $name    = sanitize($_POST['name'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    if ($name && $email && $message) {
        $success = 'Thank you for your message! We will get back to you within 24 hours.';
    } else {
        $error = 'Please fill in all required fields.';
    }
}

require_once 'includes/header.php';
?>
<div class="page-banner">
    <div class="container">
        <h1 class="page-banner-title">Contact Us</h1>
        <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="index.php">Home</a></li><li class="breadcrumb-item active">Contact</li></ol></nav>
    </div>
</div>

<section class="section-spacing">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <div class="section-label">Get In Touch</div>
                <h2 class="section-title">We're Here to Help</h2>
                <p style="color:var(--text-secondary);margin-bottom:32px;">Have questions about a product? Need help with your order? Our team is ready to assist you.</p>
                <?php foreach ([['bi-geo-alt','Visit Us','WVSU, Caradio-an, Himamaylan City 6000, Philippines'],['bi-telephone','Call Us','+63 953 599 3164<br>+63 32 123 4567'],['bi-envelope','Email Us','jmanong04@gmail.com<br>jmanong0001@gmail.com'],['bi-clock','Business Hours','Mon - Sat: 9:00 AM - 6:00 PM<br>Sun: Closed']] as $c): ?>
                <div class="d-flex gap-3 mb-4">
                    <div style="width:48px;height:48px;background:var(--blue-glow);border:1px solid rgba(0,102,255,.2);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi <?= $c[0] ?> text-blue fs-5"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;color:var(--white-pure);margin-bottom:2px;"><?= $c[1] ?></div>
                        <div style="color:var(--text-secondary);font-size:.9rem;"><?= $c[2] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <!-- Map placeholder -->
                <div style="background:var(--black-card);border:1px solid var(--black-border);border-radius:var(--radius-md);overflow:hidden;height:200px;display:flex;align-items:center;justify-content:center;margin-top:20px;">
                    <div class="text-center" style="color:var(--text-secondary);">
                        <i class="bi bi-map fs-2 d-block mb-2 text-blue"></i>
                        <span style="font-size:.85rem;">WVSU, Caradio-an, Himamaylan City</span><br>
                        <a href="https://maps.app.goo.gl/P7SFdBvcV9VwvZhZ6" target="_blank" style="font-size:.8rem;color:var(--blue-light);">View on Google Maps</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div style="background:var(--black-card);border:1px solid var(--black-border);border-radius:var(--radius-lg);padding:40px;">
                    <h3 style="font-family:var(--font-display);color:var(--white-pure);margin-bottom:24px;">Send Us a Message</h3>
                    <?php if ($success): ?>
                    <div class="alert mb-4" style="background:rgba(0,212,170,.1);border:1px solid #00d4aa;color:#00d4aa;border-radius:var(--radius-sm);">
                        <i class="bi bi-check-circle me-2"></i><?= $success ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                    <div class="alert mb-4" style="background:rgba(255,51,102,.1);border:1px solid #ff3366;color:#ff6688;border-radius:var(--radius-sm);">
                        <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" placeholder="Joshua Manong" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="How can we help?">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn-primary-custom">
                                    <i class="bi bi-send"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

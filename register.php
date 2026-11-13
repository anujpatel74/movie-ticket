<?php
/**
 * CINEMA BOOKING - User Registration Page
 */

require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(base_url('index.php'));
}

$pageTitle = 'Create an Account';

$extraJs = '<script src="' . asset_url('js/validation.js') . '"></script>';

include __DIR__ . '/includes/header.php';
?>

<div class="container auth-wrapper">
    <div class="row justify-content-center w-100">
        <div class="col-md-7 col-lg-6 col-xl-5">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <span class="brand-icon fs-2 text-danger mb-2 d-inline-block">
                        <i class="bi bi-film"></i>
                    </span>
                    <h2 class="text-white fw-bold mb-1" style="font-size: 1.5rem;">Create Account</h2>
                    <p class="text-secondary small">Join CINEMA BOOKING to reserve your favorite seats</p>
                </div>

                <div id="passMatchError" class="alert alert-danger py-2 small d-none">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Passwords do not match. Please verify.
                </div>

                <form action="<?= base_url('backend/auth-process.php') ?>" method="POST" id="registerForm">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="register">

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control cinematic-input" placeholder="e.g. Alex Hunter" required autocomplete="name">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label text-white small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control cinematic-input" placeholder="alex@example.com" required autocomplete="email">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-white small fw-bold">Phone Number</label>
                            <input type="tel" name="phone" class="form-control cinematic-input" placeholder="+91 9876543210" autocomplete="tel">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label text-white small fw-bold">Password</label>
                            <input type="password" name="password" id="password" class="form-control cinematic-input" placeholder="Min. 6 chars" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-white small fw-bold">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control cinematic-input" placeholder="Repeat password" required minlength="6" autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent w-100 py-2 fw-semibold mb-3">
                        Complete Registration
                    </button>
                </form>

                <div class="text-center">
                    <p class="text-secondary small mb-0">
                        Already have an account? 
                        <a href="<?= base_url('login.php') ?>" class="text-danger fw-semibold ms-1">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

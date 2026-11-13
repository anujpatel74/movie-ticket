<?php
/**
 * CINEMA BOOKING - User Login Page
 */

require_once __DIR__ . '/includes/functions.php';

// If user is already logged in, redirect
if (is_logged_in()) {
    redirect(base_url('index.php'));
}

$pageTitle = 'Sign In to Your Account';

$extraJs = '<script src="' . asset_url('js/validation.js') . '"></script>';

include __DIR__ . '/includes/header.php';
?>

<div class="container auth-wrapper">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <span class="brand-icon fs-2 text-danger mb-2 d-inline-block">
                        <i class="bi bi-film"></i>
                    </span>
                    <h2 class="text-white fw-bold mb-1" style="font-size: 1.5rem;">Welcome Back</h2>
                    <p class="text-secondary small">Sign in to manage bookings and reserve movie tickets</p>
                </div>

                <form action="<?= base_url('backend/auth-process.php') ?>" method="POST" id="loginForm">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="login">

                    <div class="mb-3">
                        <label class="form-label text-white small fw-bold">Email Address</label>
                        <input type="email" name="email" id="loginEmail" class="form-control cinematic-input" placeholder="name@example.com" required autocomplete="email">
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-white small fw-bold m-0">Password</label>
                        </div>
                        <input type="password" name="password" id="loginPassword" class="form-control cinematic-input" placeholder="••••••••" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn btn-accent w-100 py-2 fw-semibold mb-3">
                        Sign In
                    </button>
                </form>

                <!-- One-click Demo Credentials Helper -->
                <div class="p-3 rounded-2 text-center mb-4" style="background: var(--bg-secondary); border: 1px solid var(--border);">
                    <span class="text-secondary fs-xs d-block mb-2">Want to test quickly? Click below:</span>
                    <button type="button" class="btn btn-sm btn-outline-dark-custom px-3 fs-xs" onclick="document.getElementById('loginEmail').value='john@example.com'; document.getElementById('loginPassword').value='User@123';">
                        <i class="bi bi-person-fill text-danger me-1"></i>Fill Demo User (john@example.com)
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-secondary small mb-0">
                        Don't have an account? 
                        <a href="<?= base_url('register.php') ?>" class="text-danger fw-semibold ms-1">Create Account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

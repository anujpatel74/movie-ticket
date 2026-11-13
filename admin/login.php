<?php
/**
 * CINEMA BOOKING - Administrator Login
 */

require_once __DIR__ . '/../includes/functions.php';

if (is_admin_logged_in()) {
    redirect(base_url('admin/index.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $error = 'Security session expired. Please retry.';
    } else {
        $usernameOrEmail = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($usernameOrEmail) || empty($password)) {
            $error = 'Please enter both username/email and password.';
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                SELECT * FROM admins 
                WHERE (username = ? OR email = ?) AND status = 'active'
                LIMIT 1
            ");
            $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];

                set_flash('success', 'Welcome to Admin Control, ' . htmlspecialchars($admin['full_name']) . '!');
                redirect(base_url('admin/index.php'));
            } else {
                $error = 'Invalid administrator credentials or inactive account.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign In - CINEMA BOOKING</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>">
    <style>
        body {
            background: #06070a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .admin-login-box {
            background: #0f121c;
            border: 1px solid rgba(255, 0, 85, 0.3);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.8);
        }
    </style>
</head>
<body>
    <div class="admin-login-box">
        <div class="text-center mb-4">
            <span class="fs-1 text-danger d-inline-block mb-2"><i class="bi bi-shield-lock-fill"></i></span>
            <h3 class="text-white fw-bold mb-1">Admin Portal</h3>
            <p class="text-secondary small">Sign in with authorized administrator credentials</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <?= csrf_input() ?>

            <div class="mb-3">
                <label class="form-label text-white small fw-bold">Admin ID / Username</label>
                <input type="text" name="username" id="adminUser" class="form-control bg-dark border-secondary text-white" placeholder="Enter username or email" autocomplete="off" required>
            </div>

            <div class="mb-4">
                <label class="form-label text-white small fw-bold">Password</label>
                <input type="password" name="password" id="adminPass" class="form-control bg-dark border-secondary text-white" placeholder="••••••••" autocomplete="new-password" required>
            </div>

            <button type="submit" class="btn btn-danger w-100 py-3 rounded-pill fw-bold mb-3" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border: none;">
                <i class="bi bi-box-arrow-in-right me-2"></i>Authenticate Admin
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= base_url('index.php') ?>" class="text-secondary small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Return to Customer Website
            </a>
        </div>
    </div>
</body>
</html>


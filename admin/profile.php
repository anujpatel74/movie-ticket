<?php
/**
 * CINEMA BOOKING - Administrator Profile & Password Management
 * Secure password update and credential verification
 */

require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Admin Profile & Security';
$pdo = getDBConnection();
$adminId = (int)$_SESSION['admin_id'];

// Fetch current fresh admin details
$stmt = $pdo->prepare("SELECT id, username, full_name, email, role, created_at, password FROM admins WHERE id = ? LIMIT 1");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    logout_admin();
    redirect(base_url('admin/login.php'));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'Security token expired. Please retry.');
        redirect(base_url('admin/profile.php'));
    }

    $action = $_POST['action'] ?? '';

    // 1. UPDATE PROFILE DETAILS
    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($fullName) || empty($email)) {
            set_flash('danger', 'Full name and email address are required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('danger', 'Please provide a valid email address.');
        } else {
            // Check if email is already taken by another admin
            $checkStmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ? LIMIT 1");
            $checkStmt->execute([$email, $adminId]);
            if ($checkStmt->fetch()) {
                set_flash('danger', 'This email address is already in use by another administrator.');
            } else {
                $upStmt = $pdo->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?");
                $upStmt->execute([$fullName, $email, $adminId]);

                $_SESSION['admin_name'] = $fullName;
                $_SESSION['admin_email'] = $email;

                set_flash('success', 'Profile information updated successfully.');
                redirect(base_url('admin/profile.php'));
            }
        }
    }

    // 2. CHANGE PASSWORD
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            set_flash('danger', 'All password fields are required.');
        } elseif (!password_verify($currentPassword, $admin['password'])) {
            set_flash('danger', 'The current password you entered is incorrect.');
        } elseif (strlen($newPassword) < 8) {
            set_flash('danger', 'New password must be at least 8 characters long.');
        } elseif ($newPassword !== $confirmPassword) {
            set_flash('danger', 'New password and confirmation password do not match.');
        } else {
            // Hash using PASSWORD_DEFAULT (BCrypt / Argon2 depending on PHP configuration)
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $upStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $upStmt->execute([$newHash, $adminId]);

            // Regenerate session identifier after credential change
            session_regenerate_id(true);

            set_flash('success', 'Your administrator password has been updated securely.');
            redirect(base_url('admin/profile.php'));
        }
    }

    redirect(base_url('admin/profile.php'));
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white fw-bold mb-1">Security & Profile</h2>
        <p class="text-secondary small mb-0">Manage administrator account credentials and password security</p>
    </div>
</div>

<div class="row g-4">
    <!-- Profile Info Card -->
    <div class="col-lg-6">
        <div class="admin-card h-100">
            <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-danger"></i>Account Information
            </h5>

            <form method="POST" action="">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="update_profile">

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">Admin Username</label>
                    <input type="text" class="form-control bg-dark border-secondary text-white opacity-75" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                    <div class="form-text text-secondary fs-xs">Username cannot be changed.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">Full Name</label>
                    <input type="text" name="full_name" class="form-control bg-dark border-secondary text-white" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control bg-dark border-secondary text-white" value="<?= htmlspecialchars($admin['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">Assigned Role</label>
                    <input type="text" class="form-control bg-dark border-secondary text-white opacity-75" value="<?= htmlspecialchars(strtoupper($admin['role'])) ?>" disabled>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small fw-bold">Account Created</label>
                    <div class="text-white small"><?= format_date($admin['created_at'], 'd M Y, h:i A') ?></div>
                </div>

                <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-4">
                    <i class="bi bi-check2 me-1"></i>Save Profile Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Password Change Card -->
    <div class="col-lg-6">
        <div class="admin-card h-100">
            <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-warning"></i>Change Password
            </h5>

            <form method="POST" action="" autocomplete="off">
                <?= csrf_input() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">Current Password</label>
                    <input type="password" name="current_password" class="form-control bg-dark border-secondary text-white" placeholder="Enter current password" required autocomplete="current-password">
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">New Password</label>
                    <input type="password" name="new_password" class="form-control bg-dark border-secondary text-white" placeholder="Minimum 8 characters" minlength="8" required autocomplete="new-password">
                    <div class="form-text text-secondary fs-xs">Use at least 8 characters with a mix of letters, numbers, and symbols.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small fw-bold">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control bg-dark border-secondary text-white" placeholder="Re-type new password" minlength="8" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border: none;">
                    <i class="bi bi-key-fill me-1"></i>Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


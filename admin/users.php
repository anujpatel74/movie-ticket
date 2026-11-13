<?php
/**
 * CINEMA BOOKING - Admin User Management
 */

require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'User Management';
$pdo = getDBConnection();

$action = $_POST['action'] ?? '';

// Toggle User Status (Active / Banned)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'toggle_status') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf($token)) {
        $userId = (int)($_POST['id'] ?? 0);
        $newStatus = ($_POST['status'] === 'active') ? 'banned' : 'active';
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $userId]);
        set_flash('success', "User status updated to {$newStatus}.");
    }
    redirect(base_url('admin/users.php'));
}

// Fetch users with booking aggregation
$users = $pdo->query("
    SELECT u.*,
           COUNT(b.id) AS booking_count,
           COALESCE(SUM(CASE WHEN b.booking_status = 'confirmed' THEN b.grand_total ELSE 0 END), 0) AS total_spend
    FROM users u
    LEFT JOIN bookings b ON b.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white fw-bold mb-1">Registered Customers</h2>
        <p class="text-secondary small mb-0">Monitor customer accounts, booking engagement, and account statuses</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Total Bookings</th>
                    <th>Lifetime Value</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <strong class="text-white d-block"><?= htmlspecialchars($u['full_name']) ?></strong>
                            <span class="badge bg-dark border border-secondary text-secondary fs-xs">ID #<?= $u['id'] ?></span>
                        </td>
                        <td>
                            <span class="text-light"><?= htmlspecialchars($u['email']) ?></span>
                        </td>
                        <td>
                            <span class="text-secondary"><?= htmlspecialchars($u['phone'] ?? '-') ?></span>
                        </td>
                        <td>
                            <span class="badge bg-purple bg-opacity-25 text-purple-light"><?= (int)$u['booking_count'] ?> Bookings</span>
                        </td>
                        <td>
                            <strong class="text-danger"><?= format_currency($u['total_spend']) ?></strong>
                        </td>
                        <td>
                            <?php if ($u['status'] === 'active'): ?>
                                <span class="badge bg-success bg-opacity-25 text-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-25 text-danger">Banned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-secondary small"><?= format_date($u['created_at'], 'd M Y') ?></span>
                        </td>
                        <td class="text-end">
                            <form action="" method="POST" class="d-inline">
                                <?= csrf_input() ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="status" value="<?= $u['status'] ?>">
                                
                                <?php if ($u['status'] === 'active'): ?>
                                    <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="return confirm('Ban this user account?');">
                                        Suspend
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        Activate
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


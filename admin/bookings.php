<?php
/**
 * CINEMA BOOKING - Admin Bookings Management
 */

require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'View Bookings';
$pdo = getDBConnection();

$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT b.*, u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
           s.show_date, s.show_time,
           m.title AS movie_title, t.name AS theatre_name, sc.screen_name,
           (SELECT GROUP_CONCAT(bs.seat_code SEPARATOR ', ') FROM booking_seats bs WHERE bs.booking_id = b.id) AS seat_codes,
           p.transaction_ref, p.payment_method
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN theatres t ON s.theatre_id = t.id
    JOIN screens sc ON s.screen_id = sc.id
    LEFT JOIN payments p ON p.booking_id = b.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.booking_reference LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR m.title LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="text-white fw-bold mb-1">Customer Bookings</h2>
        <p class="text-secondary small mb-0">Total of <?= count($bookings) ?> ticket transactions recorded</p>
    </div>

    <form class="d-flex" action="" method="GET">
        <div class="input-group">
            <input type="text" name="q" class="form-control cinematic-input" placeholder="Search ref, customer, movie..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-danger" type="submit" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border: none;">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Ref / Date</th>
                    <th>Customer</th>
                    <th>Movie & Theatre</th>
                    <th>Showtime</th>
                    <th>Seats</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-secondary">No customer bookings found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong class="text-white font-monospace d-block"><?= htmlspecialchars($b['booking_reference']) ?></strong>
                                <small class="text-secondary"><?= format_date($b['created_at'], 'd M, H:i') ?></small>
                            </td>
                            <td>
                                <div class="text-white fw-bold"><?= htmlspecialchars($b['customer_name']) ?></div>
                                <small class="text-secondary"><?= htmlspecialchars($b['customer_email']) ?></small>
                            </td>
                            <td>
                                <strong class="text-white d-block"><?= htmlspecialchars($b['movie_title']) ?></strong>
                                <small class="text-secondary"><?= htmlspecialchars($b['theatre_name']) ?> &bull; <?= htmlspecialchars($b['screen_name']) ?></small>
                            </td>
                            <td>
                                <span class="text-light"><?= format_date($b['show_date'], 'D, d M') ?></span>
                                <div class="text-danger fw-bold fs-xs"><?= format_time($b['show_time']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-light">
                                    <?= htmlspecialchars($b['seat_codes'] ?? 'None') ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-danger"><?= format_currency($b['grand_total']) ?></strong>
                                <small class="text-secondary d-block fs-xs text-uppercase"><?= htmlspecialchars($b['payment_method'] ?? 'card') ?></small>
                            </td>
                            <td>
                                <?php if ($b['booking_status'] === 'confirmed'): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success">Confirmed</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= base_url('ticket.php?ref=' . urlencode($b['booking_reference'])) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-circle me-1" title="View Digital Ticket">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <?php if ($b['booking_status'] === 'confirmed'): ?>
                                    <form action="<?= base_url('backend/ticket-process.php') ?>" method="POST" class="d-inline" onsubmit="return confirm('Cancel this booking and release the seats?');">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="action" value="cancel_booking">
                                        <input type="hidden" name="booking_reference" value="<?= htmlspecialchars($b['booking_reference']) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Cancel Booking">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


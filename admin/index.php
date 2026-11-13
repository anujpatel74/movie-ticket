<?php
/**
 * CINEMA BOOKING - Administrator Dashboard
 */

require_once __DIR__ . '/includes/auth-check.php';

$adminTitle = 'Dashboard';
$pdo = getDBConnection();

// Fetch summary metrics
$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM bookings WHERE booking_status = 'confirmed'")->fetchColumn();
$totalBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'confirmed'")->fetchColumn();
$totalMovies = (int)$pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch recent bookings
$recentStmt = $pdo->query("
    SELECT b.*, u.full_name AS customer_name, m.title AS movie_title, t.name AS theatre_name,
           (SELECT GROUP_CONCAT(bs.seat_code SEPARATOR ', ') FROM booking_seats bs WHERE bs.booking_id = b.id) AS seat_codes
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN theatres t ON s.theatre_id = t.id
    ORDER BY b.created_at DESC
    LIMIT 6
");
$recentBookings = $recentStmt->fetchAll();

// Fetch top movies
$popularMovies = $pdo->query("
    SELECT m.id, m.title, m.poster_url, m.rating, COUNT(b.id) AS total_orders, COALESCE(SUM(b.grand_total), 0) AS total_sales
    FROM movies m
    LEFT JOIN shows s ON s.movie_id = m.id
    LEFT JOIN bookings b ON b.show_id = s.id AND b.booking_status = 'confirmed'
    GROUP BY m.id
    ORDER BY total_sales DESC
    LIMIT 4
")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-white fw-bold mb-1">Operational Overview</h2>
        <p class="text-secondary small mb-0">Real-time statistics across bookings, revenue, and active screenings</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/shows.php') ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="bi bi-plus-circle me-1"></i>New Show
        </a>
        <a href="<?= base_url('admin/movies.php?action=create') ?>" class="btn btn-sm btn-danger rounded-pill px-3" style="background: linear-gradient(135deg, #ff0055 0%, #7928ca 100%); border: none;">
            <i class="bi bi-film me-1"></i>Add Movie
        </a>
    </div>
</div>

<!-- Key Stat Cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="admin-stat-card">
            <div>
                <div class="stat-label">Total Demo Revenue</div>
                <div class="stat-val text-danger mt-1"><?= format_currency($totalRevenue) ?></div>
            </div>
            <div class="stat-icon-wrap" style="background: rgba(255, 0, 85, 0.15); color: #ff0055;">
                <i class="bi bi-currency-rupee"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-stat-card">
            <div>
                <div class="stat-label">Confirmed Bookings</div>
                <div class="stat-val text-white mt-1"><?= number_format($totalBookings) ?></div>
            </div>
            <div class="stat-icon-wrap" style="background: rgba(121, 40, 202, 0.15); color: #9d4edd;">
                <i class="bi bi-ticket-perforated-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-stat-card">
            <div>
                <div class="stat-label">Movies Catalogue</div>
                <div class="stat-val text-white mt-1"><?= number_format($totalMovies) ?></div>
            </div>
            <div class="stat-icon-wrap" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                <i class="bi bi-camera-reels-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="admin-stat-card">
            <div>
                <div class="stat-label">Registered Customers</div>
                <div class="stat-val text-white mt-1"><?= number_format($totalUsers) ?></div>
            </div>
            <div class="stat-icon-wrap" style="background: rgba(34, 197, 94, 0.15); color: #22c55e;">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Bookings Table -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-clock-history me-2 text-danger"></i>Latest Customer Orders</h5>
                <a href="<?= base_url('admin/bookings.php') ?>" class="text-danger small fw-bold text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBookings)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-secondary">No customer orders recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentBookings as $rb): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-dark border border-secondary text-secondary font-monospace">
                                            <?= htmlspecialchars($rb['booking_reference']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-white"><?= htmlspecialchars($rb['customer_name']) ?></div>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 140px;"><?= htmlspecialchars($rb['movie_title']) ?></span>
                                    </td>
                                    <td>
                                        <span class="text-light small"><?= htmlspecialchars($rb['seat_codes'] ?? '-') ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-danger"><?= format_currency($rb['grand_total']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($rb['booking_status'] === 'confirmed'): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success">Confirmed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-25 text-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('ticket.php?ref=' . urlencode($rb['booking_reference'])) ?>" target="_blank" class="btn btn-sm btn-outline-light rounded-pill" title="View Ticket">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Grossing Movies -->
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="text-white fw-bold m-0"><i class="bi bi-trophy text-warning me-2"></i>Top Movies by Sales</h5>
            </div>
            <div class="p-3">
                <?php foreach ($popularMovies as $pop): ?>
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2" style="background: rgba(255,255,255,0.02);">
                        <img src="<?= movie_poster_url($pop['poster_url']) ?>" alt="<?= htmlspecialchars($pop['title']) ?>" style="width: 44px; height: 66px; aspect-ratio: 2 / 3; object-fit: cover; border-radius: 6px;" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="text-white fw-bold mb-1 text-truncate"><?= htmlspecialchars($pop['title']) ?></h6>
                            <div class="text-secondary fs-xs">
                                <span><i class="bi bi-ticket me-1"></i><?= (int)$pop['total_orders'] ?> bookings</span> &bull; 
                                <span class="text-danger fw-bold"><?= format_currency($pop['total_sales']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>


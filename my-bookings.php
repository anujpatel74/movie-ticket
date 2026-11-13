<?php
/**
 * CINEMA BOOKING - User Booking History
 */

require_once __DIR__ . '/includes/auth-check.php';

$pageTitle = 'My Bookings & Tickets';
$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Fetch all bookings for logged in user
$stmt = $pdo->prepare("
    SELECT b.*,
           s.show_date, s.show_time,
           m.id AS movie_id, m.title AS movie_title, m.poster_url, m.certificate, m.duration_mins,
           t.name AS theatre_name,
           sc.screen_name, sc.screen_type,
           (SELECT GROUP_CONCAT(bs.seat_code ORDER BY bs.seat_code SEPARATOR ', ') 
            FROM booking_seats bs WHERE bs.booking_id = b.id) AS seat_codes
    FROM bookings b
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN theatres t ON s.theatre_id = t.id
    JOIN screens sc ON s.screen_id = sc.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <div class="page-header-wrap">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="page-title">My Bookings</h1>
                <p class="page-subtitle">View your confirmed movie passes and digital admission tickets.</p>
            </div>
            <a href="<?= base_url('movies.php') ?>" class="btn btn-accent btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Book Another Movie
            </a>
        </div>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="p-5 text-center my-5 rounded-3" style="background: var(--bg-card); border: 1px dashed var(--border);">
            <div class="fs-1 text-secondary mb-3"><i class="bi bi-ticket-perforated"></i></div>
            <h4 class="text-white fw-semibold">No Bookings Found</h4>
            <p class="text-secondary small mb-4">You have not booked any movie tickets yet. Check out the latest movies in theatres!</p>
            <a href="<?= base_url('movies.php') ?>" class="btn btn-accent px-4">
                Explore Now Showing
            </a>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($bookings as $b): ?>
                <?php
                    $isConfirmed = ($b['booking_status'] === 'confirmed');
                    $showDateTime = strtotime($b['show_date'] . ' ' . $b['show_time']);
                    $isPast = ($showDateTime < time());
                ?>
                <div class="p-4 rounded-3" style="background: var(--bg-card); border: 1px solid var(--border); box-shadow: 0 4px 16px rgba(0,0,0,0.3);">
                    <div class="row align-items-center g-3">
                        <div class="col-md-2 col-sm-3 text-center text-sm-start">
                            <img src="<?= movie_poster_url($b['poster_url']) ?>" alt="<?= htmlspecialchars($b['movie_title']) ?>" class="img-fluid rounded-2" style="max-height: 120px; aspect-ratio: 2 / 3; object-fit: cover; border: 1px solid var(--border);" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                        </div>

                        <div class="col-md-7 col-sm-9">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-dark border border-secondary text-secondary font-monospace"><?= htmlspecialchars($b['booking_reference']) ?></span>
                                <?php if ($isConfirmed): ?>
                                    <span class="badge bg-dark border border-success text-success">Confirmed</span>
                                <?php else: ?>
                                    <span class="badge bg-dark border border-danger text-danger">Cancelled</span>
                                <?php endif; ?>

                                <?php if ($isPast): ?>
                                    <span class="badge bg-dark border border-secondary text-muted fs-xs">Past Show</span>
                                <?php endif; ?>
                            </div>

                            <h4 class="text-white fw-bold mb-1" style="font-size: 1.15rem;"><?= htmlspecialchars($b['movie_title']) ?></h4>
                            <div class="text-secondary small mb-2">
                                <i class="bi bi-building me-1 text-danger"></i><?= htmlspecialchars($b['theatre_name']) ?> &bull; 
                                <span class="text-light"><?= htmlspecialchars($b['screen_name']) ?></span>
                            </div>

                            <div class="d-flex flex-wrap gap-3 text-secondary small">
                                <span><i class="bi bi-calendar3 me-1 text-danger"></i><?= format_date($b['show_date'], 'D, d M Y') ?></span>
                                <span><i class="bi bi-clock me-1 text-danger"></i><?= format_time($b['show_time']) ?></span>
                                <span><i class="bi bi-ticket-perforated me-1 text-danger"></i>Seats: <strong class="text-white"><?= htmlspecialchars($b['seat_codes'] ?? 'None') ?></strong></span>
                                <span><i class="bi bi-currency-rupee me-1 text-danger"></i>Paid: <strong class="text-white"><?= format_currency($b['grand_total']) ?></strong></span>
                            </div>
                        </div>

                        <div class="col-md-3 text-md-end text-start pt-2 pt-md-0 border-top border-md-top-0 border-secondary border-opacity-25">
                            <div class="d-flex flex-md-column gap-2 justify-content-md-end justify-content-start">
                                <?php if ($isConfirmed): ?>
                                    <a href="<?= base_url('ticket.php?ref=' . urlencode($b['booking_reference'])) ?>" class="btn btn-sm btn-accent px-3">
                                        <i class="bi bi-qr-code me-1"></i>Digital Pass
                                    </a>
                                <?php endif; ?>
                                <a href="<?= base_url('movie-details.php?id=' . $b['movie_id']) ?>" class="btn btn-sm btn-outline-dark-custom px-3">
                                    Movie Info
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

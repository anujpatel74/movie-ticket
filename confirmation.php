

<?php
/**
 * CINEMA BOOKING - Booking Confirmation
 */

require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/backend/ticket-process.php';

$ref = trim($_GET['ref'] ?? '');
if (empty($ref)) {
    set_flash('danger', 'Booking reference not provided.');
    redirect(base_url('my-bookings.php'));
}

$booking = get_booking_details_by_ref($ref);
if (!$booking) {
    set_flash('danger', 'Booking not found.');
    redirect(base_url('my-bookings.php'));
}

$pageTitle = 'Booking Confirmed - ' . $booking['booking_reference'];

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="p-4 p-md-5 rounded-3 text-center" style="background: var(--bg-card); border: 1px solid var(--border); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);">
                <!-- Success Icon -->
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: rgba(34, 197, 94, 0.12); border: 2px solid #22c55e; color: #22c55e; font-size: 2.2rem;">
                        <i class="bi bi-check-lg"></i>
                    </div>
                </div>

                <span class="badge bg-dark border border-success text-success px-3 py-1 fs-xs text-uppercase letter-spacing mb-2">
                    Payment Successful
                </span>
                <h1 class="text-white fw-bold mb-2">Booking Confirmed!</h1>
                <p class="text-secondary small mb-4">Your digital ticket has been generated and confirmed in the cinema system.</p>

                <!-- Booking Reference Callout Box -->
                <div class="p-3 rounded-2 mb-4 d-inline-block mx-auto" style="background: var(--bg-secondary); border: 1px dashed var(--accent);">
                    <span class="text-secondary fs-xs text-uppercase d-block">Booking Reference</span>
                    <span class="text-white font-monospace fs-4 fw-bold letter-spacing-1"><?= htmlspecialchars($booking['booking_reference']) ?></span>
                </div>

                <!-- Ticket Mini Card -->
                <div class="p-4 rounded-2 text-start mb-4" style="background: var(--bg-secondary); border: 1px solid var(--border);">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3 text-center text-md-start">
                            <img src="<?= movie_poster_url($booking['poster_url']) ?>" alt="" style="width: 100px; height: 145px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border);" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                        </div>
                        <div class="col-md-9">
                            <h4 class="text-white fw-bold mb-1"><?= htmlspecialchars($booking['movie_title']) ?></h4>
                            <div class="text-secondary small mb-3">
                                <span class="badge bg-dark border border-secondary text-secondary me-1"><?= htmlspecialchars($booking['certificate'] ?: 'UA') ?></span>
                                <span class="badge bg-dark border border-secondary text-light me-1"><?= htmlspecialchars($booking['screen_type']) ?></span>
                                <span><?= format_duration((int)$booking['duration_mins']) ?></span>
                            </div>

                            <div class="row g-2 small">
                                <div class="col-sm-6 text-secondary">
                                    <i class="bi bi-building text-danger me-1"></i><strong>Cinema:</strong><br>
                                    <span class="text-white"><?= htmlspecialchars($booking['theatre_name']) ?></span>
                                </div>
                                <div class="col-sm-6 text-secondary">
                                    <i class="bi bi-calendar3 text-danger me-1"></i><strong>Show Date & Time:</strong><br>
                                    <span class="text-white"><?= format_date($booking['show_date'], 'D, d M Y') ?> at <?= format_time($booking['show_time']) ?></span>
                                </div>
                                <div class="col-sm-6 text-secondary mt-2">
                                    <i class="bi bi-ticket-perforated text-danger me-1"></i><strong>Seats (<?= (int)$booking['total_seats'] ?>):</strong><br>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <?php foreach ($booking['seats'] as $st): ?>
                                            <span class="badge bg-dark border border-danger text-danger"><?= htmlspecialchars($st['seat_code']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-sm-6 text-secondary mt-2">
                                    <i class="bi bi-credit-card text-danger me-1"></i><strong>Total Paid:</strong><br>
                                    <span class="text-danger fw-bold fs-6"><?= format_currency($booking['grand_total']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?= base_url('ticket.php?ref=' . urlencode($booking['booking_reference'])) ?>" class="btn btn-accent px-4 py-2">
                        <i class="bi bi-qr-code me-2"></i>View & Print Digital Ticket
                    </a>
                    <a href="<?= base_url('my-bookings.php') ?>" class="btn btn-outline-dark-custom px-4 py-2">
                        <i class="bi bi-clock-history me-2"></i>My Bookings
                    </a>
                    <a href="<?= base_url('index.php') ?>" class="btn btn-outline-dark-custom px-4 py-2">
                        <i class="bi bi-house me-2"></i>Return Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

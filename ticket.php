<?php
/**
 * CINEMA BOOKING - Digital Movie Ticket (Boarding Pass Style)
 */

require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/backend/ticket-process.php';

$ref = trim($_GET['ref'] ?? '');
if (empty($ref)) {
    set_flash('danger', 'Booking reference is required.');
    redirect(base_url('my-bookings.php'));
}

$booking = get_booking_details_by_ref($ref);
if (!$booking) {
    set_flash('danger', 'Ticket not found.');
    redirect(base_url('my-bookings.php'));
}

// Ensure security: user must own booking or be admin
if ($booking['user_id'] != $_SESSION['user_id'] && !is_admin_logged_in()) {
    set_flash('danger', 'Unauthorized ticket access.');
    redirect(base_url('my-bookings.php'));
}

$pageTitle = 'Digital Ticket - ' . $booking['booking_reference'];

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <!-- Top toolbar / actions (hidden in print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 640px; margin-left: auto; margin-right: auto;">
        <a href="<?= base_url('my-bookings.php') ?>" class="btn btn-outline-dark-custom btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to My Bookings
        </a>

        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-accent btn-sm">
                <i class="bi bi-printer-fill me-2"></i>Print Ticket
            </button>
        </div>
    </div>

    <!-- Digital Ticket Container -->
    <div class="digital-ticket-container">
        <div class="cinema-ticket">
            <!-- Ticket Header -->
            <div class="ticket-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4 text-white"><i class="bi bi-film"></i></span>
                    <div>
                        <span class="fw-bold tracking-wider fs-5">CINEMA BOOKING</span>
                        <div class="fs-xs opacity-75">OFFICIAL ADMISSION PASS</div>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-black bg-opacity-40 border border-white border-opacity-25 px-3 py-1 fs-xs">
                        <?= htmlspecialchars($booking['screen_type']) ?>
                    </span>
                </div>
            </div>

            <!-- Ticket Body -->
            <div class="ticket-body">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-sm-3 col-4 text-center">
                        <img src="<?= movie_poster_url($booking['poster_url']) ?>" alt="<?= htmlspecialchars($booking['movie_title']) ?>" class="img-fluid rounded-2 shadow-sm" style="max-height: 140px; aspect-ratio: 2 / 3; object-fit: cover; border: 1px solid var(--border);" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                    </div>
                    <div class="col-sm-9 col-8">
                        <span class="badge bg-danger fs-xs mb-1"><?= htmlspecialchars($booking['certificate'] ?: 'UA') ?></span>
                        <h3 class="text-white fw-bold mb-1" style="font-size: 1.35rem;"><?= htmlspecialchars($booking['movie_title']) ?></h3>
                        <p class="text-secondary small mb-2"><?= htmlspecialchars($booking['language']) ?> &bull; <?= format_duration((int)$booking['duration_mins']) ?></p>
                        <div class="text-white small">
                            <i class="bi bi-building text-danger me-1"></i><?= htmlspecialchars($booking['theatre_name']) ?>
                        </div>
                        <div class="text-secondary fs-xs">
                            <?= htmlspecialchars($booking['theatre_address']) ?>
                        </div>
                    </div>
                </div>

                <!-- Perforated Tear Line -->
                <div class="ticket-tear-line">
                    <div class="tear-dots"></div>
                </div>

                <!-- Show & Seating Grid -->
                <div class="row g-3 py-3">
                    <div class="col-6 col-md-3">
                        <span class="text-secondary fs-xs text-uppercase d-block">Show Date</span>
                        <strong class="text-white fs-6"><?= format_date($booking['show_date'], 'd M Y') ?></strong>
                        <div class="text-secondary fs-xs"><?= format_date($booking['show_date'], 'l') ?></div>
                    </div>

                    <div class="col-6 col-md-3">
                        <span class="text-secondary fs-xs text-uppercase d-block">Show Time</span>
                        <strong class="text-danger fs-6"><?= format_time($booking['show_time']) ?></strong>
                        <div class="text-secondary fs-xs">Gates open 15m prior</div>
                    </div>

                    <div class="col-6 col-md-3">
                        <span class="text-secondary fs-xs text-uppercase d-block">Auditorium</span>
                        <strong class="text-white fs-6"><?= htmlspecialchars($booking['screen_name']) ?></strong>
                    </div>

                    <div class="col-6 col-md-3">
                        <span class="text-secondary fs-xs text-uppercase d-block">Total Seats</span>
                        <strong class="text-white fs-6"><?= (int)$booking['total_seats'] ?> Seat(s)</strong>
                    </div>
                </div>

                <!-- Seats Tag List -->
                <div class="p-3 rounded-2 mb-4" style="background: var(--bg-secondary); border: 1px dashed var(--border);">
                    <span class="text-secondary fs-xs text-uppercase d-block mb-2">Reserved Seat Allocation</span>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($booking['seats'] as $st): ?>
                            <span class="badge bg-dark border border-danger text-danger px-3 py-2 fs-6">
                                Seat <?= htmlspecialchars($st['seat_code']) ?> <span class="fs-xs opacity-75">(<?= ucfirst($st['seat_type']) ?>)</span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Booking Reference & Guest Details -->
                <div class="row g-2 text-secondary fs-xs mb-3">
                    <div class="col-6">
                        <span>Guest Name:</span> <strong class="text-white"><?= htmlspecialchars($booking['user_name']) ?></strong>
                    </div>
                    <div class="col-6 text-end">
                        <span>Payment Status:</span> <strong class="text-success text-uppercase"><?= htmlspecialchars($booking['payment_status']) ?> (<?= format_currency($booking['grand_total']) ?>)</strong>
                    </div>
                </div>

                <!-- Barcode & QR Code Section -->
                <div class="ticket-barcode-wrap">
                    <div class="d-flex justify-content-center align-items-center gap-4">
                        <!-- Simulated Barcode Lines -->
                        <div style="height: 48px; width: 60%; background: repeating-linear-gradient(90deg, #111 0px, #111 3px, #fff 3px, #fff 6px, #111 6px, #111 10px, #fff 10px, #fff 12px); border-radius: 2px;"></div>
                        <!-- Simulated QR Code -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= urlencode($booking['booking_reference']) ?>" alt="QR" style="width: 52px; height: 52px; border-radius: 4px;">
                    </div>
                    <div class="ticket-barcode-code"><?= htmlspecialchars($booking['booking_reference']) ?></div>
                    <span class="text-muted fs-xs">Present this barcode at the multiplex admission turnstile</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

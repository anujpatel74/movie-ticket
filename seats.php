<?php
/**
 * CINEMA BOOKING - Interactive Seat Layout & Real-time Selection
 * Production seat booking grid
 */

require_once __DIR__ . '/includes/functions.php';

$showId = isset($_GET['show_id']) ? (int)$_GET['show_id'] : 0;
if ($showId <= 0) {
    set_flash('danger', 'Invalid showtime selection.');
    redirect(base_url('movies.php'));
}

$pdo = getDBConnection();

// Fetch show, movie, theatre, and screen details
$stmt = $pdo->prepare("
    SELECT s.*, 
           m.id AS movie_id, m.title AS movie_title, m.poster_url, m.certificate, m.duration_mins, m.language,
           t.name AS theatre_name, t.address AS theatre_address,
           sc.id AS screen_id, sc.screen_name, sc.screen_type, sc.total_seats
    FROM shows s
    JOIN movies m ON s.movie_id = m.id
    JOIN theatres t ON s.theatre_id = t.id
    JOIN screens sc ON s.screen_id = sc.id
    WHERE s.id = ? AND s.status = 'active'
    LIMIT 1
");
$stmt->execute([$showId]);
$show = $stmt->fetch();

if (!$show) {
    set_flash('danger', 'Show not found or no longer available.');
    redirect(base_url('movies.php'));
}

$pageTitle = 'Select Seats - ' . $show['movie_title'];

// Fetch all seats configured for this screen
$seatStmt = $pdo->prepare("
    SELECT * 
    FROM seats 
    WHERE screen_id = ? AND is_active = 1
    ORDER BY seat_row ASC, seat_number ASC
");
$seatStmt->execute([$show['screen_id']]);
$allSeats = $seatStmt->fetchAll();

// Group seats by row (e.g. Row A, B, C, D, E, F)
$groupedSeats = [];
foreach ($allSeats as $st) {
    $row = $st['seat_row'];
    if (!isset($groupedSeats[$row])) {
        $groupedSeats[$row] = [
            'type'  => $st['seat_type'],
            'seats' => []
        ];
    }
    $groupedSeats[$row]['seats'][] = $st;
}

// Fetch seats already booked for this show from booking_seats
$bookedStmt = $pdo->prepare("
    SELECT bs.seat_id 
    FROM booking_seats bs
    JOIN bookings b ON bs.booking_id = b.id
    WHERE bs.show_id = ? AND b.booking_status = 'confirmed'
");
$bookedStmt->execute([$showId]);
$bookedSeatIds = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

// Extra JS for seat handling
$extraJs = '<script src="' . asset_url('js/seats.js') . '"></script>';

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <!-- Show Info Breadcrumb Bar -->
    <div class="p-3 mb-4 rounded-2 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="background: var(--bg-card); border: 1px solid var(--border);">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= base_url('movie-details.php?id=' . $show['movie_id']) ?>" class="btn btn-sm btn-outline-dark-custom rounded-circle" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="text-white fw-bold m-0" style="font-size: 1.2rem;"><?= htmlspecialchars($show['movie_title']) ?></h4>
                <div class="text-secondary small">
                    <span class="text-white"><?= htmlspecialchars($show['theatre_name']) ?></span> &bull; 
                    <span class="text-light"><?= htmlspecialchars($show['screen_name']) ?></span> &bull; 
                    <span><?= format_date($show['show_date'], 'D, d M') ?> at <?= format_time($show['show_time']) ?></span>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <span class="badge bg-dark border border-danger text-danger px-3 py-2">
                <?= htmlspecialchars($show['screen_type']) ?>
            </span>
            <span class="badge bg-dark border border-secondary text-light px-3 py-2">
                <?= htmlspecialchars($show['certificate'] ?: 'UA') ?>
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Seat Map Column -->
        <div class="col-lg-8">
            <div class="seat-booking-container" 
                 id="seatGrid"
                 data-show-id="<?= $show['id'] ?>"
                 data-regular-price="<?= $show['regular_price'] ?>"
                 data-premium-price="<?= $show['premium_price'] ?>"
                 data-recliner-price="<?= $show['recliner_price'] ?>"
                 data-fee="35.00">

                <!-- Minimal Cinema Screen -->
                <div class="cinema-screen-wrap">
                    <div class="cinema-screen-curve"></div>
                    <div class="cinema-screen-label"><i class="bi bi-tv me-1"></i>SCREEN THIS WAY</div>
                </div>

                <!-- Tiers & Rows -->
                <div class="seat-layout-matrix">
                    <?php
                    $lastType = null;
                    foreach ($groupedSeats as $rowLabel => $rowGroup):
                        $currentType = $rowGroup['type'];
                        if ($currentType !== $lastType):
                            $tierNames = [
                                'recliner' => 'Luxury Recliners',
                                'premium'  => 'Prime Premium',
                                'regular'  => 'Standard Regular'
                            ];
                            $tierPrices = [
                                'recliner' => $show['recliner_price'],
                                'premium'  => $show['premium_price'],
                                'regular'  => $show['regular_price']
                            ];
                    ?>
                            <div class="seat-tier-header <?= $lastType !== null ? 'mt-4' : '' ?>">
                                <span class="seat-tier-title">
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    <?= $tierNames[$currentType] ?? ucfirst($currentType) ?>
                                </span>
                                <span class="seat-tier-price"><?= format_currency($tierPrices[$currentType] ?? 0) ?></span>
                            </div>
                    <?php
                            $lastType = $currentType;
                        endif;
                    ?>

                        <div class="seat-row" data-row="<?= $rowLabel ?>">
                            <span class="row-label"><?= $rowLabel ?></span>
                            <?php foreach ($rowGroup['seats'] as $seat): ?>
                                <?php 
                                    $isBooked = in_array((int)$seat['id'], $bookedSeatIds);
                                ?>
                                <div class="seat <?= $seat['seat_type'] ?> <?= $isBooked ? 'booked' : '' ?>"
                                     data-seat-id="<?= $seat['id'] ?>"
                                     data-seat-code="<?= htmlspecialchars($seat['seat_code']) ?>"
                                     data-seat-type="<?= htmlspecialchars($seat['seat_type']) ?>"
                                     title="<?= htmlspecialchars($seat['seat_code']) ?> (<?= ucfirst($seat['seat_type']) ?> - ₹<?= number_format($tierPrices[$seat['seat_type']] ?? 0) ?>)">
                                    <?= htmlspecialchars($seat['seat_number']) ?>
                                </div>
                            <?php endforeach; ?>
                            <span class="row-label"><?= $rowLabel ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Seat Status Legend -->
                <div class="seat-legend">
                    <div class="legend-item">
                        <div class="legend-box legend-available"></div>
                        <span>Available</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box legend-selected"></div>
                        <span>Selected</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box legend-booked"></div>
                        <span>Booked</span>
                    </div>
                    <div class="legend-item">
                        <span class="badge bg-dark border border-warning text-warning fs-xs">Gold Line</span>
                        <span>Recliner Class</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Summary & Submission Column -->
        <div class="col-lg-4">
            <div class="booking-summary-card position-sticky" style="top: 84px;">
                <h5 class="text-white fw-bold mb-3 pb-2 border-bottom border-secondary border-opacity-25" style="font-size: 1.1rem;">
                    Booking Summary
                </h5>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="<?= movie_poster_url($show['poster_url']) ?>" alt="" style="width: 52px; height: 74px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border);" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                    <div>
                        <h6 class="text-white fw-bold mb-1" style="font-size: 0.95rem;"><?= htmlspecialchars($show['movie_title']) ?></h6>
                        <small class="text-secondary d-block"><?= htmlspecialchars($show['theatre_name']) ?></small>
                        <small class="text-light"><?= format_date($show['show_date'], 'd M') ?>, <?= format_time($show['show_time']) ?></small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small text-secondary mb-1">
                        <span>Selected Seats (<span id="selectedSeatCount" class="text-white fw-bold">0</span>):</span>
                    </div>
                    <div id="selectedSeatList" class="p-2 rounded bg-black bg-opacity-50 border border-secondary border-opacity-25" style="min-height: 48px;">
                        <span class="text-secondary small fst-italic">No seats selected yet</span>
                    </div>
                </div>

                <div class="price-breakdown small pt-2 border-top border-secondary border-opacity-25 mb-4">
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>Ticket(s) Subtotal</span>
                        <span id="summarySubtotal" class="text-white">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>Convenience Fee</span>
                        <span id="summaryFee" class="text-white">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>GST / Taxes (18%)</span>
                        <span id="summaryTax" class="text-white">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-white fw-bold fs-6 pt-2 border-top border-secondary border-opacity-25 mt-2">
                        <span>Grand Total</span>
                        <span id="summaryGrandTotal" class="text-danger">₹0.00</span>
                    </div>
                </div>

                <!-- Form to Submit to backend/booking-process.php -->
                <form action="<?= base_url('backend/booking-process.php') ?>" method="POST" id="seatBookingForm">
                    <?= csrf_input() ?>
                    <input type="hidden" name="show_id" value="<?= $show['id'] ?>">
                    <input type="hidden" name="selected_seats" id="hiddenSelectedSeats" value="[]">

                    <?php if (is_logged_in()): ?>
                        <button type="submit" class="btn btn-accent w-100 py-2" id="btnProceedToPayment" disabled>
                            Select Seats to Proceed
                        </button>
                    <?php else: ?>
                        <div class="alert alert-dark border-secondary text-warning small mb-3">
                            <i class="bi bi-info-circle me-1"></i> You will be asked to sign in before final checkout.
                        </div>
                        <button type="submit" class="btn btn-accent w-100 py-2" id="btnProceedToPayment" disabled>
                            Sign In & Proceed
                        </button>
                    <?php endif; ?>
                </form>

                <div class="text-center mt-3">
                    <span class="text-secondary fs-xs">
                        <i class="bi bi-shield-check text-success me-1"></i>Seats are locked securely upon confirmation
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

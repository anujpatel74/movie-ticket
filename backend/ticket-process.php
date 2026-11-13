<?php
/**
 * CINEMA BOOKING - Ticket Processing & Retrieval Backend
 */

require_once __DIR__ . '/../includes/functions.php';

/**
 * Fetch complete details for a booking reference
 */
function get_booking_details_by_ref(string $reference): ?array {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        SELECT b.*,
               u.full_name AS user_name, u.email AS user_email, u.phone AS user_phone,
               s.show_date, s.show_time,
               m.id AS movie_id, m.title AS movie_title, m.poster_url, m.certificate, m.duration_mins, m.language,
               t.name AS theatre_name, t.address AS theatre_address,
               sc.screen_name, sc.screen_type,
               p.transaction_ref, p.payment_method, p.card_network, p.masked_account, p.created_at AS payment_date
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN shows s ON b.show_id = s.id
        JOIN movies m ON s.movie_id = m.id
        JOIN theatres t ON s.theatre_id = t.id
        JOIN screens sc ON s.screen_id = sc.id
        LEFT JOIN payments p ON p.booking_id = b.id
        WHERE b.booking_reference = ?
        LIMIT 1
    ");
    $stmt->execute([$reference]);
    $booking = $stmt->fetch();

    if (!$booking) {
        return null;
    }

    // Fetch seats associated with this booking
    $seatStmt = $pdo->prepare("
        SELECT bs.* 
        FROM booking_seats bs
        WHERE bs.booking_id = ?
        ORDER BY bs.seat_code ASC
    ");
    $seatStmt->execute([$booking['id']]);
    $booking['seats'] = $seatStmt->fetchAll();

    return $booking;
}

// Handle Cancel Booking action if triggered via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_booking') {
    if (!is_logged_in()) {
        redirect(base_url('login.php'));
    }

    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        set_flash('danger', 'Security token invalid.');
        redirect(base_url('my-bookings.php'));
    }

    $ref = trim($_POST['booking_reference'] ?? '');
    $booking = get_booking_details_by_ref($ref);

    if ($booking && ($booking['user_id'] == $_SESSION['user_id'] || is_admin_logged_in())) {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        try {
            // Update booking status to cancelled
            $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
            $stmt->execute([$booking['id']]);

            // Remove seats from booking_seats so they become available for others
            $stmt = $pdo->prepare("DELETE FROM booking_seats WHERE booking_id = ?");
            $stmt->execute([$booking['id']]);

            // Update payment to refunded
            $stmt = $pdo->prepare("UPDATE payments SET payment_status = 'refunded' WHERE booking_id = ?");
            $stmt->execute([$booking['id']]);

            $pdo->commit();
            set_flash('success', "Booking #{$ref} was cancelled successfully. Demo refund initiated.");
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Booking cancellation error: " . $e->getMessage());
            set_flash('danger', 'Unable to cancel booking at this moment due to a technical error. Please try again.');
        }
    } else {
        set_flash('danger', 'Unauthorized or booking not found.');
    }

    redirect(base_url('my-bookings.php'));
}


<?php
/**
 * CINEMA BOOKING - Demo Payment & Booking Confirmation Backend
 * Handles Transactional Concurrency Protection & Safe Mock Payment
 */

require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    set_flash('danger', 'Please log in to complete your purchase.');
    redirect(base_url('login.php'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('index.php'));
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
    set_flash('danger', 'Payment session timed out. Please try again.');
    redirect(base_url('payment.php'));
}

$checkout = $_SESSION['checkout_data'] ?? null;
if (!$checkout || empty($checkout['seats']) || empty($checkout['show_id'])) {
    set_flash('warning', 'No active booking session found. Please select your show and seats.');
    redirect(base_url('movies.php'));
}

$paymentMethod = $_POST['payment_method'] ?? 'card';
$cardNetwork = 'Visa';
$maskedAccount = '•••• •••• •••• 4242';

if ($paymentMethod === 'upi') {
    $upiId = trim($_POST['upi_id'] ?? 'user@upi');
    $cardNetwork = 'UPI Payment';
    $maskedAccount = htmlspecialchars($upiId);
} elseif ($paymentMethod === 'netbanking') {
    $bankName = trim($_POST['bank_name'] ?? 'State Bank of India');
    $cardNetwork = htmlspecialchars($bankName);
    $maskedAccount = 'NetBanking Account';
} else {
    // Card demo: Extract safe last 4 digits
    $rawCard = preg_replace('/\D/', '', $_POST['card_number'] ?? '4242');
    $last4 = substr($rawCard, -4) ?: '4242';
    $cardNetwork = 'Card (Demo)';
    $maskedAccount = '•••• •••• •••• ' . $last4;
}

$pdo = getDBConnection();

try {
    // BEGIN TRANSACTION FOR SAFE ATOMIC BOOKING
    $pdo->beginTransaction();

    $showId = (int)$checkout['show_id'];
    $seatIds = array_column($checkout['seats'], 'id');
    $inPlaceholders = implode(',', array_fill(0, count($seatIds), '?'));

    // 1. Pessimistic / strict concurrency check: Are any of these seats already booked?
    // Using FOR UPDATE on show or checking unique constraint
    $checkSql = "
        SELECT bs.seat_code 
        FROM booking_seats bs
        JOIN bookings b ON bs.booking_id = b.id
        WHERE bs.show_id = ? AND bs.seat_id IN ($inPlaceholders) AND b.booking_status = 'confirmed'
        FOR UPDATE
    ";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute(array_merge([$showId], $seatIds));
    $conflictingSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($conflictingSeats)) {
        // Concurrency conflict caught! Rollback immediately.
        $pdo->rollBack();
        set_flash('danger', 'Sorry, seat(s) ' . implode(', ', $conflictingSeats) . ' were just purchased by another guest while you were paying. Your demo account was NOT charged. Please choose different seats.');
        unset($_SESSION['checkout_data']);
        redirect(base_url("seats.php?show_id={$showId}"));
    }

    // 2. Generate unique booking and transaction references
    $bookingReference = generate_booking_reference();
    $transactionRef = generate_transaction_reference();

    // 3. Insert into bookings table
    $stmt = $pdo->prepare("
        INSERT INTO bookings (
            booking_reference, user_id, show_id, total_seats,
            subtotal, convenience_fee, tax_amount, grand_total,
            booking_status, payment_status, created_at
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            'confirmed', 'paid', NOW()
        )
    ");
    $stmt->execute([
        $bookingReference,
        $_SESSION['user_id'],
        $showId,
        $checkout['total_seats'],
        $checkout['subtotal'],
        $checkout['convenience_fee'],
        $checkout['tax_amount'],
        $checkout['grand_total']
    ]);

    $bookingId = (int)$pdo->lastInsertId();

    // 4. Insert into booking_seats table
    $seatInsertStmt = $pdo->prepare("
        INSERT INTO booking_seats (
            booking_id, show_id, seat_id, seat_code, seat_type, price
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($checkout['seats'] as $seat) {
        $seatInsertStmt->execute([
            $bookingId,
            $showId,
            $seat['id'],
            $seat['seat_code'],
            $seat['seat_type'],
            $seat['price']
        ]);
    }

    // 5. Insert safe payment log into payments table
    $stmt = $pdo->prepare("
        INSERT INTO payments (
            booking_id, transaction_ref, payment_method, amount,
            payment_status, card_network, masked_account, payment_response, created_at
        ) VALUES (
            ?, ?, ?, ?,
            'success', ?, ?, 'Demo payment simulated successfully', NOW()
        )
    ");
    $stmt->execute([
        $bookingId,
        $transactionRef,
        $paymentMethod,
        $checkout['grand_total'],
        $cardNetwork,
        $maskedAccount
    ]);

    // COMMIT ALL OPERATIONS
    $pdo->commit();

    // Clear checkout session data
    unset($_SESSION['checkout_data']);

    // Set success flash
    set_flash('success', 'Ticket booking confirmed successfully! Reference: ' . $bookingReference);

    // Redirect to confirmation page
    redirect(base_url('confirmation.php?ref=' . urlencode($bookingReference)));

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Check if unique constraint uq_show_seat_booked was triggered
    if ($e->getCode() == 23000) {
        set_flash('danger', 'One or more of the selected seats were booked simultaneously by another user. Please choose another seat.');
        redirect(base_url("seats.php?show_id={$checkout['show_id']}"));
    }

    error_log("Payment processing database exception: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
    set_flash('danger', 'Payment processing encountered an unexpected system error. Please retry your purchase.');
    redirect(base_url('payment.php'));
}


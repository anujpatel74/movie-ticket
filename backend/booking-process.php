<?php
/**
 * CINEMA BOOKING - Booking Process Handler
 * Validates selected seats on server-side and prepares checkout
 */

require_once __DIR__ . '/../includes/functions.php';

if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? base_url('movies.php');
    set_flash('warning', 'Please sign in to proceed with your ticket booking.');
    redirect(base_url('login.php'));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('movies.php'));
}

$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf($token)) {
    set_flash('danger', 'Security validation failed. Please refresh the page and select your seats again.');
    redirect(base_url('movies.php'));
}

$showId = isset($_POST['show_id']) ? (int)$_POST['show_id'] : 0;
$rawSeats = $_POST['selected_seats'] ?? '[]';

if ($showId <= 0) {
    set_flash('danger', 'Invalid showtime selected.');
    redirect(base_url('movies.php'));
}

$seats = json_decode($rawSeats, true);
if (!is_array($seats) || count($seats) === 0) {
    set_flash('warning', 'Please select at least one seat to proceed.');
    redirect(base_url("seats.php?show_id={$showId}"));
}

if (count($seats) > 8) {
    set_flash('warning', 'You can select a maximum of 8 seats per booking.');
    redirect(base_url("seats.php?show_id={$showId}"));
}

$pdo = getDBConnection();

// 1. Fetch Show and Movie details
$stmt = $pdo->prepare("
    SELECT s.*, 
           m.title AS movie_title, m.poster_url, m.certificate, m.duration_mins,
           t.name AS theatre_name, t.address AS theatre_address,
           sc.screen_name, sc.screen_type
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
    set_flash('danger', 'The selected show is no longer active or could not be found.');
    redirect(base_url('movies.php'));
}

// 2. Validate seat availability from database
$seatIds = array_map(fn($s) => (int)$s['id'], $seats);
$inPlaceholders = implode(',', array_fill(0, count($seatIds), '?'));

// Check if any seat is already booked
$stmt = $pdo->prepare("
    SELECT bs.seat_code 
    FROM booking_seats bs
    JOIN bookings b ON bs.booking_id = b.id
    WHERE bs.show_id = ? AND bs.seat_id IN ($inPlaceholders) AND b.booking_status = 'confirmed'
");
$stmt->execute(array_merge([$showId], $seatIds));
$alreadyBooked = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($alreadyBooked)) {
    set_flash('danger', 'The following seat(s) were just booked by someone else: ' . implode(', ', $alreadyBooked) . '. Please select other seats.');
    redirect(base_url("seats.php?show_id={$showId}"));
}

// 3. Re-verify seats exist in screen and calculate authoritative server-side prices
$stmt = $pdo->prepare("
    SELECT id, seat_code, seat_type 
    FROM seats 
    WHERE screen_id = ? AND id IN ($inPlaceholders)
");
$stmt->execute(array_merge([$show['screen_id']], $seatIds));
$validSeats = $stmt->fetchAll();

if (count($validSeats) !== count($seatIds)) {
    set_flash('danger', 'One or more selected seats are invalid for this screen layout.');
    redirect(base_url("seats.php?show_id={$showId}"));
}

$subtotal = 0.00;
$verifiedSeats = [];

foreach ($validSeats as $st) {
    $price = (float)$show['regular_price'];
    if ($st['seat_type'] === 'premium') $price = (float)$show['premium_price'];
    if ($st['seat_type'] === 'recliner') $price = (float)$show['recliner_price'];

    $subtotal += $price;
    $verifiedSeats[] = [
        'id'        => (int)$st['id'],
        'seat_code' => $st['seat_code'],
        'seat_type' => $st['seat_type'],
        'price'     => $price
    ];
}

$convenienceFee = 35.00;
$taxAmount = round($subtotal * 0.18, 2); // 18% GST standard
$grandTotal = $subtotal + $convenienceFee + $taxAmount;

// Store in checkout session
$_SESSION['checkout_data'] = [
    'user_id'          => $_SESSION['user_id'],
    'show_id'          => $showId,
    'movie_title'      => $show['movie_title'],
    'poster_url'       => $show['poster_url'],
    'certificate'      => $show['certificate'],
    'duration_mins'    => $show['duration_mins'],
    'theatre_name'     => $show['theatre_name'],
    'theatre_address'  => $show['theatre_address'],
    'screen_name'      => $show['screen_name'],
    'screen_type'      => $show['screen_type'],
    'show_date'        => $show['show_date'],
    'show_time'        => $show['show_time'],
    'seats'            => $verifiedSeats,
    'total_seats'      => count($verifiedSeats),
    'subtotal'         => $subtotal,
    'convenience_fee'  => $convenienceFee,
    'tax_amount'       => $taxAmount,
    'grand_total'      => $grandTotal,
    'created_timestamp'=> time()
];

redirect(base_url('payment.php'));


<?php
/**
 * CINEMA BOOKING - Real-time Seat Status Processing Endpoint
 * Verifies booked seats directly against MySQL to ensure integrity
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$showId = isset($_GET['show_id']) ? (int)$_GET['show_id'] : 0;

if ($showId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid Show ID'], 400);
}

try {
    $pdo = getDBConnection();

    if ($action === 'get_booked_seats') {
        // Fetch all seat IDs that are booked for this specific show
        $stmt = $pdo->prepare("
            SELECT bs.seat_id, bs.seat_code 
            FROM booking_seats bs
            JOIN bookings b ON bs.booking_id = b.id
            WHERE bs.show_id = ? AND b.booking_status = 'confirmed'
        ");
        $stmt->execute([$showId]);
        $bookedRows = $stmt->fetchAll();

        $seatIds = array_map(fn($row) => (int)$row['seat_id'], $bookedRows);
        $seatCodes = array_map(fn($row) => $row['seat_code'], $bookedRows);

        json_response([
            'success' => true,
            'show_id' => $showId,
            'booked_seat_ids' => $seatIds,
            'booked_seat_codes' => $seatCodes,
            'total_booked' => count($seatIds)
        ]);
    } else {
        json_response(['success' => false, 'message' => 'Unknown action'], 400);
    }
} catch (Exception $e) {
    error_log("Seat retrieval exception: " . $e->getMessage());
    json_response([
        'success' => false, 
        'message' => 'Unable to retrieve seat availability at this moment.'
    ], 500);
}


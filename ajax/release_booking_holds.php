<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid method.']);
    exit;
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in.']);
    exit;
}

$conn = db_connect();
cleanup_expired_show_seat_holds($conn);

$showId = (int) ($_POST['show_id'] ?? 0);
$holdToken = get_booking_hold_token();

if ($showId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid showtime.']);
    exit;
}

release_holds_by_token($conn, $showId, $holdToken);

echo json_encode([
    'success' => true,
    'held_seat_ids' => [],
    'hold_until' => null,
]);

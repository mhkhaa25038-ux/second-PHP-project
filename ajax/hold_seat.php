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
    echo json_encode(['success' => false, 'message' => 'Please sign in to select seats.']);
    exit;
}

$conn = db_connect();
cleanup_expired_show_seat_holds($conn);

$showId = (int) ($_POST['show_id'] ?? 0);
$showSeatId = (int) ($_POST['show_seat_id'] ?? 0);
$action = trim((string) ($_POST['action'] ?? 'select'));
$holdToken = get_booking_hold_token();

if ($showId <= 0 || $showSeatId <= 0 || !in_array($action, ['select', 'deselect'], true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid seat data.']);
    exit;
}

try {
    $conn->begin_transaction();

    $sql = "
        SELECT ss.show_seat_id, ss.show_id, ss.status, ss.hold_token, ss.hold_until,
               se.seat_row, se.seat_number
        FROM show_seats ss
        INNER JOIN seats se ON ss.seat_id = se.seat_id
        WHERE ss.show_id = ? AND ss.show_seat_id = ?
        LIMIT 1
        FOR UPDATE
    ";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Unable to check seat status.');
    }

    $stmt->bind_param('ii', $showId, $showSeatId);
    $stmt->execute();
    $seat = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$seat) {
        throw new Exception('Seats could not be found for this showtime.');
    }

    $seatLabel = ($seat['seat_row'] ?? '') . ($seat['seat_number'] ?? '');
    $status = $seat['status'] ?? 'available';
    $currentToken = (string) ($seat['hold_token'] ?? '');

    if ($action === 'select') {
        if ($status === 'booked') {
            throw new Exception('Seat ' . $seatLabel . ' has already been booked.');
        }

        if ($status === 'held' && $currentToken !== '' && $currentToken !== $holdToken) {
            throw new Exception('Seat ' . $seatLabel . ' is currently held by another user.');
        }

        $updateSql = "
            UPDATE show_seats
            SET status = 'held', hold_token = ?, hold_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
            WHERE show_seat_id = ?
        ";
        $updateStmt = $conn->prepare($updateSql);

        if (!$updateStmt) {
            throw new Exception('Unable to hold seats.');
        }

        $updateStmt->bind_param('si', $holdToken, $showSeatId);
        if (!$updateStmt->execute()) {
            $updateStmt->close();
            throw new Exception('Unable to hold seats.');
        }
        $updateStmt->close();

        $conn->commit();

        echo json_encode([
            'success' => true,
            'action' => 'selected',
            'message' => 'Seat held ' . $seatLabel . ' for 5 minutes.',
            'hold_until' => get_hold_expiry_for_token($conn, $showId, $holdToken),
            'held_seat_ids' => get_held_show_seat_ids_by_token($conn, $showId, $holdToken),
        ]);
        exit;
    }

    if ($status === 'held' && $currentToken === $holdToken) {
        $releaseSql = "
            UPDATE show_seats
            SET status = 'available', hold_token = NULL, hold_until = NULL
            WHERE show_seat_id = ?
        ";
        $releaseStmt = $conn->prepare($releaseSql);

        if (!$releaseStmt) {
            throw new Exception('Unable to release held seats.');
        }

        $releaseStmt->bind_param('i', $showSeatId);
        if (!$releaseStmt->execute()) {
            $releaseStmt->close();
            throw new Exception('Unable to release held seats.');
        }
        $releaseStmt->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'action' => 'deselected',
        'message' => 'Seat released ' . $seatLabel . '.',
        'hold_until' => get_hold_expiry_for_token($conn, $showId, $holdToken),
        'held_seat_ids' => get_held_show_seat_ids_by_token($conn, $showId, $holdToken),
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

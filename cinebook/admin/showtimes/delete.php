<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$id = (int) get('id');

if ($id <= 0) {
    set_flash_message('error', 'Invalid showtime ID.');
    redirect('admin/showtimes/index.php');
}

$bookingCount = count_showtime_bookings($conn, $id);

if ($bookingCount > 0) {
    set_flash_message('error', 'This showtime already has bookings. Do not delete it; change the status to cancelled instead.');
    redirect('admin/showtimes/index.php');
}

$stmt = $conn->prepare('DELETE FROM showtimes WHERE show_id = ?');

if (!$stmt) {
    set_flash_message('error', 'Unable to prepare the delete statement.');
    redirect('admin/showtimes/index.php');
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    set_flash_message('success', 'Showtime deleted successfully.');
} else {
    set_flash_message('error', 'Unable to delete showtime: ' . $stmt->error);
}

$stmt->close();
redirect('admin/showtimes/index.php');
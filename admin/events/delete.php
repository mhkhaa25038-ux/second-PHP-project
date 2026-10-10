<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$id = (int) get('id');

if ($id <= 0) {
    set_flash_message('error', 'Invalid event ID.');
    redirect('admin/events/index.php');
}

$stmt = $conn->prepare('DELETE FROM events WHERE event_id = ?');

if (!$stmt) {
    set_flash_message('error', 'Unable to prepare the delete statement.');
    redirect('admin/events/index.php');
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    set_flash_message('success', 'Event deleted successfully.');
} else {
    set_flash_message('error', 'Unable to delete event: ' . $stmt->error);
}

$stmt->close();
redirect('admin/events/index.php');
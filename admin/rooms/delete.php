<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$id = (int) get('id');

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    set_flash_message('success', 'Room deleted successfully.');
}

redirect('admin/rooms/index.php');
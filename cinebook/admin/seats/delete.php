<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$id = (int) get('id');

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM seats WHERE seat_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    set_flash_message('success', 'Seat deleted successfully.');
}

redirect('admin/seats/index.php');
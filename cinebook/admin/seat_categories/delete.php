<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$id = (int) get('id');

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM seat_categories WHERE category_id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        set_flash_message('success', 'Seat type deleted successfully.');
    } else {
        set_flash_message('error', 'Unable to delete the seat type. It may still be used by seats.');
    }

    $stmt->close();
}

redirect('admin/seat_categories/index.php');
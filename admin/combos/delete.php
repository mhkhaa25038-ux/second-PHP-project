<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$id = (int) get('id');

if ($id <= 0) {
    set_flash_message('error', 'Invalid combo ID.');
    redirect('admin/combos/index.php');
}

$stmt = $conn->prepare('DELETE FROM combo_products WHERE combo_id = ?');

if (!$stmt) {
    set_flash_message('error', 'Unable to prepare the delete statement.');
    redirect('admin/combos/index.php');
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    set_flash_message('success', 'Combo deleted successfully.');
} else {
    set_flash_message('error', 'Unable to delete the combo. It may already be attached to bookings.');
}

$stmt->close();
redirect('admin/combos/index.php');
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$id = (int) get('id');

if ($id <= 0) {
    set_flash_message('error', 'Invalid genre ID.');
    redirect('admin/genres/index.php');
}

$stmt = $conn->prepare("DELETE FROM genres WHERE genre_id = ?");

if (!$stmt) {
    set_flash_message('error', 'Unable to prepare the delete statement.');
    redirect('admin/genres/index.php');
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    set_flash_message('success', 'Genre deleted successfully.');
} else {
    set_flash_message('error', 'Unable to delete genre: ' . $stmt->error);
}

$stmt->close();
redirect('admin/genres/index.php');
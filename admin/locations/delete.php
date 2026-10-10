<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$id = (int) get('id');

if ($id <= 0) {
    set_flash_message('error', 'Invalid cinema / branch ID.');
    redirect('admin/locations/index.php');
}

$checkStmt = $conn->prepare("SELECT COUNT(*) AS total FROM rooms WHERE location_id = ?");
$checkStmt->bind_param('i', $id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$checkRow = $checkResult->fetch_assoc();
$checkStmt->close();

if ((int) ($checkRow['total'] ?? 0) > 0) {
    set_flash_message('error', 'This cinema / branch currently has rooms. Do not delete it directly; change its status to inactive instead.');
    redirect('admin/locations/index.php');
}

$stmt = $conn->prepare("DELETE FROM locations WHERE location_id = ?");

if (!$stmt) {
    set_flash_message('error', 'Unable to prepare the delete statement.');
    redirect('admin/locations/index.php');
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    set_flash_message('success', 'Cinema / branch deleted successfully.');
} else {
    set_flash_message('error', 'Unable to delete cinema / branch: ' . $stmt->error);
}

$stmt->close();
redirect('admin/locations/index.php');
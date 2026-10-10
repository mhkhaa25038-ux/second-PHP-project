<?php
$pageTitle = 'Edit Event';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

$stmt = $conn->prepare('SELECT * FROM events WHERE event_id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    set_flash_message('error', 'Event not found.');
    redirect('admin/events/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(post('title'));
    $description = trim(post('description'));
    $startAt = trim(post('start_at'));
    $endAt = trim(post('end_at'));
    $image = trim(post('image'));
    $status = trim(post('status')) ?: 'active';

    if ($title === '' || $startAt === '' || $endAt === '') {
        $error = 'Please enter the title, start time, and end time.';
    } elseif (strtotime($endAt) <= strtotime($startAt)) {
        $error = 'End time must be later than start time.';
    } else {
        $description = $description !== '' ? $description : null;
        $image = $image !== '' ? $image : null;

        $sql = 'UPDATE events SET title = ?, description = ?, start_at = ?, end_at = ?, image = ?, status = ? WHERE event_id = ?';
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('ssssssi', $title, $description, $startAt, $endAt, $image, $status, $id);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Event updated successfully.');
                redirect('admin/events/index.php');
            } else {
                $error = 'Unable to update event: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}

$startValue = !empty($event['start_at']) ? date('Y-m-d\\TH:i', strtotime($event['start_at'])) : '';
$endValue = !empty($event['end_at']) ? date('Y-m-d\\TH:i', strtotime($event['end_at'])) : '';
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit Event</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/events/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group full">
                <label class="admin-label">Event Title</label>
                <input class="admin-input" type="text" name="title" value="<?= e(old('title', $event['title'])) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Start</label>
                <input class="admin-input" type="datetime-local" name="start_at" value="<?= e(old('start_at', $startValue)) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">End</label>
                <input class="admin-input" type="datetime-local" name="end_at" value="<?= e(old('end_at', $endValue)) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <select class="admin-select" name="status">
                    <?php $currentStatus = old('status', $event['status']); ?>
                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Event Image (URL)</label>
                <input class="admin-input" type="text" name="image" value="<?= e(old('image', $event['image'] ?? '')) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Description</label>
                <textarea class="admin-textarea" name="description"><?= e(old('description', $event['description'] ?? '')) ?></textarea>
            </div>
        </div>

        <button class="admin-btn" type="submit">Update Event</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
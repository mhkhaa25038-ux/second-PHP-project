<?php
$pageTitle = 'Create Event';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

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

        $sql = 'INSERT INTO events (title, description, start_at, end_at, image, status) VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('ssssss', $title, $description, $startAt, $endAt, $image, $status);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Event added successfully.');
                redirect('admin/events/index.php');
            } else {
                $error = 'Unable to add event: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Event</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/events/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group full">
                <label class="admin-label">Event Title</label>
                <input class="admin-input" type="text" name="title" value="<?= e(old('title')) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Start</label>
                <input class="admin-input" type="datetime-local" name="start_at" value="<?= e(old('start_at')) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">End</label>
                <input class="admin-input" type="datetime-local" name="end_at" value="<?= e(old('end_at')) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <select class="admin-select" name="status">
                    <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Event Image (URL)</label>
                <input class="admin-input" type="text" name="image" value="<?= e(old('image')) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Description</label>
                <textarea class="admin-textarea" name="description"><?= e(old('description')) ?></textarea>
            </div>
        </div>

        <button class="admin-btn" type="submit">Save Event</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
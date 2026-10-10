<?php
$pageTitle = 'Create Room';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

$locations = $conn->query("SELECT location_id, name FROM locations ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $locationId = (int) post('location_id');
    $roomName = trim(post('room_name'));
    $capacity = (int) post('capacity');
    $status = trim(post('status'));

    if ($locationId <= 0 || $roomName === '' || $capacity <= 0) {
        $error = 'Please enter all required data.';
    } else {
        $sql = "INSERT INTO rooms (location_id, room_name, capacity, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isis', $locationId, $roomName, $capacity, $status);

        if ($stmt->execute()) {
            $stmt->close();
            set_flash_message('success', 'Room added successfully.');
            redirect('admin/rooms/index.php');
        } else {
            $error = 'Unable to add the room. The room name may already exist in the same cinema.';
        }

        $stmt->close();
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Room</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/rooms/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Cinema / Branch</label>
                <select class="admin-select" name="location_id" required>
                    <option value="">-- Select Cinema --</option>
                    <?php if ($locations && $locations->num_rows > 0): ?>
                        <?php while ($location = $locations->fetch_assoc()): ?>
                            <option value="<?= (int) $location['location_id'] ?>"><?= e($location['name']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Room Name</label>
                <input class="admin-input" type="text" name="room_name" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Capacity</label>
                <input class="admin-input" type="number" name="capacity" min="1" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <select class="admin-select" name="status">
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                </select>
            </div>
        </div>

        <button class="admin-btn" type="submit">Save Room</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
<?php
$pageTitle = 'Manage Rooms';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim(get('keyword'));
$status = trim(get('status'));
$locationId = (int) get('location_id');

$locations = $conn->query("SELECT location_id, name FROM locations ORDER BY name ASC");

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = "r.room_name LIKE ?";
    $params[] = '%' . $keyword . '%';
    $types .= 's';
}

if ($status !== '') {
    $where[] = "r.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($locationId > 0) {
    $where[] = "r.location_id = ?";
    $params[] = $locationId;
    $types .= 'i';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT r.room_id, r.room_name, r.capacity, r.status, l.name AS location_name
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    $whereSql
    ORDER BY l.name ASC, r.room_name ASC
";

$stmt = $conn->prepare($sql);
$result = false;

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Room List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/rooms/create.php">+ Add Room</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="text" name="keyword" placeholder="Search room name..." value="<?= e($keyword) ?>">

            <select class="admin-select" name="location_id">
                <option value="">-- All Cinemas --</option>
                <?php if ($locations && $locations->num_rows > 0): ?>
                    <?php while ($location = $locations->fetch_assoc()): ?>
                        <option value="<?= (int) $location['location_id'] ?>" <?= $locationId === (int) $location['location_id'] ? 'selected' : '' ?>>
                            <?= e($location['name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <select class="admin-select" name="status">
                <option value="">-- All Statuses --</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>inactive</option>
            </select>

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/rooms/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room Name</th>
                    <th>Cinema</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($room = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $room['room_id'] ?></td>
                        <td><?= e($room['room_name']) ?></td>
                        <td><?= e($room['location_name']) ?></td>
                        <td><?= (int) $room['capacity'] ?></td>
                        <td><span class="admin-badge active"><?= e($room['status']) ?></span></td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/rooms/edit.php?id=<?= (int) $room['room_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/rooms/delete.php?id=<?= (int) $room['room_id'] ?>" onclick="return confirm('Delete this room?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6"><div class="admin-empty">No matching rooms found.</div></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
if ($stmt) {
    $stmt->close();
}
require_once __DIR__ . '/../../includes/admin_layout_end.php';
?>
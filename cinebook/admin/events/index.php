<?php
$pageTitle = 'Manage Events';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim(get('keyword'));
$status = trim(get('status'));

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = '(title LIKE ? OR description LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $types .= 'ss';
}

if ($status !== '') {
    $where[] = 'status = ?';
    $params[] = $status;
    $types .= 's';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT event_id, title, description, start_at, end_at, image, status
    FROM events
    $whereSql
    ORDER BY start_at DESC, event_id DESC
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
        <h2>Event List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/events/create.php">+ Add Event</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="text" name="keyword" placeholder="Search event title / description..." value="<?= e($keyword) ?>">

            <select class="admin-select" name="status">
                <option value="">-- All Statuses --</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>inactive</option>
            </select>

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/events/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($event = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img class="admin-thumb" src="<?= e($event['image'] ?: 'https://via.placeholder.com/160x90?text=Event') ?>" alt="">
                        </td>
                        <td>
                            <strong><?= e($event['title']) ?></strong>
                            <div style="color:#94a3b8; margin-top:6px; max-width:420px;">
                                <?= e(strlen((string) ($event['description'] ?? '')) > 120 ? substr((string) ($event['description'] ?? ''), 0, 117) . '...' : (string) ($event['description'] ?? '')) ?>
                            </div>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($event['start_at'])) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($event['end_at'])) ?></td>
                        <td>
                            <span class="admin-badge <?= $event['status'] === 'active' ? 'active' : 'coming' ?>">
                                <?= e($event['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/events/edit.php?id=<?= (int) $event['event_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/events/delete.php?id=<?= (int) $event['event_id'] ?>" onclick="return confirm('Delete this event?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6"><div class="admin-empty">No matching events found.</div></td>
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
<?php
$pageTitle = 'Manage Cinemas / Branches';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim(get('keyword'));
$status = trim(get('status'));
$city = trim(get('city'));

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = "(name LIKE ? OR address LIKE ? OR phone LIKE ?)";
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
    $types .= 'sss';
}

if ($city !== '') {
    $where[] = "city LIKE ?";
    $params[] = '%' . $city . '%';
    $types .= 's';
}

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT
        l.location_id,
        l.name,
        l.address,
        l.city,
        l.phone,
        l.status,
        COUNT(r.room_id) AS room_count
    FROM locations l
    LEFT JOIN rooms r ON l.location_id = r.location_id
    $whereSql
    GROUP BY l.location_id, l.name, l.address, l.city, l.phone, l.status
    ORDER BY l.name ASC
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
        <h2>Cinema / Branch List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/locations/create.php">+ Add Cinema Branch</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="text" name="keyword" placeholder="Search cinema name / address / phone..." value="<?= e($keyword) ?>">
            <input class="admin-input" type="text" name="city" placeholder="City..." value="<?= e($city) ?>">

            <select class="admin-select" name="status">
                <option value="">-- All Statuses --</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>inactive</option>
            </select>

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/locations/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cinema Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Phone</th>
                    <th>Room Count</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($location = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $location['location_id'] ?></td>
                        <td><?= e($location['name']) ?></td>
                        <td><?= e($location['address']) ?></td>
                        <td><?= e($location['city']) ?></td>
                        <td><?= e($location['phone'] ?: 'N/A') ?></td>
                        <td><?= (int) $location['room_count'] ?></td>
                        <td>
                            <span class="admin-badge <?= $location['status'] === 'active' ? 'active' : 'coming' ?>">
                                <?= e($location['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/locations/edit.php?id=<?= (int) $location['location_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/locations/delete.php?id=<?= (int) $location['location_id'] ?>" onclick="return confirm('Delete this cinema / branch?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">
                        <div class="admin-empty">No matching cinemas / branches found.</div>
                    </td>
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
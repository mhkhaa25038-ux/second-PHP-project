<?php
$pageTitle = 'Manage Combos';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim(get('keyword'));
$status = trim(get('status'));

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = '(name LIKE ? OR description LIKE ?)';
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
    SELECT combo_id, name, description, price, image, status, created_at
    FROM combo_products
    $whereSql
    ORDER BY created_at DESC, combo_id DESC
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
        <h2>Combo List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/combos/create.php">+ Add Combo</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="text" name="keyword" placeholder="Search combo name / description..." value="<?= e($keyword) ?>">

            <select class="admin-select" name="status">
                <option value="">-- All Statuses --</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>inactive</option>
            </select>

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/combos/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Combo Name</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($combo = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img class="admin-thumb" src="<?= e($combo['image'] ?: 'https://via.placeholder.com/160x90?text=Combo') ?>" alt="">
                        </td>
                        <td>
                            <strong><?= e($combo['name']) ?></strong>
                            <div style="color:#94a3b8; margin-top:6px; max-width:420px;">
                                <?= e(strlen((string) ($combo['description'] ?? '')) > 120 ? substr((string) ($combo['description'] ?? ''), 0, 117) . '...' : (string) ($combo['description'] ?? '')) ?>
                            </div>
                        </td>
                        <td><?= format_currency($combo['price']) ?></td>
                        <td>
                            <span class="admin-badge <?= $combo['status'] === 'active' ? 'active' : 'coming' ?>">
                                <?= e($combo['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/combos/edit.php?id=<?= (int) $combo['combo_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/combos/delete.php?id=<?= (int) $combo['combo_id'] ?>" onclick="return confirm('Delete this combo?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5"><div class="admin-empty">No matching combos found.</div></td>
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
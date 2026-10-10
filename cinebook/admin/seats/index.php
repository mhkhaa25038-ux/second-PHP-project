<?php
$pageTitle = 'Manage Seats';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$roomFilter = (int) get('room_id');
$categoryFilter = (int) get('category_id');
$keyword = trim(get('keyword'));

$rooms = $conn->query("
    SELECT r.room_id, r.room_name, l.name AS location_name
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY l.name ASC, r.room_name ASC
");

$categories = $conn->query("
    SELECT category_id, name
    FROM seat_categories
    ORDER BY name ASC
");

$where = [];
$params = [];
$types = '';

if ($roomFilter > 0) {
    $where[] = "s.room_id = ?";
    $params[] = $roomFilter;
    $types .= 'i';
}

if ($categoryFilter > 0) {
    $where[] = "s.category_id = ?";
    $params[] = $categoryFilter;
    $types .= 'i';
}

if ($keyword !== '') {
    $where[] = "(s.seat_row LIKE ? OR s.seat_number LIKE ? OR CONCAT(s.seat_row, s.seat_number) LIKE ?)";
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
    $types .= 'sss';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT
        s.seat_id,
        s.seat_row,
        s.seat_number,
        s.is_aisle,
        s.is_accessible,
        r.room_name,
        l.name AS location_name,
        sc.name AS category_name
    FROM seats s
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    INNER JOIN seat_categories sc ON s.category_id = sc.category_id
    $whereSql
    ORDER BY l.name ASC, r.room_name ASC, s.seat_row ASC, CAST(s.seat_number AS UNSIGNED) ASC, s.seat_number ASC
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
        <h2>Seat List</h2>
        <div class="admin-actions">
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seats/generate.php">Quick Layout Generator</a>
            <a class="admin-btn" href="<?= BASE_URL ?>admin/seats/create.php">+ Add Seat</a>
        </div>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="text" name="keyword" placeholder="Search A, A1, B12..." value="<?= e($keyword) ?>">

            <select class="admin-select" name="room_id">
                <option value="">-- All Rooms --</option>
                <?php if ($rooms && $rooms->num_rows > 0): ?>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <option value="<?= (int) $room['room_id'] ?>" <?= $roomFilter === (int) $room['room_id'] ? 'selected' : '' ?>>
                            <?= e($room['location_name'] . ' - ' . $room['room_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <select class="admin-select" name="category_id">
                <option value="">-- All Seat Types --</option>
                <?php if ($categories && $categories->num_rows > 0): ?>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <option value="<?= (int) $category['category_id'] ?>" <?= $categoryFilter === (int) $category['category_id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seats/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cinema</th>
                    <th>Room</th>
                    <th>Row</th>
                    <th>Seat Number</th>
                    <th>Seat Type</th>
                    <th>Aisle</th>
                    <th>Accessible</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($seat = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $seat['seat_id'] ?></td>
                        <td><?= e($seat['location_name']) ?></td>
                        <td><?= e($seat['room_name']) ?></td>
                        <td><?= e($seat['seat_row']) ?></td>
                        <td><?= e($seat['seat_number']) ?></td>
                        <td><?= e($seat['category_name']) ?></td>
                        <td><?= (int) $seat['is_aisle'] === 1 ? 'Yes' : 'No' ?></td>
                        <td><?= (int) $seat['is_accessible'] === 1 ? 'Yes' : 'No' ?></td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seats/edit.php?id=<?= (int) $seat['seat_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/seats/delete.php?id=<?= (int) $seat['seat_id'] ?>" onclick="return confirm('Delete this seat?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9"><div class="admin-empty">No matching seats found.</div></td>
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
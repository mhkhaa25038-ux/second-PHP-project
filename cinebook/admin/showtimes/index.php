<?php
$pageTitle = 'Manage Showtimes';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim(get('keyword'));
$movieId = (int) get('movie_id');
$roomId = (int) get('room_id');
$status = trim(get('status'));
$showDate = trim(get('show_date'));

$movies = $conn->query("SELECT movie_id, title FROM movies ORDER BY title ASC");
$rooms = $conn->query("
    SELECT r.room_id, r.room_name, l.name AS location_name
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY l.name ASC, r.room_name ASC
");

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = '(m.title LIKE ? OR l.name LIKE ? OR r.room_name LIKE ?)';
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
    $types .= 'sss';
}

if ($movieId > 0) {
    $where[] = 's.movie_id = ?';
    $params[] = $movieId;
    $types .= 'i';
}

if ($roomId > 0) {
    $where[] = 's.room_id = ?';
    $params[] = $roomId;
    $types .= 'i';
}

if ($status !== '') {
    $where[] = 's.status = ?';
    $params[] = $status;
    $types .= 's';
}

if ($showDate !== '') {
    $where[] = 'DATE(s.start_at) = ?';
    $params[] = $showDate;
    $types .= 's';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT
        s.show_id,
        s.start_at,
        s.end_at,
        s.base_price,
        s.screen_format,
        s.subtitle_type,
        s.status,
        m.title AS movie_title,
        r.room_name,
        l.name AS location_name,
        COUNT(ss.show_seat_id) AS total_seats,
        SUM(CASE WHEN ss.status = 'available' THEN 1 ELSE 0 END) AS available_seats,
        SUM(CASE WHEN ss.status = 'held' THEN 1 ELSE 0 END) AS held_seats,
        SUM(CASE WHEN ss.status = 'booked' THEN 1 ELSE 0 END) AS booked_seats
    FROM showtimes s
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    LEFT JOIN show_seats ss ON s.show_id = ss.show_id
    $whereSql
    GROUP BY s.show_id, s.start_at, s.end_at, s.base_price, s.screen_format, s.subtitle_type, s.status, m.title, r.room_name, l.name
    ORDER BY s.start_at DESC, s.show_id DESC
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
        <h2>Showtime List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/showtimes/create.php">+ Add Showtime</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="text" name="keyword" placeholder="Search movie / cinema / room..." value="<?= e($keyword) ?>">

            <select class="admin-select" name="movie_id">
                <option value="">-- All Movies --</option>
                <?php if ($movies && $movies->num_rows > 0): ?>
                    <?php while ($movie = $movies->fetch_assoc()): ?>
                        <option value="<?= (int) $movie['movie_id'] ?>" <?= $movieId === (int) $movie['movie_id'] ? 'selected' : '' ?>>
                            <?= e($movie['title']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <select class="admin-select" name="room_id">
                <option value="">-- All Rooms --</option>
                <?php if ($rooms && $rooms->num_rows > 0): ?>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                        <option value="<?= (int) $room['room_id'] ?>" <?= $roomId === (int) $room['room_id'] ? 'selected' : '' ?>>
                            <?= e($room['location_name'] . ' - ' . $room['room_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <input class="admin-input" type="date" name="show_date" value="<?= e($showDate) ?>">

            <select class="admin-select" name="status">
                <option value="">-- All Statuses --</option>
                <option value="scheduled" <?= $status === 'scheduled' ? 'selected' : '' ?>>scheduled</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>completed</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
            </select>

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/showtimes/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Movie</th>
                    <th>Cinema / Room</th>
                    <th>Time</th>
                    <th>Format</th>
                    <th>Base Price</th>
                    <th>Seats</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($show = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?= e($show['movie_title']) ?></strong>
                        </td>
                        <td>
                            <?= e($show['location_name']) ?><br>
                            <span style="color:#94a3b8;"><?= e($show['room_name']) ?></span>
                        </td>
                        <td>
                            <strong><?= date('d/m/Y H:i', strtotime($show['start_at'])) ?></strong><br>
                            <span style="color:#94a3b8;">to <?= date('d/m/Y H:i', strtotime($show['end_at'])) ?></span>
                        </td>
                        <td>
                            <?= e($show['screen_format'] ?: '2D') ?><br>
                            <span style="color:#94a3b8;"><?= e($show['subtitle_type'] ?: 'No subtitle') ?></span>
                        </td>
                        <td><?= format_currency($show['base_price']) ?></td>
                        <td>
                            Total: <?= (int) $show['total_seats'] ?><br>
                            <span style="color:#86efac;">Available: <?= (int) $show['available_seats'] ?></span><br>
                            <span style="color:#fde68a;">Held: <?= (int) $show['held_seats'] ?></span><br>
                            <span style="color:#fca5a5;">Booked: <?= (int) $show['booked_seats'] ?></span>
                        </td>
                        <td>
                            <?php
                            $badgeClass = 'coming';
                            if ($show['status'] === 'scheduled') {
                                $badgeClass = 'showing';
                            } elseif ($show['status'] === 'completed') {
                                $badgeClass = 'active';
                            }
                            ?>
                            <span class="admin-badge <?= $badgeClass ?>"><?= e($show['status']) ?></span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/showtimes/edit.php?id=<?= (int) $show['show_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/showtimes/delete.php?id=<?= (int) $show['show_id'] ?>" onclick="return confirm('Delete this showtime?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8"><div class="admin-empty">No matching showtimes found.</div></td>
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
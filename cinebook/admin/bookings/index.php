<?php
$pageTitle = 'Booking Management';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim((string) get('keyword', ''));
$status = trim((string) get('status', ''));
$allowedStatuses = ['pending', 'confirmed', 'paid', 'cancelled', 'expired'];

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = "(b.booking_code LIKE CONCAT('%', ?, '%') OR u.name LIKE CONCAT('%', ?, '%') OR m.title LIKE CONCAT('%', ?, '%'))";
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= 'sss';
}

if ($status !== '' && in_array($status, $allowedStatuses, true)) {
    $where[] = 'b.status = ?';
    $params[] = $status;
    $types .= 's';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT
        b.booking_id,
        b.booking_code,
        COALESCE(u.name, 'Guest') AS user_name,
        COALESCE(m.title, 'N/A') AS movie_title,
        s.start_at,
        COALESCE(r.room_name, 'N/A') AS room_name,
        b.total,
        b.status
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.user_id
    LEFT JOIN showtimes s ON b.show_id = s.show_id
    LEFT JOIN movies m ON s.movie_id = m.movie_id
    LEFT JOIN rooms r ON s.room_id = r.room_id
    $whereSql
    ORDER BY b.created_at DESC, b.booking_id DESC
";

$stmt = $conn->prepare($sql);
if ($stmt && $types !== '') {
    $stmt->bind_param($types, ...$params);
}

$bookings = false;
if ($stmt && $stmt->execute()) {
    $bookings = $stmt->get_result();
}

function booking_status_badge(string $status): array
{
    return match ($status) {
        'paid', 'confirmed' => [ucfirst($status), 'active'],
        'pending' => ['Pending', 'coming'],
        'cancelled', 'expired' => [ucfirst($status), 'inactive'],
        default => [ucfirst($status), 'inactive'],
    };
}
?>

<div class="admin-content">
    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Booking List</h2>
        </div>

        <form method="GET" class="admin-filter-bar">
            <input
                type="text"
                name="keyword"
                value="<?= e($keyword) ?>"
                placeholder="Search booking code, customer, or movie..."
                class="admin-input"
            >
    

            <select name="status" class="admin-select">
                <option value="">All statuses</option>
                <?php foreach ($allowedStatuses as $statusOption): ?>
                    <option value="<?= e($statusOption) ?>" <?= $status === $statusOption ? 'selected' : '' ?>>
                        <?= e(ucfirst($statusOption)) ?>
                    </option>
                <?php endforeach; ?>
            </select> 
          

            <button class="admin-btn" type="submit">Search</button>
            <a href="index.php" class="admin-btn-outline">Reset</a>
        </form>

        <br>

        <?php if ($bookings && $bookings->num_rows > 0): ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Booking Code</th>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Showtime</th>
                            <th>Room</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $bookings->fetch_assoc()): ?>
                            <?php [$statusText, $statusClass] = booking_status_badge((string) $row['status']); ?>
                            <tr>
                                <td><?= e($row['booking_code']) ?></td>
                                <td><?= e($row['user_name']) ?></td>
                                <td><?= e($row['movie_title']) ?></td>
                                <td><?= !empty($row['start_at']) ? date('d/m/Y H:i', strtotime($row['start_at'])) : 'N/A' ?></td>
                                <td><?= e($row['room_name']) ?></td>
                                <td><?= format_currency($row['total']) ?></td>
                                <td>
                                    <span class="admin-badge <?= e($statusClass) ?>"><?= e($statusText) ?></span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a class="admin-btn-outline" href="detail.php?id=<?= (int) $row['booking_id'] ?>">View</a>
                                        <a class="admin-btn-soft" href="print.php?id=<?= (int) $row['booking_id'] ?>&auto=1" target="_blank" rel="noopener">Print Invoice</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty">
                No bookings found.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
if ($stmt) {
    $stmt->close();
}
require_once __DIR__ . '/../../includes/admin_layout_end.php';
?>
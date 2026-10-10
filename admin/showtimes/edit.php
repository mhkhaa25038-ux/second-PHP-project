<?php
$pageTitle = 'Edit Showtime';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

$detailSql = '
    SELECT s.*, m.title AS movie_title, r.room_name, l.name AS location_name
    FROM showtimes s
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE s.show_id = ?
    LIMIT 1
';
$detailStmt = $conn->prepare($detailSql);
$detailStmt->bind_param('i', $id);
$detailStmt->execute();
$showtime = $detailStmt->get_result()->fetch_assoc();
$detailStmt->close();

if (!$showtime) {
    set_flash_message('error', 'Showtime not found.');
    redirect('admin/showtimes/index.php');
}

$movies = $conn->query("SELECT movie_id, title, status, duration_min FROM movies ORDER BY status ASC, title ASC");
$rooms = $conn->query("
    SELECT r.room_id, r.room_name, l.name AS location_name, r.status
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY l.name ASC, r.room_name ASC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieId = (int) post('movie_id');
    $roomId = (int) post('room_id');
    $startAt = trim(post('start_at'));
    $endAt = trim(post('end_at'));
    $cleanupMinutes = max(0, (int) post('cleanup_minutes', '15'));
    $basePrice = (float) post('base_price');
    $screenFormat = trim(post('screen_format')) ?: '2D';
    $subtitleType = trim(post('subtitle_type'));
    $status = trim(post('status')) ?: 'scheduled';

    if ($endAt === '' && $startAt !== '' && $movieId > 0) {
        $durationStmt = $conn->prepare("SELECT duration_min FROM movies WHERE movie_id = ? LIMIT 1");
        if ($durationStmt) {
            $durationStmt->bind_param('i', $movieId);
            $durationStmt->execute();
            $movieRow = $durationStmt->get_result()->fetch_assoc();
            $durationStmt->close();
            $durationMin = (int) ($movieRow['duration_min'] ?? 0);
            if ($durationMin > 0) {
                $endAt = date('Y-m-d\TH:i', strtotime($startAt . ' +' . ($durationMin + $cleanupMinutes) . ' minutes'));
            }
        }
    }

    if ($movieId <= 0 || $roomId <= 0 || $startAt === '' || $endAt === '' || $basePrice <= 0) {
        $error = 'Please enter a movie, room, time, and a valid base price.';
    } elseif (strtotime($endAt) <= strtotime($startAt)) {
        $error = 'End time must be later than start time.';
    } else {
        $overlapSql = "
            SELECT COUNT(*) AS total
            FROM showtimes
            WHERE room_id = ?
              AND show_id <> ?
              AND status = 'scheduled'
              AND NOT (end_at <= ? OR start_at >= ?)
        ";
        $overlapStmt = $conn->prepare($overlapSql);
        $overlapStmt->bind_param('iiss', $roomId, $id, $startAt, $endAt);
        $overlapStmt->execute();
        $overlapRow = $overlapStmt->get_result()->fetch_assoc();
        $overlapStmt->close();

        if ((int) ($overlapRow['total'] ?? 0) > 0) {
            $error = 'The room already has another showtime during this period. Please choose a different time slot.';
        } else {
            $bookingCount = count_showtime_bookings($conn, $id);
            $roomChanged = (int) $showtime['room_id'] !== $roomId;

            if ($roomChanged && $bookingCount > 0) {
                $error = 'This showtime already has bookings, so the room cannot be changed.';
            } else {
                $subtitleType = $subtitleType !== '' ? $subtitleType : null;

                try {
                    $conn->begin_transaction();
                    $sql = 'UPDATE showtimes SET movie_id = ?, room_id = ?, start_at = ?, end_at = ?, base_price = ?, screen_format = ?, subtitle_type = ?, status = ? WHERE show_id = ?';
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception('Showtime prepare error.');
                    }
                    $stmt->bind_param('iissdsssi', $movieId, $roomId, $startAt, $endAt, $basePrice, $screenFormat, $subtitleType, $status, $id);
                    if (!$stmt->execute()) {
                        throw new Exception('Unable to update the showtime.');
                    }
                    $stmt->close();

                    if (!sync_show_seats($conn, $id, $roomId, $basePrice, $roomChanged && $bookingCount === 0)) {
                        throw new Exception('Unable to sync seats by showtime.');
                    }

                    $conn->commit();
                    set_flash_message('success', 'Showtime updated successfully.');
                    redirect('admin/showtimes/index.php');
                } catch (Throwable $e) {
                    $conn->rollback();
                    $error = $e->getMessage();
                }
            }
        }
    }
}

$startValue = !empty($showtime['start_at']) ? date('Y-m-d\TH:i', strtotime($showtime['start_at'])) : '';
$endValue = !empty($showtime['end_at']) ? date('Y-m-d\TH:i', strtotime($showtime['end_at'])) : '';
?>
<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Edit Showtime</h2>
            <p class="admin-card-subtitle">Edit the movie, room, showtime, and price. The system will keep current booking data unchanged as long as the room is not changed.</p>
        </div>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/showtimes/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Movie</label>
                <select class="admin-select" name="movie_id" id="showtime-movie-id" required>
                    <option value="">-- Select Movie --</option>
                    <?php if ($movies && $movies->num_rows > 0): ?>
                        <?php while ($movie = $movies->fetch_assoc()): ?>
                            <option value="<?= (int) $movie['movie_id'] ?>" data-duration="<?= (int) ($movie['duration_min'] ?? 0) ?>" <?= (int) old('movie_id', (string) $showtime['movie_id']) === (int) $movie['movie_id'] ? 'selected' : '' ?>>
                                <?= e($movie['title']) ?> (<?= (int) ($movie['duration_min'] ?? 0) ?> min - <?= e($movie['status']) ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Screening Room</label>
                <select class="admin-select" name="room_id" required>
                    <option value="">-- Select Room --</option>
                    <?php if ($rooms && $rooms->num_rows > 0): ?>
                        <?php while ($room = $rooms->fetch_assoc()): ?>
                            <option value="<?= (int) $room['room_id'] ?>" <?= (int) old('room_id', (string) $showtime['room_id']) === (int) $room['room_id'] ? 'selected' : '' ?>>
                                <?= e($room['location_name'] . ' - ' . $room['room_name'] . ' (' . $room['status'] . ')') ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Start</label>
                <input class="admin-input" type="datetime-local" name="start_at" id="showtime-start-at" value="<?= e(old('start_at', $startValue)) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Cleanup Time (minutes)</label>
                <input class="admin-input" type="number" name="cleanup_minutes" id="showtime-cleanup-minutes" min="0" step="5" value="<?= e(old('cleanup_minutes', '15')) ?>">
            </div>

            <div class="admin-form-group">
                <label class="admin-label">End</label>
                <input class="admin-input" type="datetime-local" name="end_at" id="showtime-end-at" value="<?= e(old('end_at', $endValue)) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Base Price</label>
                <input class="admin-input" type="number" name="base_price" min="1000" step="1000" value="<?= e(old('base_price', $showtime['base_price'])) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <?php $currentStatus = old('status', $showtime['status']); ?>
                <select class="admin-select" name="status">
                    <option value="scheduled" <?= $currentStatus === 'scheduled' ? 'selected' : '' ?>>scheduled</option>
                    <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>completed</option>
                    <option value="cancelled" <?= $currentStatus === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Format</label>
                <?php $currentFormat = old('screen_format', $showtime['screen_format'] ?: '2D'); ?>
                <select class="admin-select" name="screen_format">
                    <option value="2D" <?= $currentFormat === '2D' ? 'selected' : '' ?>>2D</option>
                    <option value="3D" <?= $currentFormat === '3D' ? 'selected' : '' ?>>3D</option>
                    <option value="IMAX" <?= $currentFormat === 'IMAX' ? 'selected' : '' ?>>IMAX</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Subtitles / Dubbing</label>
                <input class="admin-input" type="text" name="subtitle_type" placeholder="Vietnamese subtitles, Vietnamese dubbing..." value="<?= e(old('subtitle_type', $showtime['subtitle_type'] ?? '')) ?>">
            </div>
        </div>

        <button class="admin-btn" type="submit">Update Showtime</button>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const movieSelect = document.getElementById('showtime-movie-id');
    const startInput = document.getElementById('showtime-start-at');
    const cleanupInput = document.getElementById('showtime-cleanup-minutes');
    const endInput = document.getElementById('showtime-end-at');
    if (!movieSelect || !startInput || !cleanupInput || !endInput) return;

    function formatLocalDateTime(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    function calculateEndTime() {
        const option = movieSelect.options[movieSelect.selectedIndex];
        const duration = Number(option?.dataset.duration || 0);
        const cleanupMinutes = Number(cleanupInput.value || 0);
        if (!startInput.value || !duration) return;
        const startDate = new Date(startInput.value);
        if (Number.isNaN(startDate.getTime())) return;
        startDate.setMinutes(startDate.getMinutes() + duration + cleanupMinutes);
        endInput.value = formatLocalDateTime(startDate);
    }

    movieSelect.addEventListener('change', calculateEndTime);
    startInput.addEventListener('change', calculateEndTime);
    cleanupInput.addEventListener('input', calculateEndTime);
});
</script>
<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>

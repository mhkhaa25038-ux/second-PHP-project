<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/admin_layout_start.php';

$conn = db_connect();

function fetch_total(mysqli $conn, string $sql): int
{
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }
    return 0;
}

$totalMovies = fetch_total($conn, "SELECT COUNT(*) AS total FROM movies");
$totalRooms = fetch_total($conn, "SELECT COUNT(*) AS total FROM rooms");
$totalSeats = fetch_total($conn, "SELECT COUNT(*) AS total FROM seats");
$totalNowShowing = fetch_total($conn, "SELECT COUNT(*) AS total FROM movies WHERE status = 'now_showing'");
$totalComingSoon = fetch_total($conn, "SELECT COUNT(*) AS total FROM movies WHERE status = 'coming_soon'");
$totalLocations = fetch_total($conn, "SELECT COUNT(*) AS total FROM locations");
$totalShowtimes = fetch_total($conn, "SELECT COUNT(*) AS total FROM showtimes");
$totalEvents = fetch_total($conn, "SELECT COUNT(*) AS total FROM events");
$totalCombos = fetch_total($conn, "SELECT COUNT(*) AS total FROM combo_products");

$latestMovies = $conn->query("
    SELECT movie_id, title, language, duration_min, status, created_at
    FROM movies
    ORDER BY created_at DESC
    LIMIT 5
");

$latestShowtimes = $conn->query("
    SELECT s.show_id, s.start_at, s.status, m.title AS movie_title, r.room_name, l.name AS location_name
    FROM showtimes s
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY s.created_at DESC
    LIMIT 5
");

function movie_status_label(string $status): array
{
    return match ($status) {
        'now_showing' => ['Now Showing', 'showing'],
        'coming_soon' => ['Coming Soon', 'coming'],
        default => ['Hidden', 'inactive'],
    };
}

function showtime_status_label(string $status): array
{
    return match ($status) {
        'scheduled' => ['Scheduled', 'dashboard-tag schedule'],
        'completed' => ['Completed', 'dashboard-tag complete'],
        'cancelled' => ['Cancelled', 'dashboard-tag cancel'],
        default => [ucfirst($status), 'dashboard-tag'],
    };
}
?>

<div class="dashboard-hero">
    <div>
       <p class="dashboard-eyebrow">Control Center</p>
<h2>Manage cinema data with clarity and efficiency.</h2>
<p class="dashboard-hero-text">
    Access essential insights and keep track of movies, schedules, and branch activity in one place.
</p>
    </div>

    <div class="dashboard-hero-meta">
        <div class="dashboard-hero-item">
            <span>Now Showing</span>
            <strong><?= $totalNowShowing ?></strong>
        </div>
        <div class="dashboard-hero-item">
            <span>Coming Soon</span>
            <strong><?= $totalComingSoon ?></strong>
        </div>
        <div class="dashboard-hero-item">
            <span>Locations</span>
            <strong><?= $totalLocations ?></strong>
        </div>
    </div>
</div>

<div class="dashboard-section">
    <div class="dashboard-section-head">
        <div>
            <h3>Key Metrics</h3>
            <p>Prioritize the numbers that administrators need to see first.</p>
        </div>
        <div class="admin-actions dashboard-actions-inline">
            <a class="admin-btn" href="<?= BASE_URL ?>admin/showtimes/index.php">Manage Showtimes</a>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/movies/index.php">Manage Movies</a>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/reports/index.php">View Reports</a>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>">User Site</a>
        </div>
    </div>

    <div class="dashboard-kpi-grid primary">
        <div class="dashboard-kpi-card accent-pink">
            <span class="dashboard-kpi-label">Total Movies</span>
            <strong class="dashboard-kpi-value"><?= $totalMovies ?></strong>
            <small>Movies currently stored in the system</small>
        </div>

        <div class="dashboard-kpi-card accent-purple">
            <span class="dashboard-kpi-label">Total Showtimes</span>
            <strong class="dashboard-kpi-value"><?= $totalShowtimes ?></strong>
            <small>Show schedules already created</small>
        </div>

        <div class="dashboard-kpi-card accent-blue">
            <span class="dashboard-kpi-label">Cinema Locations</span>
            <strong class="dashboard-kpi-value"><?= $totalLocations ?></strong>
            <small>Number of cinema branches being managed</small>
        </div>

        <div class="dashboard-kpi-card accent-gold">
            <span class="dashboard-kpi-label">Active Combos</span>
            <strong class="dashboard-kpi-value"><?= $totalCombos ?></strong>
            <small>Additional products available at the counter</small>
        </div>
    </div>

    <div class="dashboard-kpi-grid secondary">
        <div class="dashboard-mini-card">
            <span>Rooms</span>
            <strong><?= $totalRooms ?></strong>
        </div>
        <div class="dashboard-mini-card">
            <span>Seats</span>
            <strong><?= $totalSeats ?></strong>
        </div>
        <div class="dashboard-mini-card">
            <span>Events</span>
            <strong><?= $totalEvents ?></strong>
        </div>
        <div class="dashboard-mini-card">
            <span>Now Showing Movies</span>
            <strong><?= $totalNowShowing ?></strong>
        </div>
        <div class="dashboard-mini-card">
            <span>Coming Soon Movies</span>
            <strong><?= $totalComingSoon ?></strong>
        </div>
    </div>
</div>

<div class="dashboard-content-grid">
    <section class="admin-card dashboard-card-block">
        <div class="dashboard-section-head compact">
            <div>
                <h3>Recently Added Movies</h3>
                <p>Quickly review the latest movie entries.</p>
            </div>
            <a class="admin-btn-soft" href="<?= BASE_URL ?>admin/movies/index.php">View All</a>
        </div>

        <?php if ($latestMovies && $latestMovies->num_rows > 0): ?>
            <div class="admin-table-wrap">
                <table class="admin-table admin-table-compact">
                    <thead>
                        <tr>
                            <th>Movie</th>
                            <th>Language</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($movie = $latestMovies->fetch_assoc()): ?>
                            <?php [$movieStatusText, $movieStatusClass] = movie_status_label((string) $movie['status']); ?>
                            <tr>
                                <td>
                                    <strong><?= e($movie['title']) ?></strong>
                                </td>
                                <td><?= e($movie['language'] ?: 'N/A') ?></td>
                                <td><?= (int) $movie['duration_min'] ?> min</td>
                                <td><span class="admin-badge <?= e($movieStatusClass) ?>"><?= e($movieStatusText) ?></span></td>
                                <td><?= !empty($movie['created_at']) ? date('d/m/Y', strtotime($movie['created_at'])) : 'N/A' ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="admin-empty">No recent movies found.</div>
        <?php endif; ?>
    </section>

    <section class="admin-card dashboard-card-block">
        <div class="dashboard-section-head compact">
            <div>
                <h3>Recently Created Showtimes</h3>
                <p>Monitor the latest schedules added to the system.</p>
            </div>
            <a class="admin-btn-soft" href="<?= BASE_URL ?>admin/showtimes/index.php">View All</a>
        </div>

        <?php if ($latestShowtimes && $latestShowtimes->num_rows > 0): ?>
            <div class="dashboard-showtime-list">
                <?php while ($show = $latestShowtimes->fetch_assoc()): ?>
                    <?php [$showStatusText, $showStatusClass] = showtime_status_label((string) $show['status']); ?>
                    <div class="dashboard-showtime-item">
                        <div class="dashboard-showtime-top">
                            <strong><?= e($show['movie_title']) ?></strong>
                            <span class="<?= e($showStatusClass) ?>"><?= e($showStatusText) ?></span>
                        </div>
                        <p><?= e($show['location_name']) ?> · <?= e($show['room_name']) ?></p>
                        <small><?= !empty($show['start_at']) ? date('d/m/Y H:i', strtotime($show['start_at'])) : 'N/A' ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="admin-empty">No recent showtimes found.</div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/admin_layout_end.php'; ?>
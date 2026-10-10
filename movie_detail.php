<?php
require_once __DIR__ . '/includes/helpers.php';

$conn = db_connect();
$movieId = (int) get('id');

if ($movieId <= 0) {
    set_flash_message('error', 'Invalid movie.');
    redirect('movies.php');
}

$movieSql = "
    SELECT
        m.movie_id,
        m.title,
        m.synopsis,
        m.language,
        m.duration_min,
        m.trailer_url,
        m.poster_url,
        m.status,
        m.age_rating,
        m.release_date,
        m.created_at,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
    FROM movies m
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    WHERE m.movie_id = ?
    GROUP BY
        m.movie_id,
        m.title,
        m.synopsis,
        m.language,
        m.duration_min,
        m.trailer_url,
        m.poster_url,
        m.status,
        m.age_rating,
        m.release_date,
        m.created_at
    LIMIT 1
";

$movieStmt = $conn->prepare($movieSql);
$movieStmt->bind_param('i', $movieId);
$movieStmt->execute();
$movieResult = $movieStmt->get_result();
$movie = $movieResult->fetch_assoc();
$movieStmt->close();

if (!$movie) {
    set_flash_message('error', 'Movie not found.');
    redirect('movies.php');
}

$dateStmt = $conn->prepare("
    SELECT DISTINCT DATE(s.start_at) AS show_date
    FROM showtimes s
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE s.movie_id = ?
      AND s.status = 'scheduled'
      AND l.status = 'active'
      AND DATE(s.start_at) >= CURDATE()
    ORDER BY show_date ASC
    LIMIT 14
");
$dateStmt->bind_param('i', $movieId);
$dateStmt->execute();
$dateResult = $dateStmt->get_result();
$availableDates = [];
while ($row = $dateResult->fetch_assoc()) {
    if (!empty($row['show_date'])) {
        $availableDates[] = $row['show_date'];
    }
}
$dateStmt->close();

$selectedDate = get('date');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $selectedDate) || !in_array($selectedDate, $availableDates, true)) {
    $selectedDate = $availableDates[0] ?? date('Y-m-d');
}

$locations = [];
$locationCountMap = [];
if (!empty($availableDates)) {
    $locationCountStmt = $conn->prepare("
        SELECT
            l.location_id,
            l.name,
            l.address,
            l.city,
            l.phone,
            COUNT(*) AS total_showtimes
        FROM showtimes s
        INNER JOIN rooms r ON s.room_id = r.room_id
        INNER JOIN locations l ON r.location_id = l.location_id
        WHERE s.movie_id = ?
          AND s.status = 'scheduled'
          AND l.status = 'active'
          AND DATE(s.start_at) = ?
        GROUP BY l.location_id, l.name, l.address, l.city, l.phone
        ORDER BY l.name ASC
    ");
    $locationCountStmt->bind_param('is', $movieId, $selectedDate);
    $locationCountStmt->execute();
    $locationCountResult = $locationCountStmt->get_result();
    while ($row = $locationCountResult->fetch_assoc()) {
        $locationId = (int) $row['location_id'];
        $locations[] = $row;
        $locationCountMap[$locationId] = (int) $row['total_showtimes'];
    }
    $locationCountStmt->close();
}

$selectedLocationId = (int) get('location_id');
$locationIds = array_map(static fn($location) => (int) $location['location_id'], $locations);
if ($selectedLocationId <= 0 || !in_array($selectedLocationId, $locationIds, true)) {
    $selectedLocationId = $locationIds[0] ?? 0;
}

$showtimeGroups = [];
$selectedLocation = null;
if ($selectedLocationId > 0 && $selectedDate !== '' && !empty($availableDates)) {
    foreach ($locations as $location) {
        if ((int) $location['location_id'] === $selectedLocationId) {
            $selectedLocation = $location;
            break;
        }
    }

    $showtimeStmt = $conn->prepare("
        SELECT
            s.show_id,
            s.start_at,
            s.end_at,
            s.base_price,
            s.screen_format,
            s.subtitle_type,
            s.status,
            r.room_id,
            r.room_name,
            l.name AS location_name
        FROM showtimes s
        INNER JOIN rooms r ON s.room_id = r.room_id
        INNER JOIN locations l ON r.location_id = l.location_id
        WHERE s.movie_id = ?
          AND s.status = 'scheduled'
          AND l.location_id = ?
          AND DATE(s.start_at) = ?
        ORDER BY r.room_name ASC, s.start_at ASC
    ");
    $showtimeStmt->bind_param('iis', $movieId, $selectedLocationId, $selectedDate);
    $showtimeStmt->execute();
    $showtimesResult = $showtimeStmt->get_result();

    while ($show = $showtimesResult->fetch_assoc()) {
        $formatLabel = trim((string) ($show['screen_format'] ?: '2D'));
        $subtitleLabel = trim((string) ($show['subtitle_type'] ?: ''));
        $groupKey = implode('|', [(int) $show['room_id'], $formatLabel, $subtitleLabel]);

        if (!isset($showtimeGroups[$groupKey])) {
            $showtimeGroups[$groupKey] = [
                'room_name' => $show['room_name'],
                'screen_format' => $formatLabel,
                'subtitle_type' => $subtitleLabel,
                'showtimes' => [],
            ];
        }

        $showtimeGroups[$groupKey]['showtimes'][] = $show;
    }

    $showtimeStmt->close();
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="movie-detail-page">
    <div class="movie-detail-shell">
        <div class="movie-detail-main">
            <div class="movie-detail-poster">
                <img
                    src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/500x750?text=Movie') ?>"
                    alt="<?= e($movie['title']) ?>"
                >
            </div>

            <div class="movie-detail-info">
                <div class="movie-detail-topline">
                    <span class="movie-status-badge <?= $movie['status'] === 'coming_soon' ? 'coming' : 'showing' ?>">
                        <?= $movie['status'] === 'coming_soon' ? 'COMING SOON' : 'NOW SHOWING' ?>
                    </span>
                </div>

                <h1 class="movie-detail-title"><?= e($movie['title']) ?></h1>

                <div class="movie-detail-info-lines">
                    <p><strong>Genre:</strong> <?= e($movie['genres'] ?: 'Updating') ?></p>
                    <p><strong>Duration:</strong> <?= (int) $movie['duration_min'] ?> min</p>
                    <p><strong>Language:</strong> <?= e($movie['language'] ?: 'Updating') ?></p>
                    <p><strong>Age Rating:</strong> <?= e($movie['age_rating'] ?: 'Updating') ?></p>
                    <p><strong>Release Date:</strong> <?= !empty($movie['release_date']) ? date('d-m-Y', strtotime($movie['release_date'])) : 'Updating' ?></p>
                </div>

                <div class="movie-detail-actions">
                    <a class="movie-book-btn-dark" href="#showtimes">Book Now</a>
                    <a class="movie-detail-outline-btn" href="<?= BASE_URL ?>movies.php">Back to Movies</a>
                </div>
                <br>

                <div class="movie-detail-box">
                    <button type="button" class="detail-toggle-btn">
                        Details
                    </button>

                    <div class="movie-detail-desc">
                        <p class="detail-label">Details:</p>
                        <p><?= e($movie['synopsis'] ?: 'No movie description is available yet.') ?></p>
                    </div>
                </div>

                <div class="movie-detail-extra">
                    <div class="movie-detail-extra-item">
                        <span>Trailer</span>
                        <?php if (!empty($movie['trailer_url'])): ?>
                            <a href="<?= e($movie['trailer_url']) ?>" target="_blank">Watch trailer</a>
                        <?php else: ?>
                            <strong>Updating</strong>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="movie-detail-block" id="showtimes">
            <div class="movie-detail-block-head">
                <h2>Showtimes</h2>
            </div>

            <?php if (!empty($availableDates)): ?>
                <div class="schedule-toolbar movie-schedule-toolbar">
                    <div class="schedule-filter-group">
                        <div class="schedule-filter-label">Date</div>
                        <div class="schedule-chip-row">
                            <?php foreach ($availableDates as $dateItem): ?>
                                <?php $isActiveDate = $dateItem === $selectedDate; ?>
                                <a
                                    class="schedule-chip <?= $isActiveDate ? 'active' : '' ?>"
                                    href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movieId ?>&date=<?= e($dateItem) ?>#showtimes"
                                >
                                    <strong><?= date('d/m', strtotime($dateItem)) ?></strong>
                                    <span><?= date('D', strtotime($dateItem)) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (!empty($locations)): ?>
                        <div class="schedule-filter-group">
                            <div class="schedule-filter-label">Cinema</div>
                            <div class="schedule-chip-row">
                                <?php foreach ($locations as $location): ?>
                                    <?php $locationId = (int) $location['location_id']; ?>
                                    <a
                                        class="schedule-chip <?= $locationId === $selectedLocationId ? 'active' : '' ?>"
                                        href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movieId ?>&date=<?= e($selectedDate) ?>&location_id=<?= $locationId ?>#showtimes"
                                    >
                                        <strong><?= e($location['name']) ?></strong>
                                        <span><?= e($location['city'] ?: 'Cinema') ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($selectedLocation): ?>
                    <div class="schedule-cinema-head movie-schedule-head">
                        <div>
                            <h3><?= e($selectedLocation['name']) ?></h3>
                            <p>
                                <?= e($selectedLocation['address'] ?: 'Address updating') ?>
                                <?php if (!empty($selectedLocation['phone'])): ?>
                                    • Hotline: <?= e($selectedLocation['phone']) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="schedule-summary-badge">
                            <?= (int) ($locationCountMap[$selectedLocationId] ?? 0) ?> showtimes
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($showtimeGroups)): ?>
                    <div class="schedule-session-board">
                        <?php foreach ($showtimeGroups as $group): ?>
                            <div class="schedule-session-row">
                                <div class="schedule-session-label">
                                    <strong><?= e($group['room_name']) ?></strong>
                                    <span>
                                        <?= e($group['screen_format']) ?>
                                        <?php if ($group['subtitle_type'] !== ''): ?>
                                            • <?= e($group['subtitle_type']) ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="schedule-times">
                                    <?php foreach ($group['showtimes'] as $show): ?>
                                        <a class="showtime-pill" href="<?= BASE_URL ?>select_seats.php?show_id=<?= (int) $show['show_id'] ?>">
                                            <strong><?= date('H:i', strtotime($show['start_at'])) ?></strong>
                                            <span><?= format_currency($show['base_price']) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="schedule-empty">No showtimes are available for this cinema on the selected date.</div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">There are currently no showtimes for this movie.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.detail-toggle-btn');

    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const detailBox = button.nextElementSibling;

            if (detailBox) {
                detailBox.classList.toggle('show');

                button.textContent = detailBox.classList.contains('show')
                    ? 'Hide details'
                    : 'Details';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

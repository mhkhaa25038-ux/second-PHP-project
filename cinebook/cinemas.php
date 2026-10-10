<?php
require_once __DIR__ . '/includes/helpers.php';

$conn = db_connect();

$dateResult = $conn->query("
    SELECT DISTINCT DATE(s.start_at) AS show_date
    FROM showtimes s
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE s.status = 'scheduled'
      AND l.status = 'active'
      AND DATE(s.start_at) >= CURDATE()
    ORDER BY show_date ASC
    LIMIT 14
");

$availableDates = [];
if ($dateResult) {
    while ($row = $dateResult->fetch_assoc()) {
        if (!empty($row['show_date'])) {
            $availableDates[] = $row['show_date'];
        }
    }
}

if (empty($availableDates)) {
    for ($i = 0; $i < 7; $i++) {
        $availableDates[] = date('Y-m-d', strtotime('+' . $i . ' day'));
    }
}

$selectedDate = get('date');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $selectedDate) || !in_array($selectedDate, $availableDates, true)) {
    $selectedDate = $availableDates[0];
}

$locationsSql = "
    SELECT
        l.location_id,
        l.name,
        l.address,
        l.city,
        l.phone,
        l.status,
        COUNT(DISTINCT r.room_id) AS total_rooms
    FROM locations l
    LEFT JOIN rooms r ON l.location_id = r.location_id
    WHERE l.status = 'active'
    GROUP BY l.location_id, l.name, l.address, l.city, l.phone, l.status
    ORDER BY l.city ASC, l.name ASC
";
$locationsResult = $conn->query($locationsSql);
$locations = [];
if ($locationsResult) {
    while ($row = $locationsResult->fetch_assoc()) {
        $locations[] = $row;
    }
}

$locationCountMap = [];
$locationCountStmt = $conn->prepare("
    SELECT l.location_id, COUNT(*) AS total_showtimes
    FROM showtimes s
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE s.status = 'scheduled'
      AND l.status = 'active'
      AND DATE(s.start_at) = ?
    GROUP BY l.location_id
");
$locationCountStmt->bind_param('s', $selectedDate);
$locationCountStmt->execute();
$locationCountResult = $locationCountStmt->get_result();
while ($row = $locationCountResult->fetch_assoc()) {
    $locationCountMap[(int) $row['location_id']] = (int) $row['total_showtimes'];
}
$locationCountStmt->close();

$selectedLocationId = (int) get('location_id');
$locationIds = array_map(static fn($location) => (int) $location['location_id'], $locations);
if ($selectedLocationId <= 0 || !in_array($selectedLocationId, $locationIds, true)) {
    $selectedLocationId = 0;
    foreach ($locations as $location) {
        $locationId = (int) $location['location_id'];
        if (($locationCountMap[$locationId] ?? 0) > 0) {
            $selectedLocationId = $locationId;
            break;
        }
    }
    if ($selectedLocationId === 0) {
        $selectedLocationId = $locationIds[0] ?? 0;
    }
}

$selectedLocation = null;
foreach ($locations as $location) {
    if ((int) $location['location_id'] === $selectedLocationId) {
        $selectedLocation = $location;
        break;
    }
}

$movieRows = [];
if ($selectedLocationId > 0) {
    $showtimeStmt = $conn->prepare("
        SELECT
            m.movie_id,
            m.title,
            m.poster_url,
            m.duration_min,
            m.age_rating,
            m.language,
            GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres,
            s.show_id,
            s.start_at,
            s.base_price,
            s.screen_format,
            s.subtitle_type,
            r.room_name,
            l.name AS location_name
        FROM showtimes s
        INNER JOIN movies m ON s.movie_id = m.movie_id
        INNER JOIN rooms r ON s.room_id = r.room_id
        INNER JOIN locations l ON r.location_id = l.location_id
        LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
        LEFT JOIN genres g ON mg.genre_id = g.genre_id
        WHERE s.status = 'scheduled'
          AND l.location_id = ?
          AND DATE(s.start_at) = ?
        GROUP BY
            m.movie_id,
            m.title,
            m.poster_url,
            m.duration_min,
            m.age_rating,
            m.language,
            s.show_id,
            s.start_at,
            s.base_price,
            s.screen_format,
            s.subtitle_type,
            r.room_name,
            l.name
        ORDER BY m.title ASC, s.start_at ASC
    ");
    $showtimeStmt->bind_param('is', $selectedLocationId, $selectedDate);
    $showtimeStmt->execute();
    $showtimeResult = $showtimeStmt->get_result();
    while ($row = $showtimeResult->fetch_assoc()) {
        $movieRows[] = $row;
    }
    $showtimeStmt->close();
}

$movieGroups = [];
foreach ($movieRows as $row) {
    $movieId = (int) $row['movie_id'];
    if (!isset($movieGroups[$movieId])) {
        $movieGroups[$movieId] = [
            'movie_id' => $movieId,
            'title' => $row['title'],
            'poster_url' => $row['poster_url'],
            'duration_min' => (int) ($row['duration_min'] ?? 0),
            'age_rating' => $row['age_rating'],
            'language' => $row['language'],
            'genres' => $row['genres'],
            'showtimes' => [],
        ];
    }

    $movieGroups[$movieId]['showtimes'][] = $row;
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="schedule-page">
    <div class="schedule-shell">
        <div class="schedule-hero">
            <div>
    <span class="schedule-kicker">MOVIE SCHEDULE</span>
    <h1>Choose a date, select a cinema, and book your preferred showtime.</h1>
    <p>Explore all available cinema branches, view currently showing movies, and check showtimes in a clear and convenient schedule layout.</p>
</div>
        </div>

        <div class="schedule-toolbar">
            <div class="schedule-filter-group">
                <div class="schedule-filter-label">Date</div>
                <div class="schedule-chip-row">
                    <?php foreach ($availableDates as $dateItem): ?>
                        <?php $isActiveDate = $dateItem === $selectedDate; ?>
                        <a
                            class="schedule-chip <?= $isActiveDate ? 'active' : '' ?>"
                            href="<?= BASE_URL ?>cinemas.php?date=<?= e($dateItem) ?>&location_id=<?= (int) $selectedLocationId ?>"
                        >
                            <strong><?= date('d/m', strtotime($dateItem)) ?></strong>
                            <span><?= date('D', strtotime($dateItem)) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="schedule-filter-group">
                <div class="schedule-filter-label">Cinema</div>
                <div class="schedule-chip-row">
                    <?php foreach ($locations as $location): ?>
                        <?php
                            $locationId = (int) $location['location_id'];
                            $isActive = $locationId === $selectedLocationId;
                            $showCount = (int) ($locationCountMap[$locationId] ?? 0);
                        ?>
                        <a
                            class="schedule-chip <?= $isActive ? 'active' : '' ?> <?= $showCount === 0 ? 'muted' : '' ?>"
                            href="<?= BASE_URL ?>cinemas.php?date=<?= e($selectedDate) ?>&location_id=<?= $locationId ?>"
                        >
                            <strong><?= e($location['name']) ?></strong>
                            <span><?= $showCount > 0 ? $showCount . ' showtimes' : 'No showtimes' ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($selectedLocation): ?>
            <div class="schedule-cinema-head">
                <div>
                    <h2><?= e($selectedLocation['name']) ?></h2>
                    <p>
                        <?= e($selectedLocation['address'] ?: 'Address updating') ?>
                        <?php if (!empty($selectedLocation['city'])): ?>
                            • <?= e($selectedLocation['city']) ?>
                        <?php endif; ?>
                        <?php if (!empty($selectedLocation['phone'])): ?>
                            • Hotline: <?= e($selectedLocation['phone']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="schedule-summary-badge">
                    <?= count($movieGroups) ?> movies
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($movieGroups)): ?>
            <div class="schedule-movie-list">
                <?php foreach ($movieGroups as $movie): ?>
                    <article class="schedule-movie-card">
                        <a class="schedule-movie-poster" href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movie['movie_id'] ?>">
                            <img src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/300x450?text=Movie') ?>" alt="<?= e($movie['title']) ?>">
                        </a>
                        <div class="schedule-movie-content">
                            <div class="schedule-movie-head">
                                <div>
                                    <h3><a href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movie['movie_id'] ?>"><?= e($movie['title']) ?></a></h3>
                                    <p>
                                        <?= e($movie['genres'] ?: 'Updating') ?>
                                        • <?= (int) $movie['duration_min'] ?> min
                                        <?php if (!empty($movie['language'])): ?>
                                            • <?= e($movie['language']) ?>
                                        <?php endif; ?>
                                        <?php if (!empty($movie['age_rating'])): ?>
                                            • <?= e($movie['age_rating']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <a class="schedule-detail-link" href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movie['movie_id'] ?>&date=<?= e($selectedDate) ?>&location_id=<?= (int) $selectedLocationId ?>#showtimes">Details</a>
                            </div>

                            <div class="schedule-times">
                                <?php foreach ($movie['showtimes'] as $show): ?>
                                    <a class="showtime-pill" href="<?= BASE_URL ?>select_seats.php?show_id=<?= (int) $show['show_id'] ?>">
                                        <strong><?= date('H:i', strtotime($show['start_at'])) ?></strong>
                                        <span><?= e($show['room_name']) ?> • <?= e($show['screen_format'] ?: '2D') ?><?= !empty($show['subtitle_type']) ? ' • ' . e($show['subtitle_type']) : '' ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="schedule-empty">
                No movies have scheduled showtimes for this cinema on <?= date('d/m/Y', strtotime($selectedDate)) ?>.
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
    require_once __DIR__ . '/includes/helpers.php';

    $conn = db_connect();

    $keyword = trim((string) get('keyword'));
    $status  = trim((string) get('status'));

    $where  = [];
    $params = [];
    $types  = '';

    if ($keyword !== '') {
    $where[]   = "m.title LIKE ?";
    $params[]  = '%' . $keyword . '%';
    $types    .= 's';
    }

    if ($status === 'now_showing' || $status === 'coming_soon') {
    $where[]   = "m.status = ?";
    $params[]  = $status;
    $types    .= 's';
    }

    $whereSql = '';
    if (! empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $sql = "
    SELECT
        m.movie_id,
        m.title,
        m.synopsis,
        m.language,
        m.duration_min,
        m.poster_url,
        m.status,
        m.age_rating,
        m.release_date,
        m.created_at,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
    FROM movies m
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    $whereSql
    GROUP BY
        m.movie_id,
        m.title,
        m.synopsis,
        m.language,
        m.duration_min,
        m.poster_url,
        m.status,
        m.age_rating,
        m.release_date,
        m.created_at
    ORDER BY m.created_at DESC
";

    $stmt = $conn->prepare($sql);

    if ($stmt && ! empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $moviesResult = $stmt->get_result();
    } elseif ($stmt) {
    $stmt->execute();
    $moviesResult = $stmt->get_result();
    } else {
    $moviesResult = false;
    }

    require_once __DIR__ . '/includes/header.php';
?>

<section class="movies-page-wrap">
    <div class="movies-page-inner">
        <div class="movies-hero-top">
            <h1 class="movies-page-title">Movies</h1>
            <p class="movies-page-subtitle">
                Discover the latest movies playing in theaters now.
            </p>
        </div>

        <form method="GET" action="" class="movies-filter-bar">
            <div class="movies-search-box">
                <span class="movies-search-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M20 20L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>

                <input
                    type="text"
                    name="keyword"
                    class="movies-search-input"
                    placeholder="Search movies..."
                    value="<?php echo e($keyword) ?>"
                >
            </div>

            <div class="movies-status-box">
                <select name="status" class="movies-status-select">
                    <option value="">All Status</option>
                    <option value="now_showing" <?php echo $status === 'now_showing' ? 'selected' : '' ?>>Now Showing</option>
                    <option value="coming_soon" <?php echo $status === 'coming_soon' ? 'selected' : '' ?>>Coming Soon</option>
                </select>
            </div>

            <button type="submit" class="movies-submit">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M4 6H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M7 12H17" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <path d="M10 18H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
    <span>Filter</span>
</button>
        </form>

        <div class="movies-grid-dark">
            <?php if ($moviesResult && $moviesResult->num_rows > 0): ?>
                <?php while ($movie = $moviesResult->fetch_assoc()): ?>
                    <div class="movie-card-dark">
                        <a href="<?php echo BASE_URL ?>movie_detail.php?id=<?php echo (int) $movie['movie_id'] ?>" class="movie-thumb-wrap-dark">
                            <img
                                class="movie-thumb-dark"
                                src="<?php echo e($movie['poster_url'] ?: 'https://via.placeholder.com/400x600?text=Movie') ?>"
                                alt="<?php echo e($movie['title']) ?>"
                            >

                            <span class="movie-status-badge <?php echo $movie['status'] === 'coming_soon' ? 'coming' : 'showing' ?>">
                                <?php echo $movie['status'] === 'coming_soon' ? 'COMING SOON' : 'NOW SHOWING' ?>
                            </span>
                        </a>

                        <div class="movie-card-body-dark">
                            <div class="movie-info">
                                <h3><?php echo e($movie['title']) ?></h3>

                                <div class="movie-detail-lines">
                                    <p><strong>Genre:</strong> <?php echo e($movie['genres'] ?: 'Updating') ?></p>
                                    <p><strong>Duration:</strong> <?php echo (int) $movie['duration_min'] ?> min</p>
                                    <p><strong>Release Date:</strong> <?php echo ! empty($movie['release_date']) ? date('d-m-Y', strtotime($movie['release_date'])) : 'Updating' ?></p>
                                </div>
                            </div>

                            <div class="movie-card-bottom-dark">
                                <a class="movie-book-btn-dark" href="<?php echo BASE_URL ?>movie_detail.php?id=<?php echo (int) $movie['movie_id'] ?>">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">There is no movie data to display.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
    if ($stmt) {
    $stmt->close();
    }
require_once __DIR__ . '/includes/footer.php';
?>
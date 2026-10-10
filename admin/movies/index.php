<?php
$pageTitle = 'Manage Movies';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim((string) get('keyword'));
$status = trim((string) get('status'));
$language = trim((string) get('language'));

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = "(m.title LIKE ? OR m.synopsis LIKE ?)";
    $kw = '%' . $keyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $types .= 'ss';
}

if ($status !== '') {
    $where[] = "m.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($language !== '') {
    $where[] = "m.language LIKE ?";
    $params[] = '%' . $language . '%';
    $types .= 's';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT
        m.movie_id,
        m.title,
        m.language,
        m.duration_min,
        m.poster_url,
        m.status,
        m.age_rating,
        m.release_date,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
    FROM movies m
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    $whereSql
    GROUP BY
        m.movie_id,
        m.title,
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
        <h2>Movie List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/movies/create.php">+ Add Movie</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="text" name="keyword" placeholder="Search movie title / description..." value="<?= e($keyword) ?>">
            <input class="admin-input" type="text" name="language" placeholder="Language..." value="<?= e($language) ?>">

            <select class="admin-select" name="status">
                <option value="">-- All Statuses --</option>
                <option value="now_showing" <?= $status === 'now_showing' ? 'selected' : '' ?>>now_showing</option>
                <option value="coming_soon" <?= $status === 'coming_soon' ? 'selected' : '' ?>>coming_soon</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>inactive</option>
            </select>

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/movies/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Movie Title</th>
                    <th>Genre</th>
                    <th>Language</th>
                    <th>Duration</th>
                    <th>Age Rating</th>
                    <th>Release Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($movie = $result->fetch_assoc()): ?>
                    <?php
                    $badgeClass = 'coming';
                    if ($movie['status'] === 'now_showing') {
                        $badgeClass = 'showing';
                    } elseif ($movie['status'] === 'inactive') {
                        $badgeClass = 'coming';
                    }
                    ?>
                    <tr>
                        <td>
                            <img class="admin-thumb" src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/120x160?text=Movie') ?>" alt="">
                        </td>
                        <td><?= e($movie['title']) ?></td>
                        <td><?= e($movie['genres'] ?: 'No genre assigned') ?></td>
                        <td><?= e($movie['language']) ?></td>
                        <td><?= (int) $movie['duration_min'] ?> min</td>
                        <td><?= e($movie['age_rating'] ?: 'N/A') ?></td>
                        <td><?= !empty($movie['release_date']) ? date('d/m/Y', strtotime($movie['release_date'])) : 'N/A' ?></td>
                        <td>
                            <span class="admin-badge <?= $badgeClass ?>">
                                <?= e($movie['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/movies/edit.php?id=<?= (int) $movie['movie_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/movies/delete.php?id=<?= (int) $movie['movie_id'] ?>" onclick="return confirm('Delete this movie?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9"><div class="admin-empty">No matching movies found.</div></td>
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
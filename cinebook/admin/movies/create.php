<?php
$pageTitle = 'Create Movie';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

$genresResult = $conn->query("SELECT genre_id, name FROM genres ORDER BY name ASC");

$title = '';
$synopsis = '';
$language = '';
$durationMin = '';
$trailerUrl = '';
$posterUrl = '';
$status = 'coming_soon';
$ageRating = '';
$releaseDate = '';
$selectedGenres = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(post('title'));
    $synopsis = trim(post('synopsis'));
    $language = trim(post('language'));
    $durationMin = (int) post('duration_min');
    $trailerUrl = trim(post('trailer_url'));
    $posterUrl = trim(post('poster_url'));
    $status = trim(post('status'));
    $ageRating = trim(post('age_rating'));
    $releaseDate = trim(post('release_date'));

    $selectedGenres = post('genres', []);
    if (!is_array($selectedGenres)) {
        $selectedGenres = [];
    }
    $selectedGenres = array_map('intval', $selectedGenres);
    $selectedGenres = array_unique(array_filter($selectedGenres));

    if ($title === '' || $language === '' || $durationMin <= 0) {
        $error = 'Please enter the movie title, language, and duration.';
    } elseif (empty($selectedGenres)) {
        $error = 'Each movie must have at least one genre.';
    } else {
        $synopsis = $synopsis !== '' ? $synopsis : null;
        $trailerUrl = $trailerUrl !== '' ? $trailerUrl : null;
        $posterUrl = $posterUrl !== '' ? $posterUrl : null;
        $ageRating = $ageRating !== '' ? $ageRating : null;
        $releaseDate = $releaseDate !== '' ? $releaseDate : null;
        $status = $status !== '' ? $status : 'coming_soon';

        $sql = "
            INSERT INTO movies (
                title, synopsis, language, duration_min, trailer_url,
                poster_url, status, age_rating, release_date
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception('Prepare error: ' . $conn->error);
            }

            $stmt->bind_param(
                'sssisssss',
                $title,
                $synopsis,
                $language,
                $durationMin,
                $trailerUrl,
                $posterUrl,
                $status,
                $ageRating,
                $releaseDate
            );

            if (!$stmt->execute()) {
                throw new Exception('Movie insert error: ' . $stmt->error);
            }

            $movieId = (int) $stmt->insert_id;
            $stmt->close();

            $genreStmt = $conn->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");

            if (!$genreStmt) {
                throw new Exception('Unable to prepare the genre save statement.');
            }

            foreach ($selectedGenres as $genreId) {
                $genreStmt->bind_param('ii', $movieId, $genreId);

                if (!$genreStmt->execute()) {
                    throw new Exception('Unable to save genres for the movie.');
                }
            }

            $genreStmt->close();

            $conn->commit();
            set_flash_message('success', 'Movie added successfully.');
            redirect('admin/movies/index.php');
        } catch (Throwable $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Movie</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/movies/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Movie Title</label>
                <input class="admin-input" type="text" name="title" value="<?= e($title) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Language</label>
                <input class="admin-input" type="text" name="language" value="<?= e($language) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Duration (minutes)</label>
                <input class="admin-input" type="number" name="duration_min" min="1" value="<?= e((string) $durationMin) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Age Rating</label>
                <input class="admin-input" type="text" name="age_rating" value="<?= e($ageRating) ?>" placeholder="P, C13, C16...">
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <select class="admin-select" name="status">
                    <option value="coming_soon" <?= $status === 'coming_soon' ? 'selected' : '' ?>>coming_soon</option>
                    <option value="now_showing" <?= $status === 'now_showing' ? 'selected' : '' ?>>now_showing</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Release Date</label>
                <input class="admin-input" type="date" name="release_date" value="<?= e($releaseDate) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Genre <span style="color:#fca5a5;">*</span></label>

                <div style="display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:10px;">
                    <?php if ($genresResult && $genresResult->num_rows > 0): ?>
                        <?php while ($genre = $genresResult->fetch_assoc()): ?>
                            <label style="display:flex; align-items:center; gap:8px; background:#0f172a; padding:10px 12px; border:1px solid rgba(148,163,184,0.2); border-radius:10px;">
                                <input
                                    type="checkbox"
                                    name="genres[]"
                                    value="<?= (int) $genre['genre_id'] ?>"
                                    <?= in_array((int) $genre['genre_id'], $selectedGenres, true) ? 'checked' : '' ?>
                                >
                                <span><?= e($genre['name']) ?></span>
                            </label>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="color:#94a3b8;">There are no genres yet. Please create them in Manage Genres first.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Poster URL</label>
                <input class="admin-input" type="text" name="poster_url" value="<?= e($posterUrl) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Trailer URL</label>
                <input class="admin-input" type="text" name="trailer_url" value="<?= e($trailerUrl) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Description</label>
                <textarea class="admin-textarea" name="synopsis"><?= e($synopsis) ?></textarea>
            </div>
        </div>

        <button class="admin-btn" type="submit">Save Movie</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
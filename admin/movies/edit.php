<?php
$pageTitle = 'Edit Movie';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

if ($id <= 0) {
    set_flash_message('error', 'Invalid movie.');
    redirect('admin/movies/index.php');
}

$stmt = $conn->prepare("SELECT * FROM movies WHERE movie_id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
$stmt->close();

if (!$movie) {
    set_flash_message('error', 'Movie not found.');
    redirect('admin/movies/index.php');
}

$genresResult = $conn->query("SELECT genre_id, name FROM genres ORDER BY name ASC");

$currentGenres = [];
$currentGenreStmt = $conn->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = ?");
if ($currentGenreStmt) {
    $currentGenreStmt->bind_param('i', $id);
    $currentGenreStmt->execute();
    $currentGenreResult = $currentGenreStmt->get_result();

    while ($row = $currentGenreResult->fetch_assoc()) {
        $currentGenres[] = (int) $row['genre_id'];
    }

    $currentGenreStmt->close();
}

$title = $movie['title'] ?? '';
$synopsis = $movie['synopsis'] ?? '';
$language = $movie['language'] ?? '';
$durationMin = (int) ($movie['duration_min'] ?? 0);
$trailerUrl = $movie['trailer_url'] ?? '';
$posterUrl = $movie['poster_url'] ?? '';
$status = $movie['status'] ?? 'coming_soon';
$ageRating = $movie['age_rating'] ?? '';
$releaseDate = $movie['release_date'] ?? '';
$selectedGenres = $currentGenres;

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
        $error = 'Please enter all required data.';
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
            UPDATE movies
            SET title = ?, synopsis = ?, language = ?, duration_min = ?,
                trailer_url = ?, poster_url = ?, status = ?, age_rating = ?, release_date = ?
            WHERE movie_id = ?
        ";

        try {
            $conn->begin_transaction();

            $updateStmt = $conn->prepare($sql);

            if (!$updateStmt) {
                throw new Exception('Prepare error: ' . $conn->error);
            }

            $updateStmt->bind_param(
                'sssisssssi',
                $title,
                $synopsis,
                $language,
                $durationMin,
                $trailerUrl,
                $posterUrl,
                $status,
                $ageRating,
                $releaseDate,
                $id
            );

            if (!$updateStmt->execute()) {
                throw new Exception('Unable to update the movie.');
            }

            $updateStmt->close();

            $deleteGenreStmt = $conn->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
            if (!$deleteGenreStmt) {
                throw new Exception('Unable to delete the old genres.');
            }

            $deleteGenreStmt->bind_param('i', $id);

            if (!$deleteGenreStmt->execute()) {
                throw new Exception('Unable to delete the old genres.');
            }

            $deleteGenreStmt->close();

            $insertGenreStmt = $conn->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
            if (!$insertGenreStmt) {
                throw new Exception('Unable to add a new genre.');
            }

            foreach ($selectedGenres as $genreId) {
                $insertGenreStmt->bind_param('ii', $id, $genreId);

                if (!$insertGenreStmt->execute()) {
                    throw new Exception('Unable to save genres for the movie.');
                }
            }

            $insertGenreStmt->close();

            $conn->commit();
            set_flash_message('success', 'Movie updated successfully.');
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
        <h2>Edit Movie</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/movies/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
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
                <input class="admin-input" type="number" name="duration_min" min="1" value="<?= (int) $durationMin ?>" required>
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
                <input class="admin-input" type="date" name="release_date" value="<?= e($releaseDate ?: '') ?>">
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

        <button class="admin-btn" type="submit">Update Movie</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
<?php
$pageTitle = 'Manage Genres';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim(get('keyword'));

$whereSql = '';
$params = [];
$types = '';

if ($keyword !== '') {
    $whereSql = 'WHERE g.name LIKE ?';
    $params[] = '%' . $keyword . '%';
    $types .= 's';
}

$sql = "
    SELECT
        g.genre_id,
        g.name,
        COUNT(mg.movie_id) AS movie_count
    FROM genres g
    LEFT JOIN movie_genres mg ON g.genre_id = mg.genre_id
    $whereSql
    GROUP BY g.genre_id, g.name
    ORDER BY g.name ASC
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
        <h2>Genre List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/genres/create.php">+ Add Genre</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input
                class="admin-input"
                type="text"
                name="keyword"
                placeholder="Search genre name..."
                value="<?= e($keyword) ?>"
            >

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/genres/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Genre Name</th>
                    <th>Assigned Movies</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($genre = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $genre['genre_id'] ?></td>
                        <td><?= e($genre['name']) ?></td>
                        <td><?= (int) $genre['movie_count'] ?></td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/genres/edit.php?id=<?= (int) $genre['genre_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/genres/delete.php?id=<?= (int) $genre['genre_id'] ?>" onclick="return confirm('Delete this genre?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">
                        <div class="admin-empty">No matching genres found.</div>
                    </td>
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
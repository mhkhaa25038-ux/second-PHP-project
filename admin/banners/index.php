<?php
$pageTitle = 'Manage Banners';
require_once '../../config/database.php';
require_once '../../includes/admin_layout_start.php';


$conn = db_connect();

$where = [];

if (!empty($_GET['keyword'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
    $where[] = "title LIKE '%$keyword%'";
}

if (!empty($_GET['status'])) {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $where[] = "status = '$status'";
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT * FROM banners $whereSQL ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$banners = [];
while ($row = mysqli_fetch_assoc($result)) {
    $banners[] = $row;
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Banner List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/banners/create.php">
            + Add Banner
        </a>
    </div>

    <form method="GET">
        <div class="admin-filter" style="display:flex; gap:16px; flex-wrap:wrap; align-items:center;">

            <input type="text" name="keyword" value="<?= $_GET['keyword'] ?? '' ?>" placeholder="Search title..."
                class="admin-input" style="flex:1; min-width:250px;">

            <select name="status" class="admin-select" style="width:200px;">
                <option value="">-- All Statuses --</option>
                <option value="active" <?= (($_GET['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= (($_GET['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>

            <button class="admin-btn">Search</button>
            <a href="index.php" class="admin-btn-outline">Reset</a>

        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($banners)): ?>
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td><?= $banner['banner_id'] ?></td>

                            <td><?= htmlspecialchars($banner['title']) ?></td>

                            <td>
                                <?php if (!empty($banner['image_url'])): ?>
                                    <img
                                        src="<?= BASE_URL ?>public/assets/uploads/banners/<?= rawurlencode($banner['image_url']) ?>">
                                <?php else: ?>
                                    <span>No image</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span
                                    class="status-badge <?= $banner['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= $banner['status'] ?>
                                </span>
                            </td>

                            <td>
                                <a class="admin-btn-outline" href="edit.php?id=<?= $banner['banner_id'] ?>">
                                    Edit
                                </a>

                                <a class="admin-btn-danger" onclick="return confirm('Delete this banner?')"
                                    href="delete.php?id=<?= $banner['banner_id'] ?>">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">None banner</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/admin_layout_end.php'; ?>

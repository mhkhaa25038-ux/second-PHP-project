<?php
$pageTitle = 'Edit Banner';
require_once '../../config/database.php';
require_once '../../includes/admin_layout_start.php';

$conn = db_connect();
$id = $_GET['id'];

$sql = "SELECT * FROM banners WHERE banner_id=$id";
$result = mysqli_query($conn, $sql);
$banner = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $link = $_POST['link_url'];
    $status = $_POST['status'];

    if (!empty($_FILES['image']['name'])) {

        $image = time() . "_" . $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];

        move_uploaded_file($tmp, __DIR__ . "/../../public/assets/uploads/banners/" . $image);

        $sql = "UPDATE banners 
            SET title='$title', image_url='$image', link_url='$link', status='$status'
            WHERE banner_id=$id";

    } else {

        $sql = "UPDATE banners 
            SET title='$title', link_url='$link', status='$status'
            WHERE banner_id=$id";
    }

    mysqli_query($conn, $sql);
    header("Location:index.php");
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit Banner</h2>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="admin-form-grid">

            <div class="admin-form-group">
                <label>Title</label>
                <input type="text" name="title" value="<?= $banner['title'] ?>" class="admin-input">
            </div>

            <div class="admin-form-group">
                <label>Current Image</label><br>
                <?php if (!empty($banner['image_url'])): ?>
                    <img src="<?= BASE_URL ?>public/uploads/banners/<?= $banner['image_url'] ?>"
                        style="width:150px; border-radius:10px;">
                <?php else: ?>
                    <span>No image</span>
                <?php endif; ?>
            </div>

            <div class="admin-form-group">
                <label>Change Image</label>
                <input type="file" name="image" class="admin-input">
            </div>

            <div class="admin-form-group">
                <label>Link</label>
                <input type="text" name="link_url" value="<?= $banner['link_url'] ?>" class="admin-input">
            </div>

            <div class="admin-form-group">
                <label>Status</label>
                <select name="status" class="admin-select">
                    <option value="active" <?= $banner['status'] == "active" ? "selected" : "" ?>>Active</option>
                    <option value="inactive" <?= $banner['status'] == "inactive" ? "selected" : "" ?>>Inactive</option>
                </select>
            </div>

        </div>

        <button type="submit" class="admin-btn">Update</button>
    </form>
</div>

<?php require_once '../../includes/admin_layout_end.php'; ?>
<?php
$pageTitle = 'Create Banner';
require_once '../../includes/helpers.php';
require_once '../../includes/admin_layout_start.php';
$conn = db_connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $link = $_POST['link'];
    $status = $_POST['status'];

    if (!empty($_FILES['image']['name'])) {

        $imageName = time() . "_" . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $_FILES['image']['name']);

        $tmp = $_FILES['image']['tmp_name'];

        $uploadDir = __DIR__ . "/../../public/assets/uploads/banners/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadPath = $uploadDir . $imageName;

        if (move_uploaded_file($tmp, $uploadPath)) {
            echo "Upload OK";
        } else {
            echo "Upload FAIL";
        }

    }

    $sql = "INSERT INTO banners (title,image_url,link_url,status,created_at)
            VALUES ('$title','$imageName','$link','$status',NOW())";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit;
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Banner</h2>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="admin-form-grid">

            <div class="admin-form-group">
                <label>Title</label>
                <input type="text" name="title" class="admin-input" required>
            </div>

            <div class="admin-form-group">
                <label>Banner Image</label>
                <input type="file" name="image" class="admin-input" required>
            </div>

            <div class="admin-form-group">
                <label>Link URL</label>
                <input type="text" name="link" class="admin-input">
            </div>

            <div class="admin-form-group">
                <label>Status</label>
                <select name="status" class="admin-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

        </div>

        <button type="submit" class="admin-btn">Create Banner</button>
    </form>
</div>

<?php require_once '../../includes/admin_layout_end.php'; ?>
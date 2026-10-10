<?php
$pageTitle = 'Edit User';
require_once '../../config/database.php';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = $_GET['id'];

$sql = "SELECT * FROM users WHERE user_id=$id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $sql = "UPDATE users SET
        name='$name',
        email='$email',
        role='$role'
        WHERE user_id=$id";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit;
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit User</h2>
    </div>

    <form method="POST">
        <div class="admin-form-grid">

            <div class="admin-form-group">
                <label class="admin-label">Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="admin-input"
                    required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="admin-input"
                    required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Role</label>
                <select name="role" class="admin-select">
                    <option value="user" <?= $user['role'] == "user" ? "selected" : "" ?>>User</option>
                    <option value="admin" <?= $user['role'] == "admin" ? "selected" : "" ?>>Admin</option>
                </select>
            </div>

        </div>

        <button type="submit" class="admin-btn">
            Update
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
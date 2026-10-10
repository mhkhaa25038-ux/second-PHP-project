<?php
$pageTitle = 'Create User';
require_once '../../config/database.php';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (name,email,password_hash,role)
            VALUES ('$name','$email','$password','$role')";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit;
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Add User</h2>
    </div>

    <form method="POST">
        <div class="admin-form-grid">

            <div class="admin-form-group">
                <label class="admin-label">Name</label>
                <input type="text" name="name" class="admin-input" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Email</label>
                <input type="email" name="email" class="admin-input" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Password</label>
                <input type="password" name="password" class="admin-input" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Role</label>
                <select name="role" class="admin-select">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

        </div>

        <button type="submit" class="admin-btn">
            Create User
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>

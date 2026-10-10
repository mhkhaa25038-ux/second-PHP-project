<?php
$pageTitle = 'Manage Users';
require_once '../../config/database.php';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = $_GET['keyword'] ?? '';
$role = $_GET['role'] ?? '';

$sql = "SELECT * FROM users WHERE 1";

if (!empty($keyword)) {
    $keyword = mysqli_real_escape_string($conn, $keyword);
    $sql .= " AND (name LIKE '%$keyword%' OR email LIKE '%$keyword%')";
}

if (!empty($role)) {
    $role = mysqli_real_escape_string($conn, $role);
    $sql .= " AND role = '$role'";
}

$sql .= " ORDER BY user_id DESC";
$result = mysqli_query($conn, $sql);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>User List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/users/create.php">
            + Add User
        </a>
    </div>

    <form method="GET">
        <div class="admin-filter" style="display:flex; gap:16px; flex-wrap:wrap; align-items:center;">

            <input type="text" name="keyword" value="<?= $_GET['keyword'] ?? '' ?>"
                placeholder="Search by name or email..." class="admin-input" style="flex:1; min-width:250px;">

            <select name="role" class="admin-select" style="width:200px;">
                <option value="">-- All --</option>
                <option value="admin" <?= (($_GET['role'] ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
                <option value="user" <?= (($_GET['role'] ?? '') == 'user') ? 'selected' : '' ?>>User</option>
            </select>

            <button class="admin-btn">Search</button>
            <a href="index.php" class="admin-btn-outline">Reset</a>

        </div>
    </form> <br>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['user_id'] ?></td>

                            <td><?= htmlspecialchars($u['name']) ?></td>

                            <td><?= htmlspecialchars($u['email']) ?></td>

                            <td>
                                <span class="role-badge <?= $u['role'] ?>">
                                    <?= $u['role'] ?>
                                </span>
                            </td>

                            <td>
                                <a class="admin-btn-outline" href="edit.php?id=<?= $u['user_id'] ?>">
                                    Edit
                                </a>

                                <a class="admin-btn-danger" onclick="return confirm('Delete this user?')"
                                    href="delete.php?id=<?= $u['user_id'] ?>">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>

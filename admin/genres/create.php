<?php
$pageTitle = 'Create Genre';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post('name'));

    if ($name === '') {
        $error = 'Please enter the genre name.';
    } else {
        $sql = "INSERT INTO genres (name) VALUES (?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('s', $name);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Genre added successfully.');
                redirect('admin/genres/index.php');
            } else {
                $error = 'Unable to add the genre. This name may already exist.';
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Genre</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/genres/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group full">
                <label class="admin-label">Genre Name</label>
                <input class="admin-input" type="text" name="name" value="<?= e(old('name')) ?>" required>
            </div>
        </div>

        <button class="admin-btn" type="submit">Save Genre</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
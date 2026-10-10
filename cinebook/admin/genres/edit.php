<?php
$pageTitle = 'Edit Genre';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

$stmt = $conn->prepare("SELECT * FROM genres WHERE genre_id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$genre = $result->fetch_assoc();
$stmt->close();

if (!$genre) {
    set_flash_message('error', 'Genre not found.');
    redirect('admin/genres/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post('name'));

    if ($name === '') {
        $error = 'Please enter the genre name.';
    } else {
        $sql = "UPDATE genres SET name = ? WHERE genre_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('si', $name, $id);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Genre updated successfully.');
                redirect('admin/genres/index.php');
            } else {
                $error = 'Unable to update the genre. This name may already exist.';
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit Genre</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/genres/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group full">
                <label class="admin-label">Genre Name</label>
                <input class="admin-input" type="text" name="name" value="<?= e(old('name', $genre['name'])) ?>" required>
            </div>
        </div>

        <button class="admin-btn" type="submit">Update Genre</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
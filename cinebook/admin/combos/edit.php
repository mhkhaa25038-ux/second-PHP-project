<?php
$pageTitle = 'Edit Combo';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

$stmt = $conn->prepare('SELECT * FROM combo_products WHERE combo_id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$combo = $result->fetch_assoc();
$stmt->close();

if (!$combo) {
    set_flash_message('error', 'Combo not found.');
    redirect('admin/combos/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post('name'));
    $description = trim(post('description'));
    $price = (float) post('price');
    $image = trim(post('image'));
    $status = trim(post('status')) ?: 'active';

    if ($name === '' || $price <= 0) {
        $error = 'Please enter a combo name and a valid price.';
    } else {
        $description = $description !== '' ? $description : null;
        $image = $image !== '' ? $image : null;

        $sql = 'UPDATE combo_products SET name = ?, description = ?, price = ?, image = ?, status = ? WHERE combo_id = ?';
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('ssdssi', $name, $description, $price, $image, $status, $id);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Combo updated successfully.');
                redirect('admin/combos/index.php');
            } else {
                $error = 'Unable to update combo: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit Combo</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/combos/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group full">
                <label class="admin-label">Combo Name</label>
                <input class="admin-input" type="text" name="name" value="<?= e(old('name', $combo['name'])) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Price</label>
                <input class="admin-input" type="number" name="price" min="1000" step="1000" value="<?= e(old('price', $combo['price'])) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <?php $currentStatus = old('status', $combo['status']); ?>
                <select class="admin-select" name="status">
                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Combo Image (URL)</label>
                <input class="admin-input" type="text" name="image" value="<?= e(old('image', $combo['image'] ?? '')) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Description</label>
                <textarea class="admin-textarea" name="description"><?= e(old('description', $combo['description'] ?? '')) ?></textarea>
            </div>
        </div>

        <button class="admin-btn" type="submit">Update combo</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
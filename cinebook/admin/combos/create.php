<?php
$pageTitle = 'Create Combo';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

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

        $sql = 'INSERT INTO combo_products (name, description, price, image, status) VALUES (?, ?, ?, ?, ?)';
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('ssdss', $name, $description, $price, $image, $status);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Combo added successfully.');
                redirect('admin/combos/index.php');
            } else {
                $error = 'Unable to add combo: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Combo</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/combos/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group full">
                <label class="admin-label">Combo Name</label>
                <input class="admin-input" type="text" name="name" value="<?= e(old('name')) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Price</label>
                <input class="admin-input" type="number" name="price" min="1000" step="1000" value="<?= e(old('price')) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <select class="admin-select" name="status">
                    <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Combo Image (URL)</label>
                <input class="admin-input" type="text" name="image" value="<?= e(old('image')) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Description</label>
                <textarea class="admin-textarea" name="description"><?= e(old('description')) ?></textarea>
            </div>
        </div>

        <button class="admin-btn" type="submit">Save combo</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
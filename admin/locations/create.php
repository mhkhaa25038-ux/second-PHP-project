<?php
$pageTitle = 'Create Cinema Branch';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post('name'));
    $address = trim(post('address'));
    $city = trim(post('city'));
    $phone = trim(post('phone'));
    $status = trim(post('status')) ?: 'active';

    if ($name === '' || $address === '' || $city === '') {
        $error = 'Please enter the cinema name, address, and city.';
    } else {
        $phone = $phone !== '' ? $phone : null;

        $sql = "INSERT INTO locations (name, address, city, phone, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('sssss', $name, $address, $city, $phone, $status);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Cinema / branch added successfully.');
                redirect('admin/locations/index.php');
            } else {
                $error = 'Unable to add cinema / branch: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Cinema Branch</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/locations/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Cinema / Branch Name</label>
                <input class="admin-input" type="text" name="name" value="<?= e(old('name')) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">City</label>
                <input class="admin-input" type="text" name="city" value="<?= e(old('city')) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Phone Number</label>
                <input class="admin-input" type="text" name="phone" value="<?= e(old('phone')) ?>">
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <select class="admin-select" name="status">
                    <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Address</label>
                <input class="admin-input" type="text" name="address" value="<?= e(old('address')) ?>" required>
            </div>
        </div>

        <button class="admin-btn" type="submit">Save Cinema / Branch</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
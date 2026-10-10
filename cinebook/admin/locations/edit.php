<?php
$pageTitle = 'Edit Cinema Branch';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

$stmt = $conn->prepare("SELECT * FROM locations WHERE location_id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$location = $result->fetch_assoc();
$stmt->close();

if (!$location) {
    set_flash_message('error', 'Cinema / branch not found.');
    redirect('admin/locations/index.php');
}

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

        $sql = "UPDATE locations SET name = ?, address = ?, city = ?, phone = ?, status = ? WHERE location_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('sssssi', $name, $address, $city, $phone, $status, $id);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Cinema / branch updated successfully.');
                redirect('admin/locations/index.php');
            } else {
                $error = 'Unable to update cinema / branch: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit Cinema Branch</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/locations/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Cinema / Branch Name</label>
                <input class="admin-input" type="text" name="name" value="<?= e(old('name', $location['name'])) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">City</label>
                <input class="admin-input" type="text" name="city" value="<?= e(old('city', $location['city'])) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Phone Number</label>
                <input class="admin-input" type="text" name="phone" value="<?= e(old('phone', $location['phone'] ?? '')) ?>">
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Status</label>
                <?php $currentStatus = old('status', $location['status']); ?>
                <select class="admin-select" name="status">
                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Address</label>
                <input class="admin-input" type="text" name="address" value="<?= e(old('address', $location['address'])) ?>" required>
            </div>
        </div>

        <button class="admin-btn" type="submit">Update Cinema / Branch</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
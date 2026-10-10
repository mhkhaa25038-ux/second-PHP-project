<?php
$pageTitle = 'Edit Seat Type';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

if ($id <= 0) {
    set_flash_message('error', 'Invalid seat type.');
    redirect('admin/seat_categories/index.php');
}

$stmt = $conn->prepare("SELECT * FROM seat_categories WHERE category_id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

if (!$category) {
    set_flash_message('error', 'Seat type not found.');
    redirect('admin/seat_categories/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post('name'));
    $priceMultiplier = (float) post('price_multiplier');

    if ($name === '') {
        $error = 'Please enter the seat type name.';
    } elseif ($priceMultiplier <= 0) {
        $error = 'Price multiplier must be greater than 0.';
    } else {
        $sql = "UPDATE seat_categories SET name = ?, price_multiplier = ? WHERE category_id = ?";
        $updateStmt = $conn->prepare($sql);

        if (!$updateStmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $updateStmt->bind_param('sdi', $name, $priceMultiplier, $id);

            if ($updateStmt->execute()) {
                $updateStmt->close();
                set_flash_message('success', 'Seat type updated successfully.');
                redirect('admin/seat_categories/index.php');
            } else {
                $error = 'Seat type update error: ' . $updateStmt->error;
            }

            $updateStmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit Seat Type</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seat_categories/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Seat Type Name</label>
                <input class="admin-input" type="text" name="name" value="<?= e($category['name']) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Price Multiplier</label>
                <input class="admin-input" type="number" name="price_multiplier" min="0.01" step="0.01" value="<?= e($category['price_multiplier']) ?>" required>
            </div>
        </div>

        <button class="admin-btn" type="submit">Update Seat Type</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
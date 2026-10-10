<?php
$pageTitle = 'Create Seat Type';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(post('name'));
    $priceMultiplier = (float) post('price_multiplier');

    if ($name === '') {
        $error = 'Please enter the seat type name.';
    } elseif ($priceMultiplier <= 0) {
        $error = 'Price multiplier must be greater than 0.';
    } else {
        $sql = "INSERT INTO seat_categories (name, price_multiplier) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Prepare error: ' . $conn->error;
        } else {
            $stmt->bind_param('sd', $name, $priceMultiplier);

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Seat type added successfully.');
                redirect('admin/seat_categories/index.php');
            } else {
                $error = 'Seat type insert error: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Create Seat Type</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seat_categories/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Seat Type Name</label>
                <input class="admin-input" type="text" name="name" placeholder="Standard / VIP / Couple..." required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Price Multiplier</label>
                <input class="admin-input" type="number" name="price_multiplier" min="0.01" step="0.01" placeholder="1.00" required>
            </div>
        </div>

        <button class="admin-btn" type="submit">Save Seat Type</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
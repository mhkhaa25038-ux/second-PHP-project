<?php
$pageTitle = 'Manage Seat Types';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$keyword = trim(get('keyword'));
$priceMultiplier = trim(get('price_multiplier'));

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = "name LIKE ?";
    $params[] = '%' . $keyword . '%';
    $types .= 's';
}

if ($priceMultiplier !== '' && is_numeric($priceMultiplier)) {
    $where[] = "price_multiplier = ?";
    $params[] = (float) $priceMultiplier;
    $types .= 'd';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT category_id, name, price_multiplier
    FROM seat_categories
    $whereSql
    ORDER BY category_id ASC
";

$stmt = $conn->prepare($sql);
$result = false;

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Seat Type List</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/seat_categories/create.php">+ Add Seat Type</a>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="text" name="keyword" placeholder="Search seat type name..." value="<?= e($keyword) ?>">
            <input class="admin-input" type="number" step="0.01" name="price_multiplier" placeholder="Price Multiplier..." value="<?= e($priceMultiplier) ?>">

            <button class="admin-btn" type="submit">Search</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seat_categories/index.php">Reset</a>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Seat Type Name</th>
                    <th>Price Multiplier</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($category = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $category['category_id'] ?></td>
                        <td><?= e($category['name']) ?></td>
                        <td><?= number_format((float) $category['price_multiplier'], 2) ?></td>
                        <td>
                            <?php
                            $multiplier = (float) $category['price_multiplier'];
                            if ($multiplier == 1.00) {
                                echo 'Base Price';
                            } elseif ($multiplier > 1.00) {
                                echo 'Above Base Price';
                            } else {
                                echo 'Below Base Price';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seat_categories/edit.php?id=<?= (int) $category['category_id'] ?>">Edit</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/seat_categories/delete.php?id=<?= (int) $category['category_id'] ?>" onclick="return confirm('Delete this seat type?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5"><div class="admin-empty">No matching seat types found.</div></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
if ($stmt) {
    $stmt->close();
}
require_once __DIR__ . '/../../includes/admin_layout_end.php';
?>
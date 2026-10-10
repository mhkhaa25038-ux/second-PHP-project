<?php
$pageTitle = 'Edit Seat';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

$rooms = $conn->query("
    SELECT r.room_id, r.room_name, l.name AS location_name
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY l.name ASC, r.room_name ASC
");

$categories = $conn->query("SELECT category_id, name FROM seat_categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = (int) post('room_id');
    $categoryId = (int) post('category_id');
    $seatNumber = trim(post('seat_number'));
    $seatRow = trim(post('seat_row'));
    $isAisle = isset($_POST['is_aisle']) ? 1 : 0;
    $isAccessible = isset($_POST['is_accessible']) ? 1 : 0;

    if ($roomId <= 0 || $categoryId <= 0 || $seatNumber === '' || $seatRow === '') {
        $error = 'Please enter all required data.';
    } else {
        $sql = "
            INSERT INTO seats (room_id, category_id, seat_number, seat_row, is_aisle, is_accessible)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iissii', $roomId, $categoryId, $seatNumber, $seatRow, $isAisle, $isAccessible);

        if ($stmt->execute()) {
            $stmt->close();
            set_flash_message('success', 'Seat added successfully.');
            redirect('admin/seats/index.php');
        } else {
            $error = 'Unable to add the seat. It may already exist in the room.';
        }

        $stmt->close();
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Edit Seat</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seats/index.php">Back</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Room</label>
                <select class="admin-select" name="room_id" required>
                    <option value="">-- Select Room --</option>
                    <?php if ($rooms && $rooms->num_rows > 0): ?>
                        <?php while ($room = $rooms->fetch_assoc()): ?>
                            <option value="<?= (int) $room['room_id'] ?>"><?= e($room['location_name'] . ' - ' . $room['room_name']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Seat Type</label>
                <select class="admin-select" name="category_id" required>
                    <option value="">-- Select Seat Type --</option>
                    <?php if ($categories && $categories->num_rows > 0): ?>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <option value="<?= (int) $category['category_id'] ?>"><?= e($category['name']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Seat Row</label>
                <input class="admin-input" type="text" name="seat_row" placeholder="A, B, C..." required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Seat Number</label>
                <input class="admin-input" type="text" name="seat_number" placeholder="1, 2, 3..." required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">
                    <input type="checkbox" name="is_aisle"> Seat near aisle
                </label>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">
                    <input type="checkbox" name="is_accessible"> Accessible seat
                </label>
            </div>
        </div>

        <button class="admin-btn" type="submit">Save Seat</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
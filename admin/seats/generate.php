<?php
$pageTitle = 'Generate Seat Layout in Bulk';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

$rooms = $conn->query("SELECT r.room_id, r.room_name, l.name AS location_name FROM rooms r INNER JOIN locations l ON r.location_id = l.location_id ORDER BY l.name ASC, r.room_name ASC");
$categories = $conn->query("SELECT category_id, name, price_multiplier FROM seat_categories ORDER BY price_multiplier ASC, name ASC");

$categoryOptions = [];
if ($categories && $categories->num_rows > 0) {
    while ($category = $categories->fetch_assoc()) {
        $categoryOptions[] = $category;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = (int) post('room_id');
    $rowStart = strtoupper(trim(post('row_start')));
    $rowEnd = strtoupper(trim(post('row_end')));
    $seatsPerRow = (int) post('seats_per_row');
    $standardCategoryId = (int) post('standard_category_id');
    $vipCategoryId = (int) post('vip_category_id');
    $vipRowsInput = strtoupper(trim(post('vip_rows')));
    $accessibleSeatsInput = strtoupper(trim(post('accessible_seats')));
    $aisleAfterInput = trim(post('aisle_after'));
    $replaceExisting = post('replace_existing', '0') === '1';

    if ($roomId <= 0 || $rowStart === '' || $rowEnd === '' || $seatsPerRow <= 0 || $standardCategoryId <= 0) {
        $error = 'Please enter the room, row range, seats per row, and default seat type.';
    } elseif (!preg_match('/^[A-Z]$/', $rowStart) || !preg_match('/^[A-Z]$/', $rowEnd) || ord($rowEnd) < ord($rowStart)) {
        $error = 'Invalid seat row range. Only A to Z is supported.';
    } else {
        $aisleAfter = array_values(array_unique(array_filter(array_map('intval', preg_split('/\s*,\s*/', $aisleAfterInput, -1, PREG_SPLIT_NO_EMPTY)))));
        $vipRows = array_values(array_unique(array_filter(array_map('trim', preg_split('/\s*,\s*/', $vipRowsInput, -1, PREG_SPLIT_NO_EMPTY)))));
        $accessibleSeats = array_values(array_unique(array_filter(array_map('trim', preg_split('/\s*,\s*/', $accessibleSeatsInput, -1, PREG_SPLIT_NO_EMPTY)))));

        try {
            $conn->begin_transaction();

            if ($replaceExisting) {
                $deleteShowSeatsSql = "
                    DELETE ss
                    FROM show_seats ss
                    INNER JOIN seats se ON ss.seat_id = se.seat_id
                    LEFT JOIN booking_seats bs ON ss.show_seat_id = bs.show_seat_id
                    WHERE se.room_id = ?
                      AND bs.booking_seat_id IS NULL
                ";
                $deleteShowSeatsStmt = $conn->prepare($deleteShowSeatsSql);
                $deleteShowSeatsStmt->bind_param('i', $roomId);
                $deleteShowSeatsStmt->execute();
                $deleteShowSeatsStmt->close();

                $deleteSeatsSql = "
                    DELETE se
                    FROM seats se
                    LEFT JOIN show_seats ss ON se.seat_id = ss.seat_id
                    WHERE se.room_id = ?
                      AND ss.show_seat_id IS NULL
                ";
                $deleteSeatsStmt = $conn->prepare($deleteSeatsSql);
                $deleteSeatsStmt->bind_param('i', $roomId);
                $deleteSeatsStmt->execute();
                $deleteSeatsStmt->close();
            }

            $insertSql = "
                INSERT INTO seats (room_id, category_id, seat_number, seat_row, is_aisle, is_accessible)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    category_id = VALUES(category_id),
                    is_aisle = VALUES(is_aisle),
                    is_accessible = VALUES(is_accessible)
            ";
            $insertStmt = $conn->prepare($insertSql);
            if (!$insertStmt) {
                throw new Exception('Unable to prepare the seat generation query.');
            }

            $createdCount = 0;
            for ($row = ord($rowStart); $row <= ord($rowEnd); $row++) {
                $rowLabel = chr($row);
                for ($seatNumber = 1; $seatNumber <= $seatsPerRow; $seatNumber++) {
                    $seatLabel = $rowLabel . $seatNumber;
                    $categoryId = $standardCategoryId;
                    if ($vipCategoryId > 0 && in_array($rowLabel, $vipRows, true)) {
                        $categoryId = $vipCategoryId;
                    }

                    $isAccessible = in_array($seatLabel, $accessibleSeats, true) ? 1 : 0;
                    $isAisle = in_array($seatNumber, $aisleAfter, true) || in_array($seatNumber - 1, $aisleAfter, true) ? 1 : 0;
                    $seatNumberText = (string) $seatNumber;

                    $insertStmt->bind_param('iissii', $roomId, $categoryId, $seatNumberText, $rowLabel, $isAisle, $isAccessible);
                    if (!$insertStmt->execute()) {
                        throw new Exception('Unable to generate seat ' . $seatLabel . '.');
                    }
                    $createdCount++;
                }
            }
            $insertStmt->close();

            $capacitySql = "UPDATE rooms SET capacity = (SELECT COUNT(*) FROM seats WHERE room_id = ?) WHERE room_id = ?";
            $capacityStmt = $conn->prepare($capacitySql);
            $capacityStmt->bind_param('ii', $roomId, $roomId);
            $capacityStmt->execute();
            $capacityStmt->close();

            $showtimeSql = "SELECT show_id, room_id, base_price FROM showtimes WHERE room_id = ?";
            $showtimeStmt = $conn->prepare($showtimeSql);
            $showtimeStmt->bind_param('i', $roomId);
            $showtimeStmt->execute();
            $showtimeResult = $showtimeStmt->get_result();
            while ($showtime = $showtimeResult->fetch_assoc()) {
                if (!sync_show_seats($conn, (int) $showtime['show_id'], (int) $showtime['room_id'], (float) $showtime['base_price'], false)) {
                    throw new Exception('Unable to sync seats for showtime #' . (int) $showtime['show_id']);
                }
            }
            $showtimeStmt->close();

            $conn->commit();
            set_flash_message('success', 'Created / updated ' . $createdCount . ' seats for the room.');
            redirect('admin/seats/index.php?room_id=' . $roomId);
        } catch (Throwable $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>
<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Generate Seat Layout in Bulk</h2>
            <p class="admin-card-subtitle">Generate an entire room layout in one input, then let the system sync it with existing showtimes automatically.</p>
        </div>
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
                            <option value="<?= (int) $room['room_id'] ?>" <?= (int) old('room_id') === (int) $room['room_id'] ? 'selected' : '' ?>>
                                <?= e($room['location_name'] . ' - ' . $room['room_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">From Row</label>
                <input class="admin-input" type="text" name="row_start" value="<?= e(old('row_start', 'A')) ?>" maxlength="1" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">To Row</label>
                <input class="admin-input" type="text" name="row_end" value="<?= e(old('row_end', 'H')) ?>" maxlength="1" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Seats Per Row</label>
                <input class="admin-input" type="number" name="seats_per_row" min="1" max="30" value="<?= e(old('seats_per_row', '12')) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Default Seat Type</label>
                <select class="admin-select" name="standard_category_id" required>
                    <option value="">-- Select Seat Type --</option>
                    <?php foreach ($categoryOptions as $category): ?>
                        <option value="<?= (int) $category['category_id'] ?>" <?= (int) old('standard_category_id') === (int) $category['category_id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?> (x<?= e((string) $category['price_multiplier']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Seat Type VIP</label>
                <select class="admin-select" name="vip_category_id">
                    <option value="0">-- Do not use VIP --</option>
                    <?php foreach ($categoryOptions as $category): ?>
                        <option value="<?= (int) $category['category_id'] ?>" <?= (int) old('vip_category_id') === (int) $category['category_id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?> (x<?= e((string) $category['price_multiplier']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">VIP Rows</label>
                <input class="admin-input" type="text" name="vip_rows" value="<?= e(old('vip_rows', 'F,G,H')) ?>" placeholder="Example: F,G,H">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Aisle after seat number</label>
                <input class="admin-input" type="text" name="aisle_after" value="<?= e(old('aisle_after', '4,8')) ?>" placeholder="Example: 4,8 to split into 4-4-4 blocks">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Accessible Seats</label>
                <input class="admin-input" type="text" name="accessible_seats" value="<?= e(old('accessible_seats', 'A1,A2')) ?>" placeholder="Example: A1,A2,H12">
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Replace Existing Layout</label>
                <select class="admin-select" name="replace_existing">
                    <option value="0" <?= old('replace_existing', '0') === '0' ? 'selected' : '' ?>>No, only add/update</option>
                    <option value="1" <?= old('replace_existing') === '1' ? 'selected' : '' ?>>Yes, delete old seats without bookings</option>
                </select>
            </div>
        </div>

        <div class="admin-form-note">Tip: configuring 12 seats per row with aisles after seats 4 and 8 creates a 4-4-4 layout close to a real cinema.</div>

        <button class="admin-btn" type="submit">Generate Seat Layout</button>
    </form>
</div>
<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>

<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$conn = db_connect();
cleanup_expired_show_seat_holds($conn);

$bookingSession = $_SESSION['booking'] ?? null;
if (!$bookingSession || empty($bookingSession['show_id'])) {
    set_flash_message('error', 'No booking data found. Please select your seats first.');
    redirect('movies.php');
}

$showId = (int) $bookingSession['show_id'];
$userId = (int) $_SESSION['user_id'];
$holdToken = get_booking_hold_token();

$showSql = "
    SELECT
        s.show_id,
        s.start_at,
        s.end_at,
        s.base_price,
        s.screen_format,
        s.subtitle_type,
        m.movie_id,
        m.title AS movie_title,
        m.poster_url,
        r.room_name,
        l.name AS location_name,
        l.address
    FROM showtimes s
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE s.show_id = ?
    LIMIT 1
";
$showStmt = $conn->prepare($showSql);
$showStmt->bind_param('i', $showId);
$showStmt->execute();
$showtime = $showStmt->get_result()->fetch_assoc();
$showStmt->close();

if (!$showtime) {
    unset($_SESSION['booking']);
    set_flash_message('error', 'Showtime does not exist.');
    redirect('movies.php');
}

$selectedSeatIds = get_held_show_seat_ids_by_token($conn, $showId, $holdToken);
if (empty($selectedSeatIds)) {
    unset($_SESSION['booking']);
    set_flash_message('error', 'The held seats have expired or were released. Please select again.');
    redirect('select_seats.php?show_id=' . $showId);
}

$placeholders = implode(',', array_fill(0, count($selectedSeatIds), '?'));
$types = 'is' . str_repeat('i', count($selectedSeatIds));
$params = array_merge([$showId, $holdToken], $selectedSeatIds);

$seatSql = "
    SELECT
        ss.show_seat_id,
        ss.final_price,
        ss.hold_until,
        se.seat_row,
        se.seat_number,
        sc.name AS category_name
    FROM show_seats ss
    INNER JOIN seats se ON ss.seat_id = se.seat_id
    INNER JOIN seat_categories sc ON se.category_id = sc.category_id
    WHERE ss.show_id = ?
      AND ss.status = 'held'
      AND ss.hold_token = ?
      AND ss.show_seat_id IN ($placeholders)
    ORDER BY se.seat_row ASC, CAST(se.seat_number AS UNSIGNED) ASC, se.seat_number ASC
";
$seatStmt = $conn->prepare($seatSql);
$bindParams = [];
$bindParams[] = &$types;
foreach ($params as $index => $value) {
    $bindParams[] = &$params[$index];
}
call_user_func_array([$seatStmt, 'bind_param'], $bindParams);
$seatStmt->execute();
$seatResult = $seatStmt->get_result();

$selectedSeats = [];
$seatSubtotal = 0;
$holdUntil = null;

while ($seat = $seatResult->fetch_assoc()) {
    $selectedSeats[] = [
        'show_seat_id'   => (int) $seat['show_seat_id'],
        'label'          => $seat['seat_row'] . $seat['seat_number'],
        'price'          => (float) $seat['final_price'],
        'category_name'  => $seat['category_name'],
    ];
    $seatSubtotal += (float) $seat['final_price'];
    $holdUntil = $seat['hold_until'];
}
$seatStmt->close();

if (count($selectedSeats) !== count($selectedSeatIds)) {
    unset($_SESSION['booking']);
    set_flash_message('error', 'Some seats have expired. Please select again.');
    redirect('select_seats.php?show_id=' . $showId);
}

$_SESSION['booking']['selected_seat_ids'] = $selectedSeatIds;
$_SESSION['booking']['selected_seats'] = $selectedSeats;
$_SESSION['booking']['seat_subtotal'] = $seatSubtotal;
$_SESSION['booking']['hold_until'] = $holdUntil;

/*
|--------------------------------------------------------------------------
| Combo query: ĐÃ THÊM cột image
|--------------------------------------------------------------------------
*/
$comboSql = "
    SELECT combo_id, name, description, price, image
    FROM combo_products
    WHERE status = 'active'
    ORDER BY combo_id DESC
";
$comboResult = $conn->query($comboSql);
$combos = [];
if ($comboResult && $comboResult->num_rows > 0) {
    while ($row = $comboResult->fetch_assoc()) {
        $combos[(int) $row['combo_id']] = $row;
    }
}

$paymentMethods = [
    'cash' => 'Cash',
    'bank_transfer' => 'Bank Transfer',
    'e_wallet' => 'E-Wallet',
];

$error = '';
$selectedComboQty = [];
$comboSubtotal = 0;
$grandTotal = $seatSubtotal;
$paymentMethod = 'cash';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cleanup_expired_show_seat_holds($conn);

    $paymentMethod = post('payment_method', 'cash');
    $postedComboQty = $_POST['combo_qty'] ?? [];

    if (!array_key_exists($paymentMethod, $paymentMethods)) {
        $error = 'Invalid payment method.';
    } else {
        foreach ($postedComboQty as $comboId => $qty) {
            $comboId = (int) $comboId;
            $qty = max(0, (int) $qty);

            if ($qty > 0 && isset($combos[$comboId])) {
                $selectedComboQty[$comboId] = $qty;
                $comboSubtotal += ((float) $combos[$comboId]['price'] * $qty);
            }
        }

        $grandTotal = $seatSubtotal + $comboSubtotal;

        if ($grandTotal <= 0) {
            $error = 'Invalid total amount.';
        } else {
            $seatIdsSql = implode(',', array_map('intval', $selectedSeatIds));

            try {
                $conn->begin_transaction();

                $verifySeatSql = "
                    SELECT
                        ss.show_seat_id,
                        ss.status,
                        ss.hold_token,
                        ss.final_price,
                        se.seat_row,
                        se.seat_number
                    FROM show_seats ss
                    INNER JOIN seats se ON ss.seat_id = se.seat_id
                    WHERE ss.show_id = ?
                      AND ss.show_seat_id IN ($seatIdsSql)
                    FOR UPDATE
                ";
                $verifyStmt = $conn->prepare($verifySeatSql);
                $verifyStmt->bind_param('i', $showId);
                $verifyStmt->execute();
                $verifyResult = $verifyStmt->get_result();

                $verifiedSeats = [];
                while ($seat = $verifyResult->fetch_assoc()) {
                    $verifiedSeats[(int) $seat['show_seat_id']] = $seat;
                }
                $verifyStmt->close();

                if (count($verifiedSeats) !== count($selectedSeatIds)) {
                    throw new Exception('One or more seats no longer exist.');
                }

                foreach ($selectedSeatIds as $seatId) {
                    if (!isset($verifiedSeats[$seatId])) {
                        throw new Exception('Invalid seat.');
                    }

                    $seat = $verifiedSeats[$seatId];
                    if (($seat['status'] ?? '') !== 'held' || ($seat['hold_token'] ?? '') !== $holdToken) {
                        $seatLabel = ($seat['seat_row'] ?? '') . ($seat['seat_number'] ?? '');
                        throw new Exception('Seat ' . $seatLabel . ' is no longer held by this booking session.');
                    }
                }

                $seatTotalFromDb = 0;
                foreach ($verifiedSeats as $seat) {
                    $seatTotalFromDb += (float) $seat['final_price'];
                }

                $comboTotalFromDb = 0;
                foreach ($selectedComboQty as $comboId => $qty) {
                    $comboTotalFromDb += ((float) $combos[$comboId]['price'] * $qty);
                }

                $subtotal = $seatTotalFromDb + $comboTotalFromDb;
                $discountTotal = 0;
                $total = $subtotal - $discountTotal;
                $bookingCode = generate_booking_code();
                $bookingStatus = 'confirmed';

                $insertBookingSql = "
                    INSERT INTO bookings (
                        user_id, show_id, booking_code, status,
                        hold_expires_at, subtotal, discount_total, total
                    )
                    VALUES (?, ?, ?, ?, NULL, ?, ?, ?)
                ";
                $bookingStmt = $conn->prepare($insertBookingSql);
                $bookingStmt->bind_param(
                    'iissddd',
                    $userId,
                    $showId,
                    $bookingCode,
                    $bookingStatus,
                    $subtotal,
                    $discountTotal,
                    $total
                );

                if (!$bookingStmt->execute()) {
                    throw new Exception('Unable to create the booking.');
                }

                $bookingId = (int) $bookingStmt->insert_id;
                $bookingStmt->close();

                $insertSeatSql = "INSERT INTO booking_seats (booking_id, show_seat_id, unit_price) VALUES (?, ?, ?)";
                $insertSeatStmt = $conn->prepare($insertSeatSql);
                foreach ($verifiedSeats as $showSeatId => $seat) {
                    $unitPrice = (float) $seat['final_price'];
                    $insertSeatStmt->bind_param('iid', $bookingId, $showSeatId, $unitPrice);
                    if (!$insertSeatStmt->execute()) {
                        throw new Exception('Unable to save seats to the booking.');
                    }
                }
                $insertSeatStmt->close();

                if (!empty($selectedComboQty)) {
                    $insertComboSql = "INSERT INTO booking_combos (booking_id, combo_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
                    $insertComboStmt = $conn->prepare($insertComboSql);
                    foreach ($selectedComboQty as $comboId => $qty) {
                        $unitPrice = (float) $combos[$comboId]['price'];
                        $insertComboStmt->bind_param('iiid', $bookingId, $comboId, $qty, $unitPrice);
                        if (!$insertComboStmt->execute()) {
                            throw new Exception('Unable to save combos to the booking.');
                        }
                    }
                    $insertComboStmt->close();
                }

                $paymentStatus = 'paid';
                $transactionCode = 'TXN' . date('YmdHis') . rand(100, 999);
                $paidAt = date('Y-m-d H:i:s');

                $insertPaymentSql = "
                    INSERT INTO payments (booking_id, method, amount, status, paid_at, transaction_code)
                    VALUES (?, ?, ?, ?, ?, ?)
                ";
                $paymentStmt = $conn->prepare($insertPaymentSql);
                $paymentStmt->bind_param('isdsss', $bookingId, $paymentMethod, $total, $paymentStatus, $paidAt, $transactionCode);
                if (!$paymentStmt->execute()) {
                    throw new Exception('Unable to save payment information.');
                }
                $paymentStmt->close();

                $updateSeatSql = "
                    UPDATE show_seats
                    SET status = 'booked', hold_until = NULL, hold_token = NULL
                    WHERE show_seat_id IN ($seatIdsSql)
                      AND hold_token = ?
                ";
                $updateSeatStmt = $conn->prepare($updateSeatSql);
                $updateSeatStmt->bind_param('s', $holdToken);
                if (!$updateSeatStmt->execute()) {
                    throw new Exception('Unable to update seat status.');
                }
                $updateSeatStmt->close();

                $conn->commit();
                unset($_SESSION['booking']);
                clear_booking_hold_token();
                redirect('success.php?booking_id=' . $bookingId);
            } catch (Throwable $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="checkout-page-wrap">
    <div class="checkout-page-inner">
        <div class="checkout-page-head">
            <div>
                <span class="movie-status-badge showing">CHECKOUT</span>
                <h1 class="checkout-page-title">Confirm Your Booking</h1>
                <p class="checkout-page-subtitle">Review your held seats, add combos if needed, and complete payment
                    before the countdown reaches 0.</p>
            </div>
        </div>

        <div class="checkout-layout-dark">
            <div class="checkout-left-dark">
                <div class="checkout-show-card-dark">
                    <div class="checkout-poster-dark">
                        <img src="<?= e($showtime['poster_url'] ?: 'https://via.placeholder.com/400x600?text=Movie') ?>"
                            alt="<?= e($showtime['movie_title']) ?>">
                    </div>
                    <div class="checkout-show-info-dark">
                        <h2><?= e($showtime['movie_title']) ?></h2>
                        <div class="checkout-show-meta-dark">
                            <span><?= e($showtime['location_name']) ?></span>
                            <span><?= e($showtime['room_name']) ?></span>
                            <span><?= date('d/m/Y H:i', strtotime($showtime['start_at'])) ?></span>
                            <span><?= e($showtime['screen_format'] ?: '2D') ?></span>
                            <span><?= e($showtime['subtitle_type'] ?: 'No subtitle') ?></span>
                        </div>
                        <div class="checkout-hold-box" data-hold-until="<?= e($holdUntil ?? '') ?>">
                            <span>Hold expires at</span>
                            <strong id="checkoutHoldTimer">--:--</strong>
                        </div>
                    </div>
                </div>

                <div class="checkout-block-dark">
                    <div class="checkout-block-head-dark">
                        <h3>Selected Seats</h3>
                    </div>
                    <div class="checkout-seat-list-dark">
                        <?php foreach ($selectedSeats as $seat): ?>
                            <div class="checkout-line-dark">
                                <div>
                                    <strong><?= e($seat['label']) ?></strong>
                                    <span><?= e($seat['category_name']) ?></span>
                                </div>
                                <strong><?= format_currency($seat['price']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="checkout-block-dark">
                    <div class="checkout-block-head-dark">
                        <h3>Combo Products</h3>
                    </div>

                    <?php if ($error !== ''): ?>
                        <div class="auth-error" style="margin-bottom:18px;"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" id="checkoutForm">
                        <?php if (!empty($combos)): ?>
                            <div class="checkout-combo-list-dark">
                                <?php foreach ($combos as $comboId => $combo): ?>
                                    <?php
                                    $qtyValue = (int) ($selectedComboQty[$comboId] ?? 0);

                                    if (!empty($combo['image'])) {
                                        if (filter_var($combo['image'], FILTER_VALIDATE_URL)) {
                                            $comboImage = $combo['image'];
                                        } else {
                                            $comboImage = BASE_URL . ltrim($combo['image'], '/');
                                        }
                                    } else {
                                        $comboImage = 'https://via.placeholder.com/140x100?text=Combo';
                                    }
                                    ?>
                                    <div class="checkout-combo-item-dark">
                                        <div class="checkout-combo-image-dark">
                                            <img src="<?= e($comboImage) ?>" alt="<?= e($combo['name']) ?>">
                                        </div>

                                        <div class="checkout-combo-info-dark">
                                            <h4><?= e($combo['name']) ?></h4>
                                            <p><?= e($combo['description'] ?? '') ?></p>
                                            <span><?= format_currency($combo['price']) ?></span>
                                        </div>

                                        <input type="number" name="combo_qty[<?= $comboId ?>]" min="0" value="<?= $qtyValue ?>"
                                            class="checkout-combo-qty-dark" data-price="<?= (float) $combo['price'] ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">There are currently no active combos.</div>
                        <?php endif; ?>

                        <div class="form-group" style="margin-top:20px;">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="movies-status-select" required>
                                <?php foreach ($paymentMethods as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= $paymentMethod === $value ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="checkout-right-dark">
                <div class="checkout-summary-card-dark">
                    <h3>Order Summary</h3>
                    <div class="checkout-summary-line-dark"><span>Seats</span><strong
                            id="seat-total-text"><?= format_currency($seatSubtotal) ?></strong></div>
                    <div class="checkout-summary-line-dark"><span>Combos</span><strong
                            id="combo-total-text"><?= format_currency($comboSubtotal) ?></strong></div>
                    <div class="checkout-summary-total-dark"><span>Total</span><strong
                            id="grand-total-text"><?= format_currency($grandTotal) ?></strong></div>
                    <div class="checkout-summary-actions-dark">
                        <a class="movie-detail-outline-btn"
                            href="<?= BASE_URL ?>select_seats.php?show_id=<?= (int) $showId ?>">Back</a>
                        <button type="submit" form="checkoutForm"
                            class="movie-book-btn-dark checkout-confirm-btn-dark">Confirm Booking</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const seatSubtotal = <?= json_encode((float) $seatSubtotal) ?>;
        const qtyInputs = document.querySelectorAll('.checkout-combo-qty-dark');
        const comboTotalText = document.getElementById('combo-total-text');
        const grandTotalText = document.getElementById('grand-total-text');
        const holdBox = document.querySelector('.checkout-hold-box');
        const holdTimer = document.getElementById('checkoutHoldTimer');

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US').format(amount) + ' VND';
        }

        function updateCheckoutTotals() {
            let comboTotal = 0;
            qtyInputs.forEach(function(input) {
                const qty = parseInt(input.value || 0, 10);
                const price = parseFloat(input.dataset.price || 0);
                comboTotal += qty * price;
            });
            comboTotalText.textContent = formatCurrency(comboTotal);
            grandTotalText.textContent = formatCurrency(seatSubtotal + comboTotal);
        }

        qtyInputs.forEach(function(input) {
            input.addEventListener('input', updateCheckoutTotals);
        });

        updateCheckoutTotals();

        if (holdBox && holdBox.dataset.holdUntil) {
            const holdUntil = new Date(holdBox.dataset.holdUntil.replace(' ', 'T')).getTime();
            const timer = setInterval(function() {
                const diff = holdUntil - Date.now();
                if (diff <= 0) {
                    clearInterval(timer);
                    holdTimer.textContent = '00:00';
                    window.location.href =
                        <?= json_encode(BASE_URL . 'select_seats.php?show_id=' . $showId) ?>;
                    return;
                }
                const totalSeconds = Math.floor(diff / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                holdTimer.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2,
                    '0');
            }, 1000);
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
require_once __DIR__ . '/includes/auth.php';

require_login();

$conn = db_connect();
$bookingId = (int) get('booking_id');

if ($bookingId <= 0) {
    set_flash_message('error', 'Booking could not be found.');
    redirect('movies.php');
}

$userId = (int) $_SESSION['user_id'];

$bookingSql = "
    SELECT
        b.booking_id,
        b.booking_code,
        b.status,
        b.subtotal,
        b.discount_total,
        b.total,
        b.created_at,
        m.title AS movie_title,
        m.poster_url,
        s.start_at,
        s.end_at,
        s.screen_format,
        s.subtitle_type,
        r.room_name,
        l.name AS location_name,
        l.address
    FROM bookings b
    INNER JOIN showtimes s ON b.show_id = s.show_id
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE b.booking_id = ?
      AND b.user_id = ?
    LIMIT 1
";
$bookingStmt = $conn->prepare($bookingSql);
$bookingStmt->bind_param('ii', $bookingId, $userId);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();
$booking = $bookingResult->fetch_assoc();
$bookingStmt->close();

if (!$booking) {
    set_flash_message('error', 'The booking does not exist or does not belong to this account.');
    redirect('movies.php');
}

$seatSql = "
    SELECT
        bs.unit_price,
        se.seat_row,
        se.seat_number,
        sc.name AS category_name
    FROM booking_seats bs
    INNER JOIN show_seats ss ON bs.show_seat_id = ss.show_seat_id
    INNER JOIN seats se ON ss.seat_id = se.seat_id
    INNER JOIN seat_categories sc ON se.category_id = sc.category_id
    WHERE bs.booking_id = ?
    ORDER BY se.seat_row ASC, CAST(se.seat_number AS UNSIGNED) ASC, se.seat_number ASC
";
$seatStmt = $conn->prepare($seatSql);
$seatStmt->bind_param('i', $bookingId);
$seatStmt->execute();
$seatResult = $seatStmt->get_result();

$comboSql = "
    SELECT
        bc.quantity,
        bc.unit_price,
        cp.name
    FROM booking_combos bc
    INNER JOIN combo_products cp ON bc.combo_id = cp.combo_id
    WHERE bc.booking_id = ?
";
$comboStmt = $conn->prepare($comboSql);
$comboStmt->bind_param('i', $bookingId);
$comboStmt->execute();
$comboResult = $comboStmt->get_result();

$paymentSql = "
    SELECT method, amount, status, paid_at, transaction_code
    FROM payments
    WHERE booking_id = ?
    ORDER BY payment_id DESC
    LIMIT 1
";
$paymentStmt = $conn->prepare($paymentSql);
$paymentStmt->bind_param('i', $bookingId);
$paymentStmt->execute();
$paymentResult = $paymentStmt->get_result();
$payment = $paymentResult->fetch_assoc();
$paymentStmt->close();

require_once __DIR__ . '/includes/header.php';
?>

<section class="success-section">
    <div class="success-box">
        <span class="badge">Booking Successful</span>
        <h1 class="page-title">Booking Confirmation</h1>
        <p class="page-subtitle">Your booking information has been saved successfully in the system.</p>

        <div class="success-code">
            Booking Code: <strong><?= e($booking['booking_code']) ?></strong>
        </div>

        <div class="checkout-layout" style="margin-top: 24px;">
            <div class="summary-card">
                <div class="checkout-movie">
                    <img src="<?= e($booking['poster_url'] ?: 'https://via.placeholder.com/300x420?text=Movie') ?>" alt="<?= e($booking['movie_title']) ?>">
                    <div>
                        <h3 style="font-size: 28px; margin-bottom: 10px;"><?= e($booking['movie_title']) ?></h3>
                        <p><strong>Cinema:</strong> <?= e($booking['location_name']) ?></p>
                        <p><strong>Room:</strong> <?= e($booking['room_name']) ?></p>
                        <p><strong>Showtime:</strong> <?= date('d/m/Y H:i', strtotime($booking['start_at'])) ?></p>
                        <p><strong>Format:</strong> <?= e($booking['screen_format'] ?: '2D') ?></p>
                        <p><strong>Subtitles:</strong> <?= e($booking['subtitle_type'] ?: 'None') ?></p>
                        <p><strong>Booking Status:</strong> <?= e($booking['status']) ?></p>
                    </div>
                </div>

                <div class="order-box" style="margin-top: 20px;">
                    <h3>Booked Seats</h3>
                    <?php while ($seat = $seatResult->fetch_assoc()): ?>
                        <div class="order-line">
                            <span><?= e($seat['seat_row'] . $seat['seat_number']) ?> <small>(<?= e($seat['category_name']) ?>)</small></span>
                            <strong><?= format_currency($seat['unit_price']) ?></strong>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="order-box" style="margin-top: 20px;">
                    <h3>Combos</h3>
                    <?php if ($comboResult && $comboResult->num_rows > 0): ?>
                        <?php while ($combo = $comboResult->fetch_assoc()): ?>
                            <div class="order-line">
                                <span><?= e($combo['name']) ?> x <?= (int) $combo['quantity'] ?></span>
                                <strong><?= format_currency($combo['unit_price'] * $combo['quantity']) ?></strong>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">No combo selected.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="checkout-card">
                <div class="order-box">
                    <h3 style="margin-bottom: 14px;">Payment</h3>

                    <div class="order-line">
                        <span>Subtotal</span>
                        <strong><?= format_currency($booking['subtotal']) ?></strong>
                    </div>

                    <div class="order-line">
                        <span>Discount</span>
                        <strong><?= format_currency($booking['discount_total']) ?></strong>
                    </div>

                    <div class="order-line order-total">
                        <span>Total Payment</span>
                        <strong><?= format_currency($booking['total']) ?></strong>
                    </div>
                </div>

                <div class="order-box" style="margin-top: 20px;">
                    <h3 style="margin-bottom: 14px;">Transaction Information</h3>

                    <?php if ($payment): ?>
                        <div class="order-line">
                            <span>Method</span>
                            <strong><?= e($payment['method']) ?></strong>
                        </div>

                        <div class="order-line">
                            <span>Status</span>
                            <strong><?= e($payment['status']) ?></strong>
                        </div>

                        <div class="order-line">
                            <span>Transaction Code</span>
                            <strong><?= e($payment['transaction_code']) ?></strong>
                        </div>

                        <div class="order-line">
                            <span>Payment Time</span>
                            <strong><?= !empty($payment['paid_at']) ? date('d/m/Y H:i', strtotime($payment['paid_at'])) : 'Updating' ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">No payment data available yet.</div>
                    <?php endif; ?>
                </div>

                <div class="movie-actions" style="margin-top: 22px;">
                    <a class="btn" href="<?= BASE_URL ?>">Back to Home</a>
                    <a class="btn btn-outline" href="<?= BASE_URL ?>movies.php">Book More Tickets</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$seatStmt->close();
$comboStmt->close();
require_once __DIR__ . '/includes/footer.php';
?>
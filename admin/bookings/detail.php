<?php
$pageTitle = 'Booking Details';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$bookingId = (int) get('id', 0);

$booking = null;
$seats = [];
$combos = [];
$payment = null;

if ($bookingId > 0) {
    $bookingSql = "
        SELECT
            b.booking_id,
            b.booking_code,
            b.status,
            b.subtotal,
            b.discount_total,
            b.total,
            b.created_at,
            COALESCE(u.name, 'Guest') AS user_name,
            COALESCE(u.email, 'N/A') AS user_email,
            COALESCE(m.title, 'N/A') AS movie_title,
            s.start_at,
            s.screen_format,
            s.subtitle_type,
            COALESCE(r.room_name, 'N/A') AS room_name,
            COALESCE(l.name, 'N/A') AS location_name,
            COALESCE(l.address, 'N/A') AS location_address
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.user_id
        LEFT JOIN showtimes s ON b.show_id = s.show_id
        LEFT JOIN movies m ON s.movie_id = m.movie_id
        LEFT JOIN rooms r ON s.room_id = r.room_id
        LEFT JOIN locations l ON r.location_id = l.location_id
        WHERE b.booking_id = ?
        LIMIT 1
    ";
    $bookingStmt = $conn->prepare($bookingSql);
    $bookingStmt->bind_param('i', $bookingId);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();
    $booking = $bookingResult->fetch_assoc();
    $bookingStmt->close();

    if ($booking) {
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
        while ($row = $seatResult->fetch_assoc()) {
            $seats[] = $row;
        }
        $seatStmt->close();

        $comboSql = "
            SELECT
                bc.quantity,
                bc.unit_price,
                cp.name
            FROM booking_combos bc
            INNER JOIN combo_products cp ON bc.combo_id = cp.combo_id
            WHERE bc.booking_id = ?
            ORDER BY cp.name ASC
        ";
        $comboStmt = $conn->prepare($comboSql);
        $comboStmt->bind_param('i', $bookingId);
        $comboStmt->execute();
        $comboResult = $comboStmt->get_result();
        while ($row = $comboResult->fetch_assoc()) {
            $combos[] = $row;
        }
        $comboStmt->close();

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
    }
}

function booking_status_badge(string $status): array
{
    return match ($status) {
        'paid', 'confirmed' => [ucfirst($status), 'active'],
        'pending' => ['Pending', 'coming'],
        'cancelled', 'expired' => [ucfirst($status), 'inactive'],
        default => [ucfirst($status), 'inactive'],
    };
}

function payment_method_label(?string $method): string
{
    if (!is_string($method) || $method === '') {
        return 'N/A';
    }

    return ucwords(str_replace('_', ' ', $method));
}
?>

<div class="admin-content">
    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Booking Details</h2>
        </div>

        <?php if ($booking): ?>
            <?php [$statusText, $statusClass] = booking_status_badge((string) $booking['status']); ?>

            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:18px;">
                <a class="admin-btn-outline" href="index.php">Back to Booking List</a>
                <a class="admin-btn-soft" href="print.php?id=<?= (int) $booking['booking_id'] ?>&auto=1" target="_blank" rel="noopener">Print Invoice</a>
            </div>

            <div class="admin-table-wrap" style="margin-bottom:18px;">
                <table class="admin-table admin-table-compact">
                    <tbody>
                        <tr>
                            <th style="width:220px;">Booking Code</th>
                            <td><?= e($booking['booking_code']) ?></td>
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td><?= e($booking['user_name']) ?><?= $booking['user_email'] !== 'N/A' ? ' (' . e($booking['user_email']) . ')' : '' ?></td>
                        </tr>
                        <tr>
                            <th>Movie</th>
                            <td><?= e($booking['movie_title']) ?></td>
                        </tr>
                        <tr>
                            <th>Cinema</th>
                            <td><?= e($booking['location_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?= e($booking['location_address']) ?></td>
                        </tr>
                        <tr>
                            <th>Room</th>
                            <td><?= e($booking['room_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Showtime</th>
                            <td><?= !empty($booking['start_at']) ? date('d/m/Y H:i', strtotime($booking['start_at'])) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Format</th>
                            <td><?= e($booking['screen_format'] ?: '2D') ?></td>
                        </tr>
                        <tr>
                            <th>Subtitle Type</th>
                            <td><?= e($booking['subtitle_type'] ?: 'None') ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="admin-badge <?= e($statusClass) ?>"><?= e($statusText) ?></span></td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?= !empty($booking['created_at']) ? date('d/m/Y H:i', strtotime($booking['created_at'])) : 'N/A' ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="admin-card" style="margin-bottom:18px;">
                <div class="admin-card-head">
                    <h2>Seat Items</h2>
                </div>
                <?php if (!empty($seats)): ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table admin-table-compact">
                            <thead>
                                <tr>
                                    <th>Seat</th>
                                    <th>Category</th>
                                    <th>Unit Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seats as $seat): ?>
                                    <tr>
                                        <td><?= e($seat['seat_row'] . $seat['seat_number']) ?></td>
                                        <td><?= e($seat['category_name']) ?></td>
                                        <td><?= format_currency($seat['unit_price']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="admin-empty">No seat items found.</div>
                <?php endif; ?>
            </div>

            <div class="admin-card" style="margin-bottom:18px;">
                <div class="admin-card-head">
                    <h2>Combo Items</h2>
                </div>
                <?php if (!empty($combos)): ?>
                    <div class="admin-table-wrap">
                        <table class="admin-table admin-table-compact">
                            <thead>
                                <tr>
                                    <th>Combo</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($combos as $combo): ?>
                                    <tr>
                                        <td><?= e($combo['name']) ?></td>
                                        <td><?= (int) $combo['quantity'] ?></td>
                                        <td><?= format_currency($combo['unit_price']) ?></td>
                                        <td><?= format_currency((float) $combo['unit_price'] * (int) $combo['quantity']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="admin-empty">No combo items selected.</div>
                <?php endif; ?>
            </div>

            <div class="admin-card">
                <div class="admin-card-head">
                    <h2>Payment Summary</h2>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table admin-table-compact">
                        <tbody>
                            <tr>
                                <th style="width:220px;">Subtotal</th>
                                <td><?= format_currency($booking['subtotal'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <th>Discount</th>
                                <td><?= format_currency($booking['discount_total'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td><strong><?= format_currency($booking['total'] ?? 0) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Payment Method</th>
                                <td><?= e(payment_method_label($payment['method'] ?? null)) ?></td>
                            </tr>
                            <tr>
                                <th>Payment Status</th>
                                <td><?= e(ucfirst((string) ($payment['status'] ?? 'N/A'))) ?></td>
                            </tr>
                            <tr>
                                <th>Transaction Code</th>
                                <td><?= e($payment['transaction_code'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Paid At</th>
                                <td><?= !empty($payment['paid_at']) ? date('d/m/Y H:i', strtotime($payment['paid_at'])) : 'N/A' ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-empty">Booking not found.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>
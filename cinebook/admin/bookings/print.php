<?php
require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$conn = db_connect();
$bookingId = (int) get('id', 0);
$autoPrint = get('auto', '') === '1';

if ($bookingId <= 0) {
    exit('Invalid booking ID.');
}

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
        COALESCE(u.phone, 'N/A') AS user_phone,
        COALESCE(m.title, 'N/A') AS movie_title,
        s.start_at,
        s.end_at,
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

if (!$booking) {
    exit('Booking not found.');
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
$seats = [];
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
$combos = [];
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

function payment_method_label(?string $method): string
{
    if (!is_string($method) || $method === '') {
        return 'N/A';
    }

    return ucwords(str_replace('_', ' ', $method));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= e($booking['booking_code']) ?></title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f8fafc;
            --surface: #ffffff;
            --line: #dbe4f0;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #ec4899;
            --accent-soft: #fdf2f8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 32px 18px;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background: var(--bg);
        }
        .invoice-shell {
            max-width: 920px;
            margin: 0 auto;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .toolbar-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toolbar button,
        .toolbar a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #f3c2dc;
            background: white;
            color: #be185d;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }
        .toolbar .primary {
            background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%);
            color: #fff;
            border-color: transparent;
        }
        .invoice-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }
        .invoice-head {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--line);
        }
        .brand-tag {
            display: inline-block;
            margin-bottom: 10px;
            padding: 6px 12px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: #be185d;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        h1, h2, h3, p { margin: 0; }
        .invoice-head h1 {
            font-size: 30px;
            margin-bottom: 6px;
        }
        .invoice-subtitle {
            color: var(--muted);
            line-height: 1.6;
            max-width: 520px;
        }
        .invoice-meta {
            min-width: 260px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px dashed var(--line);
            font-size: 14px;
        }
        .meta-row span:first-child { color: var(--muted); }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 22px;
        }
        .block {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            background: #fff;
        }
        .block h2 {
            font-size: 18px;
            margin-bottom: 14px;
        }
        .details {
            display: grid;
            gap: 10px;
        }
        .details div {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding-bottom: 10px;
            border-bottom: 1px dashed var(--line);
            font-size: 14px;
        }
        .details div span:first-child { color: var(--muted); }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            font-size: 14px;
            vertical-align: top;
        }
        th {
            background: #f8fafc;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        td.amount, th.amount { text-align: right; }
        .summary {
            margin-top: 22px;
            margin-left: auto;
            width: min(100%, 340px);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            background: #fff;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px dashed var(--line);
        }
        .summary-row:last-child { border-bottom: none; }
        .summary-total {
            font-size: 20px;
            font-weight: 800;
            color: #be185d;
        }
        .footer-note {
            margin-top: 22px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }
        .empty-note {
            padding: 14px;
            border: 1px dashed var(--line);
            border-radius: 14px;
            color: var(--muted);
            background: #f8fafc;
        }
        @media (max-width: 720px) {
            .invoice-card { padding: 20px; }
            .grid { grid-template-columns: 1fr; }
            .details div, .meta-row, .summary-row { flex-direction: column; }
        }
        @media print {
            body { padding: 0; background: #fff; }
            .toolbar { display: none !important; }
            .invoice-shell { max-width: none; }
            .invoice-card {
                border: none;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
            }
            a { text-decoration: none; color: inherit; }
        }
    </style>
</head>
<body>
    <div class="invoice-shell">
        <div class="toolbar">
            <div>
                <strong>CineBook Booking Invoice</strong>
            </div>
            <div class="toolbar-actions">
                <a href="detail.php?id=<?= (int) $booking['booking_id'] ?>">Back to Details</a>
                <button type="button" class="primary" onclick="window.print()">Print Invoice</button>
            </div>
        </div>

        <div class="invoice-card">
            <div class="invoice-head">
                <div>
                    <span class="brand-tag">CineBook</span>
                    <h1>Booking Invoice</h1>
                    <p class="invoice-subtitle">
                        This invoice summarizes the selected showtime, seats, combos, and payment details for the booking.
                    </p>
                </div>

                <div class="invoice-meta">
                    <div class="meta-row"><span>Invoice No.</span><strong><?= e($booking['booking_code']) ?></strong></div>
                    <div class="meta-row"><span>Booking Status</span><strong><?= e(ucfirst((string) $booking['status'])) ?></strong></div>
                    <div class="meta-row"><span>Created At</span><strong><?= !empty($booking['created_at']) ? date('d/m/Y H:i', strtotime($booking['created_at'])) : 'N/A' ?></strong></div>
                    <div class="meta-row"><span>Printed At</span><strong><?= date('d/m/Y H:i') ?></strong></div>
                </div>
            </div>

            <div class="grid">
                <section class="block">
                    <h2>Customer Information</h2>
                    <div class="details">
                        <div><span>Customer</span><strong><?= e($booking['user_name']) ?></strong></div>
                        <div><span>Email</span><strong><?= e($booking['user_email']) ?></strong></div>
                        <div><span>Phone</span><strong><?= e($booking['user_phone']) ?></strong></div>
                    </div>
                </section>

                <section class="block">
                    <h2>Showtime Information</h2>
                    <div class="details">
                        <div><span>Movie</span><strong><?= e($booking['movie_title']) ?></strong></div>
                        <div><span>Cinema</span><strong><?= e($booking['location_name']) ?></strong></div>
                        <div><span>Address</span><strong><?= e($booking['location_address']) ?></strong></div>
                        <div><span>Room</span><strong><?= e($booking['room_name']) ?></strong></div>
                        <div><span>Showtime</span><strong><?= !empty($booking['start_at']) ? date('d/m/Y H:i', strtotime($booking['start_at'])) : 'N/A' ?></strong></div>
                        <div><span>Format</span><strong><?= e($booking['screen_format'] ?: '2D') ?></strong></div>
                        <div><span>Subtitle Type</span><strong><?= e($booking['subtitle_type'] ?: 'None') ?></strong></div>
                    </div>
                </section>
            </div>

            <section class="block" style="margin-top: 22px;">
                <h2>Seat Items</h2>
                <?php if (!empty($seats)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 22%;">Seat</th>
                                <th>Category</th>
                                <th class="amount" style="width: 24%;">Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($seats as $seat): ?>
                                <tr>
                                    <td><?= e($seat['seat_row'] . $seat['seat_number']) ?></td>
                                    <td><?= e($seat['category_name']) ?></td>
                                    <td class="amount"><?= format_currency($seat['unit_price']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-note">No seat items found for this booking.</div>
                <?php endif; ?>
            </section>

            <section class="block" style="margin-top: 22px;">
                <h2>Combo Items</h2>
                <?php if (!empty($combos)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Combo</th>
                                <th style="width: 16%;">Qty</th>
                                <th class="amount" style="width: 22%;">Unit Price</th>
                                <th class="amount" style="width: 22%;">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($combos as $combo): ?>
                                <tr>
                                    <td><?= e($combo['name']) ?></td>
                                    <td><?= (int) $combo['quantity'] ?></td>
                                    <td class="amount"><?= format_currency($combo['unit_price']) ?></td>
                                    <td class="amount"><?= format_currency((float) $combo['unit_price'] * (int) $combo['quantity']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-note">No combos were selected for this booking.</div>
                <?php endif; ?>
            </section>

            <div class="grid" style="margin-top: 22px; align-items: start;">
                <section class="block">
                    <h2>Payment Information</h2>
                    <div class="details">
                        <div><span>Payment Method</span><strong><?= e(payment_method_label($payment['method'] ?? null)) ?></strong></div>
                        <div><span>Payment Status</span><strong><?= e(ucfirst((string) ($payment['status'] ?? 'N/A'))) ?></strong></div>
                        <div><span>Transaction Code</span><strong><?= e($payment['transaction_code'] ?? 'N/A') ?></strong></div>
                        <div><span>Paid At</span><strong><?= !empty($payment['paid_at']) ? date('d/m/Y H:i', strtotime($payment['paid_at'])) : 'N/A' ?></strong></div>
                    </div>
                </section>

                <aside class="summary">
                    <div class="summary-row"><span>Subtotal</span><strong><?= format_currency($booking['subtotal'] ?? 0) ?></strong></div>
                    <div class="summary-row"><span>Discount</span><strong><?= format_currency($booking['discount_total'] ?? 0) ?></strong></div>
                    <div class="summary-row summary-total"><span>Total</span><strong><?= format_currency($booking['total'] ?? 0) ?></strong></div>
                </aside>
            </div>

            <p class="footer-note">
                Thank you for choosing CineBook. This document is a printable invoice generated from the booking management module.
            </p>
        </div>
    </div>

    <?php if ($autoPrint): ?>
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    <?php endif; ?>
</body>
</html>
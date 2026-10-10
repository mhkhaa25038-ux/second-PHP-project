<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$dateFrom = trim((string) get('date_from'));
$dateTo = trim((string) get('date_to'));

$filterConditions = ["p.status = 'paid'"];
$filterParams = [];
$filterTypes = '';

if ($dateFrom !== '') {
    $filterConditions[] = "DATE(p.paid_at) >= ?";
    $filterParams[] = $dateFrom;
    $filterTypes .= 's';
}

if ($dateTo !== '') {
    $filterConditions[] = "DATE(p.paid_at) <= ?";
    $filterParams[] = $dateTo;
    $filterTypes .= 's';
}

$filterSql = 'WHERE ' . implode(' AND ', $filterConditions);

$runPrepared = function (string $sql, string $types = '', array $params = []) use ($conn) {
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return false;
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt;
};

$getScalar = function (string $sql, string $field, string $types = '', array $params = []) use ($runPrepared) {
    $stmt = $runPrepared($sql, $types, $params);

    if (!$stmt) {
        return 0;
    }

    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row[$field] ?? 0;
};

$totalRevenueSql = "
    SELECT COALESCE(SUM(p.amount), 0) AS total_revenue
    FROM payments p
    $filterSql
";
$totalRevenue = (float) $getScalar($totalRevenueSql, 'total_revenue', $filterTypes, $filterParams);

$ticketRevenueSql = "
    SELECT COALESCE(SUM(bs.unit_price), 0) AS ticket_revenue
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN booking_seats bs ON b.booking_id = bs.booking_id
    $filterSql
";
$ticketRevenue = (float) $getScalar($ticketRevenueSql, 'ticket_revenue', $filterTypes, $filterParams);

$comboRevenueSql = "
    SELECT COALESCE(SUM(bc.quantity * bc.unit_price), 0) AS combo_revenue
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN booking_combos bc ON b.booking_id = bc.booking_id
    $filterSql
";
$comboRevenue = (float) $getScalar($comboRevenueSql, 'combo_revenue', $filterTypes, $filterParams);

$paidBookingsSql = "
    SELECT COUNT(DISTINCT p.booking_id) AS paid_bookings
    FROM payments p
    $filterSql
";
$paidBookings = (int) $getScalar($paidBookingsSql, 'paid_bookings', $filterTypes, $filterParams);

$ticketsSoldSql = "
    SELECT COUNT(bs.booking_seat_id) AS tickets_sold
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN booking_seats bs ON b.booking_id = bs.booking_id
    $filterSql
";
$ticketsSold = (int) $getScalar($ticketsSoldSql, 'tickets_sold', $filterTypes, $filterParams);

$combosSoldSql = "
    SELECT COALESCE(SUM(bc.quantity), 0) AS combos_sold
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN booking_combos bc ON b.booking_id = bc.booking_id
    $filterSql
";
$combosSold = (int) $getScalar($combosSoldSql, 'combos_sold', $filterTypes, $filterParams);

$avgOrderValue = $paidBookings > 0 ? ($totalRevenue / $paidBookings) : 0;

$movieReportSql = "
    SELECT
        m.movie_id,
        m.title,
        COUNT(DISTINCT b.booking_id) AS booking_count,
        COALESCE(SUM(seat_summary.seat_count), 0) AS tickets_sold,
        COALESCE(SUM(seat_summary.seat_revenue), 0) AS ticket_revenue,
        COALESCE(SUM(combo_summary.combo_revenue), 0) AS combo_revenue,
        COALESCE(SUM(seat_summary.seat_revenue), 0) + COALESCE(SUM(combo_summary.combo_revenue), 0) AS total_revenue
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN showtimes s ON b.show_id = s.show_id
    INNER JOIN movies m ON s.movie_id = m.movie_id
    LEFT JOIN (
        SELECT
            booking_id,
            COUNT(*) AS seat_count,
            SUM(unit_price) AS seat_revenue
        FROM booking_seats
        GROUP BY booking_id
    ) AS seat_summary ON b.booking_id = seat_summary.booking_id
    LEFT JOIN (
        SELECT
            booking_id,
            SUM(quantity * unit_price) AS combo_revenue
        FROM booking_combos
        GROUP BY booking_id
    ) AS combo_summary ON b.booking_id = combo_summary.booking_id
    $filterSql
    GROUP BY m.movie_id, m.title
    ORDER BY total_revenue DESC, booking_count DESC
    LIMIT 5
";
$movieReportStmt = $runPrepared($movieReportSql, $filterTypes, $filterParams);
$movieReportResult = $movieReportStmt ? $movieReportStmt->get_result() : false;

$locationReportSql = "
    SELECT
        l.location_id,
        l.name AS location_name,
        COUNT(DISTINCT b.booking_id) AS booking_count,
        COALESCE(SUM(seat_summary.seat_count), 0) AS tickets_sold,
        COALESCE(SUM(seat_summary.seat_revenue), 0) + COALESCE(SUM(combo_summary.combo_revenue), 0) AS total_revenue
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN showtimes s ON b.show_id = s.show_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    LEFT JOIN (
        SELECT
            booking_id,
            COUNT(*) AS seat_count,
            SUM(unit_price) AS seat_revenue
        FROM booking_seats
        GROUP BY booking_id
    ) AS seat_summary ON b.booking_id = seat_summary.booking_id
    LEFT JOIN (
        SELECT
            booking_id,
            SUM(quantity * unit_price) AS combo_revenue
        FROM booking_combos
        GROUP BY booking_id
    ) AS combo_summary ON b.booking_id = combo_summary.booking_id
    $filterSql
    GROUP BY l.location_id, l.name
    ORDER BY total_revenue DESC, booking_count DESC
    LIMIT 5
";
$locationReportStmt = $runPrepared($locationReportSql, $filterTypes, $filterParams);
$locationReportResult = $locationReportStmt ? $locationReportStmt->get_result() : false;

$paymentMethodSql = "
    SELECT
        p.method,
        COUNT(*) AS payment_count,
        COALESCE(SUM(p.amount), 0) AS total_amount
    FROM payments p
    $filterSql
    GROUP BY p.method
    ORDER BY total_amount DESC
";
$paymentMethodStmt = $runPrepared($paymentMethodSql, $filterTypes, $filterParams);
$paymentMethodResult = $paymentMethodStmt ? $paymentMethodStmt->get_result() : false;

$recentBookingsSql = "
    SELECT
        b.booking_code,
        m.title AS movie_title,
        l.name AS location_name,
        r.room_name,
        p.method,
        p.amount,
        p.paid_at
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN showtimes s ON b.show_id = s.show_id
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    $filterSql
    ORDER BY p.paid_at DESC
    LIMIT 10
";
$recentBookingsStmt = $runPrepared($recentBookingsSql, $filterTypes, $filterParams);
$recentBookingsResult = $recentBookingsStmt ? $recentBookingsStmt->get_result() : false;

/* Additional section */

$dailyRevenueSql = "
    SELECT
        DATE(p.paid_at) AS report_date,
        COUNT(DISTINCT b.booking_id) AS booking_count,
        COALESCE(SUM(seat_summary.seat_count), 0) AS tickets_sold,
        COALESCE(SUM(combo_summary.combo_qty), 0) AS combos_sold,
        COALESCE(SUM(seat_summary.seat_revenue), 0) AS ticket_revenue,
        COALESCE(SUM(combo_summary.combo_revenue), 0) AS combo_revenue,
        COALESCE(SUM(p.amount), 0) AS total_revenue
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    LEFT JOIN (
        SELECT
            booking_id,
            COUNT(*) AS seat_count,
            SUM(unit_price) AS seat_revenue
        FROM booking_seats
        GROUP BY booking_id
    ) AS seat_summary ON b.booking_id = seat_summary.booking_id
    LEFT JOIN (
        SELECT
            booking_id,
            SUM(quantity) AS combo_qty,
            SUM(quantity * unit_price) AS combo_revenue
        FROM booking_combos
        GROUP BY booking_id
    ) AS combo_summary ON b.booking_id = combo_summary.booking_id
    $filterSql
    GROUP BY DATE(p.paid_at)
    ORDER BY report_date DESC
    LIMIT 15
";
$dailyRevenueStmt = $runPrepared($dailyRevenueSql, $filterTypes, $filterParams);
$dailyRevenueResult = $dailyRevenueStmt ? $dailyRevenueStmt->get_result() : false;

$comboTopSql = "
    SELECT
        cp.combo_id,
        cp.name AS combo_name,
        COUNT(DISTINCT b.booking_id) AS order_count,
        COALESCE(SUM(bc.quantity), 0) AS qty_sold,
        COALESCE(SUM(bc.quantity * bc.unit_price), 0) AS combo_revenue
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN booking_combos bc ON b.booking_id = bc.booking_id
    INNER JOIN combo_products cp ON bc.combo_id = cp.combo_id
    $filterSql
    GROUP BY cp.combo_id, cp.name
    ORDER BY combo_revenue DESC, qty_sold DESC
    LIMIT 5
";
$comboTopStmt = $runPrepared($comboTopSql, $filterTypes, $filterParams);
$comboTopResult = $comboTopStmt ? $comboTopStmt->get_result() : false;

$showtimeTopSql = "
    SELECT
        s.show_id,
        m.title AS movie_title,
        l.name AS location_name,
        r.room_name,
        s.start_at,
        COUNT(DISTINCT b.booking_id) AS booking_count,
        COALESCE(SUM(seat_summary.seat_count), 0) AS tickets_sold,
        COALESCE(MAX(capacity.total_seats), 0) AS total_seats,
        COALESCE(SUM(seat_summary.seat_revenue), 0) + COALESCE(SUM(combo_summary.combo_revenue), 0) AS total_revenue
    FROM payments p
    INNER JOIN bookings b ON p.booking_id = b.booking_id
    INNER JOIN showtimes s ON b.show_id = s.show_id
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    LEFT JOIN (
        SELECT
            booking_id,
            COUNT(*) AS seat_count,
            SUM(unit_price) AS seat_revenue
        FROM booking_seats
        GROUP BY booking_id
    ) AS seat_summary ON b.booking_id = seat_summary.booking_id
    LEFT JOIN (
        SELECT
            booking_id,
            SUM(quantity * unit_price) AS combo_revenue
        FROM booking_combos
        GROUP BY booking_id
    ) AS combo_summary ON b.booking_id = combo_summary.booking_id
    LEFT JOIN (
        SELECT
            show_id,
            COUNT(*) AS total_seats
        FROM show_seats
        GROUP BY show_id
    ) AS capacity ON s.show_id = capacity.show_id
    $filterSql
    GROUP BY s.show_id, m.title, l.name, r.room_name, s.start_at
    ORDER BY total_revenue DESC, tickets_sold DESC
    LIMIT 5
";
$showtimeTopStmt = $runPrepared($showtimeTopSql, $filterTypes, $filterParams);
$showtimeTopResult = $showtimeTopStmt ? $showtimeTopStmt->get_result() : false;
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Report Filters</h2>
    </div>

    <div class="admin-filter-bar">
        <form method="GET" action="">
            <input class="admin-input" type="date" name="date_from" value="<?= e($dateFrom) ?>">
            <input class="admin-input" type="date" name="date_to" value="<?= e($dateTo) ?>">
            <button class="admin-btn" type="submit">View Report</button>
            <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/reports/index.php">Reset</a>
        </form>
    </div>

    <p style="margin-top:12px; color:#94a3b8;">
        This report only includes transactions with payment status <strong>paid</strong>.
    </p>
</div>

<div style="display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:16px; margin-bottom:20px;">
    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Total Revenue</h3>
        <p style="font-size:30px; font-weight:800;"><?= format_currency($totalRevenue) ?></p>
    </div>

    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Ticket Revenue</h3>
        <p style="font-size:30px; font-weight:800;"><?= format_currency($ticketRevenue) ?></p>
    </div>

    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Combo Revenue</h3>
        <p style="font-size:30px; font-weight:800;"><?= format_currency($comboRevenue) ?></p>
    </div>

    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Paid Bookings</h3>
        <p style="font-size:30px; font-weight:800;"><?= $paidBookings ?></p>
    </div>

    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Tickets Sold</h3>
        <p style="font-size:30px; font-weight:800;"><?= $ticketsSold ?></p>
    </div>

    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Combos Sold</h3>
        <p style="font-size:30px; font-weight:800;"><?= $combosSold ?></p>
    </div>

    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Average Booking Value</h3>
        <p style="font-size:30px; font-weight:800;"><?= format_currency($avgOrderValue) ?></p>
    </div>

    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Ticket Share</h3>
        <p style="font-size:30px; font-weight:800;">
            <?= $totalRevenue > 0 ? number_format(($ticketRevenue / $totalRevenue) * 100, 1) : '0.0' ?>%
        </p>
    </div>

    <div class="admin-card" style="margin-bottom:0;">
        <h3 style="font-size:15px; color:#94a3b8; margin-bottom:8px;">Combo Share</h3>
        <p style="font-size:30px; font-weight:800;">
            <?= $totalRevenue > 0 ? number_format(($comboRevenue / $totalRevenue) * 100, 1) : '0.0' ?>%
        </p>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Top Revenue Movies</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Movie</th>
                        <th>Booking</th>
                        <th>Tickets Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($movieReportResult && $movieReportResult->num_rows > 0): ?>
                    <?php while ($row = $movieReportResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($row['title']) ?></td>
                            <td><?= (int) $row['booking_count'] ?></td>
                            <td><?= (int) $row['tickets_sold'] ?></td>
                            <td><?= format_currency($row['total_revenue']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4"><div class="admin-empty">No movie revenue data available yet.</div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Top Branches / Cinemas</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Booking</th>
                        <th>Tickets Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($locationReportResult && $locationReportResult->num_rows > 0): ?>
                    <?php while ($row = $locationReportResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($row['location_name']) ?></td>
                            <td><?= (int) $row['booking_count'] ?></td>
                            <td><?= (int) $row['tickets_sold'] ?></td>
                            <td><?= format_currency($row['total_revenue']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4"><div class="admin-empty">No branch revenue data available yet.</div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Revenue by Payment Method</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Transactions</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($paymentMethodResult && $paymentMethodResult->num_rows > 0): ?>
                    <?php while ($row = $paymentMethodResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($row['method']) ?></td>
                            <td><?= (int) $row['payment_count'] ?></td>
                            <td><?= format_currency($row['total_amount']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3"><div class="admin-empty">No payment data available yet.</div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Recent Transactions</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Booking Code</th>
                        <th>Movie</th>
                        <th>Cinema</th>
                        <th>Amount</th>
                        <th>Paid At</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recentBookingsResult && $recentBookingsResult->num_rows > 0): ?>
                    <?php while ($row = $recentBookingsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($row['booking_code']) ?></td>
                            <td><?= e($row['movie_title']) ?></td>
                            <td><?= e($row['location_name'] . ' - ' . $row['room_name']) ?></td>
                            <td><?= format_currency($row['amount']) ?></td>
                            <td><?= !empty($row['paid_at']) ? date('d/m/Y H:i', strtotime($row['paid_at'])) : 'N/A' ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><div class="admin-empty">No recent transactions yet.</div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New section -->
<div style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:20px;">
    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Revenue by Day</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Booking</th>
                        <th>Tickets Sold</th>
                        <th>Combos Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($dailyRevenueResult && $dailyRevenueResult->num_rows > 0): ?>
                    <?php while ($row = $dailyRevenueResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= !empty($row['report_date']) ? date('d/m/Y', strtotime($row['report_date'])) : 'N/A' ?></td>
                            <td><?= (int) $row['booking_count'] ?></td>
                            <td><?= (int) $row['tickets_sold'] ?></td>
                            <td><?= (int) $row['combos_sold'] ?></td>
                            <td><?= format_currency($row['total_revenue']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><div class="admin-empty">No daily revenue data available yet.</div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-head">
            <h2>Top Best-Selling Combos</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Combo</th>
                        <th>Quantity Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($comboTopResult && $comboTopResult->num_rows > 0): ?>
                    <?php while ($row = $comboTopResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($row['combo_name']) ?></td>
                            <td><?= (int) $row['qty_sold'] ?></td>
                            <td><?= format_currency($row['combo_revenue']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3"><div class="admin-empty">No combo data available yet.</div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Top Performing Showtimes</h2>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Movie</th>
                    <th>Cinema / Room</th>
                    <th>Showtime</th>
                    <th>Booking</th>
                    <th>Seats Sold</th>
                    <th>Occupancy</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($showtimeTopResult && $showtimeTopResult->num_rows > 0): ?>
                <?php while ($row = $showtimeTopResult->fetch_assoc()): ?>
                    <?php
                    $soldSeats = (int) $row['tickets_sold'];
                    $totalSeats = (int) $row['total_seats'];
                    $fillRate = $totalSeats > 0 ? ($soldSeats / $totalSeats) * 100 : 0;
                    ?>
                    <tr>
                        <td><?= e($row['movie_title']) ?></td>
                        <td><?= e($row['location_name'] . ' - ' . $row['room_name']) ?></td>
                        <td><?= !empty($row['start_at']) ? date('d/m/Y H:i', strtotime($row['start_at'])) : 'N/A' ?></td>
                        <td><?= (int) $row['booking_count'] ?></td>
                        <td><?= $soldSeats ?> / <?= $totalSeats ?></td>
                        <td><?= number_format($fillRate, 1) ?>%</td>
                        <td><?= format_currency($row['total_revenue']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7"><div class="admin-empty">No showtime data available yet.</div></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
if ($movieReportStmt) {
    $movieReportStmt->close();
}
if ($locationReportStmt) {
    $locationReportStmt->close();
}
if ($paymentMethodStmt) {
    $paymentMethodStmt->close();
}
if ($recentBookingsStmt) {
    $recentBookingsStmt->close();
}
if ($dailyRevenueStmt) {
    $dailyRevenueStmt->close();
}
if ($comboTopStmt) {
    $comboTopStmt->close();
}
if ($showtimeTopStmt) {
    $showtimeTopStmt->close();
}

require_once __DIR__ . '/../../includes/admin_layout_end.php';
?>
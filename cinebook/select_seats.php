<?php
require_once __DIR__ . '/includes/auth.php';

require_login();

$conn = db_connect();
cleanup_expired_show_seat_holds($conn);

$showId = (int) get('show_id');
$holdToken = get_booking_hold_token();

if ($showId <= 0) {
    set_flash_message('error', 'Invalid showtime.');
    redirect('movies.php');
}

$existingBooking = $_SESSION['booking'] ?? null;
if ($existingBooking && (int) ($existingBooking['show_id'] ?? 0) !== $showId) {
    release_holds_by_token($conn, (int) ($existingBooking['show_id'] ?? 0), $holdToken);
    unset($_SESSION['booking']);
}

$showtimeSql = "
    SELECT
        s.show_id,
        s.start_at,
        s.end_at,
        s.base_price,
        s.screen_format,
        s.subtitle_type,
        s.status,
        m.movie_id,
        m.title AS movie_title,
        m.poster_url,
        m.duration_min,
        r.room_id,
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
$showtimeStmt = $conn->prepare($showtimeSql);
$showtimeStmt->bind_param('i', $showId);
$showtimeStmt->execute();
$showtime = $showtimeStmt->get_result()->fetch_assoc();
$showtimeStmt->close();

if (!$showtime) {
    set_flash_message('error', 'Showtime not found.');
    redirect('movies.php');
}

$seatSql = "
    SELECT
        ss.show_seat_id,
        ss.status AS show_seat_status,
        ss.final_price,
        ss.hold_token,
        ss.hold_until,
        se.seat_id,
        se.seat_row,
        se.seat_number,
        se.is_aisle,
        se.is_accessible,
        sc.name AS category_name,
        sc.price_multiplier
    FROM show_seats ss
    INNER JOIN seats se ON ss.seat_id = se.seat_id
    INNER JOIN seat_categories sc ON se.category_id = sc.category_id
    WHERE ss.show_id = ?
    ORDER BY se.seat_row ASC, CAST(se.seat_number AS UNSIGNED) ASC, se.seat_number ASC
";
$seatStmt = $conn->prepare($seatSql);
$seatStmt->bind_param('i', $showId);
$seatStmt->execute();
$seatResult = $seatStmt->get_result();

$seatMap = [];
$availableCount = 0;
$selectedSeatIds = get_held_show_seat_ids_by_token($conn, $showId, $holdToken);
$selectedSeatIdSet = array_fill_keys($selectedSeatIds, true);

while ($seat = $seatResult->fetch_assoc()) {
    $row = $seat['seat_row'];
    $seatId = (int) $seat['show_seat_id'];

    $displayStatus = 'available';
    if ($seat['show_seat_status'] === 'booked') {
        $displayStatus = 'booked';
    } elseif ($seat['show_seat_status'] === 'held') {
        $displayStatus = ((string) ($seat['hold_token'] ?? '') === $holdToken) ? 'selected' : 'held';
    }

    if ($seat['show_seat_status'] === 'available') {
        $availableCount++;
    }

    $seat['display_status'] = $displayStatus;
    $seat['is_selected_by_me'] = isset($selectedSeatIdSet[$seatId]);
    $seatMap[$row][] = $seat;
}
$seatStmt->close();

$holdUntil = get_hold_expiry_for_token($conn, $showId, $holdToken);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cleanup_expired_show_seat_holds($conn);
    $selectedSeatIds = get_held_show_seat_ids_by_token($conn, $showId, $holdToken);

    if (empty($selectedSeatIds)) {
        $error = 'Please select at least one valid seat.';
    } else {
        $placeholders = implode(',', array_fill(0, count($selectedSeatIds), '?'));
        $types = 'is' . str_repeat('i', count($selectedSeatIds));
        $params = array_merge([$showId, $holdToken], $selectedSeatIds);

        $sql = "
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
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Unable to validate the selected seats.';
        } else {
            $bindParams = [];
            $bindParams[] = &$types;
            foreach ($params as $index => $value) {
                $bindParams[] = &$params[$index];
            }

            call_user_func_array([$stmt, 'bind_param'], $bindParams);
            $stmt->execute();
            $result = $stmt->get_result();

            $selectedSeatDetails = [];
            $subtotal = 0;
            $holdUntil = null;

            while ($seat = $result->fetch_assoc()) {
                $selectedSeatDetails[] = [
                    'show_seat_id' => (int) $seat['show_seat_id'],
                    'label' => $seat['seat_row'] . $seat['seat_number'],
                    'price' => (float) $seat['final_price'],
                    'category_name' => $seat['category_name'],
                ];
                $subtotal += (float) $seat['final_price'];
                $holdUntil = $seat['hold_until'];
            }
            $stmt->close();

            if (count($selectedSeatDetails) !== count($selectedSeatIds)) {
                $error = 'One or more seats have expired. Please select again.';
            } else {
                $_SESSION['booking'] = [
                    'show_id' => $showId,
                    'movie_id' => (int) $showtime['movie_id'],
                    'movie_title' => $showtime['movie_title'],
                    'room_name' => $showtime['room_name'],
                    'location_name' => $showtime['location_name'],
                    'start_at' => $showtime['start_at'],
                    'selected_seat_ids' => $selectedSeatIds,
                    'selected_seats' => $selectedSeatDetails,
                    'seat_subtotal' => $subtotal,
                    'hold_until' => $holdUntil,
                ];

                redirect('checkout.php');
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.seat-ui-page{
    background: linear-gradient(180deg, #0b1020 0%, #121a31 100%);
    padding: 32px 0 48px;
    min-height: calc(100vh - 120px);
}
.seat-ui-container{
    width: min(1400px, calc(100% - 32px));
    margin: 0 auto;
}
.seat-ui-top{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
}
.seat-ui-top-left h1{
    margin: 10px 0 8px;
    color: #fff;
    font-size: 32px;
    line-height: 1.2;
}
.seat-ui-top-left p{
    margin: 0;
    color: #b6bfd8;
    max-width: 820px;
    font-size: 15px;
}
.seat-ui-badge{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.08);
    color: #ffd166;
    border: 1px solid rgba(255,255,255,.12);
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
}
.seat-ui-grid{
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 24px;
    align-items: start;
}
.seat-panel{
    background: rgba(12, 18, 35, .92);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 24px;
    box-shadow: 0 18px 50px rgba(0,0,0,.25);
}
.seat-main{
    padding: 22px;
}
.seat-movie-head{
    display: grid;
    grid-template-columns: 120px minmax(0,1fr);
    gap: 18px;
    align-items: start;
    margin-bottom: 22px;
}
.seat-movie-poster{
    width: 120px;
    height: 172px;
    border-radius: 18px;
    overflow: hidden;
    background: #1b2540;
    box-shadow: 0 8px 24px rgba(0,0,0,.25);
}
.seat-movie-poster img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.seat-movie-info h2{
    margin: 0 0 10px;
    color: #fff;
    font-size: 24px;
    line-height: 1.25;
}
.seat-movie-meta{
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}
.seat-movie-meta span{
    display: inline-flex;
    align-items: center;
    min-height: 38px;
    padding: 8px 12px;
    border-radius: 12px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    color: #dde4f6;
    font-size: 13px;
}
.seat-legend{
    display: flex;
    flex-wrap: wrap;
    gap: 10px 14px;
}
.seat-legend-item{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #c8d1ea;
    font-size: 13px;
}
.seat-legend-dot{
    width: 20px;
    height: 20px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,.16);
    flex: 0 0 auto;
}
.seat-legend-dot.available{ background: #233156; }
.seat-legend-dot.selected{ background: linear-gradient(135deg, #5b8cff, #8a6bff); }
.seat-legend-dot.held{ background: #7a5b21; }
.seat-legend-dot.booked{ background: #4a536d; }
.seat-legend-dot.vip{ background: #174c3b; }

.seat-hold-box{
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(91,140,255,.12), rgba(138,107,255,.08));
    border: 1px solid rgba(91,140,255,.22);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.seat-hold-left strong{
    display: block;
    color: #fff;
    font-size: 15px;
    margin-bottom: 4px;
}
.seat-hold-left span{
    color: #c7d3f7;
    font-size: 13px;
}
.seat-hold-countdown{
    min-width: 84px;
    text-align: center;
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    padding: 10px 12px;
    border-radius: 16px;
    background: rgba(255,255,255,.08);
}

.seat-map-wrap{
    padding: 16px 10px 14px;
    border-radius: 22px;
    background:
        radial-gradient(circle at top, rgba(122, 150, 255, .12), transparent 48%),
        linear-gradient(180deg, #0f1630 0%, #0c1227 100%);
    border: 1px solid rgba(255,255,255,.06);
    overflow: hidden;
}
.seat-screen{
    width: 100%;
    margin: 0 auto 20px;
    text-align: center;
}
.seat-screen-shape{
    width: 100%;
    height: 14px;
    border-radius: 999px 999px 18px 18px;
    background: linear-gradient(180deg, #f7f9ff 0%, #a7b3d9 100%);
    box-shadow: 0 14px 34px rgba(206, 221, 255, .28);
    margin-bottom: 8px;
}
.seat-screen span{
    color: #d6def7;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .22em;
}
.seat-map{
    width: 100%;
}
.seat-row{
    display: grid;
    grid-template-columns: 34px minmax(0,1fr) 34px;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}
.seat-row-label{
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.08);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
}
.seat-row-items{
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.seat-item{
    position: relative;
    width: calc((100% - ((var(--seat-count) - 1) * 6px)) / var(--seat-count));
    max-width: 46px;
    min-width: 24px;
    aspect-ratio: 1 / 1;
    height: auto;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,.10);
    background: #233156;
    color: #eef3ff;
    font-size: clamp(8px, 0.75vw, 11px);
    font-weight: 700;
    cursor: pointer;
    transition: .18s ease;
    box-shadow: inset 0 -4px 0 rgba(255,255,255,.05);
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    overflow: hidden;
    text-align: center;
}
.seat-item:hover:not(:disabled){
    transform: translateY(-1px) scale(1.03);
    border-color: rgba(255,255,255,.26);
}
.seat-item.available{
    background: #233156;
}
.seat-item.selected{
    background: linear-gradient(135deg, #5b8cff, #8a6bff);
    box-shadow: 0 10px 22px rgba(91,140,255,.28);
}
.seat-item.held{
    background: #7a5b21;
    color: #f9ebc7;
    cursor: not-allowed;
}
.seat-item.booked{
    background: #4a536d;
    color: #cfd5e6;
    cursor: not-allowed;
}
.seat-item.vip{
    outline: 2px solid rgba(52, 211, 153, .35);
    background: #174c3b;
}
.seat-item.selected.vip{
    background: linear-gradient(135deg, #22c55e, #0ea5e9);
}
.seat-item.accessible::after{
    content: "♿";
    position: absolute;
    right: -3px;
    top: -5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    color: #12203d;
    font-size: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.seat-item.aisle{
    margin-right: 0;
}
.seat-empty-note{
    text-align: center;
    color: #9fb0da;
    margin-top: 10px;
    font-size: 12px;
}

.seat-side{
    padding: 22px;
    position: sticky;
    top: 24px;
}
.seat-side h3{
    margin: 0 0 18px;
    color: #fff;
    font-size: 22px;
}
.seat-summary-list{
    display: grid;
    gap: 12px;
    margin-bottom: 16px;
}
.seat-summary-item{
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 16px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.06);
}
.seat-summary-item span{
    color: #9aa8ca;
    font-size: 13px;
}
.seat-summary-item strong{
    color: #fff;
    font-size: 14px;
    text-align: right;
}
.seat-selected-box{
    padding: 14px;
    border-radius: 18px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.06);
    margin-bottom: 16px;
}
.seat-selected-box-title{
    color: #9aa8ca;
    font-size: 13px;
    margin-bottom: 10px;
}
.seat-selected-tags{
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.seat-tag{
    display: inline-flex;
    align-items: center;
    padding: 8px 10px;
    border-radius: 999px;
    background: rgba(91,140,255,.16);
    border: 1px solid rgba(91,140,255,.28);
    color: #eef3ff;
    font-size: 12px;
    font-weight: 700;
}
.seat-total-box{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(91,140,255,.14), rgba(138,107,255,.10));
    border: 1px solid rgba(91,140,255,.25);
    margin-bottom: 18px;
}
.seat-total-box span{
    color: #c7d3f7;
    font-size: 14px;
}
.seat-total-box strong{
    color: #fff;
    font-size: 26px;
    line-height: 1;
}
.seat-side-actions{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.seat-btn{
    min-height: 48px;
    border-radius: 14px;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    cursor: pointer;
    transition: .18s ease;
}
.seat-btn-outline{
    background: transparent;
    color: #fff;
    border: 1px solid rgba(255,255,255,.14);
}
.seat-btn-outline:hover{
    background: rgba(255,255,255,.06);
}
.seat-btn-primary{
    background: linear-gradient(135deg, #5b8cff, #8a6bff);
    color: #fff;
    box-shadow: 0 12px 24px rgba(91,140,255,.28);
}
.seat-btn-primary:hover:not(:disabled){
    transform: translateY(-1px);
}
.seat-btn-primary:disabled{
    opacity: .6;
    cursor: not-allowed;
    box-shadow: none;
}
.empty-state-seat{
    padding: 18px;
    border-radius: 18px;
    background: rgba(255,255,255,.05);
    color: #d9e1f5;
    border: 1px solid rgba(255,255,255,.06);
}
.auth-error.seat-error{
    margin-bottom: 16px;
    border-radius: 16px;
}

@media (max-width: 1100px){
    .seat-ui-grid{
        grid-template-columns: 1fr;
    }
    .seat-side{
        position: static;
    }
}
@media (max-width: 768px){
    .seat-ui-top-left h1{
        font-size: 26px;
    }
    .seat-main,
    .seat-side{
        padding: 16px;
    }
    .seat-movie-head{
        grid-template-columns: 88px minmax(0,1fr);
    }
    .seat-movie-poster{
        width: 88px;
        height: 128px;
    }
    .seat-row{
        grid-template-columns: 28px minmax(0,1fr) 28px;
        gap: 6px;
    }
    .seat-row-label{
        width: 28px;
        height: 28px;
        font-size: 10px;
        border-radius: 8px;
    }
    .seat-row-items{
        gap: 4px;
    }
    .seat-item{
        width: calc((100% - ((var(--seat-count) - 1) * 4px)) / var(--seat-count));
        max-width: 34px;
        min-width: 18px;
        border-radius: 8px;
        font-size: clamp(7px, 2vw, 10px);
    }
    .seat-screen-shape{
        height: 12px;
    }
    .seat-empty-note{
        display: none;
    }
}
</style>

<section class="seat-ui-page">
    <div class="seat-ui-container">
        <div class="seat-ui-top">
            <div class="seat-ui-top-left">
                <span class="seat-ui-badge">SELECT MOVIE SEATS</span>
                <h1><?= e($showtime['movie_title']) ?></h1>
                <p>Choose seats visually like in a cinema interface. Seats are temporarily held for 5 minutes to reduce conflicts when multiple users book at the same time.</p>
            </div>
        </div>

        <div class="seat-ui-grid">
            <div class="seat-panel seat-main">
                <div class="seat-movie-head">
                    <div class="seat-movie-poster">
                        <img src="<?= e($showtime['poster_url'] ?: 'https://via.placeholder.com/400x600?text=Movie') ?>" alt="<?= e($showtime['movie_title']) ?>">
                    </div>

                    <div class="seat-movie-info">
                        <h2><?= e($showtime['movie_title']) ?></h2>

                        <div class="seat-movie-meta">
                            <span><?= e($showtime['location_name']) ?></span>
                            <span><?= e($showtime['room_name']) ?></span>
                            <span><?= date('d/m/Y H:i', strtotime($showtime['start_at'])) ?></span>
                            <span><?= e($showtime['screen_format'] ?: '2D') ?></span>
                            <span><?= e($showtime['subtitle_type'] ?: 'Subtitles') ?></span>
                        </div>

                        <div class="seat-legend">
                            <div class="seat-legend-item"><span class="seat-legend-dot available"></span> Available seat</div>
                            <div class="seat-legend-item"><span class="seat-legend-dot selected"></span> Selected</div>
                            <div class="seat-legend-item"><span class="seat-legend-dot held"></span> Held</div>
                            <div class="seat-legend-item"><span class="seat-legend-dot booked"></span> Booked</div>
                            <div class="seat-legend-item"><span class="seat-legend-dot vip"></span> VIP / Couple</div>
                        </div>
                    </div>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="auth-error seat-error"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($seatMap)): ?>
                    <form method="POST" id="seatForm">
                        <input type="hidden" name="confirm_seats" value="1">

                        <div class="seat-hold-box">
                            <div class="seat-hold-left">
                                <strong>Temporary Seat Hold</strong>
                                <span id="seatHoldText">You have not held any seats yet.</span>
                            </div>
                            <div class="seat-hold-countdown" id="seatHoldTimer">--:--</div>
                        </div>

                        <div class="seat-map-wrap">
                            <div class="seat-screen">
                                <div class="seat-screen-shape"></div>
                                <span>SCREEN</span>
                            </div>

                            <div class="seat-map" id="seatMap"
                                 data-show-id="<?= (int) $showId ?>"
                                 data-base-url="<?= e(BASE_URL) ?>"
                                 data-hold-until="<?= e($holdUntil ?? '') ?>">
                                <?php foreach ($seatMap as $row => $seats): ?>
                                    <div class="seat-row">
                                        <div class="seat-row-label"><?= e($row) ?></div>

                                      <div class="seat-row-items" style="--seat-count: <?= max(1, count($seats)) ?>;">
                                            <?php foreach ($seats as $seat): ?>
                                                <?php
                                                    $seatLabel = $seat['seat_row'] . $seat['seat_number'];
                                                    $categoryName = strtolower((string) $seat['category_name']);
                                                    $extraClasses = [];

                                                    if (stripos($categoryName, 'vip') !== false || stripos($categoryName, 'couple') !== false) {
                                                        $extraClasses[] = 'vip';
                                                    }
                                                    if ((int) $seat['is_accessible'] === 1) {
                                                        $extraClasses[] = 'accessible';
                                                    }
                                                    if ((int) $seat['is_aisle'] === 1) {
                                                        $extraClasses[] = 'aisle';
                                                    }

                                                    $buttonClass = trim('seat-item ' . $seat['display_status'] . ' ' . implode(' ', $extraClasses));
                                                ?>
                                                <button
                                                    type="button"
                                                    class="<?= e($buttonClass) ?>"
                                                    data-seat-id="<?= (int) $seat['show_seat_id'] ?>"
                                                    data-seat-label="<?= e($seatLabel) ?>"
                                                    data-price="<?= (float) $seat['final_price'] ?>"
                                                    data-category="<?= e($seat['category_name']) ?>"
                                                    data-status="<?= e($seat['display_status']) ?>"
                                                    title="<?= e($seatLabel . ' - ' . $seat['category_name'] . ' - ' . format_currency($seat['final_price'])) ?>"
                                                    <?= in_array($seat['display_status'], ['booked', 'held'], true) ? 'disabled' : '' ?>
                                                >
                                                    <?= e($seatLabel) ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="seat-row-label"><?= e($row) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="seat-empty-note">Tip: scroll horizontally on smaller screens to see the full seat map.</div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="empty-state-seat">There is no seat data for this showtime yet.</div>
                <?php endif; ?>
            </div>

            <aside class="seat-panel seat-side">
                <h3>Booking Information</h3>

                <div class="seat-summary-list">
                    <div class="seat-summary-item">
                        <span>Movie</span>
                        <strong><?= e($showtime['movie_title']) ?></strong>
                    </div>
                    <div class="seat-summary-item">
                        <span>Cinema</span>
                        <strong><?= e($showtime['location_name']) ?></strong>
                    </div>
                    <div class="seat-summary-item">
                        <span>Room</span>
                        <strong><?= e($showtime['room_name']) ?></strong>
                    </div>
                    <div class="seat-summary-item">
                        <span>Showtime</span>
                        <strong><?= date('d/m/Y H:i', strtotime($showtime['start_at'])) ?></strong>
                    </div>
                    <div class="seat-summary-item">
                        <span>Available Seats</span>
                        <strong id="availableSeatCount"><?= (int) $availableCount ?></strong>
                    </div>
                </div>

                <div class="seat-selected-box">
                    <div class="seat-selected-box-title">Held Seats</div>
                    <div id="selectedSeatLabels" class="seat-selected-tags">No seats selected yet</div>
                </div>

                <div class="seat-total-box">
                    <span>Total Amount</span>
                    <strong id="selectedSeatTotal">0 VND</strong>
                </div>

                <div class="seat-side-actions">
                    <a class="seat-btn seat-btn-outline" href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $showtime['movie_id'] ?>">Back</a>
                    <?php if (!empty($seatMap)): ?>
                        <button type="submit" form="seatForm" class="seat-btn seat-btn-primary" id="continueButton">Continue</button>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const seatMap = document.getElementById('seatMap');
    if (!seatMap) return;

    const showId = seatMap.dataset.showId;
    const baseUrl = seatMap.dataset.baseUrl;
    const selectedSeatLabels = document.getElementById('selectedSeatLabels');
    const selectedSeatTotal = document.getElementById('selectedSeatTotal');
    const holdTimer = document.getElementById('seatHoldTimer');
    const holdText = document.getElementById('seatHoldText');
    const continueButton = document.getElementById('continueButton');
    const availableSeatCount = document.getElementById('availableSeatCount');
    const seatButtons = Array.from(seatMap.querySelectorAll('.seat-item'));

    let holdUntil = seatMap.dataset.holdUntil || '';
    let selectedSeats = new Map();
    let timerRef = null;

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US').format(amount) + ' VND';
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function setButtonState(button, status) {
        button.dataset.status = status;
        button.classList.remove('available', 'selected', 'held', 'booked');
        button.classList.add(status);
        button.disabled = (status === 'held' || status === 'booked');
    }

    function collectSelectedFromDOM() {
        selectedSeats = new Map();

        seatButtons.forEach(function (button) {
            if (button.dataset.status === 'selected') {
                selectedSeats.set(button.dataset.seatId, {
                    label: button.dataset.seatLabel,
                    price: Number(button.dataset.price || 0),
                    category: button.dataset.category || ''
                });
            }
        });
    }

    function renderAvailableCount() {
        let count = 0;
        seatButtons.forEach(function (button) {
            if (button.dataset.status === 'available') {
                count++;
            }
        });
        if (availableSeatCount) {
            availableSeatCount.textContent = count;
        }
    }

    function renderSummary() {
        let total = 0;
        const labels = [];

        selectedSeats.forEach(function (seat) {
            total += seat.price;
            labels.push('<span class="seat-tag">' + seat.label + ' • ' + seat.category + '</span>');
        });

        selectedSeatTotal.textContent = formatCurrency(total);
        selectedSeatLabels.innerHTML = labels.length ? labels.join('') : 'No seats selected yet';

        if (continueButton) {
            continueButton.disabled = selectedSeats.size === 0;
        }

        holdText.textContent = selectedSeats.size
            ? 'Holding ' + selectedSeats.size + ' seat(s). Please complete the booking before the timer expires.'
            : 'You have not held any seats yet.';

        renderAvailableCount();
    }

    function startTimer() {
        if (timerRef) {
            clearInterval(timerRef);
        }

        function updateTimer() {
            if (!holdUntil || selectedSeats.size === 0) {
                holdTimer.textContent = '--:--';
                return;
            }

            const end = new Date(holdUntil.replace(' ', 'T')).getTime();
            const diff = end - Date.now();

            if (diff <= 0) {
                holdTimer.textContent = '00:00';
                clearInterval(timerRef);
                window.location.reload();
                return;
            }

            const totalSeconds = Math.floor(diff / 1000);
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            holdTimer.textContent = pad(minutes) + ':' + pad(seconds);
        }

        updateTimer();
        timerRef = setInterval(updateTimer, 1000);
    }

    async function seatRequest(button, action) {
        const oldStatus = button.dataset.status;

        const body = new URLSearchParams();
        body.append('show_id', showId);
        body.append('show_seat_id', button.dataset.seatId);
        body.append('action', action);

        button.disabled = true;

        try {
            const response = await fetch(baseUrl + 'ajax/hold_seat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: body.toString()
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to update the seat.');
            }

            holdUntil = data.hold_until || '';
            setButtonState(button, action === 'select' ? 'selected' : 'available');
            collectSelectedFromDOM();
            renderSummary();
            startTimer();
        } catch (error) {
            setButtonState(button, oldStatus);
            alert(error.message || 'An error occurred while holding the seats.');
        }
    }

    seatButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const status = button.dataset.status;

            if (status === 'booked' || status === 'held') {
                return;
            }

            if (status === 'selected') {
                seatRequest(button, 'deselect');
            } else {
                seatRequest(button, 'select');
            }
        });
    });

    const seatForm = document.getElementById('seatForm');
    if (seatForm) {
        seatForm.addEventListener('submit', function (event) {
            if (selectedSeats.size === 0) {
                event.preventDefault();
                alert('Please select at least one seat before continuing.');
            }
        });
    }

    collectSelectedFromDOM();
    renderSummary();
    startTimer();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
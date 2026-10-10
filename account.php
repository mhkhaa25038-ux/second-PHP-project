<?php
require_once __DIR__ . '/includes/auth.php';

require_login();

$conn = db_connect();
$user = get_current_user_data();

if (!$user) {
    set_flash_message('error', 'Account information could not be found.');
    redirect('login.php');
}

$profileError = '';
$profileSuccess = '';
$passwordError = '';
$passwordSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionType = trim(post('action_type'));

    if ($actionType === 'update_profile') {
        $name = trim(post('name'));
        $phone = trim(post('phone'));
        $preferredLanguage = trim(post('preferred_language'));

        if (!verify_csrf_token(post('csrf_token'))) {
            $profileError = 'Invalid request. Please refresh the page and try again.';
        } elseif ($name === '') {
            $profileError = 'Please enter your full name.';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $profileError = 'Full name must be between 2 and 100 characters.';
        } elseif (!validate_phone($phone)) {
            $profileError = 'Invalid phone number.';
        } else {
            if ($preferredLanguage === '') {
                $preferredLanguage = 'en';
            }

            if (!in_array($preferredLanguage, get_allowed_languages(), true)) {
                $preferredLanguage = 'en';
            }

            $sql = "
                UPDATE users
                SET name = ?, phone = ?, preferred_language = ?
                WHERE user_id = ?
            ";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $profileError = 'Unable to prepare the profile update statement.';
            } else {
                $userId = (int) $user['user_id'];
                $stmt->bind_param('sssi', $name, $phone, $preferredLanguage, $userId);

                if ($stmt->execute()) {
                    $_SESSION['user_name'] = $name;
                    $profileSuccess = 'Account information updated successfully.';
                    $user = get_current_user_data();
                } else {
                    $profileError = 'Unable to update account information.';
                }

                $stmt->close();
            }
        }
    }

    if ($actionType === 'change_password') {
        $currentPassword = trim(post('current_password'));
        $newPassword = trim(post('new_password'));
        $confirmPassword = trim(post('confirm_password'));

        if (!verify_csrf_token(post('csrf_token'))) {
            $passwordError = 'Invalid request. Please refresh the page and try again.';
        } elseif ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $passwordError = 'Please complete all password fields.';
        } elseif (!password_verify($currentPassword, $user['password_hash'])) {
            $passwordError = 'Your current password is incorrect.';
        } elseif (($passwordValidationError = validate_password_strength($newPassword)) !== null) {
            $passwordError = $passwordValidationError;
        } elseif ($newPassword !== $confirmPassword) {
            $passwordError = 'New password confirmation does not match.';
        } elseif ($currentPassword === $newPassword) {
            $passwordError = 'New password must be different from the current password.';
        } else {
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $passwordError = 'Unable to prepare the password update statement.';
            } else {
                $userId = (int) $user['user_id'];
                $stmt->bind_param('si', $newPasswordHash, $userId);

                if ($stmt->execute()) {
                    $passwordSuccess = 'Password changed successfully.';
                    $user = get_current_user_data();
                } else {
                    $passwordError = 'Unable to change the password.';
                }

                $stmt->close();
            }
        }
    }
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
        m.title AS movie_title,
        m.poster_url,
        s.start_at,
        s.screen_format,
        s.subtitle_type,
        r.room_name,
        l.name AS location_name,
        (
            SELECT GROUP_CONCAT(
                CONCAT(se.seat_row, se.seat_number)
                ORDER BY se.seat_row, CAST(se.seat_number AS UNSIGNED), se.seat_number
                SEPARATOR ', '
            )
            FROM booking_seats bs
            INNER JOIN show_seats ss ON bs.show_seat_id = ss.show_seat_id
            INNER JOIN seats se ON ss.seat_id = se.seat_id
            WHERE bs.booking_id = b.booking_id
        ) AS seat_labels,
        (
            SELECT GROUP_CONCAT(
                CONCAT(cp.name, ' x', bc.quantity)
                SEPARATOR ', '
            )
            FROM booking_combos bc
            INNER JOIN combo_products cp ON bc.combo_id = cp.combo_id
            WHERE bc.booking_id = b.booking_id
        ) AS combo_labels,
        (
            SELECT p.method
            FROM payments p
            WHERE p.booking_id = b.booking_id
            ORDER BY p.payment_id DESC
            LIMIT 1
        ) AS payment_method,
        (
            SELECT p.status
            FROM payments p
            WHERE p.booking_id = b.booking_id
            ORDER BY p.payment_id DESC
            LIMIT 1
        ) AS payment_status,
        (
            SELECT p.transaction_code
            FROM payments p
            WHERE p.booking_id = b.booking_id
            ORDER BY p.payment_id DESC
            LIMIT 1
        ) AS transaction_code
    FROM bookings b
    INNER JOIN showtimes s ON b.show_id = s.show_id
    INNER JOIN movies m ON s.movie_id = m.movie_id
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
";
$bookingStmt = $conn->prepare($bookingSql);
$bookingStmt->bind_param('i', $user['user_id']);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();

require_once __DIR__ . '/includes/header.php';
?>

<section class="account-page-wrap">
    <div class="account-page-inner">
        <div class="account-page-head">
            <div>
                <span class="movie-status-badge showing">MY ACCOUNT</span>
                <h1 class="account-page-title">My Account</h1>
                <p class="account-page-subtitle">
                    Manage your personal details, change your password, and track your booking history on CineBook.
                </p>
            </div>
        </div>

        <div class="account-layout-dark">
            <div class="account-left-dark">
                <div class="account-profile-card-dark">
                    <div class="account-profile-top">
                        <div class="account-avatar-dark">
                            <?= e(mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1))) ?>
                        </div>

                        <div>
                            <h2><?= e($user['name']) ?></h2>
                            <p><?= e($user['email']) ?></p>
                            <div class="account-mini-badges">
                                <span class="account-mini-badge"><?= e($user['member_tier'] ?: 'Standard') ?></span>
                                <span class="account-mini-badge"><?= e($user['role']) ?></span>
                                <span class="account-mini-badge"><?= e($user['status']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="account-stats-dark">
                        <div class="account-stat-box">
                            <span>Loyalty Points</span>
                            <strong><?= (int) $user['loyalty_points'] ?></strong>
                        </div>

                        <div class="account-stat-box">
                            <span>Language</span>
                            <strong><?= e($user['preferred_language'] ?: 'en') ?></strong>
                        </div>

                        <div class="account-stat-box">
                            <span>Email verified</span>
                            <strong><?= (int) $user['email_verified'] === 1 ? 'Yes' : 'No' ?></strong>
                        </div>
                    </div>
                </div>

                <div class="account-form-card-dark">
                    <div class="account-block-head">
                        <h3>Update Profile</h3>
                    </div>

                    <?php if ($profileError !== ''): ?>
                        <div class="auth-error" style="margin-bottom:16px;">
                            <?= e($profileError) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($profileSuccess !== ''): ?>
                        <div class="account-success-message">
                            <?= e($profileSuccess) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action_type" value="update_profile">

                        <div class="account-form-grid">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    name="name"
                                    autocomplete="name"
                                    value="<?= e($user['name']) ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input
                                    class="form-control"
                                    type="email"
                                    value="<?= e($user['email']) ?>"
                                    readonly
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    name="phone"
                                    autocomplete="tel"
                                    value="<?= e($user['phone']) ?>"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Preferred Language</label>
                                <select class="form-control" name="preferred_language">
                                    <option value="vi" <?= ($user['preferred_language'] ?? 'en') === 'vi' ? 'selected' : '' ?>>vi</option>
                                    <option value="en" <?= ($user['preferred_language'] ?? '') === 'en' ? 'selected' : '' ?>>en</option>
                                </select>
                            </div>
                        </div>

                        <button class="movie-book-btn-dark account-save-btn" type="submit">
                            Save Changes
                        </button>
                    </form>
                </div>

                <div class="account-form-card-dark">
                    <div class="account-block-head">
                        <h3>Change Password</h3>
                    </div>

                    <?php if ($passwordError !== ''): ?>
                        <div class="auth-error" style="margin-bottom:16px;">
                            <?= e($passwordError) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($passwordSuccess !== ''): ?>
                        <div class="account-success-message">
                            <?= e($passwordSuccess) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?= csrf_input() ?>
                        <input type="hidden" name="action_type" value="change_password">

                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input
                                class="form-control"
                                type="password"
                                name="current_password"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input
                                class="form-control"
                                type="password"
                                name="new_password"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input
                                class="form-control"
                                type="password"
                                name="confirm_password"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <button class="movie-book-btn-dark account-save-btn" type="submit">
                            Change Password
                        </button>
                    </form>
                </div>
            </div>

            <div class="account-right-dark">
                <div class="account-history-card-dark">
                    <div class="account-block-head">
                        <h3>Booking History</h3>
                    </div>

                    <?php if ($bookingResult && $bookingResult->num_rows > 0): ?>
                        <div class="account-booking-list">
                            <?php while ($booking = $bookingResult->fetch_assoc()): ?>
                                <div class="account-booking-item">
                                    <div class="account-booking-poster">
                                        <img
                                            src="<?= e($booking['poster_url'] ?: 'https://via.placeholder.com/300x420?text=Movie') ?>"
                                            alt="<?= e($booking['movie_title']) ?>"
                                        >
                                    </div>

                                    <div class="account-booking-content">
                                        <div class="account-booking-top">
                                            <div>
                                                <h4><?= e($booking['movie_title']) ?></h4>
                                                <p>Booking Code: <strong><?= e($booking['booking_code']) ?></strong></p>
                                            </div>

                                            <span class="account-booking-status">
                                                <?= e($booking['status']) ?>
                                            </span>
                                        </div>

                                        <div class="account-booking-meta">
                                            <span><?= e($booking['location_name']) ?></span>
                                            <span><?= e($booking['room_name']) ?></span>
                                            <span><?= date('d/m/Y H:i', strtotime($booking['start_at'])) ?></span>
                                        </div>

                                        <div class="account-booking-meta">
                                            <span><?= e($booking['screen_format'] ?: '2D') ?></span>
                                            <span><?= e($booking['subtitle_type'] ?: 'No subtitle') ?></span>
                                            <span>Payment: <?= e($booking['payment_status'] ?: 'N/A') ?></span>
                                        </div>

                                        <div class="account-booking-detail-line">
                                            <span>Seats</span>
                                            <strong><?= e($booking['seat_labels'] ?: 'N/A') ?></strong>
                                        </div>

                                        <div class="account-booking-detail-line">
                                            <span>Combo</span>
                                            <strong><?= e($booking['combo_labels'] ?: 'None') ?></strong>
                                        </div>

                                        <div class="account-booking-detail-line">
                                            <span>Method</span>
                                            <strong><?= e($booking['payment_method'] ?: 'N/A') ?></strong>
                                        </div>

                                        <?php if (!empty($booking['transaction_code'])): ?>
                                            <div class="account-booking-detail-line">
                                                <span>Transaction Code</span>
                                                <strong><?= e($booking['transaction_code']) ?></strong>
                                            </div>
                                        <?php endif; ?>

                                        <div class="account-booking-total">
                                            <span>Total Amount</span>
                                            <strong><?= format_currency($booking['total']) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            There are no bookings in this account yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$bookingStmt->close();
require_once __DIR__ . '/includes/footer.php';
?>
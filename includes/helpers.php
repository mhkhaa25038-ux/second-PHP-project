<?php
require_once __DIR__ . '/../config/database.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path = ''): void
{
    $path = ltrim($path, '/');
    header('Location: ' . BASE_URL . $path);
    exit;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function get_current_user_data(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    $conn = db_connect();
    $userId = (int) $_SESSION['user_id'];

    $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    return $user ?: null;
}

function format_currency($amount): string
{
    return number_format((float)$amount, 0, ',', '.') . ' VND';
}

function generate_booking_code(): string
{
    return 'BK' . date('YmdHis') . rand(100, 999);
}

function set_flash_message(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_message(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function old(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if (is_string($value)) {
        return trim($value);
    }

    return $value;
}

function post(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if (is_string($value)) {
        return trim($value);
    }

    return $value;
}

function get(string $key, $default = '')
{
    $value = $_GET[$key] ?? $default;

    if (is_string($value)) {
        return trim($value);
    }

    return $value;
}

function normalize_email(?string $email): string
{
    return strtolower(trim((string) $email));
}

function validate_phone(?string $phone): bool
{
    $phone = trim((string) $phone);

    if ($phone === '') {
        return true;
    }

    return (bool) preg_match('/^[0-9+()\s.-]{8,20}$/', $phone);
}

function validate_password_strength(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must include at least one uppercase letter.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must include at least one lowercase letter.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must include at least one number.';
    }

    return null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    return is_string($token)
        && $token !== ''
        && is_string($sessionToken)
        && $sessionToken !== ''
        && hash_equals($sessionToken, $token);
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function get_allowed_languages(): array
{
    return ['vi', 'en'];
}

function get_client_ip(): string
{
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
        $_SERVER['REMOTE_ADDR'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        if (!is_string($candidate) || trim($candidate) === '') {
            continue;
        }

        $ip = trim(explode(',', $candidate)[0]);

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return 'unknown';
}

function auth_rate_limit_file(): string
{
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cinebook_auth_rate_limit.json';
}

function auth_rate_limit_read(): array
{
    $file = auth_rate_limit_file();

    if (!file_exists($file)) {
        return [];
    }

    $handle = fopen($file, 'c+');

    if (!$handle) {
        return [];
    }

    flock($handle, LOCK_SH);
    $contents = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $data = json_decode($contents ?: '[]', true);

    return is_array($data) ? $data : [];
}

function auth_rate_limit_write(array $data): void
{
    $file = auth_rate_limit_file();
    $handle = fopen($file, 'c+');

    if (!$handle) {
        return;
    }

    flock($handle, LOCK_EX);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

function auth_rate_limit_key(string $scope, string $identifier): string
{
    return hash('sha256', $scope . '|' . normalize_email($identifier) . '|' . get_client_ip());
}

function is_auth_rate_limited(string $scope, string $identifier, int &$retryAfter = 0): bool
{
    $data = auth_rate_limit_read();
    $key = auth_rate_limit_key($scope, $identifier);
    $record = $data[$key] ?? null;
    $retryAfter = 0;

    if (!is_array($record)) {
        return false;
    }

    $windowSeconds = 900;
    $maxAttempts = 5;
    $now = time();
    $attempts = array_values(array_filter($record['attempts'] ?? [], static function ($timestamp) use ($now, $windowSeconds) {
        return is_int($timestamp) && ($now - $timestamp) <= $windowSeconds;
    }));

    if (count($attempts) >= $maxAttempts) {
        $retryAfter = max(1, $windowSeconds - ($now - min($attempts)));
        return true;
    }

    return false;
}

function register_auth_failure(string $scope, string $identifier): void
{
    $data = auth_rate_limit_read();
    $key = auth_rate_limit_key($scope, $identifier);
    $windowSeconds = 900;
    $now = time();
    $attempts = [];

    if (isset($data[$key]['attempts']) && is_array($data[$key]['attempts'])) {
        foreach ($data[$key]['attempts'] as $timestamp) {
            if (is_int($timestamp) && ($now - $timestamp) <= $windowSeconds) {
                $attempts[] = $timestamp;
            }
        }
    }

    $attempts[] = $now;
    $data[$key] = ['attempts' => $attempts];
    auth_rate_limit_write($data);
}

function clear_auth_failures(string $scope, string $identifier): void
{
    $data = auth_rate_limit_read();
    $key = auth_rate_limit_key($scope, $identifier);

    if (isset($data[$key])) {
        unset($data[$key]);
        auth_rate_limit_write($data);
    }
}

function count_showtime_bookings(mysqli $conn, int $showId): int
{
    $sql = "SELECT COUNT(*) AS total FROM bookings WHERE show_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $showId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return (int) ($row['total'] ?? 0);
}

function sync_show_seats(mysqli $conn, int $showId, int $roomId, float $basePrice, bool $resetAll = false): bool
{
    if ($resetAll) {
        $deleteStmt = $conn->prepare("DELETE FROM show_seats WHERE show_id = ?");

        if (!$deleteStmt) {
            return false;
        }

        $deleteStmt->bind_param('i', $showId);

        if (!$deleteStmt->execute()) {
            $deleteStmt->close();
            return false;
        }

        $deleteStmt->close();
    } else {
        $deleteOrphanSql = "
            DELETE ss
            FROM show_seats ss
            LEFT JOIN booking_seats bs ON ss.show_seat_id = bs.show_seat_id
            LEFT JOIN seats se ON ss.seat_id = se.seat_id
            WHERE ss.show_id = ?
              AND bs.booking_seat_id IS NULL
              AND (se.seat_id IS NULL OR se.room_id <> ?)
        ";
        $deleteOrphanStmt = $conn->prepare($deleteOrphanSql);

        if ($deleteOrphanStmt) {
            $deleteOrphanStmt->bind_param('ii', $showId, $roomId);
            $deleteOrphanStmt->execute();
            $deleteOrphanStmt->close();
        }
    }

    $updateSql = "
        UPDATE show_seats ss
        INNER JOIN seats se ON ss.seat_id = se.seat_id
        INNER JOIN seat_categories sc ON se.category_id = sc.category_id
        SET ss.final_price = ROUND(? * sc.price_multiplier, 2)
        WHERE ss.show_id = ?
          AND se.room_id = ?
    ";
    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {
        return false;
    }

    $updateStmt->bind_param('dii', $basePrice, $showId, $roomId);

    if (!$updateStmt->execute()) {
        $updateStmt->close();
        return false;
    }

    $updateStmt->close();

    $insertSql = "
        INSERT INTO show_seats (show_id, seat_id, status, hold_until, hold_token, final_price)
        SELECT
            ?,
            se.seat_id,
            'available',
            NULL,
            NULL,
            ROUND(? * sc.price_multiplier, 2)
        FROM seats se
        INNER JOIN seat_categories sc ON se.category_id = sc.category_id
        LEFT JOIN show_seats ss ON ss.show_id = ? AND ss.seat_id = se.seat_id
        WHERE se.room_id = ?
          AND ss.show_seat_id IS NULL
    ";
    $insertStmt = $conn->prepare($insertSql);

    if (!$insertStmt) {
        return false;
    }

    $insertStmt->bind_param('idii', $showId, $basePrice, $showId, $roomId);
    $success = $insertStmt->execute();
    $insertStmt->close();

    return $success;
}

function cleanup_expired_show_seat_holds(mysqli $conn): void
{
    $sql = "
        UPDATE show_seats
        SET status = 'available', hold_until = NULL, hold_token = NULL
        WHERE status = 'held'
          AND hold_until IS NOT NULL
          AND hold_until <= NOW()
    ";

    $conn->query($sql);
}

function get_booking_hold_token(): string
{
    if (empty($_SESSION['booking_hold_token'])) {
        $_SESSION['booking_hold_token'] = bin2hex(random_bytes(16));
    }

    return (string) $_SESSION['booking_hold_token'];
}

function clear_booking_hold_token(): void
{
    unset($_SESSION['booking_hold_token']);
}

function release_holds_by_token(mysqli $conn, int $showId, string $token): void
{
    if ($showId <= 0 || $token === '') {
        return;
    }

    $sql = "
        UPDATE show_seats
        SET status = 'available', hold_until = NULL, hold_token = NULL
        WHERE show_id = ?
          AND status = 'held'
          AND hold_token = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return;
    }

    $stmt->bind_param('is', $showId, $token);
    $stmt->execute();
    $stmt->close();
}

function get_hold_expiry_for_token(mysqli $conn, int $showId, string $token): ?string
{
    if ($showId <= 0 || $token === '') {
        return null;
    }

    $sql = "
        SELECT MAX(hold_until) AS hold_until
        FROM show_seats
        WHERE show_id = ?
          AND status = 'held'
          AND hold_token = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('is', $showId, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $row['hold_until'] ?? null;
}

function get_held_show_seat_ids_by_token(mysqli $conn, int $showId, string $token): array
{
    if ($showId <= 0 || $token === '') {
        return [];
    }

    $sql = "
        SELECT show_seat_id
        FROM show_seats
        WHERE show_id = ?
          AND status = 'held'
          AND hold_token = ?
        ORDER BY show_seat_id ASC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('is', $showId, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = (int) $row['show_seat_id'];
    }

    $stmt->close();
    return $ids;
}

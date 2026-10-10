<?php
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    session_start();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('BASE_URL', 'http://localhost:1000/cinebook/');
define('DB_HOST', 'localhost');
define('DB_NAME', 'cinebook');
define('DB_USER', 'root');
define('DB_PASS', '');
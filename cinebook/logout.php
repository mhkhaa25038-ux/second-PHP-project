<?php
require_once __DIR__ . '/includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token(post('csrf_token'))) {
    set_flash_message('error', 'Invalid logout request.');
    redirect('');
}



$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: ' . BASE_URL . 'login.php');
exit;
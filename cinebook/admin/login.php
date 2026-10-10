<?php
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in() && is_admin()) {
    redirect('admin/index.php');
}

$conn = db_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = normalize_email(post('email'));
    $retryAfter = 0;
    $password = post('password');

    if (!verify_csrf_token(post('csrf_token'))) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } elseif (is_auth_rate_limited('admin_login', $email, $retryAfter)) {
        $minutes = (int) ceil($retryAfter / 60);
        $error = 'Too many failed admin login attempts. Please try again in about ' . $minutes . ' minute(s).';
    } else {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                register_auth_failure('admin_login', $email);
                $error = 'Invalid admin credentials.';
            } elseif (($user['status'] ?? 'active') !== 'active') {
                register_auth_failure('admin_login', $email);
                $error = 'This account is locked or inactive.';
            } elseif ($user['role'] !== 'admin') {
                register_auth_failure('admin_login', $email);
                $error = 'This account does not have admin access.';
            } else {
                clear_auth_failures('admin_login', $email);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];

                set_flash_message('success', 'Admin signed in successfully.');
                redirect('admin/index.php');
            }
        } else {
            $error = 'Unable to process admin login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CineBook</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/admin.css?v=4">
    <style>
        .admin-login-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at top left, rgba(30,64,175,0.18), transparent 25%),
                #020817;
            padding: 24px;
        }

        .admin-login-card {
            width: 100%;
            max-width: 480px;
            background: linear-gradient(180deg, #0c1629 0%, #0a1222 100%);
            border: 1px solid rgba(72,95,136,0.28);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 12px 28px rgba(0,0,0,0.28);
        }

        .admin-login-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255,16,16,0.12);
            border: 1px solid rgba(255,16,16,0.18);
            color: #ffd7d9;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .admin-login-title {
            font-size: 34px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #fff;
        }

        .admin-login-subtitle {
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        .admin-login-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
    </style>
</head>
<body>
<section class="admin-login-page">
    <div class="admin-login-card">
        <span class="admin-login-badge">ADMIN ACCESS</span>
        <h1 class="admin-login-title">Admin Sign In</h1>
        <p class="admin-login-subtitle">
            Only accounts with admin privileges can access the system administration area.
        </p>

        <?php if ($error !== ''): ?>
            <div class="admin-flash"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <?= csrf_input() ?>
            <div class="admin-form-group">
                <label class="admin-label">Email</label>
                <input class="admin-input" type="email" name="email" autocomplete="username" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Password</label>
                <input class="admin-input" type="password" name="password" autocomplete="current-password" required>
            </div>

            <div class="admin-login-actions">
                <button class="admin-btn" type="submit">Sign In as Admin</button>
                <a class="admin-btn-outline" href="<?= BASE_URL ?>">Back to Main Site</a>
            </div>
        </form>
    </div>
</section>
</body>
</html>
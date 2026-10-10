<?php
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    redirect('');
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
    } elseif (is_auth_rate_limited('login', $email, $retryAfter)) {
        $minutes = (int) ceil($retryAfter / 60);
        $error = 'Too many failed login attempts. Please try again in about ' . $minutes . ' minute(s).';
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
                register_auth_failure('login', $email);
                $error = 'Invalid email or password.';
            } elseif (($user['status'] ?? 'active') !== 'active') {
                register_auth_failure('login', $email);
                $error = 'This account is locked or inactive.';
            } else {
                clear_auth_failures('login', $email);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];

                set_flash_message('success', 'Signed in successfully.');

                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                }

                redirect('');
            }
        } else {
            $error = 'Unable to process login.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="auth-split">
        <div class="auth-visual" style="background-image: url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80');">
            <div class="auth-visual-overlay"></div>
           
        </div>

        <div class="auth-panel">
            <div class="auth-card">
                <h2 class="auth-title">Login</h2>

                <?php if ($error !== ''): ?>
                    <div class="auth-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <?= csrf_input() ?>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input
                            class="form-control"
                            type="email"
                            name="email"
                            autocomplete="username"
                            placeholder="Enter your email"
                            value="<?= e(old('email')) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input
                            class="form-control"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                  <div class="auth-row">
                     <a href="javascript:void(0)" class="forgot-link" onclick="alert('Please contact the administrator to reset your password.')">
                         Forgot password?
                     </a>
                 </div>

                    <button class="auth-submit" type="submit">Login</button>
                </form>

                <p class="auth-bottom-text">
                    Don't have an account?
                    <a href="<?= BASE_URL ?>register.php">Sign up</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    redirect('');
}

$conn = db_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = post('name');
    $email = normalize_email(post('email'));
    $phone = post('phone');
    $password = post('password');
    $confirmPassword = post('confirm_password');

    if (!verify_csrf_token(post('csrf_token'))) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } elseif ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please fill in all required fields.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $error = 'Full name must be between 2 and 100 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!validate_phone($phone)) {
        $error = 'Invalid phone number.';
    } elseif (($passwordError = validate_password_strength($password)) !== null) {
        $error = $passwordError;
    } elseif ($password !== $confirmPassword) {
        $error = 'Password confirmation does not match.';
    } else {
        $checkSql = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);

        if ($checkStmt) {
            $checkStmt->bind_param('s', $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingUser = $checkResult->fetch_assoc();
            $checkStmt->close();

            if ($existingUser) {
                $error = 'This email is already registered.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (name, email, phone, password_hash, preferred_language, status, role, loyalty_points, member_tier, email_verified)
                        VALUES (?, ?, ?, ?, 'en', 'active', 'user', 0, 'Standard', 0)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param('ssss', $name, $email, $phone, $passwordHash);

                    if ($stmt->execute()) {
                        $stmt->close();
                        set_flash_message('success', 'Registration successful. Please sign in.');
                        redirect('login.php');
                    } else {
                        $error = 'Registration failed.';
                        $stmt->close();
                    }
                } else {
                    $error = 'Unable to process registration.';
                }
            }
        } else {
            $error = 'Unable to verify the email address.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="auth-split">
        <div class="auth-visual" style="background-image: url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=1400&q=80');">
            <div class="auth-visual-overlay"></div>
            <div class="auth-visual-content">
                <h1>Create your account and start booking instantly</h1>
            </div>
        </div>

        <div class="auth-panel">
            <div class="auth-card">
                <h2 class="auth-title">Register</h2>

                <?php if ($error !== ''): ?>
                    <div class="auth-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <?= csrf_input() ?>
                    <div class="form-group">
                        <label class="form-label">Full name</label>
                        <input
                            class="form-control"
                            type="text"
                            name="name"
                            autocomplete="name"
                            minlength="2"
                            maxlength="100"
                            placeholder="Enter your full name"
                            value="<?= e(old('name')) ?>"
                            required 
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input
                            class="form-control"
                            type="email"
                            name="email"
                            autocomplete="email"
                            placeholder="Enter your email"
                            value="<?= e(old('email')) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input
                            class="form-control"
                            type="text"
                            name="phone"
                            autocomplete="tel"
                            inputmode="tel"
                            pattern="[0-9+()\s.-]{8,20}"
                            placeholder="Enter your phone number"
                            value="<?= e(old('phone')) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input
                            class="form-control"
                            type="password"
                            name="password"
                            autocomplete="new-password"
                            minlength="8"
                            placeholder="Create your password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm password</label>
                        <input
                            class="form-control"
                            type="password"
                            name="confirm_password"
                            autocomplete="new-password"
                            minlength="8"
                            placeholder="Re-enter your password"
                            required
                        >
                    </div>

                    <button class="auth-submit" type="submit">Register</button>
                </form>

                <p class="auth-bottom-text">
                    Already have an account?
                    <a href="<?= BASE_URL ?>login.php">Login</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
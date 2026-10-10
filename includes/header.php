<?php
    require_once __DIR__ . '/helpers.php';

    $currentUser = get_current_user_data();
    $flash       = get_flash_message();
    $currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineBook</title>
    <link rel="stylesheet" href="<?php echo BASE_URL ?>public/assets/css/style.css?v=6">
</head>

<body>
    <header class="site-header">
        <div class="nav-wrap">
            <div class="nav-left">
                <a class="logo" href="<?php echo BASE_URL ?>">CINEBOOK</a>

                <nav class="menu">
                    <a class="<?php echo $currentPage === 'index.php' ? 'active' : '' ?>" href="<?php echo BASE_URL ?>">Home</a>
                    <a class="<?php echo $currentPage === 'movies.php' || $currentPage === 'movie_detail.php' ? 'active' : '' ?>"
                        href="<?php echo BASE_URL ?>movies.php">Movies</a>
                    <a class="<?php echo $currentPage === 'cinemas.php' ? 'active' : '' ?>"
                        href="<?php echo BASE_URL ?>cinemas.php">Cinemas</a>
                    <a class="<?php echo $currentPage === 'account.php' ? 'active' : '' ?>"
                        href="<?php echo BASE_URL ?>account.php">Account</a>
                </nav>
            </div>

            <div class="nav-right">
                <a class="search-btn" href="<?php echo BASE_URL ?>movies.php" aria-label="Search">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" />
                        <path d="M20 20L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </a>

                <?php if ($currentUser): ?>
                    <?php
                        $displayName = trim($currentUser['name'] ?? '');
                    ?>

                    <div class="user-box">
                        <a class="user-chip"  title="<?php echo e($displayName) ?>">
                            <span class="user-avatar user-avatar-icon" aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none">
        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z"
              stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M4 20C4.8 16.9 7.7 15 12 15C16.3 15 19.2 16.9 20 20"
              stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</span>
                            <span class="user-name"><?php echo e($displayName) ?></span>
                        </a>

                        <?php if (($currentUser['role'] ?? 'user') === 'admin'): ?>
                            <a class="btn-register header-btn" href="<?php echo BASE_URL ?>admin/index.php">Admin</a>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo BASE_URL ?>logout.php" class="logout-form">
                            <?php echo csrf_input() ?>
                            <button class="nav-link logout-btn header-btn" type="submit">Logout</button>
                        </form>
                    </div>
                <?php else: ?>
                    <a class="nav-link" href="<?php echo BASE_URL ?>login.php">Login</a>
                    <a class="btn-register" href="<?php echo BASE_URL ?>register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="flash-wrap">
            <div class="flash-message flash-<?php echo e($flash['type'] ?? 'info') ?>">
                <?php echo e($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <main class="main-content">
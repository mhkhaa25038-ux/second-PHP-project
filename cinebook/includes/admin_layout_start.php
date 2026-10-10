<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pageTitle = $pageTitle ?? 'Admin Panel';
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - CineBook Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/admin.css?v=3">
</head>
<body>
<div class="admin-shell">
    <?php require_once __DIR__ . '/admin_sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1><?= e($pageTitle) ?></h1>
                <p>Manage CineBook system data</p>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="admin-flash">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
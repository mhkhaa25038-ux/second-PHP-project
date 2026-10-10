<?php
require_once __DIR__ . '/helpers.php';

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash_message('error', 'Please sign in to continue.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_logged_in()) {
        set_flash_message('error', 'Please sign in as an admin.');
        redirect('admin/login.php');
    }

    if (!is_admin()) {
        set_flash_message('error', 'You do not have permission to access the admin area.');
        redirect('');
    }
}
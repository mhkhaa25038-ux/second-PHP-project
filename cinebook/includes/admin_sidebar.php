<?php
$currentPath = $_SERVER['PHP_SELF'] ?? '';

function admin_menu_active(string $needle, string $currentPath): string
{
    return str_contains($currentPath, $needle) ? 'active' : '';
}
?>

<aside class="admin-sidebar">
    <div class="admin-brand">
        <div class="admin-brand-logo">CB</div>
        <div>
            <h2>CineBook</h2>
            <p>Admin Panel</p>
        </div>
    </div>

    <div class="admin-menu-section">
        <div class="admin-menu-label">Overview</div>
        <nav class="admin-menu">
            <a class="<?= admin_menu_active('/admin/index.php', $currentPath) ?>" href="<?= BASE_URL ?>admin/index.php">Dashboard</a>
            <a class="<?= admin_menu_active('/admin/reports/', $currentPath) ?>" href="<?= BASE_URL ?>admin/reports/index.php">Reports</a>
        </nav>
    </div>

    <div class="admin-menu-section">
        <div class="admin-menu-label">Movies</div>
        <nav class="admin-menu">
            <a class="<?= admin_menu_active('/admin/movies/', $currentPath) ?>" href="<?= BASE_URL ?>admin/movies/index.php">Manage Movies</a>
            <a class="<?= admin_menu_active('/admin/genres/', $currentPath) ?>" href="<?= BASE_URL ?>admin/genres/index.php">Manage Genres</a>
            <a class="<?= admin_menu_active('/admin/events/', $currentPath) ?>" href="<?= BASE_URL ?>admin/events/index.php">Manage Events</a>
            <a class="<?= admin_menu_active('/admin/showtimes/', $currentPath) ?>" href="<?= BASE_URL ?>admin/showtimes/index.php">Manage Showtimes</a>
            <a class="<?= admin_menu_active('/admin/banners/', $currentPath) ?>" href="<?= BASE_URL ?>admin/banners/index.php">Manage Banners</a>
        </nav>
    </div>

    <div class="admin-menu-section">
        <div class="admin-menu-label">Cinema</div>
        <nav class="admin-menu">
            <a class="<?= admin_menu_active('/admin/locations/', $currentPath) ?>" href="<?= BASE_URL ?>admin/locations/index.php">Manage Locations</a>
            <a class="<?= admin_menu_active('/admin/rooms/', $currentPath) ?>" href="<?= BASE_URL ?>admin/rooms/index.php">Manage Rooms</a>
            <a class="<?= admin_menu_active('/admin/seat_categories/', $currentPath) ?>" href="<?= BASE_URL ?>admin/seat_categories/index.php">Manage Seat Types</a>
            <a class="<?= admin_menu_active('/admin/seats/', $currentPath) ?>" href="<?= BASE_URL ?>admin/seats/index.php">Manage Seats</a>
        </nav>
    </div>

    <div class="admin-menu-section">
        <div class="admin-menu-label">Sales</div>
        <nav class="admin-menu">
            <a class="<?= admin_menu_active('/admin/combos/', $currentPath) ?>" href="<?= BASE_URL ?>admin/combos/index.php">Manage Combos</a>
        </nav>
    </div>

    <div class="admin-menu-section">
        <div class="admin-menu-label">User</div>
        <nav class="admin-menu">
            <a class="<?= admin_menu_active('/admin/users/', $currentPath) ?>" href="<?= BASE_URL ?>admin/users/index.php">Manage Users</a>
            <a class="<?= admin_menu_active('/admin/bookings/', $currentPath) ?>" href="<?= BASE_URL ?>admin/bookings/index.php">Manage Bookings</a>
        </nav>
    </div>

    <div class="admin-menu-section">
        <div class="admin-menu-label">System</div>
        <nav class="admin-menu">
            <a href="<?= BASE_URL ?>">Open User Site</a>
            <a href="<?= BASE_URL ?>logout.php">Logout</a>
        </nav>
    </div>
</aside>
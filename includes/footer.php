</main>

<footer class="site-footer-pro">
    <div class="footer-top">
        <div class="footer-brand">
            <a class="footer-logo" href="<?= BASE_URL ?>">CINEBOOK</a>
            <p class="footer-desc">
                An online movie ticket booking platform with a modern interface for browsing films, selecting seats, and managing booking history quickly.
            </p>
        </div>

        <div class="footer-links-col">
            <h4>Explore</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>">Home</a></li>
                <li><a href="<?= BASE_URL ?>movies.php">Movies</a></li>
                <li><a href="<?= BASE_URL ?>cinemas.php">Cinemas</a></li>
                <li><a href="<?= BASE_URL ?>login.php">Sign In</a></li>
                <li><a href="<?= BASE_URL ?>register.php">Register</a></li>
            </ul>
        </div>

        <div class="footer-links-col">
            <h4>Account</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>account.php">My Account</a></li>
                <li><a href="<?= BASE_URL ?>movies.php?status=now_showing">Now Showing</a></li>
                <li><a href="<?= BASE_URL ?>movies.php?status=coming_soon">Coming Soon</a></li>
                <li><a href="<?= BASE_URL ?>admin/login.php">Admin</a></li>
            </ul>
        </div>

        <div class="footer-links-col">
            <h4>Contact</h4>
            <ul class="footer-contact-list">
                <li>Email: support@cinebook.vn</li>
                <li>Hotline: 1900 1234</li>
                <li>Address: Ninh Kieu, Can Tho</li>
            </ul>

            <div class="footer-socials">
                <a href="#">Facebook</a>
                <a href="#">Instagram</a>
                <a href="#">YouTube</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2026 CineBook. All rights reserved.</p>
        <p>Developed with core PHP & MySQL.</p>
    </div>
</footer>

<script src="<?= BASE_URL ?>public/assets/js/app.js"></script>
</body>
</html>
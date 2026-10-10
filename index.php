<?php
require_once __DIR__ . '/includes/helpers.php';

$conn = db_connect();

$bannerSql = "SELECT * FROM banners WHERE status = 'active' ORDER BY created_at DESC";
$bannerResult = $conn->query($bannerSql);

$banners = [];
while ($row = $bannerResult->fetch_assoc()) {
    $banners[] = $row;
}
$bannerResult = $conn->query($bannerSql);
$heroBanner = ($bannerResult && $bannerResult->num_rows > 0) ? $bannerResult->fetch_assoc() : null;

$movieSelectionSql = "
SELECT
    m.movie_id,
    m.title,
    m.synopsis,
    m.language,
    m.duration_min,
    m.poster_url,
    m.age_rating,
    m.release_date,
    m.status,
    m.created_at,
    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
FROM movies m
LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
LEFT JOIN genres g ON mg.genre_id = g.genre_id
GROUP BY
    m.movie_id,
    m.title,
    m.synopsis,
    m.language,
    m.duration_min,
    m.poster_url,
    m.age_rating,
    m.release_date,
    m.status,
    m.created_at
ORDER BY
    CASE
        WHEN m.status = 'now_showing' THEN 1
        WHEN m.status = 'coming_soon' THEN 2
        ELSE 3
    END,
    m.release_date ASC,
    m.created_at DESC
";
$movieSelectionResult = $conn->query($movieSelectionSql);

$eventSql = "
    SELECT event_id, title, description, start_at, end_at, image, status
    FROM events
    WHERE status = 'active'
      AND end_at >= NOW()
    ORDER BY start_at ASC
    LIMIT 6
";
$eventResult = $conn->query($eventSql);

require_once __DIR__ . '/includes/header.php';

$heroTitle = $heroBanner['title'] ?? 'Interstellar Journey';
$heroImage = !empty($heroBanner['image_url'])
    ? BASE_URL . 'public/assets/uploads/banners/' . $heroBanner['image_url']
    : 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=1600&q=80';
?>

<section class="hero-section">
    <div class="hero-banner">
        <div class="hero-slider" id="heroSlider">
            <?php foreach ($banners as $index => $banner): ?>
                <?php
                $img = !empty($banner['image_url'])
                    ? BASE_URL . 'public/assets/uploads/banners/' . $banner['image_url']
                    : 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa';
                ?>
                <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>">
                    <div class="hero-image" style="background-image: url('<?= $img ?>');"></div>

                    <div class="hero-overlay"></div>

                    <div class="hero-content">
                       
                        <h1 class="hero-title"><?= e($banner['title']) ?></h1>

                       
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="hero-prev">❮</button>
        <button class="hero-next">❯</button>
    </div>
</section>
<section class="movies-section">
    <div class="section-inner">
        <div class="section-head-decor">
            <span class="decor-line"></span>
            <h2>MOVIE SELECTION</h2>
            <span class="decor-line"></span>
        </div>

        <?php if ($movieSelectionResult && $movieSelectionResult->num_rows > 0): ?>
            <div class="movie-slider-wrapper">
                <div class="movie-slider-viewport">
                    <div class="movie-slider" id="movieSlider">
                        <?php while ($movie = $movieSelectionResult->fetch_assoc()): ?>
                            <?php
                            $statusText = 'Other';
                            $statusClass = 'status-default';

                            if ($movie['status'] === 'now_showing') {
                                $statusText = 'Now Showing';
                                $statusClass = 'status-now-showing';
                            } elseif ($movie['status'] === 'coming_soon') {
                                $statusText = 'Coming Soon';
                                $statusClass = 'status-coming-soon';
                            } elseif ($movie['status'] === 'inactive') {
                                $statusText = 'Stopped Showing';
                                $statusClass = 'status-ended';
                            }
                            ?>

                            <div class="movie-card slider-card">
                                <div class="movie-thumb-wrap">
                                    <span class="movie-badge <?php echo $statusClass; ?>">
                                        <?php echo e($statusText); ?>
                                    </span>

                                    <a href="<?php echo BASE_URL ?>movie_detail.php?id=<?php echo (int) $movie['movie_id']; ?>">
                                        <img class="movie-thumb"
                                            src="<?php echo e($movie['poster_url'] ?: 'https://via.placeholder.com/300x420?text=Movie'); ?>"
                                            alt="<?php echo e($movie['title']); ?>">
                                    </a>
                                </div>

                                <div class="movie-info">
                                    <h3><?php echo e($movie['title']); ?></h3>

                                    <div class="movie-detail-lines">
                                        <p><strong>Genre:</strong> <?php echo e($movie['genres'] ?: 'Updating'); ?></p>
                                        <p><strong>Duration:</strong> <?php echo (int) $movie['duration_min']; ?> min</p>
                                        <p><strong>Release Date:</strong>
                                            <?php echo !empty($movie['release_date']) ? date('d-m-Y', strtotime($movie['release_date'])) : 'Updating'; ?>
                                        </p>
                                    </div>

                                    <a class="movie-link"
                                        href="<?php echo BASE_URL ?>movie_detail.php?id=<?php echo (int) $movie['movie_id']; ?>">
                                        View details
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="movie-slider-controls">
                    <button type="button" class="slider-btn" id="moviePrev">&#10094; Previous</button>
                    <button type="button" class="slider-btn" id="movieNext">Sau &#10095;</button>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">No movie data available.</div>
        <?php endif; ?>
    </div>
</section>
<section class="movies-section" id="events">
    <div class="section-inner">
        <div class="section-head-decor">
            <span class="decor-line"></span>
            <h2>EVENTS</h2>
            <span class="decor-line"></span>
        </div>

        <div class="section-head-accent">
            <span class="accent-dot"></span>
            <span class="accent-text">Exclusive cinema events</span>
            <span class="accent-dot"></span>
        </div>

        <?php if ($eventResult && $eventResult->num_rows > 0): ?>
            <div class="movie-slider-wrapper">
                <div class="movie-slider-viewport">
                    <div class="movie-slider" id="eventSlider">
                        <?php while ($event = $eventResult->fetch_assoc()): ?>
                            <?php
                            $isOngoing = (strtotime($event['start_at']) <= time() && strtotime($event['end_at']) >= time());
                            $eventBadge = $isOngoing ? 'Ongoing' : 'Upcoming';
                            $eventStatusClass = $isOngoing ? 'event-ongoing' : 'event-upcoming';
                            ?>
                            <div class="movie-card slider-card event-card">
                                <div class="movie-thumb-wrap">

                                    <span class="movie-badge event-status <?php echo $eventStatusClass; ?>">
                                        <?php echo e($eventBadge); ?>
                                    </span>

                                    <a href="<?php echo BASE_URL ?>event_detail.php?id=<?php echo (int) $event['event_id']; ?>">
                                        <img class="movie-thumb"
                                            src="<?php echo e($event['image'] ?: 'https://via.placeholder.com/600x400?text=Event'); ?>"
                                            alt="<?php echo e($event['title']); ?>">
                                    </a>
                                </div>

                                <div class="movie-info">


                                    <div class="movie-detail-lines">
                                        <p>
                                            <strong>Start:</strong>
                                            <?php echo date('d/m/Y H:i', strtotime($event['start_at'])); ?>
                                        </p>
                                        <p>
                                            <strong>End:</strong>
                                            <?php echo date('d/m/Y H:i', strtotime($event['end_at'])); ?>
                                        </p>
                                    </div>

                                    <a class="movie-link"
                                        href="<?php echo BASE_URL ?>event_detail.php?id=<?php echo (int) $event['event_id']; ?>">
                                        View event details
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="movie-slider-controls">
                    <button type="button" class="slider-btn" id="eventPrev">&#10094; Previous</button>
                    <button type="button" class="slider-btn" id="eventNext">Sau &#10095;</button>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">There are currently no active events.</div>
        <?php endif; ?>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initSlider(config) {
            const slider = document.getElementById(config.sliderId);
            const prevBtn = document.getElementById(config.prevId);
            const nextBtn = document.getElementById(config.nextId);

            if (!slider || !prevBtn || !nextBtn) return;

            let currentIndex = 0;
            let autoSlide = null;

            function getCardsPerView() {
                if (window.innerWidth <= 576) return config.mobile || 1;
                if (window.innerWidth <= 992) return config.tablet || 2;
                return config.desktop || 4;
            }

            function getStep() {
                if (config.step === 1) return 1;
                return getCardsPerView();
            }

            function getCardWidth() {
                const firstCard = slider.querySelector(config.cardSelector);
                if (!firstCard) return 0;

                const gap = config.gap || 24;
                return firstCard.offsetWidth + gap;
            }

            function getMaxIndex() {
                const cards = slider.querySelectorAll(config.cardSelector);
                const totalCards = cards.length;
                const cardsPerView = getCardsPerView();
                return Math.max(0, totalCards - cardsPerView);
            }

            function updateSlider() {
                const maxIndex = getMaxIndex();

                if (currentIndex > maxIndex) currentIndex = maxIndex;
                if (currentIndex < 0) currentIndex = 0;

                const moveX = currentIndex * getCardWidth();
                slider.style.transform = `translateX(-${moveX}px)`;

                prevBtn.disabled = currentIndex === 0;
                nextBtn.disabled = currentIndex >= maxIndex;

                prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
                nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
                prevBtn.style.cursor = currentIndex === 0 ? 'not-allowed' : 'pointer';
                nextBtn.style.cursor = currentIndex >= maxIndex ? 'not-allowed' : 'pointer';
            }

            function nextSlide() {
                const maxIndex = getMaxIndex();
                const step = getStep();

                if (currentIndex < maxIndex) {
                    currentIndex += step;
                    if (currentIndex > maxIndex) currentIndex = maxIndex;
                } else if (config.loop) {
                    currentIndex = 0;
                }

                updateSlider();
            }

            function prevSlide() {
                const step = getStep();

                if (currentIndex > 0) {
                    currentIndex -= step;
                    if (currentIndex < 0) currentIndex = 0;
                    updateSlider();
                }
            }

            function startAutoPlay() {
                if (!config.autoPlay) return;
                stopAutoPlay();
                autoSlide = setInterval(function () {
                    const maxIndex = getMaxIndex();

                    if (currentIndex >= maxIndex) {
                        currentIndex = 0;
                    } else {
                        currentIndex += 1;
                    }

                    updateSlider();
                }, config.interval || 4500);
            }

            function stopAutoPlay() {
                if (autoSlide) {
                    clearInterval(autoSlide);
                    autoSlide = null;
                }
            }

            nextBtn.addEventListener('click', nextSlide);
            prevBtn.addEventListener('click', prevSlide);

            slider.addEventListener('mouseenter', stopAutoPlay);
            slider.addEventListener('mouseleave', startAutoPlay);

            prevBtn.addEventListener('mouseenter', stopAutoPlay);
            nextBtn.addEventListener('mouseenter', stopAutoPlay);
            prevBtn.addEventListener('mouseleave', startAutoPlay);
            nextBtn.addEventListener('mouseleave', startAutoPlay);

            window.addEventListener('resize', updateSlider);

            updateSlider();
            startAutoPlay();
        }

        initSlider({
            sliderId: 'movieSlider',
            prevId: 'moviePrev',
            nextId: 'movieNext',
            cardSelector: '.slider-card',
            desktop: 4,
            tablet: 2,
            mobile: 1,
            gap: 24,
            step: 'page',
            autoPlay: false,
            loop: false
        });

        initSlider({
            sliderId: 'eventSlider',
            prevId: 'eventPrev',
            nextId: 'eventNext',
            cardSelector: '.event-slider-card',
            desktop: 3,
            tablet: 2,
            mobile: 1,
            gap: 24,
            step: 1,
            autoPlay: true,
            interval: 4000,
            loop: true
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initSimpleSlider(sliderId, prevId, nextId) {
            const slider = document.getElementById(sliderId);
            const prevBtn = document.getElementById(prevId);
            const nextBtn = document.getElementById(nextId);

            if (!slider || !prevBtn || !nextBtn) return;

            let currentIndex = 0;

            function getCardsPerView() {
                if (window.innerWidth <= 576) return 1;
                if (window.innerWidth <= 992) return 2;
                return 4;
            }

            function getCardWidth() {
                const firstCard = slider.querySelector('.slider-card');
                if (!firstCard) return 0;
                return firstCard.offsetWidth + 24;
            }

            function updateSlider() {
                const cards = slider.querySelectorAll('.slider-card');
                const totalCards = cards.length;
                const cardsPerView = getCardsPerView();
                const maxIndex = Math.max(0, totalCards - cardsPerView);

                if (currentIndex > maxIndex) currentIndex = maxIndex;

                const moveX = currentIndex * getCardWidth();
                slider.style.transform = `translateX(-${moveX}px)`;

                prevBtn.disabled = currentIndex === 0;
                nextBtn.disabled = currentIndex >= maxIndex;

                prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
                nextBtn.style.opacity = currentIndex >= maxIndex ? '0.5' : '1';
                prevBtn.style.cursor = currentIndex === 0 ? 'not-allowed' : 'pointer';
                nextBtn.style.cursor = currentIndex >= maxIndex ? 'not-allowed' : 'pointer';
            }

            nextBtn.addEventListener('click', function () {
                const totalCards = slider.querySelectorAll('.slider-card').length;
                const cardsPerView = getCardsPerView();
                const maxIndex = Math.max(0, totalCards - cardsPerView);

                if (currentIndex < maxIndex) {
                    currentIndex += cardsPerView;
                    if (currentIndex > maxIndex) currentIndex = maxIndex;
                    updateSlider();
                }
            });

            prevBtn.addEventListener('click', function () {
                const cardsPerView = getCardsPerView();

                if (currentIndex > 0) {
                    currentIndex -= cardsPerView;
                    if (currentIndex < 0) currentIndex = 0;
                    updateSlider();
                }
            });

            window.addEventListener('resize', updateSlider);
            updateSlider();
        }

        initSimpleSlider('eventSlider', 'eventPrev', 'eventNext');
    });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
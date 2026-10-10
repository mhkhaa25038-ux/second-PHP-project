<?php
require_once __DIR__ . '/includes/helpers.php';

$conn = db_connect();
$eventId = (int) get('id');

if ($eventId <= 0) {
    set_flash_message('error', 'Invalid event.');
    redirect('');
}

$eventSql = "
    SELECT event_id, title, description, start_at, end_at, image, status
    FROM events
    WHERE event_id = ?
      AND status = 'active'
    LIMIT 1
";
$eventStmt = $conn->prepare($eventSql);
$eventStmt->bind_param('i', $eventId);
$eventStmt->execute();
$eventResult = $eventStmt->get_result();
$event = $eventResult->fetch_assoc();
$eventStmt->close();

if (!$event) {
    set_flash_message('error', 'Event not found.');
    redirect('');
}

$relatedSql = "
    SELECT event_id, title, start_at, end_at, image
    FROM events
    WHERE status = 'active'
      AND event_id <> ?
      AND end_at >= NOW()
    ORDER BY start_at ASC
    LIMIT 3
";
$relatedStmt = $conn->prepare($relatedSql);
$relatedStmt->bind_param('i', $eventId);
$relatedStmt->execute();
$relatedEvents = $relatedStmt->get_result();

$isOngoing = (strtotime($event['start_at']) <= time() && strtotime($event['end_at']) >= time());
$badgeText = $isOngoing ? 'ONGOING' : 'UPCOMING';

require_once __DIR__ . '/includes/header.php';
?>

<section class="movie-detail-page">
    <div class="movie-detail-shell">
        <div class="movie-detail-main">
            <div class="movie-detail-poster">
                <img
                    src="<?= e($event['image'] ?: 'https://via.placeholder.com/600x800?text=Event') ?>"
                    alt="<?= e($event['title']) ?>"
                >
            </div>

            <div class="movie-detail-info">
               

                <h1 class="movie-detail-title"><?= e($event['title']) ?></h1>

                

               <div class="events-detail-desc">
                    <h3>Event Description</h3>
                    <p><?= nl2br(e($event['description'] ?: 'No detailed description is available for this event yet.')) ?></p>
               </div> <br>

                <div class="movie-detail-extra">
                    <div class="movie-detail-extra-item">
                        <span>Start time</span>
                        <strong><?= date('d/m/Y H:i', strtotime($event['start_at'])) ?></strong>
                    </div>

                    <div class="movie-detail-extra-item">
                        <span>End time</span>
                        <strong><?= date('d/m/Y H:i', strtotime($event['end_at'])) ?></strong>
                    </div>
                </div>

                <div class="movie-detail-actions">
                    <a class="movie-detail-outline-btn" href="<?= BASE_URL ?>#events">Back to Events</a>
                </div>
            </div>
        </div>

        <div class="movie-detail-block">
            <div class="movie-detail-block-head">
                <h2>Other Events</h2>
            </div>

            <?php if ($relatedEvents && $relatedEvents->num_rows > 0): ?>
                <div class="showtime-grid-dark">
                    <?php while ($related = $relatedEvents->fetch_assoc()): ?>
                        <a class="showtime-card-dark" href="<?= BASE_URL ?>event_detail.php?id=<?= (int) $related['event_id'] ?>">
                            <div class="showtime-card-time">
                                <?= date('d/m H:i', strtotime($related['start_at'])) ?>
                            </div>
                            <div class="showtime-card-location">
                                <?= e($related['title']) ?>
                            </div>
                            <div class="showtime-card-meta">
                                End: <?= date('d/m H:i', strtotime($related['end_at'])) ?>
                            </div>
                            <div class="showtime-card-price">
                                View details
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">There are no related events yet.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
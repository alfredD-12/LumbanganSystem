<?php
/**
 * Reusable Activity Item Component
 * For complaints, events, etc.
 */

function renderActivityItem($data) {
    $title = $data['title'] ?? 'N/A';
    $time = $data['time'] ?? '';
    $badge = $data['badge'] ?? '';
    $badge_class = $data['badge_class'] ?? 'badge-new';
    ?>
    <div class="activity-item">
        <div class="activity-title"><?php echo htmlspecialchars($title); ?></div>
        <div class="activity-time">
            <i class="fas fa-clock"></i>
            <?php echo htmlspecialchars($time); ?>
        </div>
        <span class="activity-badge <?php echo htmlspecialchars($badge_class); ?>">
            <?php echo htmlspecialchars($badge); ?>
        </span>
    </div>
    <?php
}

function renderEventItem($data) {
    $type = $data['type'] ?? 'meeting';
    $icon = $data['icon'] ?? 'fas fa-calendar';
    $title = $data['title'] ?? 'N/A';
    $date = $data['date'] ?? '';
    $time = $data['time'] ?? '';
    $location = $data['location'] ?? '';
    ?>
    <div class="event-item event-<?php echo htmlspecialchars($type); ?>">
        <div class="event-icon">
            <i class="<?php echo htmlspecialchars($icon); ?>"></i>
        </div>
        <div class="event-details">
            <h4 class="event-title"><?php echo htmlspecialchars($title); ?></h4>
            <p class="event-datetime">
                <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($date); ?> • 
                <i class="fas fa-clock"></i> <?php echo htmlspecialchars($time); ?>
            </p>
            <p class="event-location">
                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($location); ?>
            </p>
        </div>
    </div>
    <?php
}

<?php
/**
 * Notification Item Component for Modal
 */

function renderNotificationItem($data) {
    $initials = $data['initials'] ?? '??';
    $gradient = $data['gradient'] ?? 'linear-gradient(135deg, #3b82f6, #2563eb)';
    $name = $data['name'] ?? 'N/A';
    $role = $data['role'] ?? '';
    $title = $data['title'] ?? '';
    $message = $data['message'] ?? '';
    $time = $data['time'] ?? '';
    $unread = $data['unread'] ?? false;
    $badge_count = $data['badge_count'] ?? 0;
    
    $bg_color = $unread ? '#eff6ff' : 'white';
    ?>
    <div class="notification-item">
        <div class="notif-avatar" style="background: <?php echo htmlspecialchars($gradient); ?>;">
            <?php echo htmlspecialchars($initials); ?>
            <?php if ($unread): ?>
            <span class="notif-online-indicator"></span>
            <?php endif; ?>
        </div>
        <div class="notif-content">
            <div class="notif-header">
                <div>
                    <h6 class="notif-name"><?php echo htmlspecialchars($name); ?></h6>
                    <small class="notif-role"><?php echo htmlspecialchars($role); ?></small>
                </div>
                <div class="notif-time-badge">
                    <span class="notif-time <?php echo $unread ? 'unread' : ''; ?>">
                        <?php echo htmlspecialchars($time); ?>
                    </span>
                    <?php if ($badge_count > 0): ?>
                    <div class="notif-badge"><?php echo $badge_count; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <p class="notif-title"><?php echo htmlspecialchars($title); ?></p>
            <p class="notif-message"><?php echo htmlspecialchars($message); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Message Item Component for Modal
 */

function renderMessageItem($data) {
    $initials = $data['initials'] ?? '??';
    $gradient = $data['gradient'] ?? 'linear-gradient(135deg, #3b82f6, #2563eb)';
    $name = $data['name'] ?? 'N/A';
    $role = $data['role'] ?? '';
    $title = $data['title'] ?? '';
    $message = $data['message'] ?? '';
    $time = $data['time'] ?? '';
    $online = $data['online'] ?? false;
    $unread_count = $data['unread_count'] ?? 0;
    
    $bg_color = $unread_count > 0 ? '#eff6ff' : 'white';
    $title_weight = $unread_count > 0 ? '700' : '500';
    $name_weight = $unread_count > 0 ? '700' : '600';
    ?>
    <div class="message-item">
        <div class="msg-avatar" style="background: <?php echo htmlspecialchars($gradient); ?>;">
            <?php echo htmlspecialchars($initials); ?>
            <?php if ($online): ?>
            <span class="msg-online-indicator"></span>
            <?php endif; ?>
        </div>
        <div class="msg-content">
            <div class="msg-header">
                <div>
                    <h6 class="msg-name" style="font-weight: <?php echo $name_weight; ?>;">
                        <?php echo htmlspecialchars($name); ?>
                    </h6>
                    <small class="msg-role"><?php echo htmlspecialchars($role); ?></small>
                </div>
                <div class="msg-time-badge">
                    <span class="msg-time <?php echo $unread_count > 0 ? 'unread' : ''; ?>">
                        <?php echo htmlspecialchars($time); ?>
                    </span>
                    <?php if ($unread_count > 0): ?>
                    <div class="msg-badge"><?php echo $unread_count; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <p class="msg-title" style="font-weight: <?php echo $title_weight; ?>;">
                <?php echo htmlspecialchars($title); ?>
            </p>
            <p class="msg-message"><?php echo htmlspecialchars($message); ?></p>
        </div>
    </div>
    <?php
}

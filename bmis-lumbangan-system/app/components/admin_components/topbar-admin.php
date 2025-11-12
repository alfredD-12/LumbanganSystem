<?php
/**
 * Admin Top Bar Component
 * Reusable header bar with government seals, title, notifications, and profile
 * 
 * Usage:
 * <?php 
 *     $pageTitle = 'Your Page Title';
 *     $pageSubtitle = 'Your page description';
 *     include '../../components/admin_components/topbar-admin.php'; 
 * ?>
 */

// Set defaults if not provided
if (!isset($pageTitle)) {
    $pageTitle = 'Barangay Lumbangan Analytics Dashboard';
}
if (!isset($pageSubtitle)) {
    $pageSubtitle = 'Monitoring and managing barangay operations and resident services';
}
if (!isset($adminName)) {
    $adminName = 'Admin Secretary';
}
if (!isset($adminRole)) {
    $adminRole = 'Barangay Administrator';
}

// Auto-load CSS and JS assets (prevents duplicate loading)
$assetBase = '../..';
if (strpos($_SERVER['SCRIPT_NAME'], '/views/') !== false) {
    $assetBase = '../..';
}
?>

<script>
(function() {
    const topbarCssId = 'topbar-admin-css';
    const topbarJsId = 'topbar-admin-js';
    
    // Load CSS if not already loaded
    if (!document.getElementById(topbarCssId)) {
        const link = document.createElement('link');
        link.id = topbarCssId;
        link.rel = 'stylesheet';
        link.href = '<?= $assetBase ?>/assets/css/SecDash/topbar-admin.css';
        document.head.appendChild(link);
    }
    
    // Load JS if not already loaded
    if (!document.getElementById(topbarJsId)) {
        const script = document.createElement('script');
        script.id = topbarJsId;
        script.src = '<?= $assetBase ?>/assets/js/SecDash/topbar-admin.js';
        document.body.appendChild(script);
    }
})();
</script>

<!-- Admin Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <!-- Government Seals -->
        <div class="government-seals">
            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Bagong_Pilipinas_logo.png"
                 alt="Bagong Pilipinas" 
                 title="Bagong Pilipinas">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Seal_of_Nasugbu.png/599px-Seal_of_Nasugbu.png"
                 alt="Nasugbu Seal" 
                 title="Municipality of Nasugbu">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Seal_of_Batangas.png"
                 alt="Batangas Seal" 
                 title="Province of Batangas">
        </div>
        
        <!-- Title -->
        <div class="top-bar-title">
            <h2>
                <?= htmlspecialchars($pageTitle) ?>
                <small><?= htmlspecialchars($pageSubtitle) ?></small>
            </h2>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="top-bar-actions">
        <!-- Notifications Button -->
        <button class="action-icon-btn" title="Notifications" onclick="toggleNotifications()">
            <i class="fas fa-bell"></i>
            <span class="badge-count pulse">5</span>
        </button>

        <!-- Messages/Inbox Button -->
        <button class="action-icon-btn" title="Messages" onclick="toggleMessages()">
            <i class="fas fa-envelope"></i>
            <span class="badge-count">12</span>
        </button>
    </div>

    <!-- Admin Profile Dropdown -->
    <div class="admin-profile dropdown">
        <div class="admin-info">
            <div class="name"><?= htmlspecialchars($adminName) ?></div>
            <div class="role"><?= htmlspecialchars($adminRole) ?></div>
        </div>
        <div class="admin-avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;" title="Admin Profile">
            <i class="fas fa-user"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 200px;">
            <li>
                <div class="dropdown-item-text" style="border-bottom: 1px solid #e9ecef; padding-bottom: 10px; margin-bottom: 5px;">
                    <strong><?= htmlspecialchars($adminName) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($adminRole) ?></small>
                </div>
            </li>
            <li>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#adminProfileModal">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="../../controllers/AuthController.php?action=logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

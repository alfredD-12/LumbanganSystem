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
?>
<!-- Dropdown fallback: ensure admin profile dropdown opens/closes even if Bootstrap JS is missing or conflicting -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var profile = document.querySelector('.admin-profile');
        if (!profile) return;
        var toggle = profile.querySelector('.dropdown-toggle');
        var menu = profile.querySelector('.dropdown-menu');

        function closeProfile(e) {
            if (!profile.contains(e.target)) {
                profile.classList.remove('show');
                if (menu) menu.classList.remove('show');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
                document.removeEventListener('click', closeProfile);
            }
        }

        if (toggle) {
            toggle.addEventListener('click', function(ev) {
                ev = ev || window.event;
                if (ev && ev.preventDefault) ev.preventDefault();
                if (ev && ev.stopPropagation) ev.stopPropagation();
                
                var shown = menu && menu.classList.contains('show');
                if (shown) {
                    profile.classList.remove('show');
                    menu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.removeEventListener('click', closeProfile);
                } else {
                    profile.classList.add('show');
                    if (menu) menu.classList.add('show');
                    toggle.setAttribute('aria-expanded', 'true');
                    // Close when clicking outside
                    setTimeout(function() { document.addEventListener('click', closeProfile); }, 10);
                }
            });
        }
    });
</script>

<?php
if (!isset($adminRole)) {
    $adminRole = 'Barangay Administrator';
}

// Load official profile helper to populate fields when an official is logged in
if (file_exists(__DIR__ . '/../../helpers/official_profile_helper.php')) {
    require_once __DIR__ . '/../../helpers/official_profile_helper.php';
    $_official_profile = get_official_profile();
    if ($_official_profile) {
        $adminName = $_official_profile['full_name'] ?? $adminName; // fallback
        $adminRole = $_official_profile['role'] ?? $adminRole;
        $adminEmail = $_official_profile['email'] ?? '';
        $adminContact = $_official_profile['contact_no'] ?? '';
    }
}

// Auto-load CSS and JS assets (prevents duplicate loading)
// Prefer BASE_URL when configured so asset paths are absolute and correct
$assetBase = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '..';

// Ensure BASE_PUBLIC is available for logout URL
if (!defined('BASE_PUBLIC')) {
    @require_once dirname(__DIR__, 2) . '/config/config.php';
}
$logoutUrl = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=logout';
?>

<script>
(function() {
    const topbarCssId = 'topbar-admin-css';
    const topbarJsId = 'topbar-admin-js';
    const notificationCssId = 'notification-system-css';
    const notificationJsId = 'notification-system-js';
    
    // Load CSS if not already loaded
    if (!document.getElementById(topbarCssId)) {
        const link = document.createElement('link');
        link.id = topbarCssId;
        link.rel = 'stylesheet';
        link.href = '<?= $assetBase ?>/assets/css/SecDash/topbar-admin.css';
        document.head.appendChild(link);
    }
    
    // Load Notification System CSS if not already loaded
    if (!document.getElementById(notificationCssId)) {
        const link = document.createElement('link');
        link.id = notificationCssId;
        link.rel = 'stylesheet';
        link.href = '<?= $assetBase ?>/assets/css/notifications.css';
        document.head.appendChild(link);
    }
    
    // Load JS if not already loaded
    if (!document.getElementById(topbarJsId)) {
        const script = document.createElement('script');
        script.id = topbarJsId;
        script.src = '<?= $assetBase ?>/assets/js/SecDash/topbar-admin.js';
        document.body.appendChild(script);
    }
    
    // Load Notification System JS if not already loaded
    if (!document.getElementById(notificationJsId)) {
        const script = document.createElement('script');
        script.id = notificationJsId;
        script.src = '<?= $assetBase ?>/assets/js/notifications.js';
        document.body.appendChild(script);
    }
})();
</script>

<?php if (function_exists('render_official_profile_script')) { render_official_profile_script(); } ?>

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
        <button class="action-icon-btn" title="Notifications" data-bs-toggle="modal" data-bs-target="#notificationsModal">
            <i class="fas fa-bell"></i>
            <span class="badge-count pulse">5</span>
        </button>

    </div>

    <!-- Admin Profile Dropdown -->
    <div class="admin-profile dropdown">
        <div class="admin-info">
                <div id="adminDisplayName" class="name"><?= htmlspecialchars($adminName) ?></div>
                <div id="adminDisplayRole" class="role"><?= htmlspecialchars($adminRole) ?></div>
            </div>
        <div class="admin-avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;" title="Admin Profile">
            <i class="fas fa-user"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 200px;">
            <li>
                <div class="dropdown-item-text" style="border-bottom: 1px solid #e9ecef; padding-bottom: 10px; margin-bottom: 5px;">
                    <strong id="adminDisplayNameDropdown"><?= htmlspecialchars($adminName) ?></strong><br>
                    <small id="adminDisplayRoleDropdown" class="text-muted"><?= htmlspecialchars($adminRole) ?></small>
                </div>
            </li>
            <li>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#adminProfileModal">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="#" onclick="handleLogout(event)">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Fallback handleLogout for admin topbar if global handler isn't present -->
<script>
    if (typeof window.handleLogout !== 'function') {
        window.handleLogout = function(event) {
            if (event && event.preventDefault) event.preventDefault();
            try {
                if (window.SurveyPersistence && typeof window.SurveyPersistence.clearAll === 'function') {
                    window.SurveyPersistence.clearAll();
                }
            } catch (e) {}
            try {
                Object.keys(localStorage).forEach(function(k) {
                    if (k && k.indexOf && k.indexOf('survey_') === 0) localStorage.removeItem(k);
                });
            } catch (e) {}
            window.location.href = '<?php echo $logoutUrl; ?>';
        };
    }
</script>

<?php
// Admin Sidebar Component - Reusable sidebar for all admin pages
// Auto-loads CSS and JS assets

include_once __DIR__ . '/../../config/config.php';

// Load assets only once
if (!defined('ADMIN_SIDEBAR_ASSETS_LOADED')) {
    define('ADMIN_SIDEBAR_ASSETS_LOADED', true);
    $cssHref = rtrim(BASE_URL, '/') . '/assets/css/admins/document_type.css';
    $jsSrc = rtrim(BASE_URL, '/') . '/assets/js/SecDash/sidebar-admin.js';
    echo <<<HTML
<script>(function(d){
    var h=d.head||d.getElementsByTagName('head')[0];
    function addOnce(id, html){ 
        if(d.getElementById(id)) return; 
        var t=d.createElement('template'); 
        t.innerHTML=html; 
        h.appendChild(t.content.firstChild); 
    }
    addOnce('admin-sidebar-fonts','<link id="admin-sidebar-fonts" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">');
    addOnce('admin-sidebar-fa','<link id="admin-sidebar-fa" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">');
    addOnce('admin-sidebar-bs','<link id="admin-sidebar-bs" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        addOnce('admin-sidebar-css','<link id="admin-sidebar-css" rel="stylesheet" href="$cssHref">');
    
    // Bootstrap JS
    if(!('bootstrap' in window) && !d.getElementById('admin-sidebar-bs-js')){
        var s=d.createElement('script'); 
        s.id='admin-sidebar-bs-js'; 
        s.defer=true; 
        s.src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'; 
        h.appendChild(s);
    }
    
        addOnce('admin-sidebar-js','<script id="admin-sidebar-js" defer src="<?php echo BASE_URL . "assets/js/SecDash/sidebar-admin.js" ?>"><\/script>');
})(document);</script>
HTML;
}
render_favicon()
?>

<!-- Floating Background Shapes -->
<div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
</div>

<!-- Admin Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">
            <i class="fas fa-landmark"></i>
        </div>
        <h4>BARANGAY</h4>
        <small>Admin Portal</small>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo htmlspecialchars(BASE_PUBLIC . 'index.php?page=dashboard_official', ENT_QUOTES, 'UTF-8'); ?>"  class="<?php echo ( ($currentPage === 'admin_dashboard' || $currentPage === 'dashboard_official') ? 'active' : '' ); ?>" data-tooltip="Dashboard">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?php echo htmlspecialchars(BASE_PUBLIC . 'index.php?page=admin_announcements', ENT_QUOTES, 'UTF-8'); ?>"  class="<?php echo ($currentPage === 'admin_announcements') ? 'active' : ''; ?>" data-tooltip="Announcements">
                <i class="fas fa-bullhorn"></i>
                <span>Announcements</span>
            </a>
        </li>
        <li>
            <a href="<?php echo htmlspecialchars(BASE_PUBLIC . 'index.php?page=admin_complaints', ENT_QUOTES, 'UTF-8'); ?>"  class="<?php echo ($currentPage === 'admin_complaints') ? 'active' : ''; ?>" data-tooltip="Complaints">
                <i class="fas fa-exclamation-circle"></i>
                <span>Complaints</span>
            </a>
        </li>
        <li>
            <a href="<?php echo htmlspecialchars(BASE_PUBLIC . 'index.php?page=admin_officials', ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($currentPage === 'admin_officials') ? 'active' : ''; ?>" data-tooltip="Officials">
                <i class="fas fa-users"></i>
                <span>Officials</span>
            </a>
        </li>
        <li>
            <a href="<?php echo htmlspecialchars(BASE_PUBLIC . 'index.php?page=admin_document_requests', ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($currentPage === 'admin_documents') ? 'active' : ''; ?>" data-tooltip="Documents">
                <i class="fas fa-file-alt"></i>
                <span>Documents</span>
            </a>
        </li>
        <li>
            <a href="<?php echo htmlspecialchars(BASE_PUBLIC . 'index.php?page=admin_residents', ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($currentPage === 'admin_residents') ? 'active' : ''; ?>" data-tooltip="Residents">
                <i class="fas fa-user-friends"></i>
                <span>Residents</span>
            </a>
        </li>
        <li>
            <a href="<?php echo htmlspecialchars(BASE_PUBLIC . 'index.php?page=admin_settings', ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo ($currentPage === 'admin_settings') ? 'active' : ''; ?>" data-tooltip="Settings">
                <i class="fas fa-images"></i>
                <span>Gallery</span>
            </a>
        </li>
    </ul>

    <!-- Dark Mode Toggle -->
    <div class="sidebar-footer">
        <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
            <span>Dark Mode</span>
        </button>
    </div>
</aside>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle" title="Collapse Sidebar">
    <i class="fas fa-angle-left"></i>
</button>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>


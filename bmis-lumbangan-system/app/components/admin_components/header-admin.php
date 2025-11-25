<?php 
include_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/notification_helper.php';

// Load official profile helper to populate header fields
if (file_exists(__DIR__ . '/../../helpers/official_profile_helper.php')) {
    require_once __DIR__ . '/../../helpers/official_profile_helper.php';
    $_official_profile = get_official_profile();
    if ($_official_profile) {
        $adminName = $_official_profile['full_name'] ?? ($adminName ?? 'Admin Secretary');
        $adminRole = $_official_profile['role'] ?? ($adminRole ?? 'Barangay Administrator');
        $adminEmail = $_official_profile['email'] ?? ($adminEmail ?? '');
        $adminContact = $_official_profile['contact_no'] ?? ($adminContact ?? '');
    }
}

// Fetch notifications for admin
$admin_user_id = $_SESSION['user_id'] ?? null;
$admin_notifications = getNotifications('admin', $admin_user_id, 20, false);
$admin_unread_count = getUnreadCount('admin', $admin_user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Barangay Lumbangan Admin System'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <!-- Combined Admin Header CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/SecDash/admin-header.css'; ?>">
    <!-- Document Request CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/admins/document_admin.css'; ?>">

    <!-- Additional links if needed can be added below -->
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" integrity="sha384-zmMNeKbOwzvUmxN8Z/VoYM+i+cwyC14+U9lq4+ZL0Ro7p1GMoh8uq8/HvIBgnh9+" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.bootstrap5.min.css" rel="stylesheet" integrity="sha384-HI7qMf1hznIZrIds5RatHHAOCn/7uGgsYQCanIyCeJDebKwCnoWnm4cB9SH+Z/ab" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/datatables.min.css" rel="stylesheet" integrity="sha384-b7CCWUkHYYyObRWK8dDxH6PCbeH3SHTbH+TzwIoEUU/Ol75XipyzcYbfdNWmNWFF" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.3.4/cr-2.1.2/datatables.min.css" rel="stylesheet" integrity="sha384-Kmlp1CBAWUtz5k1YIckZJpfqfz679/v2c11h9L26srNf/fL27CqaljUGsp7P2SN9" crossorigin="anonymous">
    <!-- Tiny MCE Links -->
    <script src="https://cdn.tiny.cloud/1/hunfvs9zjm4i9ph6m4ls99rumaquw91oqwz981hcx0er0xsp/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    <script src="<?php echo BASE_URL . 'assets/js/tinymce.js'; ?>"> </script>
    <!-- Admin Complaint Page CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/complaint/admin.css'; ?>">
    <!-- Admin Complaint Page CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/complaint/admin.css'; ?>">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/announcement/announcements_modern.css?v=<?php echo time(); ?>">
</head>

<body>

    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

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
                    <?php echo htmlspecialchars($pageTitle ?? 'Barangay Lumbangan Analytics Dashboard'); ?>
                    <small><?php echo htmlspecialchars($pageSubtitle ?? 'Monitoring and managing barangay operations and resident services'); ?></small>
                </h2>
            </div>
        </div>

            <?php if (function_exists('render_official_profile_script')) { render_official_profile_script(); } ?>

        <!-- Action Buttons -->
        <div class="top-bar-actions">
            <!-- Notifications Button -->
            <button class="action-icon-btn" title="Notifications" data-bs-toggle="modal" data-bs-target="#notificationsModal">
                <i class="fas fa-bell"></i>
                <?php if ($admin_unread_count > 0): ?>
                    <span class="badge-count pulse"><?php echo $admin_unread_count; ?></span>
                <?php endif; ?>
            </button>

            <!-- Messages/Inbox Button -->
            <button class="action-icon-btn" title="Messages" data-bs-toggle="modal" data-bs-target="#messagesModal">
                <i class="fas fa-envelope"></i>
                <span class="badge-count">12</span>
            </button>
        </div>

        <!-- Admin Profile Dropdown -->
        <div class="admin-profile dropdown">
            <div class="admin-info">
                <div id="adminDisplayName" class="name"><?php echo htmlspecialchars($adminName ?? 'Admin Secretary'); ?></div>
                <div id="adminDisplayRole" class="role"><?php echo htmlspecialchars($adminRole ?? 'Barangay Administrator'); ?></div>
            </div>
            <div class="admin-avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;" title="Admin Profile">
                <i class="fas fa-user"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 200px;">
                <li>
                    <div class="dropdown-item-text" style="border-bottom: 1px solid #e9ecef; padding-bottom: 10px; margin-bottom: 5px;">
                        <strong id="adminDisplayNameDropdown"><?php echo htmlspecialchars($adminName ?? 'Admin Secretary'); ?></strong><br>
                        <small id="adminDisplayRoleDropdown" class="text-muted"><?php echo htmlspecialchars($adminRole ?? 'Barangay Administrator'); ?></small>
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
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item text-danger" href="<?php echo rtrim(BASE_PUBLIC, '/'); ?>/index.php?page=logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
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
                <a href="?page=admin_dashboard" class="<?php echo ($currentPage === 'admin_dashboard') ? 'active' : ''; ?>" data-tooltip="Dashboard">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="?page=admin_announcements" class="<?php echo ($currentPage === 'admin_announcements') ? 'active' : ''; ?>" data-tooltip="Announcements">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
            </li>
            <li>
                <a href="?page=admin_complaints" class="<?php echo ($currentPage === 'admin_complaints') ? 'active' : ''; ?>" data-tooltip="Complaints">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Complaints</span>
                </a>
            </li>
            <li>
                <a href="?page=admin_officials" class="<?php echo ($currentPage === 'admin_officials') ? 'active' : ''; ?>" data-tooltip="Officials">
                    <i class="fas fa-users"></i>
                    <span>Officials</span>
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_PUBLIC . 'index.php?page=admin_document_requests' ?>" class="<?php echo ($currentPage === 'admin_documents') ? 'active' : ''; ?>" data-tooltip="Documents">
                    <i class="fas fa-file-alt"></i>
                    <span>Documents</span>
                </a>
            </li>
            <li>
                <a href="?page=admin_residents" class="<?php echo ($currentPage === 'admin_residents') ? 'active' : ''; ?>" data-tooltip="Residents">
                    <i class="fas fa-user-friends"></i>
                    <span>Residents</span>
                </a>
            </li>
            <li>
                <a href="?page=admin_settings" class="<?php echo ($currentPage === 'admin_settings') ? 'active' : ''; ?>" data-tooltip="Settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
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

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                <!-- Modal Header -->
                <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                    <h5 class="modal-title" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;">
                        <i class="fas fa-bell"></i> Notifications
                        <?php if ($admin_unread_count > 0): ?>
                            <span style="background: #ef4444; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">
                                <?php echo $admin_unread_count; ?> New
                            </span>
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 0; background: #f8fafc;">
                    <?php if (empty($admin_notifications)): ?>
                        <div style="padding: 3rem; text-align: center; color: #94a3b8;">
                            <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p style="margin: 0; font-weight: 500;">No notifications yet</p>
                            <small>You'll see updates here when there's new activity</small>
                        </div>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($admin_notifications as $notif): 
                                $icon_map = [
                                    'document_request' => 'fa-file-alt',
                                    'announcement' => 'fa-bullhorn',
                                    'complaint' => 'fa-exclamation-circle',
                                    'survey' => 'fa-poll'
                                ];
                                $icon = $icon_map[$notif['type']] ?? 'fa-info-circle';
                                $is_unread = !$notif['is_read'];
                                $time_ago = time_ago($notif['created_at']);
                            ?>
                                <div class="notification-item" data-notif-id="<?php echo $notif['id']; ?>" 
                                     style="padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background 0.2s; <?php echo $is_unread ? 'background: #eff6ff;' : ''; ?>"
                                     onclick="handleNotificationClick(<?php echo $notif['id']; ?>, '<?php echo htmlspecialchars($notif['link'] ?? '', ENT_QUOTES); ?>')">
                                    <div style="display: flex; gap: 1rem; align-items: start;">
                                        <div style="width: 40px; height: 40px; border-radius: 8px; background: <?php echo $is_unread ? 'var(--primary-blue)' : '#cbd5e1'; ?>; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas <?php echo $icon; ?>"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="font-weight: <?php echo $is_unread ? '600' : '500'; ?>; color: #1e293b; margin-bottom: 0.25rem;">
                                                <?php echo htmlspecialchars($notif['title']); ?>
                                            </div>
                                            <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">
                                                <?php echo htmlspecialchars($notif['message']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                                <i class="fas fa-clock"></i> <?php echo $time_ago; ?>
                                            </div>
                                        </div>
                                        <?php if ($is_unread): ?>
                                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; flex-shrink: 0; margin-top: 0.5rem;"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: white; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm" style="background: transparent; border: 1px solid #cbd5e1; color: #64748b; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <?php if ($admin_unread_count > 0): ?>
                        <button type="button" class="btn btn-sm" onclick="markAllNotificationsRead()" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages/Inbox Modal -->
    <div class="modal fade" id="messagesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                <!-- Modal Header -->
                <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                    <h5 class="modal-title" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;">
                        <i class="fas fa-envelope"></i> Messages
                        <span style="background: #ef4444; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">
                            12 Unread
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 0; background: #f8fafc;">
                    <!-- Messages content would be rendered here (template only, no logic) -->
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: white; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm" style="background: transparent; border: 1px solid #cbd5e1; color: #64748b; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;" data-bs-dismiss="modal">
                        <i class="fas fa-trash-alt"></i> Clear Inbox
                    </button>
                    <button type="button" class="btn btn-sm" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;">
                        <i class="fas fa-envelope-open"></i> View All Messages
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php
/**
 * Helper function to display relative time
 */
function time_ago($datetime_str) {
    $timestamp = strtotime($datetime_str);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' week' . (floor($diff / 604800) > 1 ? 's' : '') . ' ago';
    return date('M d, Y', $timestamp);
}
?>

<script>
// Handle notification click
function handleNotificationClick(notifId, link) {
    // Mark as read via AJAX
    fetch('<?php echo BASE_PUBLIC; ?>index.php?action=mark_notification_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notifId
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              // Update UI
              const notifEl = document.querySelector('[data-notif-id="' + notifId + '"]');
              if (notifEl) {
                  notifEl.style.background = 'white';
                  const dot = notifEl.querySelector('[style*="background: #3b82f6"]');
                  if (dot) dot.remove();
              }
              
              // Update badge count
              const badge = document.querySelector('.action-icon-btn .badge-count.pulse');
              if (badge) {
                  let count = parseInt(badge.textContent) - 1;
                  if (count <= 0) {
                      badge.remove();
                  } else {
                      badge.textContent = count;
                  }
              }
          }
      });
    
    // Navigate if link provided
    if (link && link.trim() !== '') {
        setTimeout(function() {
            window.location.href = link;
        }, 300);
    }
}

// Mark all notifications as read
function markAllNotificationsRead() {
    if (!confirm('Mark all notifications as read?')) return;
    
    fetch('<?php echo BASE_PUBLIC; ?>index.php?action=mark_all_notifications_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_type=admin'
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          }
      });
}
</script>
<!-- Dropdown fallback: ensure admin profile dropdown opens/closes even if Bootstrap JS is missing or conflicting -->
<script>
    (function() {
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
                    setTimeout(function() { document.addEventListener('click', closeProfile); }, 0);
                }
            });
        }
    })();
</script>
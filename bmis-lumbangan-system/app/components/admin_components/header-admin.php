<?php include_once __DIR__ . '/../../config/config.php'; ?>

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

        <!-- Action Buttons -->
        <div class="top-bar-actions">
            <!-- Notifications Button -->
            <button class="action-icon-btn" title="Notifications" data-bs-toggle="modal" data-bs-target="#notificationsModal">
                <i class="fas fa-bell"></i>
                <span class="badge-count pulse">5</span>
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
                <div class="name"><?php echo htmlspecialchars($adminName ?? 'Admin Secretary'); ?></div>
                <div class="role"><?php echo htmlspecialchars($adminRole ?? 'Barangay Administrator'); ?></div>
            </div>
            <div class="admin-avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;" title="Admin Profile">
                <i class="fas fa-user"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 200px;">
                <li>
                    <div class="dropdown-item-text" style="border-bottom: 1px solid #e9ecef; padding-bottom: 10px; margin-bottom: 5px;">
                        <strong><?php echo htmlspecialchars($adminName ?? 'Admin Secretary'); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($adminRole ?? 'Barangay Administrator'); ?></small>
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
                    <a class="dropdown-item text-danger" href="../../controllers/AuthController.php?action=logout">
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
                        <span style="background: #ef4444; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">
                            5 New
                        </span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 0; background: #f8fafc;">
                    <!-- Notifications content would be rendered here (template only, no logic) -->
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: white; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm" style="background: transparent; border: 1px solid #cbd5e1; color: #64748b; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-sm" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
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
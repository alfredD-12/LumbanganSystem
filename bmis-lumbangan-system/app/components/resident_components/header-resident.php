<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function h($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// prepare session-driven values for display (used in markup)
$hdr_username  = $_SESSION['username'] ?? ($_SESSION['user'] ?? 'User');
$hdr_first     = $_SESSION['first_name'] ?? '';
$hdr_last      = $_SESSION['last_name'] ?? '';
$hdr_full_name = $_SESSION['full_name'] ?? trim(($hdr_first ? $hdr_first . ' ' : '') . $hdr_last);
if (!$hdr_full_name) $hdr_full_name = $hdr_username;
$hdr_email  = $_SESSION['email'] ?? '';
$hdr_mobile = $_SESSION['mobile'] ?? '';

?>

<?php
@include_once __DIR__ . '/../../config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo h($pageTitle ?? 'Barangay Lumbangan System'); ?></title>

    <!-- Server-provided logout endpoint for header JS to call (centralized via index page routing) -->
    <meta name="app-auth-logout" content="<?php echo h(rtrim(BASE_PUBLIC, '/') . '/index.php?page=logout'); ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Dashboard header CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/Dashboard/headerfooter-bdhf.css'; ?>">
    <!-- Additional for inbox modal -->
    <link rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/resident/resident-header.css'; ?>">
    <!-- If may iba kayong need or idagdag na link pakilagay nalang sa baba -->
    <link rel="stylesheet" href="<?php echo rtrim(BASE_URL, '/'); ?>/assets/css/Survey/wizard_personal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo rtrim(BASE_URL, '/'); ?>/assets/css/Survey/bhw-float.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo rtrim(BASE_URL, '/'); ?>/assets/css/announcement/public_announcements_modern.css?v=<?php echo time(); ?>">

</head>
<body>

<!-- User Dashboard Header (layout unchanged) -->
<nav class="navbar navbar-expand-lg dashboard-header user-navbar navbar-light">
  <div class="container">
    <a class="navbar-brand" href="#dashboard">
      <div class="logo-circle"><i class="fas fa-landmark"></i></div>
      <div>
        <h6 class="mb-0 fw-bold">Barangay Lumbangan</h6>
        <small class="text-muted">Resident Portal</small>
      </div>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="userNavbar">
      <ul class="navbar-nav ms-auto me-3">
        <li class="nav-item"><a class="nav-link" href="<?php echo h(BASE_PUBLIC . 'index.php?page=dashboard_resident'); ?>" id="dashboardLink"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo h(BASE_PUBLIC . 'index.php?page=public_announcement'); ?>"><i class="fas fa-bullhorn"></i> Announcements</a></li>

        <!-- Services -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-concierge-bell"></i> Services
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?php echo h(BASE_PUBLIC . 'index.php?page=resident_complaints'); ?>"><i class="fas fa-exclamation-circle"></i> Complaint Status</a></li>
            <li><a class="dropdown-item" href="<?php echo h(BASE_PUBLIC . 'index.php?page=document_request'); ?>"><i class="fas fa-file-alt"></i> Document Request</a></li>
            <li><a class="dropdown-item" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_personal'); ?>"><i class="fas fa-poll"></i> Survey Status</a></li>
          </ul>
        </li>
      </ul>

      <div class="d-none d-lg-flex align-items-center gap-2 me-3" style="border-right:1px solid rgba(0,0,0,0.1); padding-right:1.5rem;">
        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Bagong_Pilipinas_logo.png" alt="Bagong Pilipinas" style="width:38px;height:38px;object-fit:contain;">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Seal_of_Nasugbu.png/599px-Seal_of_Nasugbu.png" alt="Nasugbu Seal" style="width:38px;height:38px;object-fit:contain;">
        <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Seal_of_Batangas.png" alt="Batangas Seal" style="width:38px;height:38px;object-fit:contain;">
      </div>

      <div class="d-flex align-items-center gap-1 gap-md-2">
        <button class="navbar-icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#notificationsModal" title="Notifications"><i class="fas fa-bell"></i><span class="badge">3</span></button>
        <button class="navbar-icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#inboxModal" title="Inbox"><i class="fas fa-envelope"></i><span class="badge">1</span></button>
        <button class="navbar-icon-btn d-none d-sm-inline-block" type="button" data-bs-toggle="modal" data-bs-target="#documentsModal" title="My Documents"><i class="fas fa-file-alt"></i></button>

        <!-- User Dropdown (static markup preserved) -->
        <div class="dropdown" style="margin-left:0.5rem; border-left:1px solid rgba(0,0,0,0.1); padding-left:0.5rem;">
          <button class="user-profile-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
            <div class="user-avatar"><i class="fas fa-user"></i></div>
            <span class="d-none d-sm-inline"><?php echo h($hdr_username); ?></span>
          </button>

          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="min-width:220px;">
            <li class="px-3 py-2 border-bottom">
              <div class="d-flex align-items-center gap-2">
                <div class="user-avatar" style="width:40px;height:40px;border-radius:50%;background:#1e3a5f;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;"><i class="fas fa-user"></i></div>
                <div class="flex-grow-1" style="min-width:0">
                  <div class="fw-bold text-truncate"><?php echo h($hdr_full_name); ?></div>
                  <div class="text-muted text-truncate" style="font-size:0.8rem;">@<?php echo h($hdr_username); ?></div>
                </div>
              </div>
            </li>
            <li><a class="dropdown-item" href="#" id="btn-open-profile" data-bs-toggle="modal" data-bs-target="#userProfileModal"><i class="fas fa-user me-2"></i> My Profile</a></li>
            <li><a class="dropdown-item" href="#settings" id="btn-open-settings"><i class="fas fa-cog me-2"></i> Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger" href="<?php echo rtrim(BASE_PUBLIC, '/'); ?>/index.php?page=logout"> 
                <i class="fas fa-sign-out-alt"></i>
                Logout
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- Profile modal (small) -->
<div class="modal fade" id="userProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="fas fa-id-card me-2"></i> Profile</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-3"><strong>Name</strong><div id="wf_profile_name" class="text-muted"><?php echo h($hdr_full_name); ?></div></div>
        <div class="mb-3"><strong>Username</strong><div id="wf_profile_username" class="text-muted">@<?php echo h($hdr_username); ?></div></div>
        <div class="mb-3"><strong>Email</strong><div id="wf_profile_email" class="text-muted"><?php echo h($hdr_email); ?></div></div>
        <div class="mb-3"><strong>Mobile</strong><div id="wf_profile_mobile" class="text-muted"><?php echo h($hdr_mobile); ?></div></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>


<?php include_once __DIR__ . '/../../config/config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Barangay Lumbangan System'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <!-- Dashboard header CSS -->
     <link id="header-css" rel="stylesheet" href="<?php echo BASE_URL . 'assets/css/Dashboard/headerfooter-bdhf.css'; ?>">
    <!-- If may iba kayong need or idagdag na link pakilagay nalang sa baba -->

    

</head>
<body>

    <!-- User Dashboard Header -->
    <nav class="navbar navbar-expand-lg dashboard-header user-navbar navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#dashboard">
                <div class="logo-circle">
                    <i class="fas fa-landmark"></i>
                </div>
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
                    <li class="nav-item">
                        <a class="nav-link" href="#dashboard" id="dashboardLink">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="#announcements">
                            <i class="fas fa-bullhorn"></i> Announcements
                        </a>
                    </li>

                    <!-- Services Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-concierge-bell"></i> Services
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#complaint-status">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Complaint Status
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_PUBLIC . 'index.php?page=document_request'; ?>">
                                    <i class="fas fa-file-alt"></i>
                                    Document Request
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#survey-status">
                                    <i class="fas fa-poll"></i>
                                    Survey Status
                                </a>
                            </li>
                            
                           
                        </ul>
                    </li>
                </ul>

                <!-- Government Seals - Hidden on mobile for better spacing -->
                <div class="d-none d-lg-flex align-items-center gap-2 me-3" style="border-right: 1px solid rgba(0,0,0,0.1); padding-right: 1.5rem;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Bagong_Pilipinas_logo.png"
                        alt="Bagong Pilipinas" title="Bagong Pilipinas"
                        style="width: 38px; height: 38px; object-fit: contain;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Seal_of_Nasugbu.png/599px-Seal_of_Nasugbu.png"
                        alt="Nasugbu Seal" title="Municipality of Nasugbu"
                        style="width: 38px; height: 38px; object-fit: contain;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Seal_of_Batangas.png"
                        alt="Batangas Seal" title="Province of Batangas"
                        style="width: 38px; height: 38px; object-fit: contain;">
                </div>

                <!-- Notifications & Inbox Buttons - Mobile optimized spacing -->
                <div class="d-flex align-items-center gap-1 gap-md-2">
                    <!-- Notifications Button -->
                    <button class="navbar-icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#notificationsModal" title="Notifications" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </button>

                    <!-- Inbox Button -->
                    <button class="navbar-icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#inboxModal" title="Inbox" aria-label="Inbox">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">1</span>
                    </button>

                    <!-- Documents Button - Hidden on very small screens -->
                    <button class="navbar-icon-btn d-none d-sm-inline-block" type="button" data-bs-toggle="modal" data-bs-target="#documentsModal" title="My Documents" aria-label="My Documents">
                        <i class="fas fa-file-alt"></i>
                    </button>

                    <!-- User Profile Dropdown - Responsive margins -->
                    <div class="dropdown" style="margin-left: 0.5rem; border-left: 1px solid rgba(0,0,0,0.1); padding-left: 0.5rem;">
                        <button class="user-profile-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
                            <div class="user-avatar"><i class="fas fa-user"></i></div>
                            <span class="d-none d-sm-inline">User</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#userProfileModal">
                                    <i class="fas fa-user"></i>
                                    My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#settings">
                                    <i class="fas fa-cog"></i>
                                    Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
        </div>
    </nav>

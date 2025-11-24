<?php
// Require user authentication
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/dashboard_helper.php';
requireUser(); // Only allow logged-in users to access this page

// Load Announcement model and fetch latest 3 announcements
require_once dirname(__DIR__, 2) . '/models/Announcement.php';
require_once dirname(__DIR__, 2) . '/helpers/announcement_helper.php';

$announcementModel = new Announcement();
// Get user role (residents for regular users)
$userRole = isOfficial() ? 'officials' : 'residents';
// Fetch latest 3 published announcements for the user's role
$recentAnnouncements = $announcementModel->getPublicAnnouncements($userRole, []);
$recentAnnouncements = array_slice($recentAnnouncements, 0, 3); // Limit to 3

$fullName = getFullName();
$username = getUsername();
$firstName = getFirstName();
$userId = $_SESSION['user_id'] ?? null;

// Get dynamic dashboard stats
$dashboardStats = getUserDashboardStats($userId);
$pendingRequests = $dashboardStats['pending_requests'];
$completedRequests = $dashboardStats['completed_requests'];
$memberSinceYear = $dashboardStats['member_since'];
$verificationStatus = $dashboardStats['verification_status'];
$userAddress = $dashboardStats['address'];
$residentId = $dashboardStats['resident_id'];
$hasMonthlySurvey = $dashboardStats['has_monthly_survey'];

// Get officials data
$officials = getActiveOfficials();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barangay Lumbangan</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/Dashboard/dashboard.css?v=2">
</head>
<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- User Dashboard Header -->
    <nav class="navbar navbar-expand-lg user-navbar navbar-light">
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
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-concierge-bell"></i> Services
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?php echo htmlspecialchars((defined('BASE_PUBLIC') ? BASE_PUBLIC : '') . 'index.php?page=resident_complaints', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Complaint Status
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo htmlspecialchars((defined('BASE_PUBLIC') ? BASE_PUBLIC : '') . 'index.php?page=document_request', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                                    <i class="fas fa-file-alt"></i>
                                    Document Request Status
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo htmlspecialchars((defined('BASE_PUBLIC') ? BASE_PUBLIC : '') . 'index.php?page=survey_wizard_personal', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" data-navigate="true">
                                    <i class="fas fa-poll"></i>
                                    Survey Status
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#new-request">
                                    <i class="fas fa-plus-circle"></i>
                                    New Request
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
                        <button class="user-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
                            <div class="user-avatar"><i class="fas fa-user"></i></div>
                            <span class="d-none d-sm-inline"><?php echo htmlspecialchars($username); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="max-width: 280px; width: max-content;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar" style="width:40px;height:40px;border-radius:50%;background:#1e3a5f;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;"><i class="fas fa-user"></i></div>
                                    <div class="flex-grow-1" style="min-width:0;max-width:180px;">
                                        <div class="fw-bold text-truncate" style="max-width:100%;"><?php echo htmlspecialchars($fullName); ?></div>
                                        <div class="text-muted text-truncate" style="font-size:0.8rem;max-width:100%;">@<?php echo htmlspecialchars($username); ?></div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#userProfileModal" id="btn-open-profile">
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
                                <a class="dropdown-item text-danger" href="#" onclick="handleLogout(event)">
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

    <!-- Welcome Banner - Enhanced Interactive Design -->
    <section class="welcome-banner" id="dashboard" style="background: linear-gradient(180deg, rgba(255,255,255,0.4) 0%, rgba(224,242,254,0.3) 50%, transparent 100%); padding: 5rem 0 5rem; position: relative; overflow: hidden; border-bottom: 1px solid rgba(30,58,95,0.08);">
        <!-- Animated Background Elements -->
        <div style="position: absolute; top: 10%; right: 10%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(30,58,95,0.15) 0%, transparent 70%); border-radius: 50%; animation: float 8s ease-in-out infinite; z-index: 0;"></div>
        <div style="position: absolute; bottom: 15%; left: 5%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(197,48,48,0.15) 0%, transparent 70%); border-radius: 50%; animation: float 6s ease-in-out infinite reverse; z-index: 0;"></div>
        
        <!-- Top Highlight -->
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent 0%, rgba(30,58,95,0.2) 20%, rgba(197,48,48,0.2) 50%, rgba(30,58,95,0.2) 80%, transparent 100%);"></div>
        
        <div class="container" style="position: relative; z-index: 1;">
            <div class="row align-items-center">
                <div class="col-lg-6" style="padding-right: 3rem;">
                    <div class="welcome-content" style="animation: fadeInLeft 0.8s ease-out;">
                        <!-- Greeting Badge -->
                        <div style="display: inline-block; background: rgba(30,58,95,0.1); padding: 0.5rem 1.2rem; border-radius: 30px; margin-bottom: 1.5rem; backdrop-filter: blur(10px);">
                            <span style="color: #1e3a5f; font-size: 0.85rem; font-weight: 600;">
                                <i class="fas fa-clock" style="margin-right: 0.4rem;"></i>
                                <span id="greeting-time">Good Morning</span>
                            </span>
                        </div>
                        
                        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; line-height: 1.2;">
                            <span style="background: linear-gradient(135deg, #1e3a5f, #c53030); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Welcome back, <?php echo htmlspecialchars($firstName); ?></span>
                            <span style="display: inline-block; animation: wave 2s ease-in-out infinite; margin-left: 0.3rem;">👋</span>
                        </h1>
                        
                        <p style="font-size: 1.15rem; color: #64748b; margin-bottom: 2rem; line-height: 1.7;">
                            Your one-stop portal for all barangay services. 
                            <span style="font-weight: 600; color: #1e3a5f;">Stay connected, stay informed.</span>
                        </p>
                        
                        <!-- Quick Action Buttons -->
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
                            <a href="#services" style="
                                padding: 0.95rem 2rem;
                                background: linear-gradient(135deg, #1e3a5f, #2c5282);
                                color: white;
                                text-decoration: none;
                                border-radius: 12px;
                                font-weight: 600;
                                font-size: 1rem;
                                display: inline-flex;
                                align-items: center;
                                gap: 0.6rem;
                                box-shadow: 0 8px 20px rgba(30,58,95,0.3);
                                transition: all 0.3s;
                                border: none;
                            " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 30px rgba(30,58,95,0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(30,58,95,0.3)';">
                                <i class="fas fa-rocket"></i>
                                Request Services
                            </a>
                            <!-- ww -->


                            <a href="#directory" style="
                                padding: 0.95rem 2rem;
                                background: white;
                                color: #1e3a5f;
                                text-decoration: none;
                                border-radius: 12px;
                                font-weight: 600;
                                font-size: 1rem;
                                display: inline-flex;
                                align-items: center;
                                gap: 0.6rem;
                                box-shadow: 0 4px 15px rgba(220,38,38,0.15);
                                transition: all 0.3s;
                                border: 2px solid #dc2626;
                                color: #dc2626;
                            " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(220,38,38,0.25)'; this.style.borderColor='#b91c1c'; this.style.color='#b91c1c';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(220,38,38,0.15)'; this.style.borderColor='#dc2626'; this.style.color='#dc2626';">
                                <i class="fas fa-users"></i>
                                View Officials
                            </a>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div style="display: flex; gap: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid rgba(255,255,255,0.8);">
                            <div style="flex: 1;">
                                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.3rem; font-weight: 500;">Pending Requests</div>
                                <div style="font-size: 1.8rem; font-weight: 700; color: #1e3a5f;"><?php echo $pendingRequests; ?></div>
                            </div>
                            <div style="width: 1px; background: #e2e8f0;"></div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.3rem; font-weight: 500;">Completed</div>
                                <div style="font-size: 1.8rem; font-weight: 700; color: #10b981;"><?php echo $completedRequests; ?></div>
                            </div>
                            <div style="width: 1px; background: #e2e8f0;"></div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.3rem; font-weight: 500;">Member Since</div>
                                <div style="font-size: 1.8rem; font-weight: 700; color: #c53030;"><?php echo $memberSinceYear; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6" style="padding-left: 2rem;">
                    <!-- Interactive 3D Card Showcase -->
                    <div style="position: relative; height: 500px; animation: fadeInRight 0.8s ease-out;">
                        <!-- Main Feature Card - Optimized -->
                        <div style="
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            width: 380px;
                            height: 230px;
                            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
                            border-radius: 20px;
                            padding: 1.5rem;
                            box-shadow: 0 10px 30px rgba(30,58,95,0.25);
                            z-index: 3;
                            animation: floatCard 4s ease-in-out infinite;
                            border: 1px solid rgba(255,255,255,0.1);
                        ">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
                                <div>
                                    <div style="color: rgba(255,255,255,0.75); font-size: 0.75rem; margin-bottom: 0.3rem; font-weight: 500; letter-spacing: 0.5px;">Resident Portal</div>
                                    <div style="color: white; font-size: 1.15rem; font-weight: 700; letter-spacing: 0.5px;"><?php echo htmlspecialchars($residentId); ?></div>
                                </div>
                                <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                    <i class="fas fa-id-card" style="font-size: 1.3rem; color: white;"></i>
                                </div>
                            </div>
                            <div style="margin-bottom: 1.5rem;">
                                <div style="color: rgba(255,255,255,0.75); font-size: 0.72rem; margin-bottom: 0.3rem; font-weight: 500;">Resident Name</div>
                                <div style="color: white; font-size: 1.1rem; font-weight: 700; letter-spacing: 0.3px;"><?php echo htmlspecialchars($fullName); ?></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="color: rgba(255,255,255,0.75); font-size: 0.7rem; margin-bottom: 0.3rem; font-weight: 500;">Status</div>
                                    <?php if ($verificationStatus === 'Verified'): ?>
                                        <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.85rem; background: rgba(16,185,129,0.25); border-radius: 18px; backdrop-filter: blur(10px);">
                                            <div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; box-shadow: 0 0 6px #10b981;"></div>
                                            <span style="color: #10b981; font-size: 0.75rem; font-weight: 700;"><?php echo htmlspecialchars($verificationStatus); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.85rem; background: rgba(245, 158, 11, 0.25); border-radius: 18px; backdrop-filter: blur(10px);">
                                            <div style="width: 6px; height: 6px; background: #f59e0b; border-radius: 50%; box-shadow: 0 0 6px #f59e0b;"></div>
                                            <span style="color: #f59e0b; font-size: 0.75rem; font-weight: 700;"><?php echo htmlspecialchars($verificationStatus); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="color: rgba(255,255,255,0.7); font-size: 0.78rem; font-weight: 500;">
                                    <i class="fas fa-map-marker-alt" style="margin-right: 0.3rem;"></i>
                                    <?php echo htmlspecialchars($userAddress); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Mini Cards -->
                        <div class="showcase-card card-1" style="
                            position: absolute;
                            top: 10%;
                            right: 5%;
                            width: 140px;
                            padding: 1rem;
                            background: rgba(255,255,255,0.9);
                            backdrop-filter: blur(10px);
                            border-radius: 16px;
                            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                            z-index: 2;
                            animation: floatCardDelay1 5s ease-in-out infinite;
                        ">
                            <div style="width: 40px; height: 40px; background: rgba(30,58,95,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.8rem;">
                                <i class="fas fa-file-alt" style="color: #1e3a5f; font-size: 1.2rem;"></i>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.2rem;">Documents</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #1e3a5f;">8</div>
                        </div>
                        
                        <div class="showcase-card card-2" style="
                            position: absolute;
                            bottom: 15%;
                            left: 0%;
                            width: 150px;
                            padding: 1rem;
                            background: rgba(255,255,255,0.9);
                            backdrop-filter: blur(10px);
                            border-radius: 16px;
                            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                            z-index: 2;
                            animation: floatCardDelay2 6s ease-in-out infinite;
                        ">
                            <div style="width: 40px; height: 40px; background: rgba(197,48,48,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.8rem;">
                                <i class="fas fa-bell" style="color: #c53030; font-size: 1.2rem;"></i>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.2rem;">Notifications</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #c53030;">5</div>
                        </div>
                        
                        <div class="showcase-card card-3" style="
                            position: absolute;
                            top: 35%;
                            right: -5%;
                            width: 130px;
                            padding: 1rem;
                            background: rgba(255,255,255,0.9);
                            backdrop-filter: blur(10px);
                            border-radius: 16px;
                            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                            z-index: 1;
                            animation: floatCardDelay3 7s ease-in-out infinite;
                        ">
                            <div style="width: 40px; height: 40px; background: rgba(16,185,129,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.8rem;">
                                <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.2rem;">Completed</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;">12</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-15deg); }
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes floatCard {
            0%, 100% {
                transform: translate(-50%, -50%) translateY(0px) rotateY(0deg);
            }
            50% {
                transform: translate(-50%, -50%) translateY(-15px) rotateY(2deg);
            }
        }
        
        @keyframes floatCardDelay1 {
            0%, 100% {
                transform: translateY(0px) rotateZ(0deg);
            }
            50% {
                transform: translateY(-20px) rotateZ(2deg);
            }
        }
        
        @keyframes floatCardDelay2 {
            0%, 100% {
                transform: translateY(0px) rotateZ(0deg);
            }
            50% {
                transform: translateY(-25px) rotateZ(-3deg);
            }
        }
        
        @keyframes floatCardDelay3 {
            0%, 100% {
                transform: translateY(0px) rotateZ(0deg);
            }
            50% {
                transform: translateY(-15px) rotateZ(4deg);
            }
        }
        
        /* Hover effects for interactive cards */
        .showcase-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .showcase-card:hover {
            transform: translateY(-8px) scale(1.05) !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
        }
    </style>

    <script>
        // Dynamic greeting based on time
        function updateGreeting() {
            const hour = new Date().getHours();
            const greetingElement = document.getElementById('greeting-time');
            
            if (hour < 12) {
                greetingElement.textContent = 'Good Morning';
            } else if (hour < 18) {
                greetingElement.textContent = 'Good Afternoon';
            } else {
                greetingElement.textContent = 'Good Evening';
            }
        }
        
        updateGreeting();
    </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Wave effect inside welcome banner -->
        <div class="banner-wave">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z"
                      fill="rgba(255, 255, 255, 0.1)">
                    <animate attributeName="d" dur="10s" repeatCount="indefinite"
                      values="M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z;
                              M0,50 C150,0 350,100 600,50 C850,0 1050,100 1200,50 L1200,120 L0,120 Z;
                              M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z"/>
                </path>
            </svg>
        </div>
    </section>

    <!-- Main Dashboard Content -->
    <div class="container" style="margin-top: 2rem;">
        <!-- Quick Stats -->
        <?php
            // Announcements this week count
                require_once dirname(__DIR__, 2) . '/models/Announcement.php';
                $announcementModel = new Announcement();

                // Calculate start (Monday) and end (Sunday) of current week
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

                // Fetch published announcements within week (getAll supports start_date/end_date)
                $weekAnnouncements = $announcementModel->getAll([
                    'status' => 'published',
                    'start_date' => $startOfWeek,
                    'end_date' => $endOfWeek
                ]);
                $thisWeekAnnouncementsCount = is_array($weekAnnouncements) ? count($weekAnnouncements) : 0;

                // Pending complaints count
                require_once dirname(__DIR__, 2) . '/models/Complaint.php';
                $complaintModel = new Complaint();
                $complaintStats = $complaintModel->getStatistics();
                $pendingComplaints = isset($complaintStats['pending']) ? (int)$complaintStats['pending'] : 0;
        ?>
        <div class="stats-grid">
            <div class="stat-card" tabindex="0">
                <div class="stat-main">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo htmlspecialchars($pendingComplaints, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                    <div class="stat-label">Pending Complaints</div>
                </div>
                <div class="stat-foot">
                    <span class="stat-badge badge-pending"><?php echo htmlspecialchars($pendingComplaints, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> Pending</span>
                    <div class="stat-extra">
                    </div>
                </div>
            </div>

            <div class="stat-card" tabindex="0">
                <div class="stat-main">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-number">5</div>
                    <div class="stat-label">Document Requests</div>
                </div>
                <div class="stat-foot">
                    <span class="stat-badge badge-completed">3 Ready</span>
                    <div class="stat-extra">
                    </div>
                </div>
            </div>

              <!-- Survey card: shows last survey and next survey with Open/Upcoming status -->
              <?php
                 // Prepare survey display values from helper-provided $dashboardStats
                 $lastRaw = $dashboardStats['last_survey_date'] ?? null; // raw datetime or null
                 $lastDisplay = $lastRaw ? date('F d, Y', strtotime($lastRaw)) : 'No survey taken yet';
                 $lastIso = $lastRaw ? date('Y-m-d', strtotime($lastRaw)) : '';

                 // Next survey is first day of next month
                 $nextRaw = date('Y-m-01', strtotime('first day of next month'));
                 $nextDisplay = date('F d, Y', strtotime($nextRaw));

                 // Number flag: 1 if no survey exists for current month, 0 if one exists
                 $surveyNumber = isset($dashboardStats['survey_number']) ? (int)$dashboardStats['survey_number'] : (empty($dashboardStats['has_monthly_survey']) ? 1 : 0);
                 $hasMonthly = !empty($dashboardStats['has_monthly_survey']);
              ?>
              <div class="stat-card" id="survey-card"
                  data-last-title="CVD NCD Risk Assessment"
                  data-last-date="<?php echo $lastIso; ?>"
                  data-next-title="CVD NCD Risk Assessment"
                  data-next-date="<?php echo date('Y-m-d', strtotime($nextRaw)); ?>">
                <div class="stat-main">
                    <div class="stat-icon">
                        <i class="fas fa-poll"></i>
                    </div>
                    <div class="stat-number" id="survey-number"><?php echo $surveyNumber ? '1' : '0'; ?></div>
                    <div class="stat-label">Assessment Survey</div>
                </div>
                    <div class="stat-foot">
                    <!-- short status badge visible by default -->
                    <span id="survey-next-status-short" class="survey-badge <?php echo $hasMonthly ? 'completed' : 'upcoming'; ?>"><?php echo $hasMonthly ? 'Completed' : 'Upcoming'; ?></span>
                    <div class="stat-extra">
                        <div class="survey-details mt-3">
                        <div class="survey-last">
                            <small class="text-muted">Last survey</small>
                            <div class="fw-bold" id="survey-last-title">CVD NCD Risk Assessment</div>
                            <small id="survey-last-date" class="text-muted"><?php echo htmlspecialchars($lastDisplay, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></small>
                        </div>

                        <div class="survey-next mt-3">
                            <small class="text-muted">Next survey</small>
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <div class="fw-bold" id="survey-next-title">CVD NCD Risk Assessment</div>
                                    <small id="survey-next-date" class="text-muted"><?php echo htmlspecialchars($nextDisplay, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></small>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card" tabindex="0">
                <div class="stat-main">
                    <div class="stat-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-number"><?php echo htmlspecialchars($thisWeekAnnouncementsCount, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                    <div class="stat-label">New Announcements</div>
                </div>
                <div class="stat-foot">
                    <span class="stat-badge badge-active">This Week</span>
                    <div class="stat-extra">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batangas News Section - Carousel -->
    <section id="news" style="padding: 4rem 0; background: transparent; position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 1;">
            <div style="margin-bottom: 2.5rem; text-align: center;">
                <div style="display: inline-block; background: rgba(30, 58, 95, 0.1); padding: 0.5rem 1.2rem; border-radius: 20px; margin-bottom: 1rem;">
                    <span style="color: var(--primary-blue); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Latest Updates</span>
                </div>
                <h2 style="font-size: 2.2rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 0.75rem;">Batangas News & Updates</h2>
                <p style="color: #666; font-size: 1rem; max-width: 500px; margin: 0 auto;">Stay informed with real-time news from Batangas Provincial Government</p>
            </div>

            <!-- News Carousel Navigation -->
            <div style="position: relative; max-width: 1100px; margin: 0 auto;">
                <button onclick="prevNewsSlide()" style="
                    position: absolute;
                    left: -60px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    border: none;
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    color: #1e3a5f;
                    font-size: 1.3rem;
                    cursor: pointer;
                    transition: all 0.3s;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    z-index: 10;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                " onmouseover="this.style.background='#1e3a5f'; this.style.color='white'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.95)'; this.style.color='#1e3a5f'; this.style.transform='translateY(-50%) scale(1)';">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <button onclick="nextNewsSlide()" style="
                    position: absolute;
                    right: -60px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    border: none;
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    color: #1e3a5f;
                    font-size: 1.3rem;
                    cursor: pointer;
                    transition: all 0.3s;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    z-index: 10;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                " onmouseover="this.style.background='#1e3a5f'; this.style.color='white'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.95)'; this.style.color='#1e3a5f'; this.style.transform='translateY(-50%) scale(1)';">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <!-- Loading State -->
                <div id="dashboardNewsLoading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; text-align: center;">
                    <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; color: var(--primary-blue); margin-bottom: 1rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p style="color: #666; font-size: 0.95rem;">Fetching latest news from Batangas...</p>
                </div>

                <!-- Error State -->
                <div id="dashboardNewsError" style="display: none; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; text-align: center; color: #dc2626;">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>Unable to load news at this time. Please try again later.</p>
                </div>

                <!-- News Carousel Container -->
                <div id="newsCarousel" style="display: none; position: relative; overflow: hidden; padding: 2rem 0;">
                    <div id="newsCarouselTrack" style="display: flex; transition: transform 0.5s ease;">
                        <!-- News cards will be inserted here dynamically -->
                    </div>
                </div>

                <!-- Carousel Indicators -->
                <div id="newsIndicators" style="display: none; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                    <!-- Indicators will be inserted here dynamically -->
                </div>
            </div>

            <!-- Refresh Button -->
            <div style="text-align: center; margin-top: 2rem;">
                <button id="dashboardRefreshNews" style="
                    padding: 0.9rem 2rem;
                    background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
                    color: white;
                    border: none;
                    border-radius: 10px;
                    font-weight: 600;
                    font-size: 0.95rem;
                    cursor: pointer;
                    transition: all 0.3s;
                    box-shadow: 0 4px 15px rgba(30, 58, 95, 0.2);
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(30, 58, 95, 0.2)';">
                    <i class="fas fa-sync-alt"></i> Refresh News
                </button>
            </div>
        </div>
    </section>

    <!-- Announcements Section with Modern Design -->
    <section id="announcements" style="padding: 5rem 0; background: transparent; position: relative; overflow: hidden;">
        
        <div class="container" style="position: relative; z-index: 1;">
            <div style="margin-bottom: 3.5rem; text-align: center;">
                <div style="display: inline-block; background: rgba(30, 58, 95, 0.1); padding: 0.5rem 1.2rem; border-radius: 20px; margin-bottom: 1rem;">
                    <span style="color: var(--primary-blue); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Latest Updates</span>
                </div>
                <h2 style="font-size: 2.2rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 0.75rem;">Recent Announcements</h2>
                <p style="color: #666; font-size: 1rem; max-width: 500px; margin: 0 auto;">Stay informed with the latest news, events, and important updates from Barangay Lumbangan</p>
            </div>

            <div class="row g-4">
                <?php if (empty($recentAnnouncements)): ?>
                    <!-- No Announcements -->
                    <div class="col-12">
                        <div style="text-align: center; padding: 3rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                            <i class="fas fa-bullhorn" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <h4 style="color: #666; font-weight: 600;">No Announcements Available</h4>
                            <p style="color: #999;">Check back later for updates</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    $iconMap = [
                        'residents' => 'fa-users',
                        'officials' => 'fa-user-tie',
                        'all' => 'fa-bullhorn'
                    ];
                    $colorMap = [
                        'residents' => 'var(--primary-blue)',
                        'officials' => 'var(--secondary-blue)',
                        'all' => 'var(--accent-red)'
                    ];
                    
                    foreach ($recentAnnouncements as $index => $announcement): 
                        $audience = $announcement['audience'] ?? 'all';
                        $icon = $iconMap[$audience] ?? 'fa-bullhorn';
                        $color = $colorMap[$audience] ?? 'var(--primary-blue)';
                        $title = htmlspecialchars($announcement['title']);
                        $message = htmlspecialchars($announcement['message']);
                        $excerpt = strlen($message) > 120 ? substr($message, 0, 120) . '...' : $message;
                        $date = date('F j, Y', strtotime($announcement['created_at']));
                        $author = htmlspecialchars($announcement['author'] ?? 'Admin');
                        $image = $announcement['image'] ?? null;
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div style="background: white; border-radius: 16px; overflow: hidden; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.05); height: 100%;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)'; this.style.borderColor='rgba(30, 58, 95, 0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.05)'; this.style.borderColor='rgba(0,0,0,0.05)';">
                            <?php if ($image): ?>
                                <div style="height: 160px; background: url('<?php echo htmlspecialchars(announcement_image_url($image)); ?>') center/cover; position: relative;">
                                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(30, 58, 95, 0.3) 0%, rgba(44, 82, 130, 0.2) 100%);"></div>
                                </div>
                            <?php else: ?>
                                <div style="height: 160px; background: linear-gradient(135deg, rgba(30, 58, 95, 0.1) 0%, rgba(44, 82, 130, 0.08) 100%); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                                    <div style="position: absolute; top: -30%; right: -30%; width: 200px; height: 200px; background: rgba(30, 58, 95, 0.1); border-radius: 50%; filter: blur(30px);"></div>
                                    <i class="fas <?php echo $icon; ?>" style="font-size: 3.5rem; color: <?php echo $color; ?>; position: relative; z-index: 1;"></i>
                                </div>
                            <?php endif; ?>
                            <div style="padding: 2rem;">
                                <div style="font-size: 0.75rem; color: <?php echo $color; ?>; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 0.75rem;">
                                    📅 <?php echo $date; ?>
                                    <?php if ($audience !== 'all'): ?>
                                        <span style="margin-left: 0.5rem; background: rgba(30, 58, 95, 0.1); padding: 0.2rem 0.6rem; border-radius: 10px; font-size: 0.7rem;">
                                            <?php echo ucfirst($audience); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h4 style="font-size: 1.15rem; color: var(--primary-blue); font-weight: 700; margin-bottom: 0.75rem;"><?php echo $title; ?></h4>
                                <p style="font-size: 0.9rem; color: #666; line-height: 1.6; margin-bottom: 1.25rem;"><?php echo $excerpt; ?></p>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <small style="color: #999; font-size: 0.8rem;">
                                        <i class="fas fa-user-circle"></i> <?php echo $author; ?>
                                    </small>
                                    <a href="<?php echo htmlspecialchars((defined('BASE_PUBLIC') ? BASE_PUBLIC : '') . 'index.php?page=public_announcement', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" style="color: <?php echo $color; ?>; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; transition: gap 0.3s;">Read More <i class="fas fa-chevron-right" style="font-size: 0.8rem;"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 3rem;">
                <a href="<?php echo htmlspecialchars((defined('BASE_PUBLIC') ? BASE_PUBLIC : '') . 'index.php?page=public_announcement', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" style="display: inline-block; padding: 0.9rem 2.5rem; background: var(--primary-blue); color: white; border-radius: 10px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(30, 58, 95, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 30px rgba(30, 58, 95, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(30, 58, 95, 0.3)';">
                    View All Announcements →
                </a>
            </div>
        </div>
    </section>

    <!-- Recent Activities 3D Card Carousel -->
    <section id="activities" style="padding: 5rem 0; background: transparent; position: relative; overflow: hidden;">
        
        <div class="container" style="position: relative; z-index: 1;">
            <div style="margin-bottom: 3.5rem; text-align: center;">
                <div style="display: inline-block; background: rgba(44, 82, 130, 0.1); padding: 0.5rem 1.2rem; border-radius: 20px; margin-bottom: 1rem;">
                    <span style="color: var(--secondary-blue); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Your Journey</span>
                </div>
                <h2 style="font-size: 2.2rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 0.75rem;">Recent Activities</h2>
                <p style="color: #666; font-size: 1rem; max-width: 500px; margin: 0 auto;">Track your requests, complaints, and important transactions</p>
            </div>

            <!-- 3D Card Carousel Container -->
            <div class="activities-carousel-wrapper" style="position: relative; max-width: 1200px; margin: 0 auto; padding: 4rem 0;">
                
                <!-- Navigation Buttons -->
                <button class="carousel-nav carousel-prev" onclick="previousActivity()" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); z-index: 10; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 2px solid rgba(30, 58, 95, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);">
                    <i class="fas fa-chevron-left" style="font-size: 1.5rem; color: var(--primary-blue);"></i>
                </button>

                <button class="carousel-nav carousel-next" onclick="nextActivity()" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); z-index: 10; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 2px solid rgba(30, 58, 95, 0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);">
                    <i class="fas fa-chevron-right" style="font-size: 1.5rem; color: var(--primary-blue);"></i>
                </button>

                <!-- Cards Container -->
                <div class="activities-carousel" style="position: relative; height: 380px; perspective: 1500px; display: flex; align-items: center; justify-content: center;">
                    
                    <!-- Activity Card 1 -->
                    <div class="activity-card active" data-index="0" style="position: absolute; width: 450px; background: white; padding: 2.5rem; border-radius: 24px; border-left: 5px solid #22863a; transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 20px 60px rgba(34, 134, 58, 0.25); transform: translateX(0) scale(1); z-index: 3; opacity: 1;">
                        <div style="display: flex; gap: 1.5rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #22863a, #2ea043); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; flex-shrink: 0; box-shadow: 0 8px 20px rgba(34, 134, 58, 0.3);">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem; letter-spacing: 0.5px;">October 28, 2025</div>
                                <h4 style="font-size: 1.25rem; color: var(--primary-blue); font-weight: 700; margin-bottom: 0.75rem;">Document Request Approved</h4>
                                <p style="font-size: 0.95rem; color: #666; line-height: 1.6;">Your Barangay Clearance has been approved. Download it from My Documents section.</p>
                            </div>
                        </div>
                        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="background: linear-gradient(135deg, #22863a, #2ea043); color: white; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 4px 12px rgba(34, 134, 58, 0.3);">✓ Approved</span>
                            <button style="background: transparent; border: 2px solid #22863a; color: #22863a; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">View Details</button>
                        </div>
                    </div>

                    <!-- Activity Card 2 -->
                    <div class="activity-card" data-index="1" style="position: absolute; width: 450px; background: white; padding: 2.5rem; border-radius: 24px; border-left: 5px solid #f57c00; transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 10px 30px rgba(245, 124, 0, 0.15); transform: translateX(500px) scale(0.85); z-index: 2; opacity: 0.5; filter: blur(2px);">
                        <div style="display: flex; gap: 1.5rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f57c00, #ff9800); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; flex-shrink: 0; box-shadow: 0 8px 20px rgba(245, 124, 0, 0.3);">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem; letter-spacing: 0.5px;">October 25, 2025</div>
                                <h4 style="font-size: 1.25rem; color: var(--primary-blue); font-weight: 700; margin-bottom: 0.75rem;">Complaint Submitted</h4>
                                <p style="font-size: 0.95rem; color: #666; line-height: 1.6;">Your complaint about street lighting is received and being reviewed by our team.</p>
                            </div>
                        </div>
                        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="background: linear-gradient(135deg, #f57c00, #ff9800); color: white; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 4px 12px rgba(245, 124, 0, 0.3);">⟳ Processing</span>
                            <button style="background: transparent; border: 2px solid #f57c00; color: #f57c00; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">View Details</button>
                        </div>
                    </div>

                    <!-- Activity Card 3 -->
                    <div class="activity-card" data-index="2" style="position: absolute; width: 450px; background: white; padding: 2.5rem; border-radius: 24px; border-left: 5px solid #1976d2; transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 10px 30px rgba(25, 118, 210, 0.15); transform: translateX(-500px) scale(0.85); z-index: 2; opacity: 0.5; filter: blur(2px);">
                        <div style="display: flex; gap: 1.5rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #1976d2, #2196f3); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; flex-shrink: 0; box-shadow: 0 8px 20px rgba(25, 118, 210, 0.3);">
                                <i class="fas fa-poll-h"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem; letter-spacing: 0.5px;"><?php echo date('F 01, Y'); ?></div>
                                <h4 style="font-size: 1.25rem; color: var(--primary-blue); font-weight: 700; margin-bottom: 0.75rem;">Assessment Survey</h4>
                                <?php if ($hasMonthlySurvey): ?>
                                    <p style="font-size: 0.95rem; color: #666; line-height: 1.6;">Thank you for completing the Assessment Survey.</p>
                                <?php else: ?>
                                    <p style="font-size: 0.95rem; color: #666; line-height: 1.6;">Please complete the monthly survey to help us better serve the community.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <?php if ($hasMonthlySurvey): ?>
                                <span style="background: linear-gradient(135deg, #1976d2, #2196f3); color: white; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);">✓ Completed</span>
                            <?php else: ?>
                                <a href="<?php echo BASE_PUBLIC . 'index.php?page=survey_wizard_personal'; ?>" style="background: linear-gradient(135deg, #1976d2, #2196f3); color: white; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3); text-decoration: none;">Answer Survey</a>
                            <?php endif; ?>
                            <button style="background: transparent; border: 2px solid #1976d2; color: #1976d2; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;" data-bs-toggle="modal" data-bs-target="#surveyDetailsModal">View Details</button>
                        </div>
                    </div>

                    <!-- Activity Card 4 (Hidden initially) -->
                    <div class="activity-card" data-index="3" style="position: absolute; width: 450px; background: white; padding: 2.5rem; border-radius: 24px; border-left: 5px solid #c53030; transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 10px 30px rgba(197, 48, 48, 0.15); transform: translateX(500px) scale(0.85); z-index: 1; opacity: 0; filter: blur(2px); pointer-events: none;">
                        <div style="display: flex; gap: 1.5rem;">
                            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #c53030, #e53e3e); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; flex-shrink: 0; box-shadow: 0 8px 20px rgba(197, 48, 48, 0.3);">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.75rem; color: #999; text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem; letter-spacing: 0.5px;">October 15, 2025</div>
                                <h4 style="font-size: 1.25rem; color: var(--primary-blue); font-weight: 700; margin-bottom: 0.75rem;">Document Requested</h4>
                                <p style="font-size: 0.95rem; color: #666; line-height: 1.6;">You requested a Certificate of Indigency. Processing time: 3-5 business days.</p>
                            </div>
                        </div>
                        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="background: linear-gradient(135deg, #c53030, #e53e3e); color: white; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700; box-shadow: 0 4px 12px rgba(197, 48, 48, 0.3);">📄 Requested</span>
                            <button style="background: transparent; border: 2px solid #c53030; color: #c53030; padding: 0.6rem 1.2rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">View Details</button>
                        </div>
                    </div>

                </div>

                <!-- Carousel Indicators -->
                <div style="display: flex; justify-content: center; gap: 12px; margin-top: 3rem;">
                    <span class="carousel-indicator active" onclick="goToActivity(0)" style="width: 12px; height: 12px; border-radius: 50%; background: var(--primary-blue); cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(30, 58, 95, 0.3);"></span>
                    <span class="carousel-indicator" onclick="goToActivity(1)" style="width: 12px; height: 12px; border-radius: 50%; background: #ccc; cursor: pointer; transition: all 0.3s ease;"></span>
                    <span class="carousel-indicator" onclick="goToActivity(2)" style="width: 12px; height: 12px; border-radius: 50%; background: #ccc; cursor: pointer; transition: all 0.3s ease;"></span>
                    <span class="carousel-indicator" onclick="goToActivity(3)" style="width: 12px; height: 12px; border-radius: 50%; background: #ccc; cursor: pointer; transition: all 0.3s ease;"></span>
                </div>
            </div>
        </div>
    </section>

    <script>
    let currentActivityIndex = 0;
    const totalActivities = 4;

    function updateCarousel() {
        const cards = document.querySelectorAll('.activity-card');
        const indicators = document.querySelectorAll('.carousel-indicator');
        
        cards.forEach((card, index) => {
            const dataIndex = parseInt(card.getAttribute('data-index'));
            let position = dataIndex - currentActivityIndex;
            
            // Handle wrapping
            if (position > totalActivities / 2) position -= totalActivities;
            if (position < -totalActivities / 2) position += totalActivities;
            
            card.classList.remove('active');
            
            if (position === 0) {
                // Center card - active
                card.style.transform = 'translateX(0) scale(1) rotateY(0deg)';
                card.style.zIndex = '3';
                card.style.opacity = '1';
                card.style.filter = 'blur(0px)';
                card.style.pointerEvents = 'auto';
                card.classList.add('active');
            } else if (position === 1) {
                // Right card
                card.style.transform = 'translateX(500px) scale(0.85) rotateY(-15deg)';
                card.style.zIndex = '2';
                card.style.opacity = '0.5';
                card.style.filter = 'blur(2px)';
                card.style.pointerEvents = 'none';
            } else if (position === -1) {
                // Left card
                card.style.transform = 'translateX(-500px) scale(0.85) rotateY(15deg)';
                card.style.zIndex = '2';
                card.style.opacity = '0.5';
                card.style.filter = 'blur(2px)';
                card.style.pointerEvents = 'none';
            } else {
                // Hidden cards
                card.style.transform = position > 0 ? 'translateX(500px) scale(0.7)' : 'translateX(-500px) scale(0.7)';
                card.style.zIndex = '1';
                card.style.opacity = '0';
                card.style.filter = 'blur(3px)';
                card.style.pointerEvents = 'none';
            }
        });
        
        // Update indicators
        indicators.forEach((indicator, index) => {
            if (index === currentActivityIndex) {
                indicator.classList.add('active');
                indicator.style.background = 'var(--primary-blue)';
                indicator.style.width = '32px';
                indicator.style.borderRadius = '6px';
                indicator.style.boxShadow = '0 2px 8px rgba(30, 58, 95, 0.3)';
            } else {
                indicator.classList.remove('active');
                indicator.style.background = '#ccc';
                indicator.style.width = '12px';
                indicator.style.borderRadius = '50%';
                indicator.style.boxShadow = 'none';
            }
        });
    }

    function nextActivity() {
        currentActivityIndex = (currentActivityIndex + 1) % totalActivities;
        updateCarousel();
    }

    function previousActivity() {
        currentActivityIndex = (currentActivityIndex - 1 + totalActivities) % totalActivities;
        updateCarousel();
    }

    function goToActivity(index) {
        currentActivityIndex = index;
        updateCarousel();
    }

    // Navigation button hover effects
    document.querySelectorAll('.carousel-nav').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-50%) scale(1.1)';
            this.style.boxShadow = '0 12px 32px rgba(30, 58, 95, 0.25)';
            this.style.background = 'rgba(30, 58, 95, 0.1)';
        });
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-50%) scale(1)';
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.15)';
            this.style.background = 'rgba(255, 255, 255, 0.95)';
        });
    });

    // Auto-play carousel (optional)
    setInterval(() => {
        nextActivity();
    }, 5000);
    </script>

    <!-- Achievements/Badges - Stacked Carousel -->
    <section class="achievements-section" style="padding: 5rem 0; background: transparent; position: relative; overflow: visible;">
        <div class="container">
            <div class="section-header" style="text-align: center; margin-bottom: 3rem;">
                <h2 class="section-title" style="font-size: 2.2rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 0.75rem;">Your Achievements</h2>
                <p class="section-subtitle" style="color: #666; font-size: 1rem;">Earn badges through civic engagement</p>
            </div>
            
            <!-- Stacked Cards Carousel -->
            <div style="position: relative; width: 100%; max-width: 900px; height: 450px; margin: 0 auto; perspective: 1500px;">
                <div id="badgeStack" style="position: relative; width: 100%; height: 100%; transform-style: preserve-3d;">
                    
                    <!-- Badge Card 1: Good Citizen -->
                    <div class="badge-stack-card" data-position="0" style="
                        position: absolute;
                        width: 280px;
                        height: 350px;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    ">
                        <div style="
                            width: 100%;
                            height: 100%;
                            background: linear-gradient(135deg, #ffffff 0%, #fef3c7 100%);
                            border-radius: 24px;
                            padding: 2rem;
                            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                            border: 4px solid #fbbf24;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                        ">
                            <div style="font-size: 5rem; margin-bottom: 1rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">🏅</div>
                            <div style="font-weight: 700; font-size: 1.5rem; color: #1e3a5f; margin-bottom: 0.5rem;">Good Citizen</div>
                            <div style="font-size: 0.95rem; color: #64748b; line-height: 1.5;">Maintained clearance for 2 years</div>
                        </div>
                    </div>

                    <!-- Badge Card 2: Active Voter -->
                    <div class="badge-stack-card" data-position="1" style="
                        position: absolute;
                        width: 280px;
                        height: 350px;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    ">
                        <div style="
                            width: 100%;
                            height: 100%;
                            background: linear-gradient(135deg, #ffffff 0%, #dbeafe 100%);
                            border-radius: 24px;
                            padding: 2rem;
                            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                            border: 4px solid #3b82f6;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                        ">
                            <div style="font-size: 5rem; margin-bottom: 1rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">🗳️</div>
                            <div style="font-weight: 700; font-size: 1.5rem; color: #1e3a5f; margin-bottom: 0.5rem;">Active Voter</div>
                            <div style="font-size: 0.95rem; color: #64748b; line-height: 1.5;">Participated in voting</div>
                        </div>
                    </div>

                    <!-- Badge Card 3: Community Helper -->
                    <div class="badge-stack-card" data-position="2" style="
                        position: absolute;
                        width: 280px;
                        height: 350px;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    ">
                        <div style="
                            width: 100%;
                            height: 100%;
                            background: linear-gradient(135deg, #ffffff 0%, #d1fae5 100%);
                            border-radius: 24px;
                            padding: 2rem;
                            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                            border: 4px solid #10b981;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                        ">
                            <div style="font-size: 5rem; margin-bottom: 1rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">🤝</div>
                            <div style="font-weight: 700; font-size: 1.5rem; color: #1e3a5f; margin-bottom: 0.5rem;">Community Helper</div>
                            <div style="font-size: 0.95rem; color: #64748b; line-height: 1.5;">Joined 3 community events</div>
                        </div>
                    </div>

                    <!-- Badge Card 4: Active Participant -->
                    <div class="badge-stack-card" data-position="3" style="
                        position: absolute;
                        width: 280px;
                        height: 350px;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    ">
                        <div style="
                            width: 100%;
                            height: 100%;
                            background: linear-gradient(135deg, #ffffff 0%, #e9d5ff 100%);
                            border-radius: 24px;
                            padding: 2rem;
                            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                            border: 4px solid #8b5cf6;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                        ">
                            <div style="font-size: 5rem; margin-bottom: 1rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));">💬</div>
                            <div style="font-weight: 700; font-size: 1.5rem; color: #1e3a5f; margin-bottom: 0.5rem;">Active Participant</div>
                            <div style="font-size: 0.95rem; color: #64748b; line-height: 1.5;">Posted in community forum</div>
                        </div>
                    </div>

                    <!-- Badge Card 5: Volunteer (Locked) -->
                    <div class="badge-stack-card" data-position="4" style="
                        position: absolute;
                        width: 280px;
                        height: 350px;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    ">
                        <div style="
                            width: 100%;
                            height: 100%;
                            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
                            border-radius: 24px;
                            padding: 2rem;
                            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                            border: 4px solid #9ca3af;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                        ">
                            <div style="font-size: 5rem; margin-bottom: 1rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1)) grayscale(100%);">🌟</div>
                            <div style="font-weight: 700; font-size: 1.5rem; color: #6b7280; margin-bottom: 0.5rem;">Volunteer</div>
                            <div style="font-size: 0.95rem; color: #9ca3af; line-height: 1.5;">Help in 5 events</div>
                            <div style="font-size: 2rem; margin-top: 0.5rem;">🔒</div>
                        </div>
                    </div>

                    <!-- Badge Card 6: Community Legend (Locked) -->
                    <div class="badge-stack-card" data-position="5" style="
                        position: absolute;
                        width: 280px;
                        height: 350px;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                        transition: all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    ">
                        <div style="
                            width: 100%;
                            height: 100%;
                            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
                            border-radius: 24px;
                            padding: 2rem;
                            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
                            border: 4px solid #9ca3af;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                        ">
                            <div style="font-size: 5rem; margin-bottom: 1rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1)) grayscale(100%);">👑</div>
                            <div style="font-weight: 700; font-size: 1.5rem; color: #6b7280; margin-bottom: 0.5rem;">Community Legend</div>
                            <div style="font-size: 0.95rem; color: #9ca3af; line-height: 1.5;">Complete profile 100%</div>
                            <div style="font-size: 2rem; margin-top: 0.5rem;">🔒</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <script>
        // Badge Stack Carousel
        let currentBadgeIndex = 0;
        const totalBadges = 6;

        function updateBadgeStack() {
            const cards = document.querySelectorAll('.badge-stack-card');
            
            cards.forEach((card, index) => {
                const position = (index - currentBadgeIndex + totalBadges) % totalBadges;
                
                // Reset styles
                card.style.zIndex = '';
                card.style.transform = '';
                card.style.filter = '';
                card.style.opacity = '';
                card.style.pointerEvents = '';
                
                if (position === 0) {
                    // Front card - full size, centered, no blur
                    card.style.transform = 'translate(-50%, -50%) scale(1) rotateY(0deg) translateZ(0px)';
                    card.style.zIndex = '50';
                    card.style.filter = 'blur(0px)';
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                } else if (position === 1) {
                    // Right card - smaller, more offset, blur
                    card.style.transform = 'translate(0%, -50%) scale(0.8) rotateY(-20deg) translateZ(-100px)';
                    card.style.zIndex = '40';
                    card.style.filter = 'blur(3px)';
                    card.style.opacity = '0.5';
                    card.style.pointerEvents = 'none';
                } else if (position === 2) {
                    // Far right - even smaller, more blur
                    card.style.transform = 'translate(50%, -50%) scale(0.65) rotateY(-30deg) translateZ(-200px)';
                    card.style.zIndex = '30';
                    card.style.filter = 'blur(5px)';
                    card.style.opacity = '0.3';
                    card.style.pointerEvents = 'none';
                } else if (position === totalBadges - 1) {
                    // Left card - smaller, more offset, blur
                    card.style.transform = 'translate(-100%, -50%) scale(0.8) rotateY(20deg) translateZ(-100px)';
                    card.style.zIndex = '40';
                    card.style.filter = 'blur(3px)';
                    card.style.opacity = '0.5';
                    card.style.pointerEvents = 'none';
                } else if (position === totalBadges - 2) {
                    // Far left - even smaller, more blur
                    card.style.transform = 'translate(-150%, -50%) scale(0.65) rotateY(30deg) translateZ(-200px)';
                    card.style.zIndex = '30';
                    card.style.filter = 'blur(5px)';
                    card.style.opacity = '0.3';
                    card.style.pointerEvents = 'none';
                } else {
                    // Hidden cards
                    card.style.transform = 'translate(-50%, -50%) scale(0.4) translateZ(-300px)';
                    card.style.zIndex = '10';
                    card.style.filter = 'blur(8px)';
                    card.style.opacity = '0';
                    card.style.pointerEvents = 'none';
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateBadgeStack();
            
            // Auto-rotate every 3 seconds - continuous, cannot be controlled
            setInterval(() => {
                currentBadgeIndex = (currentBadgeIndex + 1) % totalBadges;
                updateBadgeStack();
            }, 3000);
        });

        // ========== NAVBAR SCROLL EFFECT ==========
        // Add transparent blur effect on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.user-navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>

    <!-- Resident Directory - Interactive Flip Cards Carousel -->
    <section id="directory" style="padding: 4rem 0; background: transparent; position: relative; overflow: hidden;">
        
        <div class="container" style="position: relative; z-index: 1;">
            <div style="margin-bottom: 2.5rem; text-align: center;">
                <div style="display: inline-block; background: rgba(30, 58, 95, 0.1); padding: 0.5rem 1.2rem; border-radius: 20px; margin-bottom: 1rem;">
                    <span style="color: var(--primary-blue); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Community Leaders</span>
                </div>
                <h2 style="font-size: 2.2rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 0.75rem;">Officials Directory</h2>
                <p style="color: #666; font-size: 1rem; max-width: 500px; margin: 0 auto;">Hover over cards to see contact information</p>
            </div>

            <!-- Carousel Navigation -->
            <div style="position: relative; max-width: 1100px; margin: 0 auto;">
                <button onclick="prevDirectorSlide()" style="
                    position: absolute;
                    left: -60px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    border: none;
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    color: #1e3a5f;
                    font-size: 1.3rem;
                    cursor: pointer;
                    transition: all 0.3s;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    z-index: 10;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                " onmouseover="this.style.background='#1e3a5f'; this.style.color='white'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.95)'; this.style.color='#1e3a5f'; this.style.transform='translateY(-50%) scale(1)';">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <button onclick="nextDirectorSlide()" style="
                    position: absolute;
                    right: -60px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    border: none;
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    color: #1e3a5f;
                    font-size: 1.3rem;
                    cursor: pointer;
                    transition: all 0.3s;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    z-index: 10;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                " onmouseover="this.style.background='#1e3a5f'; this.style.color='white'; this.style.transform='translateY(-50%) scale(1.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.95)'; this.style.color='#1e3a5f'; this.style.transform='translateY(-50%) scale(1)';">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <!-- Carousel Container -->
                <div style="position: relative; height: 550px; padding: 2rem 0; perspective: 2000px;">
                    <div class="directory-grid" style="position: relative; display: flex; justify-content: center; align-items: center; height: 100%;">
                        <?php if (!empty($officials)): ?>
                            <?php 
                                $colors = [
                                    ['bg' => 'linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%)', 'text' => '#1976d2', 'shadow' => 'rgba(25, 118, 210, 0.25)'],
                                    ['bg' => 'linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%)', 'text' => '#059669', 'shadow' => 'rgba(5, 150, 105, 0.25)'],
                                    ['bg' => 'linear-gradient(135deg, #fef3c7 0%, #fde68a 100%)', 'text' => '#d97706', 'shadow' => 'rgba(217, 119, 6, 0.25)'],
                                    ['bg' => 'linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%)', 'text' => '#ec4899', 'shadow' => 'rgba(236, 72, 153, 0.25)'],
                                    ['bg' => 'linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%)', 'text' => '#6366f1', 'shadow' => 'rgba(99, 102, 241, 0.25)'],
                                    ['bg' => 'linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%)', 'text' => '#a855f7', 'shadow' => 'rgba(168, 85, 247, 0.25)'],
                                ];
                            ?>
                            <?php foreach ($officials as $index => $official): ?>
                                <?php
                                    $color = $colors[$index % count($colors)];
                                    $name = htmlspecialchars($official['full_name']);
                                    $role = htmlspecialchars($official['role']);
                                    $contact_no = htmlspecialchars($official['contact_no'] ?? 'Not Available');
                                    $email = htmlspecialchars($official['email'] ?? 'Not Available');
                                    $initial = strtoupper(substr($name, 0, 1));
                                ?>
                                <div 
                                    class="director-card-placeholder"
                                    data-name="<?php echo $name; ?>"
                                    data-role="<?php echo $role; ?>"
                                    data-contact="<?php echo $contact_no; ?>"
                                    data-email="<?php echo $email; ?>"
                                    data-photo="<?php echo htmlspecialchars($official['photo_url'] ?? ''); ?>"
                                    data-initial="<?php echo $initial; ?>"
                                    data-color-bg="<?php echo $color['bg']; ?>"
                                    data-color-text="<?php echo $color['text']; ?>"
                                    data-color-shadow="<?php echo $color['shadow']; ?>"
                                >
                                    <!-- This content will be replaced by JS, but we can keep it for non-JS users -->
                                    <div style="position: absolute; top: -50%; right: -50%; width: 150px; height: 150px; background: rgba(30, 58, 95, 0.08); border-radius: 50%; filter: blur(30px);"></div>
                                    
                                    <?php if (!empty($official['photo_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($official['photo_url']); ?>" alt="<?php echo $name; ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 1.5rem; position: relative; z-index: 1; box-shadow: 0 8px 20px <?php echo $color['shadow']; ?>;">
                                    <?php else: ?>
                                        <div style="width: 80px; height: 80px; border-radius: 50%; background: <?php echo $color['bg']; ?>; display: flex; align-items: center; justify-content: center; color: <?php echo $color['text']; ?>; font-weight: 700; font-size: 2rem; margin: 0 auto 1.5rem; position: relative; z-index: 1; box-shadow: 0 8px 20px <?php echo $color['shadow']; ?>;"><?php echo $initial; ?></div>
                                    <?php endif; ?>

                                    <h4 style="font-size: 1.05rem; color: var(--primary-blue); font-weight: 700; margin-bottom: 0.4rem;"><?php echo $name; ?></h4>
                                    <p style="font-size: 0.85rem; color: #999; margin-bottom: 1rem; font-weight: 600;"><?php echo $role; ?></p>
                                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 1.5rem;"><?php echo $contact_no; ?></p>
                                    <div style="padding-top: 1rem; border-top: 1px solid #f0f0f0;">
                                        <a href="mailto:<?php echo $email; ?>" style="color: <?php echo $color['text']; ?>; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; transition: gap 0.3s;" onmouseover="this.style.gap='0.7rem';" onmouseout="this.style.gap='0.4rem';">Contact <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i></a>
                                    </div>
                                </div> 
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No officials found.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Carousel Indicators -->
                <div style="display: flex; justify-content: center; gap: 0.6rem; margin-top: 1rem;">
                    <?php if (!empty($officials)): ?>
                        <?php foreach ($officials as $index => $official): ?>
                            <div class="dir-indicator <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToDirectorSlide(<?php echo $index; ?>)" style="width: 10px; height: 10px; border-radius: 50%; background: <?php echo $index === 0 ? '#1e3a5f' : '#cbd5e1'; ?>; cursor: pointer; transition: all 0.3s;"></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Survey Details Modal -->
    <div class="modal fade" id="surveyDetailsModal" tabindex="-1" aria-labelledby="surveyDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; border-bottom: none;">
                    <h5 class="modal-title" id="surveyDetailsModalLabel" style="font-weight: 600;"><i class="fas fa-poll-h" style="margin-right: 0.5rem;"></i>About the Monthly Assessment Survey</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 1.5rem 2rem;">
                    <p style="font-size: 1rem; color: #333; line-height: 1.6;">
                        The monthly assessment survey is a vital tool for our barangay. Your participation helps us gather essential data to monitor the well-being of our community, identify emerging needs, and make informed decisions for future projects and services.
                    </p>
                    <hr style="margin: 1.5rem 0;">
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 8px;">
                        <p style="font-size: 1rem; color: #333; line-height: 1.6; font-style: italic;">
                            Ang buwanang assessment survey ay isang mahalagang kasangkapan para sa ating barangay. Ang iyong pakikilahok ay tumutulong sa amin na makakalap ng mahahalagang datos upang masubaybayan ang kapakanan ng ating komunidad, matukoy ang mga pangangailangan, at makagawa ng mga desisyon para sa mga proyekto at serbisyo sa hinaharap.
                        </p>
                    </div>
                    <p class="mt-4" style="font-size: 0.9rem; color: #666;">
                        Thank you for your cooperation!
                    </p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.12);">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; border: none; padding: 1rem 1.5rem;">
                    <h5 class="modal-title" style="font-weight: 600; font-size: 1rem; display: flex; align-items: center; gap: 0.6rem;">
                        <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fas fa-bell"></i>
                        </div>
                        Notifications
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size: 0.8rem;"></button>
                </div>
                <div class="modal-body" style="padding: 1.25rem 1.5rem; background: #f8fafc;">
                    <div style="background: white; border-radius: 12px; padding: 1rem; margin-bottom: 0.8rem; border-left: 3px solid #10b981; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateX(4px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
                        <div style="display: flex; gap: 0.85rem; align-items: start;">
                            <div style="width: 38px; height: 38px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; font-size: 0.95rem;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.3rem; font-size: 0.9rem;">Document Ready for Pickup</div>
                                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.4rem; line-height: 1.4;">Your Barangay Clearance is ready. Collect it at the Barangay Hall.</div>
                                <div style="font-size: 0.7rem; color: #94a3b8; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-clock"></i> 2 hours ago
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="background: white; border-radius: 12px; padding: 1rem; margin-bottom: 0.8rem; border-left: 3px solid #f59e0b; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateX(4px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
                        <div style="display: flex; gap: 0.85rem; align-items: start;">
                            <div style="width: 38px; height: 38px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; font-size: 0.95rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.3rem; font-size: 0.9rem;">Scheduled Maintenance Tomorrow</div>
                                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.4rem; line-height: 1.4;">There will be network maintenance from 10 PM to 2 AM.</div>
                                <div style="font-size: 0.7rem; color: #94a3b8; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-clock"></i> 5 hours ago
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="background: white; border-radius: 12px; padding: 1rem; border-left: 3px solid #3b82f6; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s; cursor: pointer;" onmouseover="this.style.transform='translateX(4px)'; this.style.boxShadow='0 3px 12px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
                        <div style="display: flex; gap: 0.85rem; align-items: start;">
                            <div style="width: 38px; height: 38px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; font-size: 0.95rem;">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.3rem; font-size: 0.9rem;">New Survey Available</div>
                                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.4rem; line-height: 1.4;">Help us improve! A new community survey is now open.</div>
                                <div style="font-size: 0.7rem; color: #94a3b8; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-clock"></i> 1 day ago
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Modal -->
    <div class="modal fade" id="documentsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.12);">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; border: none; padding: 1rem 1.5rem;">
                    <h5 class="modal-title" style="font-weight: 600; font-size: 1rem; display: flex; align-items: center; gap: 0.6rem;">
                        <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        My Documents
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size: 0.8rem;"></button>
                </div>
                <div class="modal-body" style="padding: 1.25rem 1.5rem; background: #f8fafc;">
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                        <div style="background: white; border-radius: 12px; padding: 1.2rem; display: flex; gap: 1rem; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #c53030, #ff6b6b); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 3px 10px rgba(197,48,48,0.25);">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.25rem; font-size: 0.95rem;">Barangay Clearance</div>
                                <div style="font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-calendar"></i> Oct 28, 2025
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: 2px solid #1e3a5f; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: transparent; color: #1e3a5f; transition: all 0.3s;" onmouseover="this.style.background='#1e3a5f'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#1e3a5f';"><i class="fas fa-eye"></i> View</button>
                                <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: none; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s; box-shadow: 0 3px 10px rgba(30,58,95,0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(30,58,95,0.2)';"><i class="fas fa-download"></i> Download</button>
                            </div>
                        </div>
                        <div style="background: white; border-radius: 12px; padding: 1.2rem; display: flex; gap: 1rem; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #c53030, #ff6b6b); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 3px 10px rgba(197,48,48,0.25);">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.25rem; font-size: 0.95rem;">Cedula Copy</div>
                                <div style="font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-calendar"></i> Oct 15, 2025
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: 2px solid #1e3a5f; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: transparent; color: #1e3a5f; transition: all 0.3s;" onmouseover="this.style.background='#1e3a5f'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#1e3a5f';"><i class="fas fa-eye"></i> View</button>
                                <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: none; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s; box-shadow: 0 3px 10px rgba(30,58,95,0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(30,58,95,0.2)';"><i class="fas fa-download"></i> Download</button>
                            </div>
                        </div>
                        <div style="background: white; border-radius: 12px; padding: 1.2rem; display: flex; gap: 1rem; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #c53030, #ff6b6b); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 3px 10px rgba(197,48,48,0.25);">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.25rem; font-size: 0.95rem;">Income Certificate</div>
                                <div style="font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 0.3rem;">
                                    <i class="fas fa-calendar"></i> Aug 10, 2025
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: 2px solid #1e3a5f; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: transparent; color: #1e3a5f; transition: all 0.3s;" onmouseover="this.style.background='#1e3a5f'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#1e3a5f';"><i class="fas fa-eye"></i> View</button>
                                <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: none; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s; box-shadow: 0 3px 10px rgba(30,58,95,0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(30,58,95,0.2)';"><i class="fas fa-download"></i> Download</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inbox Modal -->
    <div class="modal fade" id="inboxModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.12);">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; border: none; padding: 1rem 1.5rem;">
                    <h5 class="modal-title" style="font-weight: 600; font-size: 1rem; display: flex; align-items: center; gap: 0.6rem;">
                        <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        Inbox
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size: 0.8rem;"></button>
                </div>
                <div class="modal-body" style="padding: 1.25rem 1.5rem; background: #f8fafc;">
                    <div class="message-item">
                        <div class="message-avatar">B</div>
                        <div class="message-content">
                            <div class="message-sender">
                                <span>Barangay Administrator</span>
                                <span class="unread-badge">1</span>
                            </div>
                            <div class="message-text">Your Barangay Clearance is ready for pickup...</div>
                            <div class="message-time">Today, 2:30 PM</div>
                        </div>
                    </div>
                    <div class="message-item">
                        <div class="message-avatar" style="background: linear-gradient(135deg, #10b981, #059669);">H</div>
                        <div class="message-content">
                            <div class="message-sender">Hon. Juan Dela Cruz</div>
                            <div class="message-text">Thank you for attending yesterday's community meeting...</div>
                            <div class="message-time">Yesterday, 4:15 PM</div>
                        </div>
                    </div>
                    <div class="message-item">
                        <div class="message-avatar" style="background: linear-gradient(135deg, #f59e0b, #d97706);">S</div>
                        <div class="message-content">
                            <div class="message-sender">Services Team</div>
                            <div class="message-text">Reminder: Your survey response has been recorded...</div>
                            <div class="message-time">Oct 30, 10:00 AM</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Get in Touch</h2>
                <p class="section-subtitle">Contact the Barangay for assistance and inquiries</p>
            </div>
            <div class="contact-cards-grid">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-title">Emergency</div>
                    <div class="contact-details">
                        <strong>(043) 415-XXXX</strong><br>
                        Available 24/7
                    </div>
                    <span class="contact-badge">Emergency</span>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-title">Office Hours</div>
                    <div class="contact-details">
                        Mon-Fri: 8:00 AM - 5:00 PM<br>
                        Sat: 8:00 AM - 12:00 PM
                    </div>
                    <span class="contact-badge">Weekdays</span>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-title">Location</div>
                    <div class="contact-details">
                        Barangay Hall<br>
                        Lumbangan, Nasugbu, Batangas
                    </div>
                    <button class="contact-badge" type="button" data-bs-toggle="modal" data-bs-target="#mapModal" style="background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)); color: white; border: none; cursor: pointer; padding: 8px 16px; border-radius: 20px; font-weight: 600;">Visit Us</button>
                </div>
            </div>
        </div>
    </section>

    <!-- User Profile Modal -->
    <div class="modal fade" id="userProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                <!-- Modal Header -->
                <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                    <h5 class="modal-title" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;"><i class="fas fa-id-card"></i> Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 2rem;">
                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 2rem; align-items: center;">
                        <!-- Left: Avatar -->
                        <div style="text-align: center;">
                            <div style="width: 120px; height: 120px; border-radius: 12px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                <i class="fas fa-user" style="font-size: 2.5rem; color: var(--primary-blue);"></i>
                            </div>
                        </div>

                        <!-- Right: Profile Information -->
                        <div>
                            <div class="profile-info-item" style="margin-bottom: 1rem;">
                                <label style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.3rem;">Full Name</label>
                                <p id="profileName" style="color: var(--primary-blue); font-weight: 600; font-size: 1rem; margin: 0;"><?php echo htmlspecialchars($fullName); ?></p>
                            </div>

                            <div class="profile-info-item" style="margin-bottom: 1rem;">
                                <label style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.3rem;">Email Address</label>
                                <p id="profileEmail" style="color: #666; font-size: 0.95rem; margin: 0;"><?php echo htmlspecialchars($_SESSION['email'] ?? 'Not provided'); ?></p>
                            </div>

                            <div class="profile-info-item">
                                <label style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.3rem;">Contact Number</label>
                                <p id="profileContact" style="color: #666; font-size: 0.95rem; margin: 0;"><?php echo htmlspecialchars($_SESSION['mobile'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 2rem; background: white; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm" style="background: white; border: 1px solid #ddd; color: #666; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500;" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500;" data-bs-toggle="modal" data-bs-target="#editProfileModal" data-bs-dismiss="modal">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                <!-- Modal Header -->
                <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                    <h5 class="modal-title" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;"><i class="fas fa-user-edit"></i> Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 2rem;">
                    <form id="editProfileForm">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div>
                                <label for="editName" style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.5rem;">Full Name</label>
                                <input type="text" class="form-control" id="editName" placeholder="Enter your full name" style="border-radius: 6px; padding: 0.6rem 0.8rem; border: 1px solid #e0e0e0; font-size: 0.95rem;">
                            </div>

                            <div>
                                <label for="editEmail" style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.5rem;">Email Address</label>
                                <input type="email" class="form-control" id="editEmail" placeholder="Enter your email" style="border-radius: 6px; padding: 0.6rem 0.8rem; border: 1px solid #e0e0e0; font-size: 0.95rem;">
                            </div>

                            <div style="grid-column: 1 / -1;">
                                <label for="editContact" style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.5rem;">Contact Number</label>
                                <input type="tel" class="form-control" id="editContact" placeholder="Enter your contact number" style="border-radius: 6px; padding: 0.6rem 0.8rem; border: 1px solid #e0e0e0; font-size: 0.95rem;">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 2rem; background: white; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm" style="background: white; border: 1px solid #ddd; color: #666; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500;" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500;" onclick="saveProfileChanges()">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div class="modal fade" id="mapModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-map-location-dot"></i> Barangay Lumbangan Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 0; height: 500px;">
                    <iframe width="100%" height="100%" frameborder="0" style="border:0" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3871.9!2d121.3786445!3d13.7301896!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd61e4e4e4e4e5%3A0x1234567890abcdef!2sLumbangan%2C%20Nasugbu%2C%20Batangas!5e0!3m2!1sen!2sph!4v1669012345678" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="modal-footer">
                    <p style="margin: 0; color: #718096; font-size: 0.9rem; width: 100%;">
                        <i class="fas fa-map-marker-alt" style="color: var(--accent-red);"></i>
                        <strong>Barangay Hall, Lumbangan, Nasugbu, Batangas</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">Barangay Lumbangan</h5>
                    <p style="color: rgba(255,255,255,0.85); line-height: 1.8;">
                        Committed to serving the community with transparency, integrity, and excellence. Building a better future for all residents.
                    </p>
                    <div class="social-links mt-4">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#dashboard">Dashboard</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#announcements">Announcements</a></li>
                        <li><a href="#profile">My Profile</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">Contact Information</h5>
                    <ul class="footer-links">
                        <li style="color: rgba(255,255,255,0.85);"><i class="fas fa-map-marker-alt" style="margin-right: 10px;"></i>Lumbangan, Nasugbu, Batangas</li>
                        <li style="color: rgba(255,255,255,0.85);"><i class="fas fa-phone" style="margin-right: 10px;"></i>(043) XXX-XXXX</li>
                        <li style="color: rgba(255,255,255,0.85);"><i class="fas fa-envelope" style="margin-right: 10px;"></i>barangay.lumbangan@nasugbu.gov.ph</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p style="margin: 0; color: rgba(255,255,255,0.7);">
                    © <?php echo date('Y'); ?> Barangay Lumbangan, Nasugbu, Batangas. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Handle navigation for survey status - runs BEFORE dashboard.js
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[data-navigate="true"]');
            if (link) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const href = link.getAttribute('href');
                if (href) {
                    window.location.href = href;
                }
            }
        }, true); // Use capture phase to run before dashboard.js
    </script>
    
    <script src="<?php echo BASE_URL; ?>/assets/js/Dashboard/dashboard.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/Dashboard/scroll-animations.js"></script>

    <script>

        if (typeof window.handleLogout !== 'function') {
            window.handleLogout = function(event) {
                if (event && event.preventDefault) event.preventDefault();

                try {
                    if (window.SurveyPersistence && typeof window.SurveyPersistence.clearAll === 'function') {
                        window.SurveyPersistence.clearAll();
                    }
                } catch (e) {
                    // ignore
                }

                try {
                    // Remove any survey_ keys
                    Object.keys(localStorage).forEach(function(k) {
                        if (k && k.indexOf && k.indexOf('survey_') === 0) {
                            localStorage.removeItem(k);
                        }
                    });
                } catch (e) {
                    // ignore
                }

                // Redirect to the centralized logout route handled by the front controller
                var logoutUrl = '<?php echo (defined("BASE_PUBLIC") ? rtrim(BASE_PUBLIC, "/") : rtrim(dirname(__DIR__, 2) . "/public", "/")); ?>/index.php?page=logout';
                window.location.href = logoutUrl;
            };
        }
    </script>

    <script>
        // Profile modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            setupEditProfileModal();
        });

        function setupEditProfileModal() {
            // When opening the edit modal, populate the form with current values
            const editProfileModal = document.getElementById('editProfileModal');
            editProfileModal.addEventListener('show.bs.modal', function() {
                document.getElementById('editName').value = document.getElementById('profileName').textContent;
                document.getElementById('editEmail').value = document.getElementById('profileEmail').textContent;
                document.getElementById('editContact').value = document.getElementById('profileContact').textContent;
            });
        }

        function saveProfileChanges() {
            const newName = document.getElementById('editName').value.trim();
            const newEmail = document.getElementById('editEmail').value.trim();
            const newContact = document.getElementById('editContact').value.trim();

            // Validation
            if (!newName) {
                alert('Please enter your full name');
                return;
            }

            if (!newEmail || !newEmail.includes('@')) {
                alert('Please enter a valid email address');
                return;
            }

            if (!newContact || newContact.length < 10) {
                alert('Please enter a valid contact number');
                return;
            }

            // TODO: Send AJAX request to server to update profile in database
            console.log('Profile update requested:', {
                name: newName,
                email: newEmail,
                contact: newContact
            });

            // Show success message
            showSuccessNotification('Profile update feature coming soon! Changes are not saved yet.');

            // Close the edit modal
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
            editModal.hide();
        }

        function showSuccessNotification(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.setAttribute('role', 'alert');
            alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 8px; border: none;';
            alertDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-check-circle" style="color: #22863a; font-size: 1.2rem;"></i>
                    <span style="font-weight: 500;">${message}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }



        // ============ RESIDENT DIRECTORY STACKED CARDS CAROUSEL ============
        
        let currentDirectorIndex = 0;
        const totalDirectors = <?php echo count($officials); ?>;
        
        // Carousel Navigation
        function nextDirectorSlide() {
            currentDirectorIndex = (currentDirectorIndex + 1) % totalDirectors;
            updateDirectorCarousel();
        }
        
        function prevDirectorSlide() {
            currentDirectorIndex = (currentDirectorIndex - 1 + totalDirectors) % totalDirectors;
            updateDirectorCarousel();
        }
        
        function goToDirectorSlide(index) {
            currentDirectorIndex = index;
            updateDirectorCarousel();
        }
        
        function updateDirectorCarousel() {
            const cards = document.querySelectorAll('.director-card');
            
            cards.forEach((card, index) => {
                // Calculate relative position from current card
                let position = index - currentDirectorIndex;
                
                // Handle wrap-around
                if (position > totalDirectors / 2) position -= totalDirectors;
                if (position < -totalDirectors / 2) position += totalDirectors;
                
                // Reset all styles
                card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                
                // Reset flip on all cards except center
                const inner = card.querySelector('.flip-card-inner');
                if (inner && position !== 0) {
                    inner.style.transform = 'rotateY(0deg)';
                }
                
                if (position === 0) {
                    // CENTER CARD - Main focus
                    card.style.transform = 'translateX(0) scale(1) rotateY(0deg)';
                    card.style.zIndex = '100';
                    card.style.opacity = '1';
                    card.style.filter = 'blur(0px)';
                    card.style.pointerEvents = 'auto'; // Enable hover
                } else if (position === -1) {
                    // LEFT CARD - Peeking behind, tilted left
                    card.style.transform = 'translateX(-75%) scale(0.85) rotateY(20deg)';
                    card.style.zIndex = '50';
                    card.style.opacity = '0.6';
                    card.style.filter = 'blur(1px)';
                    card.style.pointerEvents = 'none'; // Disable hover
                } else if (position === 1) {
                    // RIGHT CARD - Peeking behind, tilted right
                    card.style.transform = 'translateX(75%) scale(0.85) rotateY(-20deg)';
                    card.style.zIndex = '50';
                    card.style.opacity = '0.6';
                    card.style.filter = 'blur(1px)';
                    card.style.pointerEvents = 'none'; // Disable hover
                } else if (position === -2) {
                    // FAR LEFT - More stacked behind
                    card.style.transform = 'translateX(-120%) scale(0.7) rotateY(30deg)';
                    card.style.zIndex = '25';
                    card.style.opacity = '0.3';
                    card.style.filter = 'blur(2px)';
                    card.style.pointerEvents = 'none'; // Disable hover
                } else if (position === 2) {
                    // FAR RIGHT - More stacked behind
                    card.style.transform = 'translateX(120%) scale(0.7) rotateY(-30deg)';
                    card.style.zIndex = '25';
                    card.style.opacity = '0.3';
                    card.style.filter = 'blur(2px)';
                    card.style.pointerEvents = 'none'; // Disable hover
                } else {
                    // HIDDEN CARDS
                    card.style.transform = `translateX(${position > 0 ? '150%' : '-150%'}) scale(0.5)`;
                    card.style.zIndex = '1';
                    card.style.opacity = '0';
                    card.style.filter = 'blur(3px)';
                    card.style.pointerEvents = 'none'; // Disable hover
                }
            });
            
            // Update indicators
            const indicators = document.querySelectorAll('.dir-indicator');
            indicators.forEach((indicator, index) => {
                if (index === currentDirectorIndex) {
                    indicator.style.background = '#1e3a5f';
                    indicator.style.width = '24px';
                    indicator.classList.add('active');
                } else {
                    indicator.style.background = '#cbd5e1';
                    indicator.style.width = '10px';
                    indicator.classList.remove('active');
                }
            });
        }
        
        // Add flip functionality to existing director cards
        document.addEventListener('DOMContentLoaded', function() {
            const directorGrid = document.querySelector('.directory-grid');
            const directorCards = directorGrid.querySelectorAll('.director-card-placeholder');
            
            directorCards.forEach((card, index) => {
                // Add class and data attributes
                card.classList.add('director-card');
                const categories = ['executive', 'executive', 'kagawad', 'kagawad', 'kagawad', 'staff'];
                card.dataset.category = categories[index] || 'staff';
                // Set fixed dimensions for stacked carousel
                card.style.position = 'absolute';
                card.style.left = '50%';
                card.style.top = '50%';
                card.style.marginLeft = '-160px'; // Half of card width (320px)
                card.style.marginTop = '-210px'; // Half of card height (420px)
                card.style.width = '320px';
                card.style.height = '420px';
                card.style.perspective = '1000px';
                card.style.cursor = 'pointer';
                card.style.transformStyle = 'preserve-3d'; // This should be on the parent

                const name = card.dataset.name;
                const role = card.dataset.role;
                const contact = card.dataset.contact;
                const email = card.dataset.email;
                const photo = card.dataset.photo;
                const initial = card.dataset.initial;
                const colorBg = card.dataset.colorBg;
                const colorText = card.dataset.colorText;
                const colorShadow = card.dataset.colorShadow;

                const photoContent = photo
                    ? `<img src="${photo}" alt="${name}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 1.5rem; position: relative; z-index: 1; box-shadow: 0 8px 20px ${colorShadow};">`
                    : `<div style="width: 80px; height: 80px; border-radius: 50%; background: ${colorBg}; display: flex; align-items: center; justify-content: center; color: ${colorText}; font-weight: 700; font-size: 2rem; margin: 0 auto 1.5rem; position: relative; z-index: 1; box-shadow: 0 8px 20px ${colorShadow};">${initial}</div>`;

                const originalContent = `
                    <div style="position: absolute; top: -50%; right: -50%; width: 150px; height: 150px; background: rgba(30, 58, 95, 0.08); border-radius: 50%; filter: blur(30px);"></div>
                    ${photoContent}
                    <h4 style="font-size: 1.05rem; color: var(--primary-blue); font-weight: 700; margin-bottom: 0.4rem;">${name}</h4>
                    <p style="font-size: 0.85rem; color: #999; margin-bottom: 1rem; font-weight: 600;">${role}</p>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 1.5rem;">${contact}</p>
                    <div style="padding-top: 1rem; border-top: 1px solid #f0f0f0;">
                        <a href="mailto:${email}" style="color: ${colorText}; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; transition: gap 0.3s;" onmouseover="this.style.gap='0.7rem';" onmouseout="this.style.gap='0.4rem';">Contact <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i></a>
                    </div>
                `;
                
                // Create flip card structure
                card.innerHTML = `
                    <div class="flip-card-inner" style="
                        position: relative;
                        width: 100%;
                        height: 100%;
                        transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
                        transform-style: preserve-3d;
                    ">
                        <div class="flip-card-front" style="
                            position: absolute;
                            width: 100%;
                            height: 100%;
                            backface-visibility: hidden;
                            -webkit-backface-visibility: hidden;
                            border-radius: 20px;
                            overflow: hidden;
                            background: white; padding: 2rem; text-align: center; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 16px rgba(0,0,0,0.06);
                        ">
                            ${originalContent}
                        </div>
                        <div class="flip-card-back" style="
                            position: absolute;
                            width: 100%;
                            height: 100%;
                            backface-visibility: hidden;
                            -webkit-backface-visibility: hidden;
                            transform: rotateY(180deg) translateZ(1px); /* Added translateZ for iOS */
                            background: white;
                            border-radius: 20px;
                            padding: 2rem 1.5rem 1.75rem 1.5rem;
                            display: flex;
                            flex-direction: column;
                            justify-content: space-between;
                            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
                            border: 1px solid rgba(0,0,0,0.05);
                        ">
                            <div style="text-align: center;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #e0f2fe, #bfdbfe); display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; border: 2px solid #dbeafe;">
                                    <i class="fas fa-user" style="font-size: 1.5rem; color: #1e3a5f;"></i>
                                </div>
                                <h4 style="font-size: 0.95rem; margin-bottom: 0.25rem; font-weight: 700; color: #1e3a5f; line-height: 1.2;">${name || 'Official'}</h4>
                                <p style="font-size: 0.75rem; color: #64748b; font-weight: 500; line-height: 1.2; margin-bottom: 0;">${role || 'Staff'}</p>
                            </div>
                            
                            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 1rem 0;">
                                <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.7rem; padding: 0.65rem 0.8rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-envelope" style="font-size: 0.85rem; color: #1e3a5f; flex-shrink: 0; width: 18px; text-align: center;"></i>
                                    <span style="color: #475569; font-size: 0.72rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; line-height: 1.2;">${email || 'Not Available'}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.7rem; padding: 0.65rem 0.8rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-phone" style="font-size: 0.85rem; color: #1e3a5f; flex-shrink: 0; width: 18px; text-align: center;"></i>
                                    <span style="color: #475569; font-size: 0.72rem; line-height: 1.2;">${contact || 'Not Available'}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.6rem; padding: 0.65rem 0.8rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <i class="fas fa-map-marker-alt" style="font-size: 0.85rem; color: #1e3a5f; flex-shrink: 0; width: 18px; text-align: center;"></i>
                                    <span style="color: #475569; font-size: 0.72rem; line-height: 1.3;">Brgy. Lumbangan, Nasugbu, Batangas</span>
                                </div>
                            </div>
                            
                            <button onclick="event.stopPropagation(); alert('Message feature coming soon!');" style="
                                width: 100%;
                                padding: 0.8rem;
                                border: none;
                                border-radius: 8px;
                                background: linear-gradient(135deg, #1e3a5f, #2c5282);
                                color: white;
                                font-weight: 600;
                                font-size: 0.8rem;
                                cursor: pointer;
                                transition: all 0.3s;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 0.5rem;
                                box-shadow: 0 4px 12px rgba(30,58,95,0.25);
                            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(30,58,95,0.35)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(30,58,95,0.25)';">
                                <i class="fas fa-paper-plane" style="font-size: 0.72rem;"></i>
                                Send Message
                            </button>
                        </div>
                    </div>
                `;
                
                // Add flip on hover - only for center card
                const inner = card.querySelector('.flip-card-inner');
                const front = card.querySelector('.flip-card-front');
                
                card.addEventListener('mouseenter', function() {
                    // Only flip if this is the center card
                    if (card.dataset.index == currentDirectorIndex) {
                        inner.style.transform = 'rotateY(180deg)';
                        front.style.transform = 'translateY(-12px)';
                        front.style.boxShadow = '0 24px 48px rgba(0,0,0,0.12)';
                        front.style.borderColor = 'rgba(30, 58, 95, 0.15)';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    inner.style.transform = 'rotateY(0deg)';
                    front.style.transform = 'translateY(0)';
                    front.style.boxShadow = '0 4px 16px rgba(0,0,0,0.06)';
                    front.style.borderColor = 'rgba(0,0,0,0.05)';
                });
                
                // Store card index for checking
                card.dataset.index = index;
            });
            
            // Initialize carousel on page load
            setTimeout(() => {
                updateDirectorCarousel();
            }, 100);
        });

        // ============================================
        // MOBILE ENHANCEMENTS AND OPTIMIZATIONS
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Detect touch device
            const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
            if (isTouchDevice) {
                document.body.classList.add('touch-device');
            }

            // Mobile navbar auto-close
            const navbarCollapse = document.getElementById('userNavbar');
            const navbarToggler = document.querySelector('.navbar-toggler');
            
            if (navbarCollapse && navbarToggler) {
                // Close navbar when clicking outside
                document.addEventListener('click', function(event) {
                    const isClickInside = navbarCollapse.contains(event.target) || 
                                        navbarToggler.contains(event.target);
                    
                    if (!isClickInside && navbarCollapse.classList.contains('show')) {
                        navbarToggler.click();
                    }
                });

                // Close navbar on link click (mobile)
                if (window.innerWidth <= 991) {
                    const navLinks = document.querySelectorAll('.nav-link');
                    navLinks.forEach(link => {
                        link.addEventListener('click', function() {
                            if (navbarCollapse.classList.contains('show')) {
                                navbarToggler.click();
                            }
                        });
                    });
                }
            }

            // Modal mobile optimizations
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    // Prevent body scroll on mobile
                    if (window.innerWidth <= 768) {
                        document.body.style.overflow = 'hidden';
                        document.body.style.position = 'fixed';
                        document.body.style.width = '100%';
                    }
                });

                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.style.overflow = '';
                    document.body.style.position = '';
                    document.body.style.width = '';
                });
            });

            // Touch feedback for interactive elements
            if (isTouchDevice) {
                const touchElements = document.querySelectorAll('.message-item, .navbar-icon-btn, .announcement-card, .stat-card');
                
                touchElements.forEach(element => {
                    element.addEventListener('touchstart', function() {
                        this.style.opacity = '0.7';
                    });
                    
                    element.addEventListener('touchend', function() {
                        setTimeout(() => {
                            this.style.opacity = '';
                        }, 150);
                    });
                });
            }

            // Smooth scroll for anchor links (mobile optimized)
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href !== '#' && document.querySelector(href)) {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        const offset = window.innerWidth <= 768 ? 70 : 100;
                        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });

                        // Close mobile menu if open
                        if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                            navbarToggler.click();
                        }
                    }
                });
            });

            // Handle orientation change
            window.addEventListener('orientationchange', function() {
                setTimeout(() => {
                    const openModal = document.querySelector('.modal.show');
                    if (openModal) {
                        const modalBody = openModal.querySelector('.modal-body');
                        if (modalBody && window.innerHeight < 500) {
                            modalBody.style.maxHeight = (window.innerHeight - 150) + 'px';
                        } else if (modalBody) {
                            modalBody.style.maxHeight = '';
                        }
                    }
                }, 200);
            });

            // Optimize modals for very small screens
            if (window.innerWidth <= 375) {
                const modalTitles = document.querySelectorAll('.modal-title');
                modalTitles.forEach(title => {
                    if (title.textContent.length > 15) {
                        title.style.fontSize = '0.9rem';
                    }
                });
            }

            // Add swipe gesture for modals on mobile
            if (isTouchDevice && window.innerWidth <= 768) {
                modals.forEach(modal => {
                    let touchStartY = 0;
                    let touchEndY = 0;
                    const modalContent = modal.querySelector('.modal-content');
                    
                    if (modalContent) {
                        modalContent.addEventListener('touchstart', function(e) {
                            touchStartY = e.changedTouches[0].screenY;
                        }, { passive: true });

                        modalContent.addEventListener('touchend', function(e) {
                            touchEndY = e.changedTouches[0].screenY;
                            const swipeDistance = touchStartY - touchEndY;
                            
                            // Swipe down to close (>100px)
                            if (swipeDistance < -100) {
                                const closeButton = modal.querySelector('.btn-close');
                                if (closeButton) {
                                    closeButton.click();
                                }
                            }
                        }, { passive: true });
                    }
                });
            }

            // Improve touch icon buttons feedback
            const iconButtons = document.querySelectorAll('.navbar-icon-btn, .user-profile-btn');
            iconButtons.forEach(btn => {
                btn.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.9)';
                    this.style.transition = 'transform 0.1s';
                });
                
                btn.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
            });

            // Prevent zoom on double tap for iOS
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(e) {
                const now = Date.now();
                if (now - lastTouchEnd <= 300) {
                    e.preventDefault();
                }
                lastTouchEnd = now;
            }, false);

            // Log mobile optimization status
            if (window.innerWidth <= 768) {
                console.log('Mobile optimizations active');
                console.log('Touch device:', isTouchDevice);
                console.log('Screen width:', window.innerWidth + 'px');
            }
        });

        // ========== BATANGAS NEWS CAROUSEL ==========
        class DashboardNewsFetcher {
            constructor() {
                this.newsCarousel = document.getElementById('newsCarousel');
                this.newsCarouselTrack = document.getElementById('newsCarouselTrack');
                this.newsLoading = document.getElementById('dashboardNewsLoading');
                this.newsError = document.getElementById('dashboardNewsError');
                this.newsIndicators = document.getElementById('newsIndicators');
                this.refreshBtn = document.getElementById('dashboardRefreshNews');
                this.currentSlide = 0;
                this.newsArticles = [];
                this.itemsPerSlide = 3; // Show 3 cards at a time
                
                this.init();
            }
            
            init() {
                this.fetchNews();
                
                if (this.refreshBtn) {
                    this.refreshBtn.addEventListener('click', () => {
                        this.fetchNews();
                        const icon = this.refreshBtn.querySelector('i');
                        if (icon) {
                            icon.style.animation = 'spin 1s linear';
                            setTimeout(() => {
                                icon.style.animation = '';
                            }, 1000);
                        }
                    });
                }

                // Handle window resize
                window.addEventListener('resize', () => {
                    this.updateItemsPerSlide();
                    this.goToSlide(this.currentSlide);
                });
            }

            updateItemsPerSlide() {
                if (window.innerWidth <= 768) {
                    this.itemsPerSlide = 1;
                } else if (window.innerWidth <= 992) {
                    this.itemsPerSlide = 2;
                } else {
                    this.itemsPerSlide = 3;
                }
            }
            
            showLoading() {
                if (this.newsLoading) this.newsLoading.style.display = 'flex';
                if (this.newsCarousel) this.newsCarousel.style.display = 'none';
                if (this.newsError) this.newsError.style.display = 'none';
                if (this.newsIndicators) this.newsIndicators.style.display = 'none';
            }

            showError() {
                if (this.newsLoading) this.newsLoading.style.display = 'none';
                if (this.newsCarousel) this.newsCarousel.style.display = 'none';
                if (this.newsError) this.newsError.style.display = 'flex';
                if (this.newsIndicators) this.newsIndicators.style.display = 'none';
            }

            showContent() {
                if (this.newsLoading) this.newsLoading.style.display = 'none';
                if (this.newsCarousel) this.newsCarousel.style.display = 'block';
                if (this.newsError) this.newsError.style.display = 'none';
                if (this.newsIndicators) this.newsIndicators.style.display = 'flex';
            }
            
            async fetchNews() {
                this.showLoading();
                
                try {
                    const response = await fetch('https://corsproxy.io/?url=' + encodeURIComponent('https://portal.batangas.gov.ph/news/'));
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const html = await response.text();
                    const news = this.extractNewsFromHTML(html);
                    this.newsArticles = news.slice(0, 9); // Limit to 9 articles
                    this.displayNews();
                } catch (error) {
                    console.error('Error fetching news:', error);
                    this.showError();
                }
            }
            
            extractNewsFromHTML(html) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newsItems = [];
                const seen = new Set();
                const articles = doc.querySelectorAll('article, .post');
                
                articles.forEach(article => {
                    const title = article.querySelector('h1, h2, h3')?.textContent.trim();
                    const date = article.querySelector('time, .date')?.textContent.trim();
                    const rawExcerpt = article.querySelector('.entry-content, .excerpt')?.textContent?.trim() || '';
                    const excerpt = rawExcerpt ? (rawExcerpt.slice(0, 120) + '...') : '';
                    const anchor = article.querySelector('a[href*="batangas.gov.ph"]');
                    const link = anchor?.href || article.querySelector('a')?.href || null;
                    let image = article.querySelector('img')?.getAttribute('src') || null;

                    if (image && image.startsWith('/')) {
                        const base = (new URL('https://portal.batangas.gov.ph')).origin;
                        image = base + image;
                    }

                    const dedupeKey = link || title;
                    if (!dedupeKey || seen.has(dedupeKey)) return;
                    seen.add(dedupeKey);

                    if (title && link) {
                        newsItems.push({ title, date, excerpt, link, image });
                    }
                });
                
                return newsItems;
            }
            
            displayNews() {
                if (!this.newsCarouselTrack) return;
                
                this.newsCarouselTrack.innerHTML = '';
                this.updateItemsPerSlide();
                
                this.newsArticles.forEach(article => {
                    const card = document.createElement('div');
                    card.style.cssText = `
                        flex: 0 0 calc(33.333% - 1rem);
                        margin: 0 0.5rem;
                        background: white;
                        border-radius: 16px;
                        overflow: hidden;
                        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
                        transition: all 0.3s;
                        display: flex;
                        flex-direction: column;
                    `;
                    
                    card.innerHTML = `
                        <div style="position: relative; overflow: hidden; height: 200px;">
                            <img src="${article.image || 'https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png'}" 
                                 alt="${article.title}"
                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                                 onerror="this.src='https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png'">
                        </div>
                        <div style="padding: 1.5rem; display: flex; flex-direction: column; flex: 1;">
                            ${article.date ? `
                                <div style="font-size: 0.8rem; color: #888; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="far fa-calendar-alt"></i> ${article.date}
                                </div>
                            ` : ''}
                            <h5 style="font-size: 1.1rem; font-weight: 700; color: #1e3a5f; margin-bottom: 0.75rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                ${article.title}
                            </h5>
                            <p style="font-size: 0.85rem; color: #64748b; line-height: 1.6; margin-bottom: 1rem; flex: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                ${article.excerpt}
                            </p>
                            <a href="${article.link}" target="_blank" 
                               style="color: var(--primary-blue); text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s;"
                               onmouseover="this.style.gap='0.75rem'"
                               onmouseout="this.style.gap='0.5rem'">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    `;
                    
                    card.onmouseover = function() {
                        this.style.transform = 'translateY(-5px)';
                        this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                        const img = this.querySelector('img');
                        if (img) img.style.transform = 'scale(1.05)';
                    };
                    
                    card.onmouseout = function() {
                        this.style.transform = 'translateY(0)';
                        this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.08)';
                        const img = this.querySelector('img');
                        if (img) img.style.transform = 'scale(1)';
                    };
                    
                    this.newsCarouselTrack.appendChild(card);
                });
                
                this.createIndicators();
                this.showContent();
                this.goToSlide(0);
            }

            createIndicators() {
                if (!this.newsIndicators) return;
                
                this.newsIndicators.innerHTML = '';
                const totalSlides = Math.ceil(this.newsArticles.length / this.itemsPerSlide);
                
                for (let i = 0; i < totalSlides; i++) {
                    const indicator = document.createElement('div');
                    indicator.style.cssText = `
                        width: ${i === 0 ? '30px' : '10px'};
                        height: 10px;
                        border-radius: 5px;
                        background: ${i === 0 ? 'var(--primary-blue)' : '#cbd5e1'};
                        cursor: pointer;
                        transition: all 0.3s;
                    `;
                    indicator.onclick = () => this.goToSlide(i);
                    this.newsIndicators.appendChild(indicator);
                }
            }

            goToSlide(index) {
                const totalSlides = Math.ceil(this.newsArticles.length / this.itemsPerSlide);
                this.currentSlide = Math.max(0, Math.min(index, totalSlides - 1));
                
                const slideWidth = 100 / this.itemsPerSlide;
                const offset = this.currentSlide * slideWidth * this.itemsPerSlide;
                this.newsCarouselTrack.style.transform = `translateX(-${offset}%)`;
                
                // Update indicators
                const indicators = this.newsIndicators?.children;
                if (indicators) {
                    Array.from(indicators).forEach((indicator, i) => {
                        indicator.style.width = i === this.currentSlide ? '30px' : '10px';
                        indicator.style.background = i === this.currentSlide ? 'var(--primary-blue)' : '#cbd5e1';
                    });
                }

                // Apply blur effect to non-active cards
                const allCards = this.newsCarouselTrack?.children;
                if (allCards) {
                    const startIndex = this.currentSlide * this.itemsPerSlide;
                    const endIndex = startIndex + this.itemsPerSlide;
                    
                    Array.from(allCards).forEach((card, i) => {
                        if (i >= startIndex && i < endIndex) {
                            // Active cards - no blur
                            card.style.filter = 'blur(0px)';
                            card.style.opacity = '1';
                            card.style.transition = 'all 0.5s ease';
                        } else {
                            // Inactive cards - apply blur
                            card.style.filter = 'blur(3px)';
                            card.style.opacity = '0.4';
                            card.style.transition = 'all 0.5s ease';
                        }
                    });
                }
            }

            nextSlide() {
                const totalSlides = Math.ceil(this.newsArticles.length / this.itemsPerSlide);
                if (this.currentSlide < totalSlides - 1) {
                    this.goToSlide(this.currentSlide + 1);
                } else {
                    this.goToSlide(0); // Loop back to start
                }
            }

            prevSlide() {
                if (this.currentSlide > 0) {
                    this.goToSlide(this.currentSlide - 1);
                } else {
                    // Loop to end
                    const totalSlides = Math.ceil(this.newsArticles.length / this.itemsPerSlide);
                    this.goToSlide(totalSlides - 1);
                }
            }
        }

        // Initialize news fetcher
        let dashboardNewsFetcher;
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('newsCarousel')) {
                dashboardNewsFetcher = new DashboardNewsFetcher();
            }
        });

        // Navigation functions for buttons
        function nextNewsSlide() {
            if (dashboardNewsFetcher) {
                dashboardNewsFetcher.nextSlide();
            }
        }

        function prevNewsSlide() {
            if (dashboardNewsFetcher) {
                dashboardNewsFetcher.prevSlide();
            }
        }

        // Add spin animation
        const newsStyle = document.createElement('style');
        newsStyle.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(newsStyle);

        // ========== SCROLL ENTRANCE ANIMATIONS ==========
        // Add CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            .scroll-animate {
                opacity: 0;
                transform: translateY(50px);
                transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            }

            .scroll-animate.animate-in {
                opacity: 1;
                transform: translateY(0);
            }

            .scroll-animate-left {
                opacity: 0;
                transform: translateX(-50px);
                transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            }

            .scroll-animate-left.animate-in {
                opacity: 1;
                transform: translateX(0);
            }

            .scroll-animate-right {
                opacity: 0;
                transform: translateX(50px);
                transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            }

            .scroll-animate-right.animate-in {
                opacity: 1;
                transform: translateX(0);
            }

            .scroll-animate-scale {
                opacity: 0;
                transform: scale(0.9);
                transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            }

            .scroll-animate-scale.animate-in {
                opacity: 1;
                transform: scale(1);
            }

            .scroll-animate-fade {
                opacity: 0;
                transition: opacity 1s ease-out;
            }

            .scroll-animate-fade.animate-in {
                opacity: 1;
            }

            /* Stagger animation delays */
            .scroll-animate.delay-1 { transition-delay: 0.1s; }
            .scroll-animate.delay-2 { transition-delay: 0.2s; }
            .scroll-animate.delay-3 { transition-delay: 0.3s; }
            .scroll-animate.delay-4 { transition-delay: 0.4s; }
            .scroll-animate.delay-5 { transition-delay: 0.5s; }
            .scroll-animate.delay-6 { transition-delay: 0.6s; }
        `;
        document.head.appendChild(style);

        // Function to check if element is in viewport
        function isInViewport(element, offset = 100) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) - offset &&
                rect.bottom >= 0
            );
        }

        // Function to add animation classes to elements
        function addScrollAnimations() {
            // Add animation class to sections
            const sections = document.querySelectorAll('section');
            sections.forEach((section, index) => {
                if (!section.classList.contains('welcome-banner')) {
                    section.classList.add('scroll-animate');
                }
            });

            // Add animation to announcement cards
            const announcementCards = document.querySelectorAll('#announcements .col-md-6');
            announcementCards.forEach((card, index) => {
                card.classList.add('scroll-animate');
                if (index % 2 === 0) {
                    card.classList.add('delay-' + (index + 1));
                } else {
                    card.classList.add('delay-' + (index + 1));
                }
            });

            // Add animation to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.classList.add('scroll-animate-scale');
                card.classList.add('delay-' + ((index % 4) + 1));
            });

            // Add animation to service cards
            const serviceCards = document.querySelectorAll('#services .col-md-4');
            serviceCards.forEach((card, index) => {
                card.classList.add('scroll-animate');
                card.classList.add('delay-' + ((index % 3) + 1));
            });

            // Add animation to directory section
            const directorySection = document.querySelector('#directory');
            if (directorySection) {
                directorySection.classList.add('scroll-animate-fade');
            }

            // Add animation to badges
            const badges = document.querySelectorAll('.badge-item');
            badges.forEach((badge, index) => {
                badge.classList.add('scroll-animate-scale');
                badge.classList.add('delay-' + ((index % 6) + 1));
            });

            // Add animation to resident cards
            const residentCards = document.querySelectorAll('.resident-card');
            residentCards.forEach((card, index) => {
                card.classList.add('scroll-animate');
                card.classList.add('delay-' + ((index % 3) + 1));
            });
        }

        // Function to animate elements on scroll
        function animateOnScroll() {
            const animatedElements = document.querySelectorAll('.scroll-animate, .scroll-animate-left, .scroll-animate-right, .scroll-animate-scale, .scroll-animate-fade');
            
            animatedElements.forEach(element => {
                if (isInViewport(element, 100)) {
                    element.classList.add('animate-in');
                }
            });
        }

        // Initialize scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            addScrollAnimations();
            
            // Check on page load
            setTimeout(() => {
                animateOnScroll();
            }, 100);
            
            // Check on scroll
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                if (scrollTimeout) {
                    clearTimeout(scrollTimeout);
                }
                
                scrollTimeout = setTimeout(() => {
                    animateOnScroll();
                }, 10);
            }, { passive: true });
            
            // Check on resize
            window.addEventListener('resize', animateOnScroll);
        });
    </script>

  <?php include dirname(__DIR__, 2) . '/components/ai_chatbot.php'; ?>

  <script src="<?php echo BASE_URL; ?>assets/js/ai_chatbot.js"></script>

</body>
</html>

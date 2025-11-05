<?php
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
    <link href="../../assets/css/Dashboard/dashboard.css" rel="stylesheet">
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
                                <a class="dropdown-item" href="#document-status">
                                    <i class="fas fa-file-alt"></i>
                                    Document Request Status
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#survey-status">
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

                <!-- Government Seals -->
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

                <!-- Notifications & Inbox Buttons -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Notifications Button -->
                    <button class="navbar-icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#notificationsModal" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </button>

                    <!-- Inbox Button -->
                    <button class="navbar-icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#inboxModal" title="Inbox">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">1</span>
                    </button>

                    <!-- Documents Button -->
                    <button class="navbar-icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#documentsModal" title="My Documents">
                        <i class="fas fa-file-alt"></i>
                    </button>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown" style="margin-left: 1.5rem; border-left: 1px solid rgba(0,0,0,0.1); padding-left: 1.5rem;">
                        <button class="user-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <div class="user-avatar"><i class="fas fa-user"></i></div>
                            <span>User</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#profile">
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

    <!-- Welcome Banner -->
    <section class="welcome-banner" id="dashboard">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="welcome-content">
                        <h1>Welcome back, User! 👋</h1>
                        <p>Here's what's happening with your barangay services today.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="user-info-card">
                        <div class="user-info-item">
                            <div class="user-info-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div>
                                <small style="color: #718096; font-weight: 600;">Resident ID</small>
                                <div class="fw-bold" style="color: var(--primary-blue); font-size: 1rem;">RES-2025-001</div>
                            </div>
                        </div>
                        <div class="user-info-item">
                            <div class="user-info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <small style="color: #718096; font-weight: 600;">Purok</small>
                                <div class="fw-bold" style="color: var(--primary-blue); font-size: 1rem;">Purok 1</div>
                            </div>
                        </div>
                        <div class="user-info-item" style="margin-bottom: 0;">
                            <div class="user-info-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <small style="color: #718096; font-weight: 600;">Status</small>
                                <div class="fw-bold text-success" style="font-size: 1rem;">Verified Resident</div>
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
    <div class="container" style="margin-top: -1rem;">
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card" tabindex="0">
                <div class="stat-main">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-number">3</div>
                    <div class="stat-label">Active Complaints</div>
                </div>
                <div class="stat-foot">
                    <span class="stat-badge badge-pending">2 Pending</span>
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
            <div class="stat-card" id="survey-card"
                 data-last-title="Barangay Facilities Survey"
                 data-last-date="2025-10-15"
                 data-next-title="Resident Satisfaction Survey"
                 data-next-date="2025-12-01">
                <div class="stat-main">
                    <div class="stat-icon">
                        <i class="fas fa-poll"></i>
                    </div>
                    <div class="stat-number" id="survey-number">0</div>
                    <div class="stat-label">Surveys</div>
                </div>
                <div class="stat-foot">
                    <!-- short status badge visible by default -->
                    <span id="survey-next-status-short" class="survey-badge upcoming">Upcoming</span>
                    <div class="stat-extra">
                        <div class="survey-details mt-3">
                        <div class="survey-last">
                            <small class="text-muted">Last survey</small>
                            <div class="fw-bold" id="survey-last-title">Barangay Facilities Survey</div>
                            <small id="survey-last-date" class="text-muted">Oct 15, 2025</small>
                        </div>

                        <div class="survey-next mt-3">
                            <small class="text-muted">Next survey</small>
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <div class="fw-bold" id="survey-next-title">Resident Satisfaction Survey</div>
                                    <small id="survey-next-date" class="text-muted">Dec 01, 2025</small>
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
                    <div class="stat-number">8</div>
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

    <!-- Announcements Section -->
    <section class="announcements-section" id="announcements">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">Latest Updates</div>
                <h2 class="section-title">Recent Announcements</h2>
                <p class="section-subtitle">Stay informed with the latest news, events, and advisories from Barangay Lumbangan</p>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="announcement-card">
                        <div class="announcement-img">
                            <i class="fas fa-broom"></i>
                        </div>
                        <div class="announcement-body">
                            <div class="announcement-date">
                                <i class="fas fa-calendar"></i>
                                November 5, 2025
                            </div>
                            <h5 class="announcement-title">Barangay Cleanup Drive</h5>
                            <p class="announcement-text">
                                Join us for a community-wide cleanup drive this Saturday. All residents are encouraged to participate. Meeting at Barangay Hall, 7:00 AM.
                            </p>
                            <a href="#" class="announcement-read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="announcement-card">
                        <div class="announcement-img">
                            <i class="fas fa-medkit"></i>
                        </div>
                        <div class="announcement-body">
                            <div class="announcement-date">
                                <i class="fas fa-calendar"></i>
                                November 3, 2025
                            </div>
                            <h5 class="announcement-title">Free Medical Mission</h5>
                            <p class="announcement-text">
                                Free medical consultation and medicines available for all residents on November 10th. Bring your valid ID and barangay clearance.
                            </p>
                            <a href="#" class="announcement-read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="announcement-card">
                        <div class="announcement-img">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="announcement-body">
                            <div class="announcement-date">
                                <i class="fas fa-calendar"></i>
                                November 1, 2025
                            </div>
                            <h5 class="announcement-title">Scheduled Power Interruption</h5>
                            <p class="announcement-text">
                                Please be advised of a scheduled power interruption on November 7th from 9:00 AM to 3:00 PM for maintenance work.
                            </p>
                            <a href="#" class="announcement-read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="#all-announcements" class="btn-custom btn-outline-custom">
                    View All Announcements
                </a>
            </div>
        </div>
    </section>

    <!-- Recent Activities Timeline -->
    <section class="recent-activities-section">
        <div class="container">
            <div class="section-header">
                <div class="section-badge">Your Journey</div>
                <h2 class="section-title">Recent Activities</h2>
                <p class="section-subtitle">Track your requests, complaints, and transactions</p>
            </div>
            <div class="timeline-container">
                <div class="timeline-item">
                    <div class="timeline-dot"><i class="fas fa-check-circle"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-date">October 28, 2025</div>
                        <div class="timeline-title">Document Request Approved</div>
                        <div class="timeline-description">Your request for Barangay Clearance has been approved. You can now download it from My Documents section.</div>
                        <span class="timeline-status status-approved">✓ Approved</span>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"><i class="fas fa-hourglass-half"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-date">October 25, 2025</div>
                        <div class="timeline-title">Complaint Submitted</div>
                        <div class="timeline-description">Your complaint about street lighting has been received and is being reviewed.</div>
                        <span class="timeline-status status-processing">⟳ Processing</span>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"><i class="fas fa-clipboard-list"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-date">October 20, 2025</div>
                        <div class="timeline-title">Survey Completed</div>
                        <div class="timeline-description">Thank you for completing the Barangay Facilities Survey. Your feedback is valuable to us.</div>
                        <span class="timeline-status status-approved">✓ Completed</span>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"><i class="fas fa-clock"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-date">October 15, 2025</div>
                        <div class="timeline-title">Document Request Pending</div>
                        <div class="timeline-description">Your request for Cedula copy is pending. You will be notified when it's ready for pickup.</div>
                        <span class="timeline-status status-pending">⏳ Pending</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievements/Badges -->
    <section class="achievements-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Your Achievements</h2>
                <p class="section-subtitle">Earn badges through civic engagement</p>
            </div>
            <div class="badges-grid">
                <div class="badge-item">
                    <div class="badge-icon">🏅</div>
                    <div class="badge-name">Good Citizen</div>
                    <div class="badge-description">Maintained clearance for 2 years</div>
                </div>
                <div class="badge-item">
                    <div class="badge-icon">🗳️</div>
                    <div class="badge-name">Active Voter</div>
                    <div class="badge-description">Participated in voting</div>
                </div>
                <div class="badge-item">
                    <div class="badge-icon">🤝</div>
                    <div class="badge-name">Community Helper</div>
                    <div class="badge-description">Joined 3 community events</div>
                </div>
                <div class="badge-item">
                    <div class="badge-icon">💬</div>
                    <div class="badge-name">Active Participant</div>
                    <div class="badge-description">Posted in community forum</div>
                </div>
                <div class="badge-item locked">
                    <div class="badge-icon">🌟</div>
                    <div class="badge-name">Volunteer</div>
                    <div class="badge-description">Help in 5 events</div>
                </div>
                <div class="badge-item locked">
                    <div class="badge-icon">👑</div>
                    <div class="badge-name">Community Legend</div>
                    <div class="badge-description">Complete profile 100%</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Resident Directory -->
    <section class="directory-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Resident Directory</h2>
                <p class="section-subtitle">Search and connect with other residents and officials</p>
            </div>
            <div class="directory-search">
                <input type="text" placeholder="Search by name, purok, or role...">
            </div>
            <div class="directory-grid">
                <div class="resident-card">
                    <div class="resident-avatar">J</div>
                    <div class="resident-name">Hon. Juan Dela Cruz</div>
                    <div class="resident-role">Punong Barangay</div>
                    <div class="resident-contact">(043) 415-XXXX</div>
                </div>
                <div class="resident-card">
                    <div class="resident-avatar">M</div>
                    <div class="resident-name">Maria Santos</div>
                    <div class="resident-role">Health Officer</div>
                    <div class="resident-contact">(043) 415-XXXX</div>
                </div>
                <div class="resident-card">
                    <div class="resident-avatar">P</div>
                    <div class="resident-name">Pedro Reyes</div>
                    <div class="resident-role">Kagawad</div>
                    <div class="resident-contact">Purok 2</div>
                </div>
                <div class="resident-card">
                    <div class="resident-avatar">A</div>
                    <div class="resident-name">Ana Mercado</div>
                    <div class="resident-role">Kagawad</div>
                    <div class="resident-contact">Purok 3</div>
                </div>
                <div class="resident-card">
                    <div class="resident-avatar">J</div>
                    <div class="resident-name">Jose Garcia</div>
                    <div class="resident-role">SK Chairman</div>
                    <div class="resident-contact">(043) 415-XXXX</div>
                </div>
                <div class="resident-card">
                    <div class="resident-avatar">G</div>
                    <div class="resident-name">Grace Flores</div>
                    <div class="resident-role">Barangay Secretary</div>
                    <div class="resident-contact">(043) 415-XXXX</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-bell"></i> Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="notification-item">
                        <div class="notification-icon notification-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Document Ready for Pickup</div>
                            <div class="notification-message">Your Barangay Clearance is ready. Collect it at the Barangay Hall.</div>
                            <div class="notification-time">2 hours ago</div>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon notification-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">Scheduled Maintenance Tomorrow</div>
                            <div class="notification-message">There will be network maintenance from 10 PM to 2 AM.</div>
                            <div class="notification-time">5 hours ago</div>
                        </div>
                    </div>
                    <div class="notification-item">
                        <div class="notification-icon notification-info">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">New Survey Available</div>
                            <div class="notification-message">Help us improve! A new community survey is now open.</div>
                            <div class="notification-time">1 day ago</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Modal -->
    <div class="modal fade" id="documentsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-alt"></i> My Documents</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div style="padding: 0.5rem 0;">
                        <div style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; gap: 1.5rem; align-items: center;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #c53030, #ff6b6b); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.3rem;">Barangay Clearance</div>
                                <div style="font-size: 0.85rem; color: #aaa;">Generated on Oct 28, 2025</div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button style="padding: 0.5rem 1rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: rgba(30, 58, 95, 0.1); color: #1e3a5f; transition: all 0.3s;" onmouseover="this.style.background='rgba(30, 58, 95, 0.2)';" onmouseout="this.style.background='rgba(30, 58, 95, 0.1)';">View</button>
                                <button style="padding: 0.5rem 1rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Download</button>
                            </div>
                        </div>
                        <div style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; gap: 1.5rem; align-items: center;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #c53030, #ff6b6b); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.3rem;">Cedula Copy</div>
                                <div style="font-size: 0.85rem; color: #aaa;">Generated on Oct 15, 2025</div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button style="padding: 0.5rem 1rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: rgba(30, 58, 95, 0.1); color: #1e3a5f; transition: all 0.3s;" onmouseover="this.style.background='rgba(30, 58, 95, 0.2)';" onmouseout="this.style.background='rgba(30, 58, 95, 0.1)';">View</button>
                                <button style="padding: 0.5rem 1rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Download</button>
                            </div>
                        </div>
                        <div style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; gap: 1.5rem; align-items: center;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #c53030, #ff6b6b); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.3rem;">Residency Certificate</div>
                                <div style="font-size: 0.85rem; color: #aaa;">Generated on Sep 22, 2025</div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button style="padding: 0.5rem 1rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: rgba(30, 58, 95, 0.1); color: #1e3a5f; transition: all 0.3s;" onmouseover="this.style.background='rgba(30, 58, 95, 0.2)';" onmouseout="this.style.background='rgba(30, 58, 95, 0.1)';">View</button>
                                <button style="padding: 0.5rem 1rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Download</button>
                            </div>
                        </div>
                        <div style="padding: 1.5rem; display: flex; gap: 1.5rem; align-items: center;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #c53030, #ff6b6b); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1e3a5f; margin-bottom: 0.3rem;">Income Certificate</div>
                                <div style="font-size: 0.85rem; color: #aaa;">Generated on Aug 10, 2025</div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button style="padding: 0.5rem 1rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: rgba(30, 58, 95, 0.1); color: #1e3a5f; transition: all 0.3s;" onmouseover="this.style.background='rgba(30, 58, 95, 0.2)';" onmouseout="this.style.background='rgba(30, 58, 95, 0.1)';">View</button>
                                <button style="padding: 0.5rem 1rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">Download</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inbox Modal -->
    <div class="modal fade" id="inboxModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-envelope"></i> Inbox</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
    <section class="contact-section">
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
                    © 2025 Barangay Lumbangan, Nasugbu, Batangas. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/Dashboard/dashboard.js"></script>
</body>
</html>

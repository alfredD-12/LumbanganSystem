<!-- User Dashboard Header Component -->
<!-- Self-contained with inline CSS - No external CSS dependencies! -->
<!-- Required: Bootstrap 5.3.2, Font Awesome 6.4.0 -->

<style>
    /* CSS Variables */
    :root {
        --primary-blue: #1e3a5f;
        --secondary-blue: #2c5282;
        --accent-red: #c53030;
        --light-bg: #f7fafc;
        --dark-text: #2d3748;
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Animations */
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Navbar Styles */
    .user-navbar {
        transition: all 0.3s ease;
        padding: 1.2rem 0;
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(20px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.08) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.5) !important;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .user-navbar.scrolled {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(25px) saturate(200%) !important;
        -webkit-backdrop-filter: blur(25px) saturate(200%) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
        padding: 0.8rem 0;
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--primary-blue);
        transition: var(--transition-smooth);
    }

    .navbar-brand:hover {
        transform: translateX(5px);
    }

    .logo-circle {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        box-shadow: 0 6px 20px rgba(30, 58, 95, 0.35);
        transition: var(--transition-smooth);
    }

    .logo-circle:hover {
        transform: rotate(360deg) scale(1.15);
        box-shadow: 0 8px 25px rgba(30, 58, 95, 0.4);
    }

    .nav-link {
        color: var(--dark-text) !important;
        font-weight: 500;
        margin: 0 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: var(--transition-smooth);
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-blue), var(--accent-red));
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateX(-50%);
        border-radius: 3px;
        box-shadow: 0 2px 8px rgba(197, 48, 48, 0.3);
    }

    .nav-link:hover {
        color: var(--primary-blue) !important;
        background: rgba(30, 58, 95, 0.05);
    }

    .nav-link:hover::before {
        width: 85%;
    }

    .nav-link.active {
        color: var(--primary-blue) !important;
        font-weight: 600;
        background: rgba(30, 58, 95, 0.08);
    }

    .nav-link.active::before {
        width: 85%;
    }

    /* Dropdown Menu */
    .dropdown-menu {
        border: none;
        border-radius: 18px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.2);
        padding: 0.75rem;
        margin-top: 0.8rem;
        backdrop-filter: blur(15px) saturate(160%);
        -webkit-backdrop-filter: blur(15px) saturate(160%);
        background: rgba(255,255,255,0.95);
        border: 1px solid rgba(255,255,255,0.5);
        animation: slideDown 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        min-width: 220px;
    }

    .dropdown-item {
        padding: 0.85rem 1.35rem;
        border-radius: 12px;
        margin-bottom: 0.35rem;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
        font-weight: 500;
        color: var(--dark-text);
    }

    .dropdown-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 4px;
        height: 0;
        background: linear-gradient(180deg, var(--primary-blue), var(--secondary-blue));
        transform: translateY(-50%);
        transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 2px;
    }

    .dropdown-item:hover {
        background: linear-gradient(135deg, rgba(30, 58, 95, 0.12), rgba(197, 48, 48, 0.08));
        color: var(--primary-blue);
        transform: translateX(8px);
        padding-left: 1.45rem;
    }

    .dropdown-item:hover::before {
        height: 24px;
    }

    .dropdown-item i {
        width: 20px;
        font-size: 1rem;
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .dropdown-divider {
        margin: 0.5rem 0;
        border-top: 1px solid rgba(0,0,0,0.08);
    }

    /* Icon Buttons */
    .navbar-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(30, 58, 95, 0.1);
        color: var(--primary-blue);
        border: none;
        cursor: pointer;
        transition: var(--transition-smooth);
        position: relative;
        margin: 0 0.5rem;
        font-size: 1.1rem;
    }

    .navbar-icon-btn:hover {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        transform: scale(1.1);
    }

    .navbar-icon-btn .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--accent-red);
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
    }

    /* User Profile Button */
    .user-profile-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0.6rem 1.2rem;
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        color: white;
        border-radius: 50px;
        border: none;
        font-weight: 600;
        transition: var(--transition-smooth);
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(30, 58, 95, 0.3);
        position: relative;
        overflow: hidden;
    }

    .user-profile-btn::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: all 0.6s;
        transform: translateX(-100%);
    }

    .user-profile-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(30, 58, 95, 0.4);
    }

    .user-profile-btn:hover::before {
        transform: translateX(100%);
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-blue);
        font-weight: 700;
        font-size: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: var(--transition-smooth);
    }

    .user-profile-btn:hover .user-avatar {
        transform: scale(1.15);
    }

    /* Mobile Responsive */
    @media (max-width: 991px) {
        .user-navbar .navbar-collapse {
            background: transparent;
            padding: 0.5rem 0.25rem;
            border-radius: 12px;
            margin-top: 0.25rem;
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity 220ms ease, transform 220ms ease;
        }

        .user-navbar .navbar-collapse.show {
            opacity: 1;
            transform: translateY(0);
        }

        .user-navbar .navbar-nav .nav-link {
            display: block;
            width: 100%;
            padding: 0.65rem 1rem;
            margin: 0.25rem 0;
            border-radius: 8px;
        }

        .user-navbar .nav-link::before { 
            display: none; 
        }

        .user-navbar .dropdown-menu {
            position: static;
            float: none;
            box-shadow: none;
            border-radius: 10px;
            margin-top: 0.25rem;
            background: transparent;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            padding: 0;
            transition: max-height 280ms ease, opacity 220ms ease, padding 220ms ease;
        }

        .user-navbar .nav-item.open .dropdown-menu,
        .user-navbar .dropdown.open .dropdown-menu {
            max-height: 420px;
            opacity: 1;
            padding: 0.5rem;
        }

        .user-navbar .user-profile-btn {
            width: 100%;
            justify-content: center;
            margin-top: 0.5rem;
        }

        .user-navbar .navbar-brand { 
            gap: 8px; 
        }
    }
</style>

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
                    <button class="user-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-label="User menu">
                        <div class="user-avatar"><i class="fas fa-user"></i></div>
                        <span class="d-none d-sm-inline">User</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
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
    </div>
</nav>

<!-- Navbar Scroll Effect Script -->
<script>
    // Add transparent blur effect on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.user-navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });
</script>

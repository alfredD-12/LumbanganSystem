<?php
// Header component: Loads namespaced CSS to prevent bleeding, but mirrors dashboard.css styling exactly

$scriptDir   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$projectBase = rtrim(preg_replace('#/app/.*$#', '', $scriptDir), '/');
$assetBase   = $projectBase . '/app/assets';

if (!defined('HEADER_ASSETS_LOADED')) {
  define('HEADER_ASSETS_LOADED', true);
  echo <<<HTML
<script>(function(d){
  var h=d.head||d.getElementsByTagName('head')[0];
  function addOnce(id, html){ if(d.getElementById(id)) return; var t=d.createElement('template'); t.innerHTML=html; h.appendChild(t.content.firstChild); }
  addOnce('header-fonts','<link id="header-fonts" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">');
  addOnce('header-fa','<link id="header-fa" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">');
  addOnce('header-css','<link id="header-css" rel="stylesheet" href="{$assetBase}/css/Dashboard/headerfooter-bdhf.css">');
  // Ensure Bootstrap 5 bundle is available
  if(!('bootstrap' in window) && !d.getElementById('header-bs')){
    var s=d.createElement('script'); s.id='header-bs'; s.defer=true; s.src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js'; h.appendChild(s);
  }
  addOnce('header-js','<script id="header-js" defer src="{$assetBase}/js/Dashboard/dashboard.js"><\\/script>');
})(document);</script>
HTML;
}
?>
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
<script>
// Initialize shrink-on-scroll for the header (runs once)
(function(){
  if (window.__headerScrollInit) return;
  window.__headerScrollInit = true;

  var nav = document.querySelector('.dashboard-header.user-navbar');
  if (!nav) return;

  function apply() {
    var scrolled = (window.scrollY || window.pageYOffset || 0) > 8;
    if (scrolled) {
      if (!nav.classList.contains('scrolled')) nav.classList.add('scrolled');
    } else {
      if (nav.classList.contains('scrolled')) nav.classList.remove('scrolled');
    }
  }

  // Run on load, scroll, and after small delay (for late paints)
  apply();
  window.addEventListener('scroll', apply, { passive: true });
  window.addEventListener('resize', apply, { passive: true });
  setTimeout(apply, 0);
})();
</script>
<!-- Inject Message Item Styles for Inbox Modal -->
<style>
    /* Force perfect rounded corners on modals */
    .modal-content {
        overflow: hidden !important;
    }
    .modal-header {
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
    }
    .modal-footer {
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    
    .message-item {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        display: flex;
        gap: 1.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
        background: white;
    }
    .message-item:hover {
        background: rgba(30, 58, 95, 0.03);
        transform: translateX(5px);
    }
    .message-item:last-child {
        border-bottom: none;
    }
    .message-avatar {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #1e3a5f, #2c5282);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        flex-shrink: 0;
        font-size: 1.2rem;
    }
    .message-content {
        flex: 1;
    }
    .message-sender {
        font-weight: 700;
        color: #1e3a5f;
        margin-bottom: 0.3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .message-text {
        color: #718096;
        font-size: 0.9rem;
        margin-bottom: 0.3rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .message-time {
        font-size: 0.8rem;
        color: #aaa;
    }
    .unread-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: #c53030;
        color: white;
        border-radius: 50%;
        font-size: 0.75rem;
        font-weight: 700;
    }
</style>

<script>
// Inject complete styled modals matching dashboard.php exactly
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Starting modal injection');
    
    // Check if Bootstrap is loaded
    function checkBootstrap() {
        if (typeof bootstrap !== 'undefined' || (window.bootstrap && window.bootstrap.Modal)) {
            console.log('Bootstrap detected, injecting modals');
            injectAllModals();
        } else {
            console.log('Bootstrap not yet loaded, waiting...');
            setTimeout(checkBootstrap, 100);
        }
    }
    
    function injectAllModals() {
        function injectModal(id, htmlContent){
            if(document.getElementById(id)) {
                console.log('Modal already exists:', id);
                return;
            }
            var wrapper = document.createElement('div');
            wrapper.innerHTML = htmlContent.trim();
            var modal = wrapper.firstChild;
            document.body.appendChild(modal);
            console.log('Successfully injected modal:', id);
        }

    // Notifications Modal - Exact copy from dashboard.php
    var notificationsModalHTML = `
        <div class="modal fade" id="notificationsModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.12); font-family: 'Poppins', sans-serif;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; border: none; padding: 1rem 1.5rem;">
                        <h5 class="modal-title" style="font-weight: 600; font-size: 1rem; display: flex; align-items: center; gap: 0.6rem; font-family: 'Poppins', sans-serif;">
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
                                <div style="flex: 1; font-family: 'Poppins', sans-serif;">
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
                                <div style="flex: 1; font-family: 'Poppins', sans-serif;">
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
                                <div style="flex: 1; font-family: 'Poppins', sans-serif;">
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
        </div>`;
    injectModal('notificationsModal', notificationsModalHTML);

    // Documents Modal - Exact copy from dashboard.php
    var documentsModalHTML = `
        <div class="modal fade" id="documentsModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.12); font-family: 'Poppins', sans-serif;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; border: none; padding: 1rem 1.5rem;">
                        <h5 class="modal-title" style="font-weight: 600; font-size: 1rem; display: flex; align-items: center; gap: 0.6rem; font-family: 'Poppins', sans-serif;">
                            <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            My Documents
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size: 0.8rem;"></button>
                    </div>
                    <div class="modal-body" style="padding: 1.25rem 1.5rem; background: #f8fafc;">
                        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                            <div style="background: white; border-radius: 12px; padding: 1.2rem; display: flex; gap: 1rem; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s; font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
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
                                    <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: 2px solid #1e3a5f; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: transparent; color: #1e3a5f; transition: all 0.3s; font-family: 'Poppins', sans-serif;" onmouseover="this.style.background='#1e3a5f'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#1e3a5f';"><i class="fas fa-eye"></i> View</button>
                                    <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: none; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s; box-shadow: 0 3px 10px rgba(30,58,95,0.2); font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(30,58,95,0.2)';"><i class="fas fa-download"></i> Download</button>
                                </div>
                            </div>
                            <div style="background: white; border-radius: 12px; padding: 1.2rem; display: flex; gap: 1rem; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s; font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
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
                                    <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: 2px solid #1e3a5f; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: transparent; color: #1e3a5f; transition: all 0.3s; font-family: 'Poppins', sans-serif;" onmouseover="this.style.background='#1e3a5f'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#1e3a5f';"><i class="fas fa-eye"></i> View</button>
                                    <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: none; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s; box-shadow: 0 3px 10px rgba(30,58,95,0.2); font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(30,58,95,0.2)';"><i class="fas fa-download"></i> Download</button>
                                </div>
                            </div>
                            <div style="background: white; border-radius: 12px; padding: 1.2rem; display: flex; gap: 1rem; align-items: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04); transition: all 0.3s; font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.04)';">
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
                                    <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: 2px solid #1e3a5f; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: transparent; color: #1e3a5f; transition: all 0.3s; font-family: 'Poppins', sans-serif;" onmouseover="this.style.background='#1e3a5f'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#1e3a5f';"><i class="fas fa-eye"></i> View</button>
                                    <button style="padding: 0.5rem 0.9rem; border-radius: 8px; border: none; font-size: 0.75rem; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white; transition: all 0.3s; box-shadow: 0 3px 10px rgba(30,58,95,0.2); font-family: 'Poppins', sans-serif;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(30, 58, 95, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 10px rgba(30,58,95,0.2)';"><i class="fas fa-download"></i> Download</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    injectModal('documentsModal', documentsModalHTML);

    // Inbox Modal - Exact copy from dashboard.php
    var inboxModalHTML = `
        <div class="modal fade" id="inboxModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.12); font-family: 'Poppins', sans-serif;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: white; border: none; padding: 1rem 1.5rem;">
                        <h5 class="modal-title" style="font-weight: 600; font-size: 1rem; display: flex; align-items: center; gap: 0.6rem; font-family: 'Poppins', sans-serif;">
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
        </div>`;
    injectModal('inboxModal', inboxModalHTML);

    // User Profile Modal - Exact copy from dashboard.php with CSS from dashboard.css
    var userProfileModalHTML = `
        <div class="modal fade" id="userProfileModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); font-family: 'Poppins', sans-serif;">
                    <!-- Modal Header -->
                    <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                        <h5 class="modal-title" style="color: #1e3a5f; font-weight: 600; font-size: 1.1rem; font-family: 'Poppins', sans-serif;"><i class="fas fa-id-card"></i> Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body" style="padding: 2rem;">
                        <div style="display: grid; grid-template-columns: 150px 1fr; gap: 2rem; align-items: center;">
                            <!-- Left: Avatar -->
                            <div style="text-align: center;">
                                <div style="width: 120px; height: 120px; border-radius: 12px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i class="fas fa-user" style="font-size: 2.5rem; color: #1e3a5f;"></i>
                                </div>
                            </div>

                            <!-- Right: Profile Information -->
                            <div>
                                <div class="profile-info-item" style="padding: 1rem; background: #f7fafc; border-radius: 8px; border-left: 4px solid #2c5282; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); margin-bottom: 1rem; cursor: pointer;" onmouseover="this.style.background='#edf2f7'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='#f7fafc'; this.style.transform='translateX(0)';">
                                    <label style="color: #718096; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem; font-family: 'Poppins', sans-serif;">Full Name</label>
                                    <p id="profileName" style="color: #2d3748; font-size: 1rem; margin: 0; font-family: 'Poppins', sans-serif;">Juan Dela Cruz</p>
                                </div>

                                <div class="profile-info-item" style="padding: 1rem; background: #f7fafc; border-radius: 8px; border-left: 4px solid #2c5282; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); margin-bottom: 1rem; cursor: pointer;" onmouseover="this.style.background='#edf2f7'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='#f7fafc'; this.style.transform='translateX(0)';">
                                    <label style="color: #718096; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem; font-family: 'Poppins', sans-serif;">Email Address</label>
                                    <p id="profileEmail" style="color: #2d3748; font-size: 1rem; margin: 0; font-family: 'Poppins', sans-serif;">juan.delacruz@email.com</p>
                                </div>

                                <div class="profile-info-item" style="padding: 1rem; background: #f7fafc; border-radius: 8px; border-left: 4px solid #2c5282; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;" onmouseover="this.style.background='#edf2f7'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='#f7fafc'; this.style.transform='translateX(0)';">
                                    <label style="color: #718096; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem; font-family: 'Poppins', sans-serif;">Contact Number</label>
                                    <p id="profileContact" style="color: #2d3748; font-size: 1rem; margin: 0; font-family: 'Poppins', sans-serif;">+63 9XX-XXX-XXXX</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 2rem; background: white; border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-sm" style="background: white; border: 1px solid #ddd; color: #666; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500; font-family: 'Poppins', sans-serif;" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-sm" style="background: #1e3a5f; border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500; font-family: 'Poppins', sans-serif;" data-bs-toggle="modal" data-bs-target="#editProfileModal" data-bs-dismiss="modal">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    injectModal('userProfileModal', userProfileModalHTML);
    
    console.log('All modals injected successfully');
    
    // Initialize dropdowns with a slight delay to ensure DOM is ready
    setTimeout(function() {
        initializeDropdowns();
        addManualDropdownListeners();
    }, 200);
    }
    
    // Initialize Bootstrap dropdowns
    function initializeDropdowns() {
        console.log('Initializing dropdowns...');
        
        // Get all dropdown toggles
        var dropdownElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        console.log('Found dropdown elements:', dropdownElements.length);
        
        dropdownElements.forEach(function(element) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                // Check if dropdown is already initialized
                var existingDropdown = bootstrap.Dropdown.getInstance(element);
                if (!existingDropdown) {
                    new bootstrap.Dropdown(element);
                    console.log('Dropdown initialized for:', element);
                } else {
                    console.log('Dropdown already initialized for:', element);
                }
            } else {
                console.error('Bootstrap.Dropdown not available');
            }
        });
    }
    
    // Add manual click listeners as fallback
    function addManualDropdownListeners() {
        console.log('Adding manual dropdown listeners...');
        var userBtn = document.querySelector('.user-profile-btn');
        if (userBtn) {
            console.log('User button found, adding click listener');
            userBtn.addEventListener('click', function(e) {
                console.log('User button clicked');
                var dropdownMenu = this.nextElementSibling;
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                    console.log('Toggling dropdown menu');
                    dropdownMenu.classList.toggle('show');
                    this.setAttribute('aria-expanded', dropdownMenu.classList.contains('show'));
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userBtn.contains(e.target)) {
                    var dropdownMenu = userBtn.nextElementSibling;
                    if (dropdownMenu && dropdownMenu.classList.contains('show')) {
                        dropdownMenu.classList.remove('show');
                        userBtn.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        } else {
            console.error('User button not found');
        }
    }
    
    // Start checking for Bootstrap
    checkBootstrap();
});
</script>
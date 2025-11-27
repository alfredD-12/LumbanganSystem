// Sidebar Toggle Functionality
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sidebarToggle = document.getElementById('sidebarToggle');
const mobileMenuToggle = document.getElementById('mobileMenuToggle');

        // Desktop toggle (collapse/expand)
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(30, 58, 95, 0.3);
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
            
            // Toggle classes immediately
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
            
            // Update button title
            this.title = isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar';
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Mobile toggle (show/hide)
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            
            // Always remove collapsed class on mobile to show full content
            if (window.innerWidth <= 991) {
                sidebar.classList.remove('collapsed');
            }
            
            const icon = this.querySelector('i');
            
            if (sidebar.classList.contains('show')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 991) {
                if (!sidebar.contains(event.target) && 
                    !mobileMenuToggle.contains(event.target) && 
                    sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    const icon = mobileMenuToggle.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        });

        // Load saved sidebar state
        window.addEventListener('DOMContentLoaded', function() {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
            }
        });

        // Mobile Top Bar Scroll Effect
        const topBar = document.querySelector('.top-bar');
        const contentSection = document.querySelector('.content-section');
        let lastScroll = 0;

        function handleScroll() {
            if (window.innerWidth <= 991) {
                // Use window.pageYOffset or window.scrollY for the scroll position
                const currentScroll = window.pageYOffset || window.scrollY || document.documentElement.scrollTop;
                
                if (currentScroll > 50) {
                    topBar.classList.add('scrolled');
                } else {
                    topBar.classList.remove('scrolled');
                }
                
                lastScroll = currentScroll;
            } else {
                // Remove scrolled class on desktop
                topBar.classList.remove('scrolled');
            }
        }

        // Listen to scroll on window
        window.addEventListener('scroll', handleScroll);

        // Chart tab switching with animation
        document.querySelectorAll('.chart-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const parent = this.parentElement;
                parent.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Add a subtle animation effect
                const container = this.closest('.chart-card');
                container.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    container.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Sidebar menu active state
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function(e) {
                // Prevent default navigation so we can update active state and handle mobile close first
                // but allow manual navigation below for normal links (non-hash, same-origin)
                e.preventDefault();

                document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // On mobile, close sidebar after selection
                if (window.innerWidth <= 991) {
                    sidebar.classList.remove('show');
                    const icon = mobileMenuToggle.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }

                // Determine href and navigate if it's a normal link (not an in-page hash or handled by SPA)
                try {
                    var href = this.getAttribute('href') || '';
                    // Skip hash anchors and javascript:void links
                    if (!href || href === '#' || href.startsWith('javascript:')) return;

                    // If it's an anchor intended for in-page navigation (starts with #) it was already handled above
                    if (href.startsWith('#')) return;

                    // Resolve relative URLs to absolute so same-origin check is reliable
                    var resolved = new URL(href, window.location.href).href;

                    // Only perform full navigation for same-origin URLs
                    if (resolved.startsWith(window.location.origin)) {
                        // Small defer to allow UI state updates/animations to run
                        setTimeout(function() { window.location.href = resolved; }, 30);
                    }
                } catch (err) {
                    console.error('Sidebar navigation error', err);
                }
            });
        });

        // Animate stat cards on load
        window.addEventListener('load', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add responsive behavior
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Handle scroll effect
                handleScroll();
                
                if (window.innerWidth > 991) {
                    // Desktop view
                    sidebar.classList.remove('show');
                    const icon = mobileMenuToggle.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                    
                    // Restore sidebar state from localStorage or use default
                    const savedState = localStorage.getItem('sidebarCollapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('sidebar-collapsed');
                    } else {
                        sidebar.classList.remove('collapsed');
                        mainContent.classList.remove('sidebar-collapsed');
                    }
                } else {
                    // Mobile view - reset to default
                    sidebar.classList.remove('show');
                    // Don't add collapsed class on mobile
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('sidebar-collapsed');
                }
            }, 250);
        });

        // Interactive line chart points
        document.querySelectorAll('.chart-point').forEach((point, index) => {
            const values = ['215', '248', '189', '267', '231', '298', '254'];
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            point.addEventListener('mouseenter', function() {
                // Show value label
                const valueLabel = this.nextElementSibling;
                if (valueLabel && valueLabel.classList.contains('chart-value-label')) {
                    valueLabel.style.opacity = '1';
                }
                
                // Create tooltip
                const tooltip = document.createElement('div');
                tooltip.className = 'chart-tooltip';
                tooltip.style.cssText = `
                    position: fixed;
                    background: rgba(30, 58, 95, 0.95);
                    color: white;
                    padding: 0.75rem 1rem;
                    border-radius: 8px;
                    font-size: 0.85rem;
                    font-weight: 600;
                    z-index: 10000;
                    pointer-events: none;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                    backdrop-filter: blur(10px);
                `;
                tooltip.innerHTML = `
                    <div style="margin-bottom: 0.25rem; color: rgba(255,255,255,0.8); font-size: 0.75rem;">${days[index]}</div>
                    <div style="font-size: 1.1rem;">${values[index]} Registrations</div>
                `;
                document.body.appendChild(tooltip);
                
                // Position tooltip
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                
                this._tooltip = tooltip;
            });
            
            point.addEventListener('mouseleave', function() {
                // Hide value label
                const valueLabel = this.nextElementSibling;
                if (valueLabel && valueLabel.classList.contains('chart-value-label')) {
                    valueLabel.style.opacity = '0';
                }
                
                // Remove tooltip
                if (this._tooltip) {
                    this._tooltip.remove();
                    this._tooltip = null;
                }
            });
        });

        // Notification and Messages functions
        function toggleNotifications() {
            alert('Notifications:\n\n' +
                  '• New complaint filed (5 min ago)\n' +
                  '• Document request approved (15 min ago)\n' +
                  '• New resident registration (1 hour ago)\n' +
                  '• Meeting reminder: Barangay Council (2 hours ago)\n' +
                  '• System update available (3 hours ago)');
        }

        function toggleMessages() {
            alert('Messages (12 unread):\n\n' +
                  '• Juan Dela Cruz: Document request follow-up\n' +
                  '• Maria Santos: Complaint inquiry\n' +
                  '• Barangay Captain: Meeting agenda\n' +
                  '• System Admin: Security update notice\n' +
                  '• Treasury Office: Budget approval request\n' +
                  '...and 7 more messages');
        }

        // Calendar navigation functions
        let currentMonth = 10; // November (0-indexed)
        let currentYear = 2025;

        function previousMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            updateCalendar();
        }

        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            updateCalendar();
        }

        function updateCalendar() {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                          'July', 'August', 'September', 'October', 'November', 'December'];
            
            const monthYearElement = document.querySelector('.calendar-month-year');
            if (monthYearElement) {
                monthYearElement.textContent = months[currentMonth] + ' ' + currentYear;
            }

            // Note: Full calendar rendering would require more complex logic
            // This is a simplified version for demonstration
            alert('Calendar updated to: ' + months[currentMonth] + ' ' + currentYear + '\n\n' +
                  'In a full implementation, this would:\n' +
                  '• Render the correct number of days for the month\n' +
                  '• Highlight the current day if viewing current month\n' +
                  '• Show events on their respective dates\n' +
                  '• Update the events list to show upcoming events');
}

// Admin Profile Management
function saveAdminProfileChanges() {
    var form = document.getElementById('editAdminProfileForm');
    if (!form) return alert('Profile form not found');

    var fd = new FormData(form);
    // include official_id if present in window.officialProfile
    if (window.officialProfile && window.officialProfile.id) fd.append('official_id', window.officialProfile.id);

    var saveBtn = form.querySelector('button[type="button"], .btn-save');
    if (saveBtn) saveBtn.disabled = true;

    fetch('index.php?action=update_official_profile', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(res) { return res.json().catch(function(){ return { success: false, message: 'Invalid server response' }; }); })
        .then(function(json) {
            if (json && json.success) {
                // update local UI values
                var name = fd.get('full_name') || fd.get('name') || '';
                var email = fd.get('email') || '';
                var contact = fd.get('contact_no') || fd.get('contact') || '';

                var profileNameEl = document.getElementById('adminProfileName'); if (profileNameEl) profileNameEl.textContent = name || window.officialProfile.full_name;
                var profileEmailEl = document.getElementById('adminProfileEmail'); if (profileEmailEl) profileEmailEl.textContent = email;
                var profileContactEl = document.getElementById('adminProfileContact'); if (profileContactEl) profileContactEl.textContent = contact;
                var topName = document.querySelector('.admin-info .name'); if (topName) topName.textContent = name || window.officialProfile.full_name;

                // update window.officialProfile
                if (window.officialProfile) {
                    window.officialProfile.full_name = name || window.officialProfile.full_name;
                    window.officialProfile.email = email;
                    window.officialProfile.contact_no = contact;
                }

                // Close edit modal
                try {
                    var editModalElement = document.getElementById('editAdminProfileModal');
                    var editModal = bootstrap.Modal.getInstance(editModalElement) || new bootstrap.Modal(editModalElement);
                    if (editModal) editModal.hide();
                } catch (e) {}

                // show inline saved message above the Save Changes button
                showInlineSavedMessage(saveBtn, 'Saved Successfully');
            } else {
                var msg = (json && json.message) ? json.message : 'Unable to save profile';
                showInlineSavedMessage(saveBtn, msg, true);
            }
        })
        .catch(function(err){
            console.error('Save profile error', err);
            showInlineSavedMessage(saveBtn, 'Error saving profile', true);
        })
        .finally(function(){ if (saveBtn) saveBtn.disabled = false; });
}

function showInlineSavedMessage(saveBtn, message, isError) {
    // If we don't have a reference to the button, try to find the modal footer button
    var target = saveBtn || document.querySelector('#editAdminProfileModal .modal-footer button[onclick*="saveAdminProfileChanges"]');
    if (!target) {
        // fallback: use alert
        if (isError) alert(message); else console.log(message);
        return;
    }

    // ensure parent has relative positioning
    var parent = target.parentElement || target.closest('.modal-footer');
    if (parent && window.getComputedStyle(parent).position === 'static') parent.style.position = 'relative';

    // remove existing message if present
    var existing = parent.querySelector('.inline-save-msg');
    if (existing) existing.remove();

    var msg = document.createElement('div');
    msg.className = 'inline-save-msg';
    msg.style.position = 'absolute';
    msg.style.left = '50%';
    msg.style.transform = 'translateX(-50%)';
    msg.style.top = '-36px';
    msg.style.padding = '6px 10px';
    msg.style.borderRadius = '6px';
    msg.style.fontSize = '0.85rem';
    msg.style.fontWeight = '600';
    msg.style.display = 'flex';
    msg.style.alignItems = 'center';
    msg.style.gap = '8px';
    msg.style.zIndex = 2000;
    msg.style.opacity = '0';
    msg.style.transition = 'opacity 180ms ease, transform 180ms ease';
    if (isError) {
        msg.style.background = '#ef4444';
        msg.style.color = 'white';
    } else {
        msg.style.background = '#16a34a';
        msg.style.color = 'white';
    }

    msg.innerHTML = (isError ? '<i class="fas fa-exclamation-circle"></i>' : '<i class="fas fa-check-circle"></i>') + ' ' + (message || (isError ? 'Error' : 'Saved Successfully'));

    parent.appendChild(msg);

    // animate in
    requestAnimationFrame(function(){ msg.style.opacity = '1'; msg.style.transform = 'translateX(-50%) translateY(0)'; });

    // hide after 2.2s
    setTimeout(function(){
        msg.style.opacity = '0';
        msg.style.transform = 'translateX(-50%) translateY(-6px)';
        setTimeout(function(){ msg.remove(); }, 220);
    }, 2200);
}

// ============================================
// DARK MODE TOGGLE
// ============================================

const themeToggle = document.getElementById('themeToggle');
const themeIcon = themeToggle.querySelector('i');
const themeText = themeToggle.querySelector('span');

// Check for saved theme preference or default to light mode
const currentTheme = localStorage.getItem('theme') || 'light';
if (currentTheme === 'dark') {
    document.body.classList.add('dark-mode');
    themeIcon.classList.remove('fa-moon');
    themeIcon.classList.add('fa-sun');
    themeText.textContent = 'Light Mode';
}

// Theme toggle functionality
themeToggle.addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    
    if (document.body.classList.contains('dark-mode')) {
        // Switch to dark mode
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        themeText.textContent = 'Light Mode';
        localStorage.setItem('theme', 'dark');
        
        // Add ripple effect
        createRipple(this);
    } else {
        // Switch to light mode
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
        themeText.textContent = 'Dark Mode';
        localStorage.setItem('theme', 'light');
        
        // Add ripple effect
        createRipple(this);
    }
});

// Create ripple effect for theme toggle
function createRipple(button) {
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    
    ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.4);
        width: ${size}px;
        height: ${size}px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        animation: ripple-effect 0.6s ease-out;
        pointer-events: none;
    `;
    
    button.style.position = 'relative';
    button.style.overflow = 'hidden';
    button.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
}

// Add ripple animation to stylesheet
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple-effect {
        to {
            transform: translate(-50%, -50%) scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(rippleStyle);
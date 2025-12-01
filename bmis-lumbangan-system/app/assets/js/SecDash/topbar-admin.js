/* ============================================
   ADMIN TOP BAR - Reusable Component JS
   Handles dropdown, notifications, messages
   ============================================ */

// Initialize dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.querySelector('.admin-avatar.dropdown-toggle');
    const dropdownMenu = document.querySelector('.admin-profile .dropdown-menu');
    const adminProfile = document.querySelector('.admin-profile');
    
    if (dropdownToggle && dropdownMenu) {
        console.log('Admin profile dropdown initialized');
        
        // Manual click handler
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Admin avatar clicked');
            
            // Toggle show class
            const isShown = dropdownMenu.classList.contains('show');
            
            if (isShown) {
                dropdownMenu.classList.remove('show');
                adminProfile.classList.remove('show');
            } else {
                dropdownMenu.classList.add('show');
                adminProfile.classList.add('show');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!adminProfile.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                adminProfile.classList.remove('show');
            }
        });
    } else {
        console.warn('Dropdown elements not found:', {
            toggle: !!dropdownToggle,
            menu: !!dropdownMenu
        });
    }
});

// Notification toggle function - More robust
function toggleNotifications() {
    const modalElement = document.getElementById('notificationsModal');
    if (!modalElement) {
        console.error('Notifications modal not found');
        return;
    }
    
    try {
        const notificationsModal = new bootstrap.Modal(modalElement);
        notificationsModal.show();
    } catch (e) {
        console.error('Error opening notifications modal:', e);
    }
}

// Messages toggle function - More robust
function toggleMessages() {
    const modalElement = document.getElementById('messagesModal');
    if (!modalElement) {
        console.error('Messages modal not found');
        return;
    }
    
    try {
        const messagesModal = new bootstrap.Modal(modalElement);
        messagesModal.show();
    } catch (e) {
        console.error('Error opening messages modal:', e);
    }
}

// Export functions for external use
window.toggleNotifications = toggleNotifications;
window.toggleMessages = toggleMessages;

// Fallback: ensure clicking any element that targets #adminProfileModal opens it
document.addEventListener('click', function(e) {
    var trigger = e.target.closest && e.target.closest('a[data-bs-target="#adminProfileModal"], [data-bs-target="#adminProfileModal"]');
    if (!trigger) return;
    // Prevent default anchor behavior
    e.preventDefault();
    var modalEl = document.getElementById('adminProfileModal');
    if (!modalEl) {
        console.warn('adminProfileModal element not found in DOM');
        return;
    }

    try {
        // Prefer Bootstrap modal if available
        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            var m = new bootstrap.Modal(modalEl);
            m.show();
            return;
        }
    } catch (err) {
        console.debug('bootstrap modal show failed, falling back', err);
    }

    // Fallback: manually show modal (basic) — add backdrop and classes
    modalEl.classList.add('show');
    modalEl.style.display = 'block';
    document.body.classList.add('modal-open');
    // add backdrop
    if (!document.querySelector('.modal-backdrop')) {
        var bd = document.createElement('div');
        bd.className = 'modal-backdrop fade show';
        document.body.appendChild(bd);
    }
});

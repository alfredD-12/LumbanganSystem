/* ============================================
   ADMIN TOP BAR - Reusable Component JS
   Handles dropdown, notifications, messages
   ============================================ */

// Initialize dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.querySelector('.admin-avatar.dropdown-toggle');
    const dropdownMenu = document.querySelector('.admin-profile .dropdown-menu');
    
    if (dropdownToggle && dropdownMenu) {
        // Manual click handler
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle show class
            dropdownMenu.classList.toggle('show');
            dropdownToggle.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                dropdownToggle.classList.remove('show');
            }
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

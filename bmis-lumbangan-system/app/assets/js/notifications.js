/**
 * Barangay Lumbangan Notification System
 * Client-side JavaScript for real-time notifications
 * Works with both admin and resident headers
 */

(function() {
    'use strict';
    
    // Configuration
    const NOTIFICATION_CONFIG = {
        apiUrl: '/Lumbangan_BMIS/bmis-lumbangan-system/app/api/notifications.php',
        pollInterval: 30000, // Poll every 30 seconds
        maxNotifications: 10, // Show max 10 in dropdown
        autoMarkRead: true // Auto mark as read when clicking notification
    };
    
    let pollTimer = null;
    let currentUnreadCount = 0;
    
    // ========================================================
    // INITIALIZATION
    // ========================================================
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupNotifications);
        } else {
            setupNotifications();
        }
    }
    
    function setupNotifications() {
        // Find notification bell button
        const bellBtn = document.querySelector('[data-bs-target="#notificationsModal"]');
        if (!bellBtn) {
            console.warn('Notification bell button not found');
            return;
        }
        
        // Initial fetch
        fetchNotifications();
        
        // Start polling
        startPolling();
        
        // Setup event listeners
        setupEventListeners();
        
        console.log('Notification system initialized');
    }
    
    // ========================================================
    // POLLING
    // ========================================================
    function startPolling() {
        stopPolling(); // Clear any existing timer
        
        pollTimer = setInterval(() => {
            fetchNotifications();
        }, NOTIFICATION_CONFIG.pollInterval);
    }
    
    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }
    
    // ========================================================
    // FETCH NOTIFICATIONS
    // ========================================================
    async function fetchNotifications() {
        try {
            const response = await fetch(
                `${NOTIFICATION_CONFIG.apiUrl}?action=fetch&limit=${NOTIFICATION_CONFIG.maxNotifications}`,
                {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                updateBadge(data.unread_count);
                updateDropdown(data.notifications);
                currentUnreadCount = data.unread_count;
            } else {
                console.error('Failed to fetch notifications:', data.error);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }
    
    // ========================================================
    // UPDATE UI
    // ========================================================
    function updateBadge(count) {
        const badges = document.querySelectorAll('.navbar-icon-btn .badge, .action-icon-btn .badge-count');
        
        badges.forEach(badge => {
            const btn = badge.closest('[data-bs-target="#notificationsModal"]');
            if (btn) {
                badge.textContent = count;
                
                // Hide badge if no notifications
                if (count === 0) {
                    badge.style.display = 'none';
                    badge.classList.remove('pulse');
                } else {
                    badge.style.display = '';
                    
                    // Add pulse animation for new notifications
                    if (count > currentUnreadCount) {
                        badge.classList.add('pulse');
                        setTimeout(() => badge.classList.remove('pulse'), 2000);
                    }
                }
            }
        });
    }
    
    function updateDropdown(notifications) {
        const modal = document.getElementById('notificationsModal');
        if (!modal) return;
        
        const modalBody = modal.querySelector('.modal-body');
        const modalHeader = modal.querySelector('.modal-header');
        
        if (!modalBody) return;
        
        // Update header title
        if (modalHeader) {
            const unreadCount = notifications.filter(n => n.is_read === '0' || n.is_read === 0).length;
            const titleHtml = `
                <h5 class="modal-title" style="color: #1e293b; font-weight: 600; font-size: 1.1rem;">
                    <i class="fas fa-bell"></i> Notifications
                    ${unreadCount > 0 ? `<span style="background: #ef4444; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">${unreadCount} New</span>` : ''}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            `;
            modalHeader.innerHTML = titleHtml;
        }
        
        // Update body content
        if (notifications.length === 0) {
            modalBody.innerHTML = `
                <div style="padding: 3rem; text-align: center; background: white;">
                    <i class="fas fa-bell-slash" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                    <p style="color: #64748b; margin: 0;">No notifications yet</p>
                </div>
            `;
        } else {
            modalBody.innerHTML = `
                <div style="padding: 0; background: white;">
                    ${notifications.map(notif => renderNotification(notif)).join('')}
                </div>
            `;
            
            // Attach event listeners to notification items
            attachNotificationListeners();
        }
    }
    
    function renderNotification(notif) {
        const isUnread = notif.is_read === '0' || notif.is_read === 0;
        const icon = getNotificationIcon(notif.notification_type);
        const color = getNotificationColor(notif.notification_type);
        const timeAgo = formatTimeAgo(notif.created_at);
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 data-id="${notif.id}" 
                 data-link="${notif.link || '#'}"
                 style="padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; transition: background 0.2s; ${isUnread ? 'background: #f0f9ff !important;' : 'background: white !important;'} position: relative;"
                 onmouseover="this.style.background='#f8fafc'"
                 onmouseout="this.style.background='${isUnread ? '#f0f9ff' : 'white'}'">
                <div style="display: flex; gap: 1rem; align-items: start;">
                    <div class="notification-content" style="display: flex; gap: 1rem; align-items: start; flex: 1; cursor: pointer;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: ${color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="${icon}" style="color: white; font-size: 1rem;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: #1e293b !important; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="color: #1e293b !important;">${escapeHtml(notif.title)}</span>
                                ${isUnread ? '<span style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%;"></span>' : ''}
                            </div>
                            <div style="color: #64748b !important; font-size: 0.875rem; margin-bottom: 0.25rem; line-height: 1.4;">
                                ${escapeHtml(notif.message)}
                            </div>
                            <div style="color: #94a3b8 !important; font-size: 0.75rem;">
                                <i class="far fa-clock"></i> ${timeAgo}
                            </div>
                        </div>
                    </div>
                    <button class="notification-delete-btn" data-id="${notif.id}" 
                            style="background: transparent; border: none; color: #94a3b8; padding: 0.5rem; cursor: pointer; transition: color 0.2s; flex-shrink: 0;"
                            onmouseover="this.style.color='#ef4444'"
                            onmouseout="this.style.color='#94a3b8'"
                            title="Delete notification (only for you)">
                        <i class="fas fa-times" style="font-size: 1rem;"></i>
                    </button>
                </div>
            </div>
        `;
    }
    
    // ========================================================
    // EVENT LISTENERS
    // ========================================================
    function setupEventListeners() {
        const modal = document.getElementById('notificationsModal');
        if (!modal) return;
        
        // Mark all as read button (if exists in footer)
        const markAllBtn = modal.querySelector('[data-action="mark-all-read"]');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', markAllAsRead);
        }
        
        // Refresh when modal opens
        modal.addEventListener('shown.bs.modal', () => {
            fetchNotifications();
        });
    }
    
    function attachNotificationListeners() {
        // Handle notification content click (mark read and navigate)
        const contents = document.querySelectorAll('.notification-content');
        contents.forEach(content => {
            content.addEventListener('click', async function(e) {
                e.stopPropagation();
                const item = this.closest('.notification-item');
                const notifId = item.dataset.id;
                const link = item.dataset.link;
                
                // Mark as read
                if (NOTIFICATION_CONFIG.autoMarkRead) {
                    await markAsRead(notifId);
                }
                
                // Navigate to link
                if (link && link !== '#') {
                    window.location.href = link;
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('notificationsModal'));
                if (modal) {
                    modal.hide();
                }
            });
        });
        
        // Handle delete button click
        const deleteButtons = document.querySelectorAll('.notification-delete-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.stopPropagation();
                const notifId = this.dataset.id;
                await deleteNotification(notifId);
            });
        });
    }
    
    // ========================================================
    // ACTIONS
    // ========================================================
    async function markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('id', notificationId);
            
            const response = await fetch(NOTIFICATION_CONFIG.apiUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Refresh notifications
                fetchNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
    
    async function markAllAsRead() {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_all_read');
            
            const response = await fetch(NOTIFICATION_CONFIG.apiUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Refresh notifications
                fetchNotifications();
            }
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }
    
    async function deleteNotification(notificationId) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', notificationId);
            
            const response = await fetch(NOTIFICATION_CONFIG.apiUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Refresh notifications
                fetchNotifications();
            } else {
                console.error('Failed to delete notification:', data.error);
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    }
    
    // ========================================================
    // HELPERS
    // ========================================================
    function getNotificationIcon(type) {
        const icons = {
            'announcement': 'fas fa-bullhorn',
            'complaint': 'fas fa-exclamation-circle',
            'document_request': 'fas fa-file-alt',
            'default': 'fas fa-bell'
        };
        return icons[type] || icons.default;
    }
    
    function getNotificationColor(type) {
        const colors = {
            'announcement': '#3b82f6', // Blue
            'complaint': '#ef4444',    // Red
            'document_request': '#10b981', // Green
            'default': '#6366f1'       // Indigo
        };
        return colors[type] || colors.default;
    }
    
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' hr ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + ' day(s) ago';
        
        return date.toLocaleDateString();
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // ========================================================
    // VISIBILITY CHANGE (pause polling when tab inactive)
    // ========================================================
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
        } else {
            fetchNotifications(); // Refresh immediately
            startPolling();
        }
    });
    
    // Start initialization
    init();
    
    // Expose functions globally if needed
    window.NotificationSystem = {
        refresh: fetchNotifications,
        markAsRead: markAsRead,
        markAllAsRead: markAllAsRead,
        deleteNotification: deleteNotification
    };
})();

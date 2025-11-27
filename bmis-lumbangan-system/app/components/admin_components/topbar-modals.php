<?php
/**
 * Top Bar Modals Component
 * Contains Notifications and Messages modals for the admin topbar
 * 
 * REQUIREMENTS:
 * - Automatically loads notifications via JavaScript from notifications.php API
 * - Requires notifications.js to be loaded
 * - Requires notifications.css for styling
 * 
 * Usage:
 * <?php include 'path/to/components/topbar-modals.php'; ?>
 */
?>

<!-- Notifications Modal -->
<div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
            <!-- Modal Header -->
            <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                <h5 class="modal-title" id="notificationsModalLabel" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;">
                    <i class="fas fa-bell"></i> Notifications
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 0; background: #f8fafc;">
                <div class="notification-loading">
                    <i class="fas fa-spinner"></i>
                    <p>Loading notifications...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: white; border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-sm btn-notification-action" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-sm btn-notification-action primary" data-action="mark-all-read">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Messages/Inbox Modal -->
<div class="modal fade" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
            <!-- Modal Header -->
            <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                <h5 class="modal-title" id="messagesModalLabel" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;">
                    <i class="fas fa-envelope"></i> Messages
                    <span style="background: #ef4444; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">
                        12 Unread
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 0; background: #f8fafc;">
                <!-- Messages content would be rendered here (template only, no logic) -->
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: white; border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-sm" style="background: transparent; border: 1px solid #cbd5e1; color: #64748b; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;" data-bs-dismiss="modal">
                    <i class="fas fa-trash-alt"></i> Clear Inbox
                </button>
                <button type="button" class="btn btn-sm" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;">
                    <i class="fas fa-envelope-open"></i> View All Messages
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Fix aria-hidden focus issue when modals close
(function() {
    const notifModal = document.getElementById('notificationsModal');
    const msgModal = document.getElementById('messagesModal');
    
    if (notifModal) {
        notifModal.addEventListener('hide.bs.modal', function() {
            const focused = this.querySelector(':focus');
            if (focused) focused.blur();
        });
    }
    
    if (msgModal) {
        msgModal.addEventListener('hide.bs.modal', function() {
            const focused = this.querySelector(':focus');
            if (focused) focused.blur();
        });
    }
})();
</script>

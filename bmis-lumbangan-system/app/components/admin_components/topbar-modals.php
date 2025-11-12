<?php
/**
 * Top Bar Modals Component
 * Contains Notifications and Messages modals for the admin topbar
 * 
 * REQUIREMENTS:
 * - Must have dashboard_data.php loaded with 'notifications' and 'messages' arrays
 * - Must have modal-items.php component loaded (renderNotificationItem, renderMessageItem)
 * 
 * Usage:
 * <?php 
 *     // 1. Load data and components first
 *     $dashboardData = require_once 'path/to/config/dashboard_data.php';
 *     require_once 'path/to/components/modal-items.php';
 *     
 *     // 2. Include modals
 *     include 'path/to/components/topbar-modals.php'; 
 * ?>
 */

// Validate required data exists
if (!isset($dashboardData) || !isset($dashboardData['notifications']) || !isset($dashboardData['messages'])) {
    echo "<!-- WARNING: topbar-modals.php requires \$dashboardData with 'notifications' and 'messages' arrays -->";
    return;
}

// Validate required functions exist
if (!function_exists('renderNotificationItem') || !function_exists('renderMessageItem')) {
    echo "<!-- WARNING: topbar-modals.php requires modal-items.php to be loaded (renderNotificationItem, renderMessageItem) -->";
    return;
}
?>

<!-- Notifications Modal -->
<div class="modal fade" id="notificationsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
            <!-- Modal Header -->
            <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                <h5 class="modal-title" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;">
                    <i class="fas fa-bell"></i> Notifications 
                    <span style="background: #ef4444; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">
                        <?= count(array_filter($dashboardData['notifications'], function($n) { return $n['unread'] ?? false; })) ?> New
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 0; background: #f8fafc;">
                <?php foreach ($dashboardData['notifications'] as $notification): ?>
                    <?php renderNotificationItem($notification); ?>
                <?php endforeach; ?>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; background: white; border-radius: 0 0 12px 12px;">
                <button type="button" class="btn btn-sm" style="background: transparent; border: 1px solid #cbd5e1; color: #64748b; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-sm" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 8px; font-weight: 500;">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Messages/Inbox Modal -->
<div class="modal fade" id="messagesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
            <!-- Modal Header -->
            <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                <h5 class="modal-title" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;">
                    <i class="fas fa-envelope"></i> Messages 
                    <span style="background: #ef4444; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; margin-left: 0.5rem;">
                        <?= array_sum(array_column($dashboardData['messages'], 'unread_count')) ?> Unread
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding: 0; background: #f8fafc;">
                <?php foreach ($dashboardData['messages'] as $message): ?>
                    <?php renderMessageItem($message); ?>
                <?php endforeach; ?>
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

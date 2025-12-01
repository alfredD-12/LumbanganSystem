<?php
/**
 * Dashboard Data Configuration
 * Centralized data for SecDash dashboard
 * Now fetches REAL data from database!
 */

// Load database statistics helper
require_once dirname(__DIR__) . '/helpers/dashboard_stats_helper.php';

$statsHelper = new DashboardStatsHelper();

// Fetch real statistics
$totalResidents = $statsHelper->getTotalResidents();
$residentsThisMonth = $statsHelper->getResidentsThisMonth();
$pendingComplaints = $statsHelper->getPendingComplaints();
$complaintsLastWeek = $statsHelper->getComplaintsLastWeek();
$resolvedComplaints = $statsHelper->getResolvedComplaints();
$resolvedThisWeek = $statsHelper->getResolvedThisWeek();
$totalDocRequests = $statsHelper->getTotalDocumentRequests();
$docRequestsToday = $statsHelper->getDocumentRequestsToday();

return [
    // Statistics Cards Data - REAL DATA FROM DATABASE
    'stats' => [
        [
            'icon' => 'fas fa-users',
            'icon_color' => 'blue',
            'number' => number_format($totalResidents),
            'label' => 'Total Registered Residents',
            'change' => '+' . $residentsThisMonth . ' this month',
            'change_type' => 'up'
        ],
        [
            'icon' => 'fas fa-exclamation-circle',
            'icon_color' => 'yellow',
            'number' => $pendingComplaints,
            'label' => 'Pending Complaints',
            'change' => ($complaintsLastWeek > 0 ? '-' : '') . $complaintsLastWeek . ' from last week',
            'change_type' => $complaintsLastWeek > 0 ? 'down' : 'up'
        ],
        [
            'icon' => 'fas fa-check-circle',
            'icon_color' => 'green',
            'number' => $resolvedComplaints,
            'label' => 'Resolved Complaints',
            'change' => '+' . $resolvedThisWeek . ' this week',
            'change_type' => 'up'
        ],
        [
            'icon' => 'fas fa-file-alt',
            'icon_color' => 'red',
            'number' => $totalDocRequests,
            'label' => 'Document Requests',
            'change' => $docRequestsToday . ' new today',
            'change_type' => 'up'
        ]
    ],

    // Recent Complaints/Activities - REAL DATA FROM DATABASE
    'complaints' => array_map(function($complaint) use ($statsHelper) {
        return [
            'title' => $complaint['incident_title'],
            'time' => $statsHelper->timeAgo($complaint['created_at']),
            'badge' => strtoupper($complaint['status_label'] ?? 'NEW'),
            'badge_class' => $statsHelper->getStatusBadgeClass($complaint['status_label'] ?? 'NEW')
        ];
    }, $statsHelper->getRecentComplaints(6)),

    // Document Management Data - REAL DATA FROM DATABASE
    'documents' => [
        'pending_approvals' => $statsHelper->getPendingApprovalsByType(),
        'today_released' => $statsHelper->getReleasedToday(),
        'queue' => $statsHelper->getDocumentQueue(),
        'monthly_progress' => $statsHelper->getMonthlyProgress()
    ],

    // Upcoming Events
    'events' => [
        [
            'type' => 'meeting',
            'icon' => 'fas fa-users',
            'title' => 'Barangay Council Meeting',
            'date' => 'Today',
            'time' => '2:00 PM',
            'location' => 'Barangay Hall'
        ],
        [
            'type' => 'health',
            'icon' => 'fas fa-heartbeat',
            'title' => 'Community Health Program',
            'date' => 'Tomorrow',
            'time' => '9:00 AM',
            'location' => 'Health Center'
        ],
        [
            'type' => 'community',
            'icon' => 'fas fa-hands-helping',
            'title' => 'Youth Skills Training',
            'date' => 'Nov 15',
            'time' => '10:00 AM',
            'location' => 'Youth Center'
        ],
        [
            'type' => 'meeting',
            'icon' => 'fas fa-clipboard-list',
            'title' => 'Budget Planning Session',
            'date' => 'Nov 18',
            'time' => '1:00 PM',
            'location' => 'Conference Room'
        ]
    ],

    // Notifications Data
    'notifications' => [
        [
            'initials' => 'JD',
            'gradient' => 'linear-gradient(135deg, #3b82f6, #2563eb)',
            'name' => 'Juan Dela Cruz',
            'role' => 'Resident - Zone 3',
            'title' => 'New Document Request',
            'message' => 'Submitted a request for Barangay Clearance',
            'time' => '2 mins ago',
            'unread' => true,
            'badge_count' => 1
        ],
        [
            'initials' => 'MS',
            'gradient' => 'linear-gradient(135deg, #10b981, #059669)',
            'name' => 'Maria Santos',
            'role' => 'Resident - Zone 1',
            'title' => 'Complaint Status Update',
            'message' => 'Street lighting issue has been marked as resolved',
            'time' => '15 mins ago',
            'unread' => true,
            'badge_count' => 1
        ],
        [
            'initials' => 'PR',
            'gradient' => 'linear-gradient(135deg, #f59e0b, #d97706)',
            'name' => 'Pedro Reyes',
            'role' => 'Resident - Zone 5',
            'title' => 'Payment Confirmation',
            'message' => 'Document processing fee has been paid',
            'time' => '1 hour ago',
            'unread' => false,
            'badge_count' => 0
        ],
        [
            'initials' => 'AL',
            'gradient' => 'linear-gradient(135deg, #8b5cf6, #7c3aed)',
            'name' => 'Ana Lopez',
            'role' => 'Resident - Zone 2',
            'title' => 'New Inquiry',
            'message' => 'Asked about requirements for business permit',
            'time' => '3 hours ago',
            'unread' => false,
            'badge_count' => 0
        ]
    ],

    // Messages Data
    'messages' => [
        [
            'initials' => 'JD',
            'gradient' => 'linear-gradient(135deg, #3b82f6, #2563eb)',
            'name' => 'Juan Dela Cruz',
            'role' => 'Resident - Zone 3',
            'title' => 'Barangay Clearance Request',
            'message' => 'Good morning! I would like to follow up on my clearance request.',
            'time' => '5 mins ago',
            'online' => true,
            'unread_count' => 3
        ],
        [
            'initials' => 'MS',
            'gradient' => 'linear-gradient(135deg, #10b981, #059669)',
            'name' => 'Maria Santos',
            'role' => 'Resident - Zone 5',
            'title' => 'Certificate of Residency Request',
            'message' => 'Hi! I submitted my request yesterday. Can you check the status po?',
            'time' => 'Yesterday',
            'online' => true,
            'unread_count' => 2
        ],
        [
            'initials' => 'PR',
            'gradient' => 'linear-gradient(135deg, #f59e0b, #d97706)',
            'name' => 'Pedro Reyes',
            'role' => 'Resident - Zone 2',
            'title' => 'Thank you for processing my documents!',
            'message' => 'I already received my certificate. Salamat po!',
            'time' => '2 days ago',
            'online' => true,
            'unread_count' => 0
        ],
        [
            'initials' => 'AL',
            'gradient' => 'linear-gradient(135deg, #8b5cf6, #7c3aed)',
            'name' => 'Ana Lopez',
            'role' => 'Resident - Zone 4',
            'title' => 'Business Permit Application',
            'message' => 'What are the requirements for business permit application?',
            'time' => '1 week ago',
            'online' => false,
            'unread_count' => 0
        ]
    ]
];

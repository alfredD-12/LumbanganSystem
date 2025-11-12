<?php
/**
 * Dashboard Data Configuration
 * Centralized data for SecDash dashboard
 * Easy to modify - all data in one place!
 */

return [
    // Statistics Cards Data
    'stats' => [
        [
            'icon' => 'fas fa-users',
            'icon_color' => 'blue',
            'number' => '2,847',
            'label' => 'Total Registered Residents',
            'change' => '+124 this month',
            'change_type' => 'up'
        ],
        [
            'icon' => 'fas fa-exclamation-circle',
            'icon_color' => 'yellow',
            'number' => '45',
            'label' => 'Pending Complaints',
            'change' => '-12 from last week',
            'change_type' => 'down'
        ],
        [
            'icon' => 'fas fa-check-circle',
            'icon_color' => 'green',
            'number' => '189',
            'label' => 'Resolved Complaints',
            'change' => '+23 this week',
            'change_type' => 'up'
        ],
        [
            'icon' => 'fas fa-file-alt',
            'icon_color' => 'red',
            'number' => '67',
            'label' => 'Document Requests',
            'change' => '15 new today',
            'change_type' => 'up'
        ]
    ],

    // Recent Complaints/Activities
    'complaints' => [
        [
            'title' => 'Noise disturbance complaint',
            'time' => '5 minutes ago',
            'badge' => 'NEW',
            'badge_class' => 'badge-new'
        ],
        [
            'title' => 'Street lighting repair needed',
            'time' => '25 minutes ago',
            'badge' => 'PENDING',
            'badge_class' => 'badge-pending'
        ],
        [
            'title' => 'Garbage collection issue resolved',
            'time' => '1 hour ago',
            'badge' => 'RESOLVED',
            'badge_class' => 'badge-completed'
        ],
        [
            'title' => 'Water supply problem reported',
            'time' => '2 hours ago',
            'badge' => 'PENDING',
            'badge_class' => 'badge-pending'
        ],
        [
            'title' => 'Road damage complaint',
            'time' => '3 hours ago',
            'badge' => 'NEW',
            'badge_class' => 'badge-new'
        ],
        [
            'title' => 'Stray animal concern addressed',
            'time' => '5 hours ago',
            'badge' => 'RESOLVED',
            'badge_class' => 'badge-completed'
        ]
    ],

    // Document Management Data
    'documents' => [
        'pending_approvals' => [
            ['name' => 'Barangay Clearance', 'count' => 6],
            ['name' => 'Certificate of Indigency', 'count' => 4],
            ['name' => 'Certificate of Residency', 'count' => 2]
        ],
        'today_released' => [
            ['num' => '11', 'label' => 'Clearances'],
            ['num' => '5', 'label' => 'Indigency'],
            ['num' => '2', 'label' => 'Residency']
        ],
        'queue' => [
            ['label' => 'Barangay Clearance', 'count' => 21, 'width' => '70%'],
            ['label' => 'Indigency Cert.', 'count' => 16, 'width' => '55%'],
            ['label' => 'Residency Cert.', 'count' => 10, 'width' => '35%']
        ],
        'monthly_progress' => [
            'current' => 164,
            'target' => 200,
            'percentage' => 82,
            'remaining' => 36
        ]
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

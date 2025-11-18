<?php
require_once __DIR__ . '/../models/Announcement.php';
require_once __DIR__ . '/../helpers/session_helper.php';
// Ensure config constants (BASE_URL, BASE_PUBLIC) are available
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

class PublicAnnouncementController {
    private $model;
    
    public function __construct() {
        $this->model = new Announcement();
        date_default_timezone_set('Asia/Manila');
    }
    
    public function index() {
        // Always allow access from public - sessions are optional
        // If user is logged in, use their role; otherwise default to 'residents'
        
        // Determine role from session or GET parameter
        $role = 'residents'; // default
        
        if (isset($_GET['role']) && in_array($_GET['role'], ['residents', 'officials'])) {
            $role = $_GET['role'];
        } elseif (isLoggedIn()) {
            $role = isOfficial() ? 'officials' : 'residents';
        }
        
        // Get filter values for view
        $start_date = isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : null;
        $end_date = isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : null;
        $q = isset($_GET['q']) && trim($_GET['q']) !== '' ? trim($_GET['q']) : '';
        
        // Build filters array for model
        $filters = [
            'start_date' => $start_date ? $start_date . ' 00:00:00' : null,
            'end_date' => $end_date ? $end_date . ' 23:59:59' : null,
            'q' => $q
        ];
        
        // Get announcements
        $allAnnouncements = $this->model->getPublicAnnouncements($role, $filters);
        
        // Separate today's and older announcements
        $todayDate = date('Y-m-d');
        $featuredLimit = 6;
        $todaysAnnouncements = [];
        $olderAnnouncements = [];
        
        foreach ($allAnnouncements as $item) {
            $createdDate = date('Y-m-d', strtotime($item['created_at']));
            if ($createdDate === $todayDate) {
                $todaysAnnouncements[] = $item;
            } else {
                $olderAnnouncements[] = $item;
            }
        }
        
        // Combine remaining today's with older
        $remainingToday = array_slice($todaysAnnouncements, $featuredLimit);
        $announcements = array_merge($remainingToday, $olderAnnouncements);
        
    // Load view (renamed to public_announcement.php)
    include __DIR__ . '/../views/public_announcement/public_announcement.php';
    }
}

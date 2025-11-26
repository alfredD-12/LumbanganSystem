<?php
require_once __DIR__ . '/../models/Announcement.php';
require_once __DIR__ . '/../config/Database.php';
//require_once __DIR__ . '/../helpers/notification_helper.php';
// Ensure config constants (BASE_URL, BASE_PUBLIC) are available
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

class AnnouncementController {
    private $model;
    private $uploadDir;
    
    public function __construct() {
        $this->model = new Announcement();
        // Use the existing uploads folder inside the app directory (do not create new root-level uploads)
        $appUploadRoot = dirname(__DIR__) . '/uploads';
        $uploadSubdir = 'announcementimage';
        $this->uploadDir = $appUploadRoot . '/' . $uploadSubdir;

        // If project-root uploads exist, attempt non-destructive migration into the app uploads folder only
        $oldRoot = dirname(dirname(__DIR__)) . '/uploads';
        $this->migrateImages($oldRoot);
        
        // Auto-archive expired
        $this->model->autoArchiveExpired();
    }
    
    private function migrateImages($uploadRoot) {
        // Move images from an old uploads root into the app uploads folder if both locations exist.
        if (!is_dir($uploadRoot) || !is_dir($this->uploadDir)) {
            // Nothing to migrate or destination missing; do not create new directories automatically.
            return;
        }

        $images = $this->model->getImagesForMigration();
        foreach ($images as $row) {
            $img = $row['image'];
            $oldPath = rtrim($uploadRoot, '/') . '/' . $img;
            $newPath = rtrim($this->uploadDir, '/') . '/' . $img;
            if (file_exists($oldPath) && !file_exists($newPath)) {
                @rename($oldPath, $newPath);
            }
        }
    }
    
    public function index() {
        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filters = $this->getFilters();
            $this->handlePostActions($filters);
            return;
        }
        
        // Handle AJAX load more
        if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
            $filters = $this->getFilters();
            $this->loadMore($filters);
            return;
        }
        
        // Get filter values for view
        $start_date = isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : null;
        $end_date = isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : null;
        $q = isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : '';
        $status_filter = isset($_GET['status']) && in_array($_GET['status'], ['draft','published','archived']) ? $_GET['status'] : null;
        $type_filter = isset($_GET['type']) && trim($_GET['type']) !== '' ? trim($_GET['type']) : null;
        
        // Build filters array for model
        $filters = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'search' => $q,
            'status' => $status_filter,
            'type' => $type_filter
        ];
        
        // Get edit data if editing
        $editData = null;
        if (isset($_GET['edit'])) {
            $editData = $this->model->getById(intval($_GET['edit']));
        }
        
        // Get announcements
        $limit = 9;
        $offset = isset($_GET['offset']) ? max(0, intval($_GET['offset'])) : 0;
        $filters['limit'] = $limit;
        $filters['offset'] = $offset;
        
        $announcements = $this->model->getAll($filters);
        
        // Calculate has_more and next_offset for view
        $has_more = count($announcements) >= $limit;
        $next_offset = $offset + $limit;

        // Get statistics for the current filter set
        $stats = $this->model->getStats($filters);
        
    // Load view (renamed to announcement.php)
    include __DIR__ . '/../views/announcement/announcement.php';
    }
    
    private function getFilters() {
        return [
            'start_date' => isset($_GET['start_date']) && $_GET['start_date'] !== '' ? $_GET['start_date'] : null,
            'end_date' => isset($_GET['end_date']) && $_GET['end_date'] !== '' ? $_GET['end_date'] : null,
            'search' => isset($_GET['search']) && trim($_GET['search']) !== '' ? trim($_GET['search']) : '',
            'status' => isset($_GET['status']) && in_array($_GET['status'], ['draft','published','archived']) ? $_GET['status'] : null,
            'type' => isset($_GET['type']) && trim($_GET['type']) !== '' ? trim($_GET['type']) : null
        ];
    }
    
    private function handlePostActions($filters) {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'message' => $_POST['message'] ?? '',
                'audience' => $_POST['audience'] ?? 'all',
                'status' => $_POST['status'] ?? 'published',
                'type' => $_POST['type'] ?? 'general',
                'author' => $_POST['author'] ?? 'Official',
                'expires_at' => isset($_POST['expires_at']) && $_POST['expires_at'] !== '' ? $_POST['expires_at'] : null,
                'image' => $this->handleImageUpload('image')
            ];
            
            $announcementId = $this->model->create($data);
            
            // Send notification if announcement is published (only if helper is available)
            if ($announcementId && $data['status'] === 'published') {
                if (function_exists('notifyNewAnnouncement')) {
                    //notifyNewAnnouncement($announcementId, $data['title'], $data['audience']);
                }
            }
            
            if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'reload' => true]);
                exit;
            }
            
            header('Location: ' . $this->buildRedirectUrl($filters));
            exit;
        }
        
        if ($action === 'update') {
            $id = intval($_POST['id']);
            $existing = $this->model->getById($id);
            
            $data = [
                'title' => $_POST['title'] ?? '',
                'message' => $_POST['message'] ?? '',
                'audience' => $_POST['audience'] ?? 'all',
                'status' => $_POST['status'] ?? 'published',
                'author' => $_POST['author'] ?? 'Official',
                'type' => $_POST['type'] ?? 'general',
                'expires_at' => isset($_POST['expires_at']) && $_POST['expires_at'] !== '' ? $_POST['expires_at'] : null,
                'image' => $this->handleImageUpload('image', $existing['image'] ?? null)
            ];
            
            $this->model->update($id, $data);
            header('Location: ' . $this->buildRedirectUrl($filters));
            exit;
        }
        
        if ($action === 'delete' || $action === 'archive') {
            $id = intval($_POST['id']);
            $this->model->archive($id);
            header('Location: ' . $this->buildRedirectUrl($filters));
            exit;
        }
    }
    
    private function loadMore($filters) {
        // Respect optional limit param (sent by JS) otherwise default to 9
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 9;
        $offset = isset($_GET['offset']) ? max(0, intval($_GET['offset'])) : 0;
        $filters['limit'] = $limit;
        $filters['offset'] = $offset;

        $announcements = $this->model->getAll($filters);

        $html = '';
        foreach ($announcements as $a) {
            ob_start();
            include __DIR__ . '/../views/announcement/_card.php';
            $html .= ob_get_clean();
        }

        $count = count($announcements);
        $next_offset = $offset + $limit;
        $has_more = $count >= $limit;

        header('Content-Type: application/json');
        echo json_encode([
            'html' => $html,
            'count' => $count,
            'next_offset' => $next_offset,
            'has_more' => $has_more
        ]);
        exit;
    }
    
    private function handleImageUpload($fileField, $existing = null) {
        if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) {
            return $existing;
        }
        
        $file = $_FILES[$fileField];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $existing;
        }
        
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array(mime_content_type($file['tmp_name']), $allowed)) {
            return $existing;
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = $this->uploadDir . '/' . $name;
        
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            if ($existing && file_exists($this->uploadDir . '/' . $existing)) {
                @unlink($this->uploadDir . '/' . $existing);
            }
            return $name;
        }
        
        return $existing;
    }
    
    private function buildRedirectUrl($filters) {
        $params = [];
        $params[] = 'page=admin_announcements';
        if ($filters['status']) $params[] = 'status=' . urlencode($filters['status']);
        if ($filters['start_date']) $params[] = 'start_date=' . urlencode($filters['start_date']);
        if ($filters['end_date']) $params[] = 'end_date=' . urlencode($filters['end_date']);
        if (!empty($filters['search'])) $params[] = 'search=' . urlencode($filters['search']);
        return 'index.php?' . implode('&', $params);
    }
}

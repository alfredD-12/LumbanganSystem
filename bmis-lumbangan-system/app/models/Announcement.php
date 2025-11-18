<?php
require_once __DIR__ . '/../config/Database.php';

class Announcement {
    private $conn;
    private $table = 'announcements';
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    
    // Auto-archive expired announcements
    public function autoArchiveExpired() {
        $sql = "UPDATE announcements 
                SET status = 'archived' 
                WHERE status = 'published' 
                AND expires_at IS NOT NULL 
                AND expires_at < NOW()";
        return $this->conn->exec($sql);
    }
    
    // Get all announcements with filters
    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(title LIKE ? OR message LIKE ? OR author LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['start_date'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
            if (isset($filters['offset'])) {
                $sql .= " OFFSET " . intval($filters['offset']);
            }
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get public announcements for residents/officials
    public function getPublicAnnouncements($role, $filters = []) {
        $where = ["status = 'published'"];
        $where[] = "(expires_at IS NULL OR expires_at > NOW())";
        $where[] = "(audience = ? OR audience = 'all')";
        $params = [$role];
        
        if (!empty($filters['q'])) {
            $where[] = "(title LIKE ? OR message LIKE ?)";
            $search = '%' . $filters['q'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['start_date'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get single announcement by ID
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create new announcement
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (title, message, image, audience, status, expires_at, author) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['message'],
            $data['image'] ?? null,
            $data['audience'] ?? 'all',
            $data['status'] ?? 'published',
            $data['expires_at'] ?? null,
            $data['author']
        ]);
    }
    
    // Update announcement
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET title = ?, message = ?, image = ?, audience = ?, status = ?, expires_at = ?, author = ? 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['message'],
            $data['image'] ?? null,
            $data['audience'] ?? 'all',
            $data['status'] ?? 'published',
            $data['expires_at'] ?? null,
            $data['author'],
            $id
        ]);
    }
    
    // Archive announcement (soft delete)
    public function archive($id) {
        $sql = "UPDATE {$this->table} SET status = 'archived' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Get images for migration
    public function getImagesForMigration() {
        $sql = "SELECT image FROM {$this->table} WHERE image IS NOT NULL AND image != ''";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

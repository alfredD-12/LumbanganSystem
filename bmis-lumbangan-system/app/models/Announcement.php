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

        if (!empty($filters['type'])) {
            $where[] = "`type` = ?";
            $params[] = $filters['type'];
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
            // Allow searching by title, message, or type (so users can type a type name)
            $where[] = "(title LIKE ? OR message LIKE ? OR `type` LIKE ?)";
            $search = '%' . $filters['q'] . '%';
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
        // include 'type' column when creating announcements
        $sql = "INSERT INTO {$this->table} (title, message, image, audience, status, expires_at, author, `type`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['message'],
            $data['image'] ?? null,
            $data['audience'] ?? 'all',
            $data['status'] ?? 'published',
            $data['expires_at'] ?? null,
            $data['author'],
            $data['type'] ?? 'general'
        ]);
    }
    
    // Update announcement
    public function update($id, $data) {
        // include 'type' when updating announcements
        $sql = "UPDATE {$this->table} 
                SET title = ?, message = ?, image = ?, audience = ?, status = ?, expires_at = ?, author = ?, `type` = ? 
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
            $data['type'] ?? 'general',
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

    // Get statistics for announcements (totals and counts by status/type)
    public function getStats($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search']) || !empty($filters['q'])) {
            $searchVal = '';
            if (!empty($filters['search'])) $searchVal = $filters['search'];
            if (!empty($filters['q'])) $searchVal = $filters['q'];
            $where[] = "(title LIKE ? OR message LIKE ? OR author LIKE ?)";
            $search = '%' . $searchVal . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['start_date'])) {
            // allow both date-only or datetime strings
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['type'])) {
            $where[] = "`type` = ?";
            $params[] = $filters['type'];
        }

        $whereSql = '';
        if ($where) {
            $whereSql = ' WHERE ' . implode(' AND ', $where);
        }

        // Totals and status breakdown
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(status = 'published') AS published,
                    SUM(status = 'draft') AS draft,
                    SUM(status = 'archived') AS archived
                FROM {$this->table}" . $whereSql;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Counts by type
        $typesSql = "SELECT `type`, COUNT(*) AS cnt FROM {$this->table}" . $whereSql . " GROUP BY `type` ORDER BY cnt DESC";
        $tstmt = $this->conn->prepare($typesSql);
        $tstmt->execute($params);
        $types = [];
        while ($r = $tstmt->fetch(PDO::FETCH_ASSOC)) {
            $types[$r['type']] = intval($r['cnt']);
        }

        return [
            'total' => intval($row['total'] ?? 0),
            'published' => intval($row['published'] ?? 0),
            'draft' => intval($row['draft'] ?? 0),
            'archived' => intval($row['archived'] ?? 0),
            'types' => $types
        ];
    }
}

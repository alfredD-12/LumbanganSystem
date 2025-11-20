<?php
class Gallery {
    private $db;
    
    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            if (!$this->db) {
                throw new Exception("Database connection failed");
            }
        } catch (Exception $e) {
            error_log("Gallery Model Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getAll($activeOnly = true) {
        $sql = "SELECT * FROM gallery";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($title, $description, $imagePath, $displayOrder = 0) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO gallery (title, description, image_path, display_order) 
                 VALUES (?, ?, ?, ?)"
            );
            $result = $stmt->execute([$title, $description, $imagePath, $displayOrder]);
            if (!$result) {
                error_log("Gallery Create Error: " . implode(", ", $stmt->errorInfo()));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Gallery Create Exception: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $title, $description, $imagePath = null, $displayOrder = null) {
        if ($imagePath) {
            $stmt = $this->db->prepare(
                "UPDATE gallery SET title = ?, description = ?, image_path = ?, display_order = ? 
                 WHERE id = ?"
            );
            return $stmt->execute([$title, $description, $imagePath, $displayOrder, $id]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE gallery SET title = ?, description = ?, display_order = ? 
                 WHERE id = ?"
            );
            return $stmt->execute([$title, $description, $displayOrder, $id]);
        }
    }
    
    public function delete($id) {
        // Get image path to delete file
        $gallery = $this->getById($id);
        if ($gallery) {
            $imagePath = __DIR__ . '/../uploads/gallery/' . $gallery['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $stmt = $this->db->prepare("DELETE FROM gallery WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function toggleActive($id) {
        $stmt = $this->db->prepare(
            "UPDATE gallery SET is_active = NOT is_active WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }
}

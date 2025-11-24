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
    
    public function create($title, $description, $imagePath) {
        try {
            // Get the highest current display_order and add 1
            $orderStmt = $this->db->query("SELECT MAX(display_order) FROM gallery");
            $maxOrder = $orderStmt->fetchColumn();
            $newOrder = ($maxOrder === null) ? 1 : $maxOrder + 1;

            $stmt = $this->db->prepare(
                "INSERT INTO gallery (title, description, image_path, display_order) 
                 VALUES (?, ?, ?, ?)"
            );
            $result = $stmt->execute([$title, $description, $imagePath, $newOrder]);
            if (!$result) {
                error_log("Gallery Create Error: " . implode(", ", $stmt->errorInfo()));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Gallery Create Exception: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $title, $description, $imagePath = null) {
        if ($imagePath) {
            $stmt = $this->db->prepare(
                "UPDATE gallery SET title = ?, description = ?, image_path = ? WHERE id = ?"
            );
            return $stmt->execute([$title, $description, $imagePath, $id]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE gallery SET title = ?, description = ? WHERE id = ?"
            );
            return $stmt->execute([$title, $description, $id]);
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

    

    public function updateOrder($orderedIds) {
        $this->db->beginTransaction();
        try {
            foreach ($orderedIds as $index => $id) {
                // The order is the array index. Add 1 if you don't want a 0-based order.
                $displayOrder = $index + 1; 
                $stmt = $this->db->prepare('UPDATE gallery SET display_order = :display_order WHERE id = :id');
                $stmt->bindValue(':display_order', $displayOrder, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }
            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            // Log the error for debugging
            error_log("Update Order Exception: " . $e->getMessage());
            return false;
        }
    }
}

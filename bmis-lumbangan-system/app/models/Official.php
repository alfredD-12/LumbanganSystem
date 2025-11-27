<?php
class Official {
    private $conn;
    private $table = "officials";

    public $id;
    public $full_name;
    public $username;
    public $password_hash;
    public $role;
    public $contact_no;
    public $email;
    public $active;
    public $last_login_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Find official by username
     */
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE username = :username AND active = 1 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if username exists (active only)
     */
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table . " WHERE username = :username AND active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if email exists (active only)
     */
    public function emailExists($email) {
        if (empty($email)) return false;
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email AND active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if contact number exists (active only)
     */
    public function contactExists($contact_no) {
        if (empty($contact_no)) return false;
        $query = "SELECT id FROM " . $this->table . " WHERE contact_no = :contact_no AND active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':contact_no', $contact_no);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin($officialId) {
        $query = "UPDATE " . $this->table . " 
                  SET last_login_at = NOW() 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $officialId);
        return $stmt->execute();
    }

    /**
     * Get official by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE id = :id AND active = 1 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update official profile fields (full_name, email, contact_no)
     */
    public function updateProfile($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['full_name'])) {
            $fields[] = 'full_name = :full_name';
            $params[':full_name'] = $data['full_name'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params[':email'] = $data['email'];
        }
        if (isset($data['contact_no'])) {
            $fields[] = 'contact_no = :contact_no';
            $params[':contact_no'] = $data['contact_no'];
        }

        if (empty($fields)) return false;

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $p => $v) {
            $stmt->bindValue($p, $v);
        }

        return $stmt->execute();
    }

    /**
     * Get all officials (optionally only active)
     */
    public function getAll($onlyActive = true) {
        $query = "SELECT * FROM " . $this->table;
        if ($onlyActive) $query .= " WHERE active = 1";
        $query .= " ORDER BY role ASC, full_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new official (returns inserted id or false)
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (full_name, username, password_hash, role, contact_no, email, active, last_login_at) VALUES (:full_name, :username, :password_hash, :role, :contact_no, :email, 1, NULL)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':full_name', $data['full_name'] ?? '');
        $stmt->bindValue(':username', $data['username'] ?? '');
        $stmt->bindValue(':password_hash', $data['password_hash'] ?? '');
        $stmt->bindValue(':role', $data['role'] ?? '');
        $stmt->bindValue(':contact_no', $data['contact_no'] ?? null);
        $stmt->bindValue(':email', $data['email'] ?? null);
        if ($stmt->execute()) return $this->conn->lastInsertId();
        return false;
    }

    /**
     * Update any official fields by id
     */
    public function updateById($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        $allowed = ['full_name','username','password_hash','role','contact_no','email','active'];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (empty($fields)) return false;
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        return $stmt->execute();
    }

    /**
     * Soft-delete official by setting active = 0
     */
    public function deleteById($id) {
        $query = "UPDATE " . $this->table . " SET active = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}

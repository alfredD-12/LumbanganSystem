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
}

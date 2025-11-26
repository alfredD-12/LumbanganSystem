<?php
class User {
    private $conn;
    private $table_users = "users";
    private $table_persons = "persons";
    private $table_families = "families";

    public $id;
    public $person_id;
    public $username;
    public $email;
    public $mobile;
    public $password_hash;
    public $status;
    public $last_login_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Find user by username
     */
    public function findByUsername($username) {
        $query = "SELECT u.*, p.first_name, p.last_name, p.middle_name, p.suffix
                  FROM " . $this->table_users . " u
                  INNER JOIN " . $this->table_persons . " p ON u.person_id = p.id
                  WHERE u.username = :username
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $query = "SELECT u.*, p.first_name, p.last_name, p.middle_name, p.suffix
                  FROM " . $this->table_users . " u
                  INNER JOIN " . $this->table_persons . " p ON u.person_id = p.id
                  WHERE u.email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_users . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_users . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin($userId) {
        $query = "UPDATE " . $this->table_users . " 
                  SET last_login_at = NOW() 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    /**
     * Create new user (registration)
     * Returns the new user ID or false on failure
     */
    public function create($personData, $userData) {
        try {
            $this->conn->beginTransaction();

            // 1. Create person record.
            // household_id is NULL and family_id is 0 (or a designated 'unassigned' ID)
            // as the user is not yet part of a household/family.
            // is_head is 0 by default.
            $person_query = "INSERT INTO persons 
                            (family_id, household_id, last_name, first_name, middle_name, suffix, sex, birthdate, 
                             marital_status, is_head, created_at, updated_at) 
                            VALUES 
                            (0, NULL, :last_name, :first_name, :middle_name, :suffix, :sex, :birthdate, 
                             :marital_status, 0, NOW(), NOW())";
            
            $person_stmt = $this->conn->prepare($person_query);
            $person_stmt->bindParam(':last_name', $personData['last_name']);
            $person_stmt->bindParam(':first_name', $personData['first_name']);
            $person_stmt->bindParam(':middle_name', $personData['middle_name']);
            $person_stmt->bindParam(':suffix', $personData['suffix']);
            
            $person_stmt->bindValue(':sex', $personData['sex'] ?? null, $personData['sex'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $person_stmt->bindValue(':birthdate', $personData['birthdate'] ?? null, $personData['birthdate'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $person_stmt->bindParam(':marital_status', $personData['marital_status']);
            $person_stmt->execute();
            $person_id = $this->conn->lastInsertId();

            // 2. Create user account
            $user_query = "INSERT INTO users 
                          (person_id, username, email, mobile, password_hash, status, created_at, updated_at) 
                          VALUES 
                          (:person_id, :username, :email, :mobile, :password_hash, 'active', NOW(), NOW())";
            
            $user_stmt = $this->conn->prepare($user_query);
            $user_stmt->bindParam(':person_id', $person_id);
            $user_stmt->bindParam(':username', $userData['username']);
            $user_stmt->bindParam(':email', $userData['email']);
            $user_stmt->bindParam(':mobile', $userData['mobile']);
            $user_stmt->bindParam(':password_hash', $userData['password_hash']);
            $user_stmt->execute();
            $user_id = $this->conn->lastInsertId();

            $this->conn->commit();
            return $user_id;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("User registration error: " . $e->getMessage());
            return false;
        }
    }
}

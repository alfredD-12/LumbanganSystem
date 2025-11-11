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

            // 1. First, create or get a household and family
            // For registration, we'll create a temporary household/family
            $household_query = "INSERT INTO households (purok_id, household_no, address, created_at, updated_at) 
                               VALUES (NULL, NULL, :address, NOW(), NOW())";
            $household_stmt = $this->conn->prepare($household_query);
            $temp_address = 'Pending - To be updated';
            $household_stmt->bindParam(':address', $temp_address);
            $household_stmt->execute();
            $household_id = $this->conn->lastInsertId();

            // 2. Create family
            $family_query = "INSERT INTO families (household_id, family_number, residency_status, created_at, updated_at) 
                            VALUES (:household_id, NULL, 'Permanent', NOW(), NOW())";
            $family_stmt = $this->conn->prepare($family_query);
            $family_stmt->bindParam(':household_id', $household_id);
            $family_stmt->execute();
            $family_id = $this->conn->lastInsertId();

            // 3. Create person
            $person_query = "INSERT INTO persons 
                            (family_id, last_name, first_name, middle_name, suffix, sex, birthdate, 
                             marital_status, family_position, created_at, updated_at) 
                            VALUES 
                            (:family_id, :last_name, :first_name, :middle_name, :suffix, :sex, :birthdate, 
                             :marital_status, 'Head', NOW(), NOW())";
            
            $person_stmt = $this->conn->prepare($person_query);
            $person_stmt->bindParam(':family_id', $family_id);
            $person_stmt->bindParam(':last_name', $personData['last_name']);
            $person_stmt->bindParam(':first_name', $personData['first_name']);
            $person_stmt->bindParam(':middle_name', $personData['middle_name']);
            $person_stmt->bindParam(':suffix', $personData['suffix']);
            // Allow NULL for sex and birthdate (will be filled in survey)
            $sex_value = $personData['sex'] ?? null;
            $birthdate_value = $personData['birthdate'] ?? null;
            if ($sex_value === null) {
                $person_stmt->bindValue(':sex', null, \PDO::PARAM_NULL);
            } else {
                $person_stmt->bindValue(':sex', $sex_value, \PDO::PARAM_STR);
            }
            if ($birthdate_value === null) {
                $person_stmt->bindValue(':birthdate', null, \PDO::PARAM_NULL);
            } else {
                $person_stmt->bindValue(':birthdate', $birthdate_value, \PDO::PARAM_STR);
            }
            $person_stmt->bindParam(':marital_status', $personData['marital_status']);
            $person_stmt->execute();
            $person_id = $this->conn->lastInsertId();

            // 4. Update family to set head_person_id
            $update_family_query = "UPDATE families SET head_person_id = :person_id WHERE id = :family_id";
            $update_family_stmt = $this->conn->prepare($update_family_query);
            $update_family_stmt->bindParam(':person_id', $person_id);
            $update_family_stmt->bindParam(':family_id', $family_id);
            $update_family_stmt->execute();

            // 5. Create user account
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

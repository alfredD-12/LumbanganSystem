<?php

class EmailVerification {
    private $conn;
    private $table = 'email_verifications';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new email verification record with temporary registration data
     */
    public function createVerification($email, $registrationData) {
        // Generate 6-digit code
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        
        // Store registration data as JSON
        $personData = json_encode([
            'first_name' => $registrationData['first_name'],
            'last_name' => $registrationData['last_name'],
            'middle_name' => $registrationData['middle_name'] ?? '',
            'suffix' => $registrationData['suffix'] ?? null,
            'sex' => $registrationData['sex'] ?? null,
            'birthdate' => $registrationData['birthdate'] ?? null,
            'marital_status' => $registrationData['marital_status'] ?? 'Single'
        ]);
        
        $userData = json_encode([
            'username' => $registrationData['username'],
            'email' => $email,
            'mobile' => $registrationData['mobile'] ?? '',
            'password_hash' => $registrationData['password_hash']
        ]);
        
        // Delete any existing pending verifications for this email
        $this->deletePendingByEmail($email);
        
        // Insert new verification
        $query = "INSERT INTO " . $this->table . " 
                  (email, code, token, person_data, user_data, expires_at) 
                  VALUES (:email, :code, :token, :person_data, :user_data, DATE_ADD(NOW(), INTERVAL 1 HOUR))";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':person_data', $personData);
        $stmt->bindParam(':user_data', $userData);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'code' => $code,
                'token' => $token,
                'email' => $email
            ];
        }
        
        return ['success' => false];
    }

    /**
     * Verify the code entered by user
     */
    public function verifyCode($email, $code) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE email = :email 
                  AND code = :code 
                  AND verified_at IS NULL 
                  AND expires_at > NOW()
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        }
        
        return null;
    }

    /**
     * Mark verification as complete
     */
    public function markAsVerified($token) {
        $query = "UPDATE " . $this->table . " 
                  SET verified_at = NOW() 
                  WHERE token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        
        return $stmt->execute();
    }

    /**
     * Get verification by token
     */
    public function getByToken($token) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE token = :token 
                  AND verified_at IS NULL 
                  AND expires_at > NOW()
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Delete pending verifications for an email
     */
    private function deletePendingByEmail($email) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE email = :email 
                  AND verified_at IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }

    /**
     * Check if email has pending verification
     */
    public function hasPendingVerification($email) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE email = :email 
                  AND verified_at IS NULL 
                  AND expires_at > NOW()
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Clean up expired verifications (optional - can be run via cron)
     */
    public function cleanupExpired() {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE expires_at < NOW() 
                  OR (verified_at IS NOT NULL AND verified_at < DATE_SUB(NOW(), INTERVAL 24 HOUR))";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}

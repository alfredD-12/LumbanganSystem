<?php

require_once dirname(__DIR__) . '/services/SystemClock.php';

class EmailVerification {
    private $conn;
    private $table = 'email_verifications';
    private $clock;

    public function __construct($db, ClockInterface $clock = null) {
        $this->conn = $db;
        $this->clock = $clock ?: new SystemClock();
    }

    /**
     * Create a new email verification record with temporary registration data
     */
    public function createVerification($identifier, $registrationData) {
        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        $createdAt = $this->clock->now()->format('Y-m-d H:i:s');
        $expiresAt = $this->clock->now()
            ->add(new DateInterval('PT1H'))
            ->format('Y-m-d H:i:s');
        
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
            'email' => $registrationData['email'] ?? '',
            'mobile' => $registrationData['mobile'] ?? '',
            'password_hash' => $registrationData['password_hash']
        ]);
        
        // Delete any existing pending verifications for this identifier
        $this->deletePendingByIdentifier($identifier);
        
        // Insert new verification
        $query = "INSERT INTO " . $this->table . " 
                  (email, code, token, person_data, user_data, created_at, expires_at) 
                  VALUES (:identifier, :code, :token, :person_data, :user_data, :created_at, :expires_at)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':person_data', $personData);
        $stmt->bindParam(':user_data', $userData);
        $stmt->bindParam(':created_at', $createdAt);
        $stmt->bindParam(':expires_at', $expiresAt);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'code' => $code,
                'token' => $token,
                'identifier' => $identifier
            ];
        }
        
        return ['success' => false];
    }

    /**
     * Verify the code entered by user
     */
    public function verifyCode($identifier, $code) {
        $now = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE email = :identifier 
                  AND code = :code 
                  AND verified_at IS NULL 
                  AND expires_at > :now
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':now', $now);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Mark verification as complete
     */
    public function markAsVerified($token) {
        $verifiedAt = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "UPDATE " . $this->table . " 
                  SET verified_at = :verified_at 
                  WHERE token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':verified_at', $verifiedAt);
        
        return $stmt->execute();
    }

    /**
     * Get verification by token
     */
    public function getByToken($token) {
        $now = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE token = :token 
                  AND verified_at IS NULL 
                  AND expires_at > :now
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':now', $now);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Delete pending verifications for an email
     */
    private function deletePendingByIdentifier($identifier) {
        // Delete ALL verification records for this email (pending AND verified)
        // so old verified rows don't cause a duplicate-entry error on re-registration attempts
        $query = "DELETE FROM " . $this->table . " 
                  WHERE email = :identifier";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->execute();
    }

    /**
     * Check if email has pending verification
     */
    public function hasPendingVerification($identifier) {
        $now = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE email = :identifier 
                  AND verified_at IS NULL 
                  AND expires_at > :now
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->bindParam(':now', $now);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function getPendingVerification($identifier)
    {
        $now = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "SELECT *
                  FROM " . $this->table . "
                  WHERE email = :identifier
                    AND verified_at IS NULL
                    AND expires_at > :now
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':identifier', $identifier);
        $stmt->bindParam(':now', $now);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Clean up expired verifications (optional - can be run via cron)
     */
    public function cleanupExpired() {
        $now = $this->clock->now()->format('Y-m-d H:i:s');
        $verifiedCutoff = $this->clock->now()
            ->sub(new DateInterval('PT24H'))
            ->format('Y-m-d H:i:s');
        $query = "DELETE FROM " . $this->table . " 
                  WHERE expires_at < :now
                  OR (verified_at IS NOT NULL AND verified_at < :verified_cutoff)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':now', $now);
        $stmt->bindParam(':verified_cutoff', $verifiedCutoff);
        return $stmt->execute();
    }
}

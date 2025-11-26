<?php
class PasswordReset {
    private $conn;
    private $table = "password_resets";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a password reset token
     */
    public function createToken($user_id, $email) {
        // Generate a 6-digit code for simplicity
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = bin2hex(random_bytes(32));
        
        // Use database time instead of PHP time to avoid timezone issues
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, email, code, token, expires_at) 
                  VALUES (:user_id, :email, :code, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))
                  ON DUPLICATE KEY UPDATE 
                    code = :code,
                    token = :token,
                    expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR),
                    used_at = NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':token', $token);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'code' => $code,
                'token' => $token
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create reset token'
        ];
    }

    /**
     * Verify reset code
     */
    public function verifyCode($email, $code) {
        $query = "SELECT id, user_id, code, token, expires_at, used_at 
                  FROM " . $this->table . " 
                  WHERE email = :email 
                  AND code = :code 
                  AND used_at IS NULL 
                  AND expires_at > NOW()
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':code', $code);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mark reset as used
     */
    public function markAsUsed($reset_id) {
        $query = "UPDATE " . $this->table . " 
                  SET used_at = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $reset_id);

        return $stmt->execute();
    }

    /**
     * Check if reset token exists and is valid
     */
    public function getByToken($token) {
        $query = "SELECT id, user_id, email, code, expires_at, used_at 
                  FROM " . $this->table . " 
                  WHERE token = :token 
                  AND used_at IS NULL 
                  AND expires_at > NOW()
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Delete expired tokens
     */
    public function cleanupExpiredTokens() {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE expires_at < NOW() 
                  AND used_at IS NOT NULL";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}

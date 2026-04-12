<?php

require_once dirname(__DIR__) . '/services/SystemClock.php';

class PasswordReset
{
    private $conn;
    private $table = 'password_resets';
    private $clock;

    public function __construct($db, ClockInterface $clock = null)
    {
        $this->conn = $db;
        $this->clock = $clock ?: new SystemClock();
    }

    public function createToken($user_id, $email)
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = bin2hex(random_bytes(32));
        $expiresAt = $this->clock->now()
            ->add(new DateInterval('PT' . PASSWORD_RESET_TOKEN_EXPIRY_MINUTES . 'M'))
            ->format('Y-m-d H:i:s');
        $createdAt = $this->clock->now()->format('Y-m-d H:i:s');

        $existing = $this->conn->prepare("SELECT id FROM {$this->table} WHERE email = :email LIMIT 1");
        $existing->bindParam(':email', $email);
        $existing->execute();
        $row = $existing->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $query = "UPDATE {$this->table}
                      SET user_id = :user_id,
                          code = :code,
                          token = :token,
                          expires_at = :expires_at,
                          used_at = NULL
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $row['id']);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expiresAt);
        } else {
            $query = "INSERT INTO {$this->table}
                      (user_id, email, code, token, expires_at, created_at)
                      VALUES (:user_id, :email, :code, :token, :expires_at, :created_at)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->bindParam(':created_at', $createdAt);
        }

        if (!$stmt->execute()) {
            return [
                'success' => false,
                'message' => 'Failed to create reset token',
            ];
        }

        return [
            'success' => true,
            'code' => $code,
            'token' => $token,
        ];
    }

    public function verifyCode($email, $code)
    {
        $nowSql = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "SELECT id, user_id, code, token, expires_at, used_at
                  FROM {$this->table}
                  WHERE email = :email
                    AND code = :code
                    AND used_at IS NULL
                    AND expires_at > :now_sql
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':now_sql', $nowSql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markAsUsed($reset_id)
    {
        $usedAt = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "UPDATE {$this->table} SET used_at = :used_at WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $reset_id);
        $stmt->bindParam(':used_at', $usedAt);

        return $stmt->execute();
    }

    public function getByToken($token)
    {
        $nowSql = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "SELECT id, user_id, email, code, expires_at, used_at
                  FROM {$this->table}
                  WHERE token = :token
                    AND used_at IS NULL
                    AND expires_at > :now_sql
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':now_sql', $nowSql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cleanupExpiredTokens()
    {
        $cutoff = $this->clock->now()->format('Y-m-d H:i:s');
        $query = "DELETE FROM {$this->table}
                  WHERE expires_at < :cutoff
                    AND used_at IS NOT NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cutoff', $cutoff);
        return $stmt->execute();
    }
}

<?php

class RateLimitService
{
        private $conn;
        private $table = 'ip_rate_limits';

        public function __construct($db)
        {
                $this->conn = $db;
        }

        public function isWhitelistedIp($ipAddress)
        {
                $whitelistPath = dirname(__DIR__) . '/config/ip_whitelist.php';
                if (!file_exists($whitelistPath)) {
                        return false;
                }

                $whitelist = require $whitelistPath;
                if (!is_array($whitelist)) {
                        return false;
                }

                return in_array($ipAddress, $whitelist, true);
        }

        public function checkIpRateLimit($ipAddress)
        {
                $maxAttempts = defined('RATE_LIMIT_IP_MAX_ATTEMPTS') ? (int) RATE_LIMIT_IP_MAX_ATTEMPTS : 5;
                $windowMinutes = defined('RATE_LIMIT_IP_WINDOW_MINUTES') ? (int) RATE_LIMIT_IP_WINDOW_MINUTES : 1;

                if ($this->isWhitelistedIp($ipAddress)) {
                        return [
                                'blocked' => false,
                                'retry_after' => 0,
                                'attempt_count' => 0
                        ];
                }

                $stmt = $this->conn->prepare("SELECT attempt_count, window_start FROM {$this->table} WHERE ip_address = :ip LIMIT 1");
                $stmt->bindValue(':ip', $ipAddress);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                        return [
                                'blocked' => false,
                                'retry_after' => 0,
                                'attempt_count' => 0
                        ];
                }

                $windowStart = strtotime($row['window_start']);
                $windowEnd = $windowStart + ($windowMinutes * 60);
                $remaining = max(0, $windowEnd - time());

                if (time() >= $windowEnd) {
                        return [
                                'blocked' => false,
                                'retry_after' => 0,
                                'attempt_count' => 0
                        ];
                }

                $attemptCount = (int) $row['attempt_count'];
                return [
                        'blocked' => $attemptCount >= $maxAttempts,
                        'retry_after' => $attemptCount >= $maxAttempts ? $remaining : 0,
                        'attempt_count' => $attemptCount
                ];
        }

        public function recordAttempt($ipAddress)
        {
                $windowMinutes = defined('RATE_LIMIT_IP_WINDOW_MINUTES') ? (int) RATE_LIMIT_IP_WINDOW_MINUTES : 1;

                if ($this->isWhitelistedIp($ipAddress)) {
                        return;
                }

                $stmt = $this->conn->prepare("SELECT id, attempt_count, window_start FROM {$this->table} WHERE ip_address = :ip LIMIT 1");
                $stmt->bindValue(':ip', $ipAddress);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$row) {
                        $insert = $this->conn->prepare("INSERT INTO {$this->table} (ip_address, attempt_count, window_start, last_attempt_at) VALUES (:ip, 1, NOW(), NOW())");
                        $insert->bindValue(':ip', $ipAddress);
                        $insert->execute();
                        return;
                }

                $windowStart = strtotime($row['window_start']);
                $windowEnd = $windowStart + ($windowMinutes * 60);

                if (time() >= $windowEnd) {
                        $reset = $this->conn->prepare("UPDATE {$this->table} SET attempt_count = 1, window_start = NOW(), last_attempt_at = NOW() WHERE id = :id");
                        $reset->bindValue(':id', $row['id'], PDO::PARAM_INT);
                        $reset->execute();
                        return;
                }

                $update = $this->conn->prepare("UPDATE {$this->table} SET attempt_count = attempt_count + 1, last_attempt_at = NOW() WHERE id = :id");
                $update->bindValue(':id', $row['id'], PDO::PARAM_INT);
                $update->execute();
        }

        public function cleanupOldRecords()
        {
                $delete = $this->conn->prepare("DELETE FROM {$this->table} WHERE window_start < (NOW() - INTERVAL 1 HOUR)");
                $delete->execute();
        }
}

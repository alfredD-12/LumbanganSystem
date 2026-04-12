<?php

require_once dirname(__DIR__) . '/services/SystemClock.php';

class RateLimitService
{
    private $conn;
    private $table = 'ip_rate_limits';
    private $clock;

    public function __construct($db, ClockInterface $clock = null)
    {
        $this->conn = $db;
        $this->clock = $clock ?: new SystemClock();
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
                'attempt_count' => 0,
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
                'attempt_count' => 0,
            ];
        }

        $now = $this->clock->now();
        $windowStart = strtotime((string) $row['window_start']);
        $windowEnd = $windowStart + ($windowMinutes * 60);
        $remaining = max(0, $windowEnd - $now->getTimestamp());

        if ($now->getTimestamp() >= $windowEnd) {
            return [
                'blocked' => false,
                'retry_after' => 0,
                'attempt_count' => 0,
            ];
        }

        $attemptCount = (int) $row['attempt_count'];
        return [
            'blocked' => $attemptCount >= $maxAttempts,
            'retry_after' => $attemptCount >= $maxAttempts ? $remaining : 0,
            'attempt_count' => $attemptCount,
        ];
    }

    public function recordFailure($ipAddress)
    {
        $windowMinutes = defined('RATE_LIMIT_IP_WINDOW_MINUTES') ? (int) RATE_LIMIT_IP_WINDOW_MINUTES : 1;
        $now = $this->clock->now();
        $nowSql = $now->format('Y-m-d H:i:s');

        if ($this->isWhitelistedIp($ipAddress)) {
            return;
        }

        $stmt = $this->conn->prepare("SELECT id, attempt_count, window_start FROM {$this->table} WHERE ip_address = :ip LIMIT 1");
        $stmt->bindValue(':ip', $ipAddress);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $insert = $this->conn->prepare(
                "INSERT INTO {$this->table} (ip_address, attempt_count, window_start, last_attempt_at)
                 VALUES (:ip, 1, :window_start, :last_attempt_at)"
            );
            $insert->bindValue(':ip', $ipAddress);
            $insert->bindValue(':window_start', $nowSql);
            $insert->bindValue(':last_attempt_at', $nowSql);
            $insert->execute();
            return;
        }

        $windowStart = strtotime((string) $row['window_start']);
        $windowEnd = $windowStart + ($windowMinutes * 60);

        if ($now->getTimestamp() >= $windowEnd) {
            $reset = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET attempt_count = 1, window_start = :window_start, last_attempt_at = :last_attempt_at
                 WHERE id = :id"
            );
            $reset->bindValue(':window_start', $nowSql);
            $reset->bindValue(':last_attempt_at', $nowSql);
            $reset->bindValue(':id', $row['id'], PDO::PARAM_INT);
            $reset->execute();
            return;
        }

        $update = $this->conn->prepare(
            "UPDATE {$this->table}
             SET attempt_count = attempt_count + 1, last_attempt_at = :last_attempt_at
             WHERE id = :id"
        );
        $update->bindValue(':last_attempt_at', $nowSql);
        $update->bindValue(':id', $row['id'], PDO::PARAM_INT);
        $update->execute();
    }

    public function recordAttempt($ipAddress)
    {
        $this->recordFailure($ipAddress);
    }

    public function cleanupOldRecords()
    {
        $cutoff = $this->clock->now()->sub(new DateInterval('PT1H'))->format('Y-m-d H:i:s');
        $delete = $this->conn->prepare("DELETE FROM {$this->table} WHERE window_start < :cutoff");
        $delete->bindValue(':cutoff', $cutoff);
        $delete->execute();
    }
}

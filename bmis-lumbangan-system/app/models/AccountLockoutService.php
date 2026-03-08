<?php

class AccountLockoutService
{
        private $conn;
        private $table = 'account_lockouts';

        public function __construct($db)
        {
                $this->conn = $db;
        }

        public function isAccountLocked($username)
        {
                $stmt = $this->conn->prepare("SELECT locked_until FROM {$this->table} WHERE username = :username LIMIT 1");
                $stmt->bindValue(':username', $username);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row || empty($row['locked_until'])) {
                        return [
                                'locked' => false,
                                'retry_after' => 0
                        ];
                }

                $lockedUntil = strtotime($row['locked_until']);
                $retryAfter = $lockedUntil - time();
                if ($retryAfter <= 0) {
                        $this->clearExpiredLock($username);
                        return [
                                'locked' => false,
                                'retry_after' => 0
                        ];
                }

                return [
                        'locked' => true,
                        'retry_after' => $retryAfter
                ];
        }

        public function recordFailure($username)
        {
                $threshold = defined('ACCOUNT_LOCKOUT_THRESHOLD') ? (int) ACCOUNT_LOCKOUT_THRESHOLD : 5;
                $baseMinutes = defined('ACCOUNT_LOCKOUT_BASE_MINUTES') ? (int) ACCOUNT_LOCKOUT_BASE_MINUTES : 15;
                $multiplier = defined('ACCOUNT_LOCKOUT_BACKOFF_MULTIPLIER') ? (int) ACCOUNT_LOCKOUT_BACKOFF_MULTIPLIER : 2;
                $maxMinutes = defined('ACCOUNT_LOCKOUT_MAX_MINUTES') ? (int) ACCOUNT_LOCKOUT_MAX_MINUTES : 120;

                $stmt = $this->conn->prepare("SELECT id, consecutive_failures, lockout_count, locked_until FROM {$this->table} WHERE username = :username LIMIT 1");
                $stmt->bindValue(':username', $username);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$row) {
                        $insert = $this->conn->prepare("INSERT INTO {$this->table} (username, consecutive_failures, last_failure_at, created_at, updated_at) VALUES (:username, 1, NOW(), NOW(), NOW())");
                        $insert->bindValue(':username', $username);
                        $insert->execute();
                        return [
                                'locked' => false,
                                'retry_after' => 0,
                                'consecutive_failures' => 1,
                                'attempts_remaining' => max(0, $threshold - 1)
                        ];
                }

                $isCurrentlyLocked = !empty($row['locked_until']) && strtotime($row['locked_until']) > time();
                if ($isCurrentlyLocked) {
                        return [
                                'locked' => true,
                                'retry_after' => strtotime($row['locked_until']) - time(),
                                'consecutive_failures' => (int) $row['consecutive_failures'],
                                'attempts_remaining' => 0
                        ];
                }

                $newFailures = (int) $row['consecutive_failures'] + 1;

                if ($newFailures >= $threshold) {
                        $newLockoutCount = (int) $row['lockout_count'] + 1;
                        $minutes = (int) min($maxMinutes, $baseMinutes * pow($multiplier, max(0, $newLockoutCount - 1)));

                        $lock = $this->conn->prepare(
                                "UPDATE {$this->table}
                 SET consecutive_failures = 0,
                     lockout_count = :lockout_count,
                     locked_until = DATE_ADD(NOW(), INTERVAL :minutes MINUTE),
                     last_failure_at = NOW(),
                     updated_at = NOW()
                 WHERE id = :id"
                        );
                        $lock->bindValue(':lockout_count', $newLockoutCount, PDO::PARAM_INT);
                        $lock->bindValue(':minutes', $minutes, PDO::PARAM_INT);
                        $lock->bindValue(':id', $row['id'], PDO::PARAM_INT);
                        $lock->execute();

                        return [
                                'locked' => true,
                                'retry_after' => $minutes * 60,
                                'consecutive_failures' => 0,
                                'attempts_remaining' => 0,
                                'lockout_minutes' => $minutes
                        ];
                }

                $update = $this->conn->prepare(
                        "UPDATE {$this->table}
             SET consecutive_failures = :failures,
                 last_failure_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id"
                );
                $update->bindValue(':failures', $newFailures, PDO::PARAM_INT);
                $update->bindValue(':id', $row['id'], PDO::PARAM_INT);
                $update->execute();

                return [
                        'locked' => false,
                        'retry_after' => 0,
                        'consecutive_failures' => $newFailures,
                        'attempts_remaining' => max(0, $threshold - $newFailures)
                ];
        }

        public function recordSuccess($username)
        {
                $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE username = :username LIMIT 1");
                $stmt->bindValue(':username', $username);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                        $update = $this->conn->prepare(
                                "UPDATE {$this->table}
                 SET consecutive_failures = 0,
                     locked_until = NULL,
                     last_success_at = NOW(),
                     updated_at = NOW()
                 WHERE id = :id"
                        );
                        $update->bindValue(':id', $row['id'], PDO::PARAM_INT);
                        $update->execute();
                        return;
                }

                $insert = $this->conn->prepare(
                        "INSERT INTO {$this->table} (username, consecutive_failures, lockout_count, last_success_at, created_at, updated_at)
             VALUES (:username, 0, 0, NOW(), NOW(), NOW())"
                );
                $insert->bindValue(':username', $username);
                $insert->execute();
        }

        private function clearExpiredLock($username)
        {
                $update = $this->conn->prepare(
                        "UPDATE {$this->table}
             SET locked_until = NULL,
                 consecutive_failures = 0,
                 updated_at = NOW()
             WHERE username = :username"
                );
                $update->bindValue(':username', $username);
                $update->execute();
        }
}

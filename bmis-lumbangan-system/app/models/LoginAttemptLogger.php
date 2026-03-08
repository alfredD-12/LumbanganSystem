<?php

class LoginAttemptLogger
{
        private $conn;
        private $table = 'login_attempts';

        public function __construct($db)
        {
                $this->conn = $db;
        }

        public function logAttempt($username, $ipAddress, $userAgent, $result, $failureReason = null)
        {
                $geoHint = $this->buildGeoHint();

                $stmt = $this->conn->prepare(
                        "INSERT INTO {$this->table}
            (username, ip_address, user_agent, attempt_result, failure_reason, geolocation_hint, attempted_at)
            VALUES (:username, :ip_address, :user_agent, :attempt_result, :failure_reason, :geolocation_hint, NOW())"
                );
                $stmt->bindValue(':username', $username);
                $stmt->bindValue(':ip_address', $ipAddress);
                $stmt->bindValue(':user_agent', $this->truncate($userAgent, 255));
                $stmt->bindValue(':attempt_result', $result);
                $stmt->bindValue(':failure_reason', $failureReason);
                $stmt->bindValue(':geolocation_hint', $geoHint);
                $stmt->execute();
        }

        public function countRecentFailuresByIp($ipAddress, $minutes = 5)
        {
                $stmt = $this->conn->prepare(
                        "SELECT COUNT(*) AS failure_count
             FROM {$this->table}
             WHERE ip_address = :ip
               AND attempt_result = 'failure'
               AND attempted_at >= (NOW() - INTERVAL :minutes MINUTE)"
                );
                $stmt->bindValue(':ip', $ipAddress);
                $stmt->bindValue(':minutes', (int) $minutes, PDO::PARAM_INT);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int) ($row['failure_count'] ?? 0);
        }

        public function countRecentFailuresByUsername($username, $minutes = 30)
        {
                $stmt = $this->conn->prepare(
                        "SELECT COUNT(*) AS failure_count
             FROM {$this->table}
             WHERE username = :username
               AND attempt_result = 'failure'
               AND attempted_at >= (NOW() - INTERVAL :minutes MINUTE)"
                );
                $stmt->bindValue(':username', $username);
                $stmt->bindValue(':minutes', (int) $minutes, PDO::PARAM_INT);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int) ($row['failure_count'] ?? 0);
        }

        public function getUsernameWindowRetryAfter($username, $minutes = 15)
        {
                $stmt = $this->conn->prepare(
                        "SELECT attempted_at
             FROM {$this->table}
             WHERE username = :username
               AND attempt_result = 'failure'
               AND attempted_at >= (NOW() - INTERVAL :minutes MINUTE)
             ORDER BY attempted_at ASC
             LIMIT 1"
                );
                $stmt->bindValue(':username', $username);
                $stmt->bindValue(':minutes', (int) $minutes, PDO::PARAM_INT);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                        return 0;
                }

                $earliest = strtotime($row['attempted_at']);
                $windowEnd = $earliest + ((int) $minutes * 60);
                return max(0, $windowEnd - time());
        }

        public function countDistinctFailedIpByUsername($username, $minutes = 10)
        {
                $stmt = $this->conn->prepare(
                        "SELECT COUNT(DISTINCT ip_address) AS ip_count
             FROM {$this->table}
             WHERE username = :username
               AND attempt_result = 'failure'
               AND attempted_at >= (NOW() - INTERVAL :minutes MINUTE)"
                );
                $stmt->bindValue(':username', $username);
                $stmt->bindValue(':minutes', (int) $minutes, PDO::PARAM_INT);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int) ($row['ip_count'] ?? 0);
        }

        private function buildGeoHint()
        {
                $country = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['GEOIP_COUNTRY_CODE'] ?? '';
                if (!empty($country)) {
                        return $this->truncate($country, 100);
                }

                return null;
        }

        private function truncate($value, $maxLen)
        {
                $value = (string) $value;
                if (strlen($value) <= $maxLen) {
                        return $value;
                }

                return substr($value, 0, $maxLen);
        }
}

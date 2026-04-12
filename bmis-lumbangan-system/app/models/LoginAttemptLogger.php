<?php

require_once dirname(__DIR__) . '/services/SystemClock.php';

class LoginAttemptLogger
{
    private $conn;
    private $table = 'login_attempts';
    private $clock;

    public function __construct($db, ClockInterface $clock = null)
    {
        $this->conn = $db;
        $this->clock = $clock ?: new SystemClock();
    }

    public function logAttempt($identifier, $ipAddress, $userAgent, $result, $failureReason = null, $scope = 'login')
    {
        $geoHint = $this->buildGeoHint();
        $storageIdentifier = $this->normalizeIdentifier($identifier, $scope);
        $nowSql = $this->clock->now()->format('Y-m-d H:i:s');

        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table}
             (username, ip_address, user_agent, attempt_result, failure_reason, geolocation_hint, attempted_at)
             VALUES (:username, :ip_address, :user_agent, :attempt_result, :failure_reason, :geolocation_hint, :attempted_at)"
        );
        $stmt->bindValue(':username', $storageIdentifier);
        $stmt->bindValue(':ip_address', $ipAddress);
        $stmt->bindValue(':user_agent', $this->truncate($userAgent, 255));
        $stmt->bindValue(':attempt_result', $result);
        $stmt->bindValue(':failure_reason', $failureReason);
        $stmt->bindValue(':geolocation_hint', $geoHint);
        $stmt->bindValue(':attempted_at', $nowSql);
        $stmt->execute();
    }

    public function countRecentAttemptsByIp($ipAddress, $minutes = 5, $scope = null)
    {
        return $this->countRecentByIp($ipAddress, $minutes, null, $scope);
    }

    public function countRecentFailuresByIp($ipAddress, $minutes = 5, $scope = null)
    {
        return $this->countRecentByIp($ipAddress, $minutes, 'failure', $scope);
    }

    public function countRecentFailuresByUsername($username, $minutes = 30)
    {
        return $this->countRecentFailuresByIdentifier($username, $minutes, 'login');
    }

    public function countRecentFailuresByIdentifier($identifier, $minutes = 30, $scope = 'login')
    {
        return $this->countRecentByIdentifier($identifier, $minutes, 'failure', $scope);
    }

    public function countRecentSuccessesByIdentifier($identifier, $minutes = 30, $scope = 'login')
    {
        return $this->countRecentByIdentifier($identifier, $minutes, 'success', $scope);
    }

    public function countRecentAttemptsByIdentifier($identifier, $minutes = 30, $scope = 'login')
    {
        return $this->countRecentByIdentifier($identifier, $minutes, null, $scope);
    }

    public function getUsernameWindowRetryAfter($username, $minutes = 15)
    {
        return $this->getIdentifierWindowRetryAfter($username, $minutes, 'failure', 'login');
    }

    public function getIdentifierWindowRetryAfter($identifier, $minutes = 15, $metric = 'failure', $scope = 'login')
    {
        $storageIdentifier = $this->normalizeIdentifier($identifier, $scope);
        $windowStart = $this->clock->now()->sub(new DateInterval('PT' . (int) $minutes . 'M'))->format('Y-m-d H:i:s');

        $sql = "SELECT attempted_at
                FROM {$this->table}
                WHERE username = :username
                  AND attempted_at >= :window_start";

        if ($metric === 'failure') {
            $sql .= " AND attempt_result = 'failure'";
        }

        $sql .= " ORDER BY attempted_at ASC LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':username', $storageIdentifier);
        $stmt->bindValue(':window_start', $windowStart);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 0;
        }

        $earliest = strtotime((string) $row['attempted_at']);
        $windowEnd = $earliest + ((int) $minutes * 60);
        return max(0, $windowEnd - $this->clock->now()->getTimestamp());
    }

    public function countDistinctFailedIpByUsername($username, $minutes = 10)
    {
        return $this->countDistinctFailedIpByIdentifier($username, $minutes, 'login');
    }

    public function countDistinctFailedIpByIdentifier($identifier, $minutes = 10, $scope = 'login')
    {
        $storageIdentifier = $this->normalizeIdentifier($identifier, $scope);
        $windowStart = $this->clock->now()->sub(new DateInterval('PT' . (int) $minutes . 'M'))->format('Y-m-d H:i:s');

        $stmt = $this->conn->prepare(
            "SELECT COUNT(DISTINCT ip_address) AS ip_count
             FROM {$this->table}
             WHERE username = :username
               AND attempt_result = 'failure'
               AND attempted_at >= :window_start"
        );
        $stmt->bindValue(':username', $storageIdentifier);
        $stmt->bindValue(':window_start', $windowStart);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['ip_count'] ?? 0);
    }

    public function countRecentSuccessesByIp($ipAddress, $minutes = 5, $scope = null)
    {
        return $this->countRecentByIp($ipAddress, $minutes, 'success', $scope);
    }

    private function countRecentByIdentifier($identifier, $minutes, $result = null, $scope = 'login')
    {
        $storageIdentifier = $this->normalizeIdentifier($identifier, $scope);
        $windowStart = $this->clock->now()->sub(new DateInterval('PT' . (int) $minutes . 'M'))->format('Y-m-d H:i:s');

        $sql = "SELECT COUNT(*) AS event_count
                FROM {$this->table}
                WHERE username = :username
                  AND attempted_at >= :window_start";

        if ($result !== null) {
            $sql .= " AND attempt_result = :attempt_result";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':username', $storageIdentifier);
        $stmt->bindValue(':window_start', $windowStart);

        if ($result !== null) {
            $stmt->bindValue(':attempt_result', $result);
        }

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['event_count'] ?? 0);
    }

    private function countRecentByIp($ipAddress, $minutes, $result = null, $scope = null)
    {
        $windowStart = $this->clock->now()->sub(new DateInterval('PT' . (int) $minutes . 'M'))->format('Y-m-d H:i:s');
        $sql = "SELECT COUNT(*) AS event_count
                FROM {$this->table}
                WHERE ip_address = :ip
                  AND attempted_at >= :window_start";

        if ($result !== null) {
            $sql .= " AND attempt_result = :attempt_result";
        }

        if ($scope !== null && $scope !== '') {
            $sql .= " AND username LIKE :scope_prefix";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':ip', $ipAddress);
        $stmt->bindValue(':window_start', $windowStart);

        if ($result !== null) {
            $stmt->bindValue(':attempt_result', $result);
        }

        if ($scope !== null && $scope !== '') {
            $stmt->bindValue(':scope_prefix', $scope . ':%');
        }

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['event_count'] ?? 0);
    }

    private function normalizeIdentifier($identifier, $scope)
    {
        $value = trim((string) $identifier);
        $scope = trim((string) $scope);

        if ($value === '') {
            return $scope . ':anonymous';
        }

        if ($scope !== '' && str_starts_with($value, $scope . ':')) {
            return strtolower($value);
        }

        return strtolower($scope . ':' . $value);
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

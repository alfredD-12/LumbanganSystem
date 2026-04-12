<?php

require_once __DIR__ . '/SystemClock.php';

class SecurityCleanupService
{
    private $conn;
    private $clock;

    public function __construct($db, ClockInterface $clock = null)
    {
        $this->conn = $db;
        $this->clock = $clock ?: new SystemClock();
    }

    public function cleanup()
    {
        $now = $this->clock->now();

        $queries = [
            [
                'sql' => "DELETE FROM ip_rate_limits WHERE window_start < :cutoff",
                'params' => [':cutoff' => $now->sub(new DateInterval('PT1H'))->format('Y-m-d H:i:s')],
            ],
            [
                'sql' => "DELETE FROM login_attempts WHERE attempted_at < :cutoff",
                'params' => [':cutoff' => $now->sub(new DateInterval('P90D'))->format('Y-m-d H:i:s')],
            ],
            [
                'sql' => "DELETE FROM brute_force_alerts WHERE alert_sent_at < :cutoff",
                'params' => [':cutoff' => $now->sub(new DateInterval('P90D'))->format('Y-m-d H:i:s')],
            ],
            [
                'sql' => "DELETE FROM account_lockouts
                          WHERE locked_until IS NULL
                            AND last_failure_at IS NOT NULL
                            AND last_failure_at < :cutoff",
                'params' => [':cutoff' => $now->sub(new DateInterval('P90D'))->format('Y-m-d H:i:s')],
            ],
            [
                'sql' => "DELETE FROM password_resets
                          WHERE expires_at < :cutoff
                            AND used_at IS NOT NULL",
                'params' => [':cutoff' => $now->format('Y-m-d H:i:s')],
            ],
        ];

        foreach ($queries as $query) {
            $stmt = $this->conn->prepare($query['sql']);
            foreach ($query['params'] as $name => $value) {
                $stmt->bindValue($name, $value);
            }
            $stmt->execute();
        }
    }
}

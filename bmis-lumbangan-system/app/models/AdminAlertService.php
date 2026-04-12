<?php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/email_config.php';
require_once dirname(__DIR__) . '/services/SystemClock.php';
require_once dirname(__DIR__) . '/services/SecurityAlertServiceInterface.php';

class AdminAlertService implements SecurityAlertServiceInterface
{
    private $conn;
    private $clock;

    public function __construct($db, ClockInterface $clock = null)
    {
        $this->conn = $db;
        $this->clock = $clock ?: new SystemClock();
    }

    public function evaluateAndSend($ipAddress, $target, $scope, LoginAttemptLogger $logger, $justLocked = false)
    {
        $alertThreshold = defined('ADMIN_ALERT_THRESHOLD_IP') ? (int) ADMIN_ALERT_THRESHOLD_IP : 10;
        $distributedIpThreshold = defined('DISTRIBUTED_ATTACK_DISTINCT_IP_THRESHOLD') ? (int) DISTRIBUTED_ATTACK_DISTINCT_IP_THRESHOLD : 5;
        $ipWindowMinutes = 5;
        $identifierWindowMinutes = $this->getIdentifierWindowMinutes($scope);
        $ipFailures = $logger->countRecentFailuresByIp($ipAddress, $ipWindowMinutes);
        $identifierFailures = $logger->countRecentFailuresByIdentifier($target, $identifierWindowMinutes, $scope);
        $distinctIpCount = $logger->countDistinctFailedIpByIdentifier($target, 10, $scope);

        if ($ipFailures >= $alertThreshold) {
            $alertType = 'ip_threshold';
            if ($this->shouldSendAlert($alertType, $ipAddress, 60)) {
                $details = sprintf(
                    '[%s] IP %s reached %d failed attempts in %d minutes. Identifier %s has %d failed attempts in %d minutes.',
                    $scope,
                    $ipAddress,
                    $ipFailures,
                    $ipWindowMinutes,
                    $target,
                    $identifierFailures,
                    $identifierWindowMinutes
                );
                $emailSent = $this->sendAlertEmail($alertType, $ipAddress, $ipFailures, $details);
                $this->logAlert($alertType, $ipAddress, $ipFailures, $emailSent, $details);
            }
        }

        if ($justLocked) {
            $alertType = 'account_lockout';
            if ($this->shouldSendAlert($alertType, $target, 30)) {
                $details = sprintf(
                    '[%s] Identifier %s was temporarily locked after %d failed attempts within %d minutes from IP %s. The triggering IP recorded %d failed attempts in %d minutes.',
                    $scope,
                    $target,
                    $identifierFailures,
                    $identifierWindowMinutes,
                    $ipAddress,
                    $ipFailures,
                    $ipWindowMinutes
                );
                $emailSent = $this->sendAlertEmail($alertType, $target, $identifierFailures, $details);
                $this->logAlert($alertType, $target, $identifierFailures, $emailSent, $details);
            }
        }

        if ($distinctIpCount >= $distributedIpThreshold) {
            $alertType = 'distributed_attack';
            if ($this->shouldSendAlert($alertType, $target, 60)) {
                $details = sprintf(
                    '[%s] Identifier %s received %d failed attempts from %d distinct IPs within 10 minutes.',
                    $scope,
                    $target,
                    $identifierFailures,
                    $distinctIpCount
                );
                $emailSent = $this->sendAlertEmail($alertType, $target, $distinctIpCount, $details);
                $this->logAlert($alertType, $target, $distinctIpCount, $emailSent, $details);
            }
        }
    }

    private function getIdentifierWindowMinutes($scope)
    {
        switch ((string) $scope) {
            case 'registration_request':
                return (int) REGISTRATION_REQUEST_WINDOW_MINUTES;
            case 'registration_verify':
                return (int) REGISTRATION_VERIFY_WINDOW_MINUTES;
            case 'password_reset_request':
                return (int) PASSWORD_RESET_REQUEST_WINDOW_MINUTES;
            case 'password_reset_verify':
                return (int) PASSWORD_RESET_VERIFY_WINDOW_MINUTES;
            case 'password_reset_submit':
                return (int) PASSWORD_RESET_SUBMIT_WINDOW_MINUTES;
            case 'login':
            default:
                return (int) ACCOUNT_RATE_LIMIT_WINDOW_MINUTES;
        }
    }

    private function shouldSendAlert($alertType, $target, $cooldownMinutes)
    {
        $cutoff = $this->clock->now()->sub(new DateInterval('PT' . (int) $cooldownMinutes . 'M'))->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare(
            "SELECT id FROM brute_force_alerts
             WHERE alert_type = :alert_type
               AND target = :target
               AND alert_sent_at >= :cutoff
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->bindValue(':alert_type', $alertType);
        $stmt->bindValue(':target', $target);
        $stmt->bindValue(':cutoff', $cutoff);
        $stmt->execute();

        return !$stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function logAlert($type, $target, $attemptCount, $emailSent, $details)
    {
        $alertSentAt = $this->clock->now()->format('Y-m-d H:i:s');
        $stmt = $this->conn->prepare(
            "INSERT INTO brute_force_alerts (alert_type, target, attempt_count, alert_sent_at, email_sent, details)
             VALUES (:alert_type, :target, :attempt_count, :alert_sent_at, :email_sent, :details)"
        );
        $stmt->bindValue(':alert_type', $type);
        $stmt->bindValue(':target', $target);
        $stmt->bindValue(':attempt_count', (int) $attemptCount, PDO::PARAM_INT);
        $stmt->bindValue(':alert_sent_at', $alertSentAt);
        $stmt->bindValue(':email_sent', $emailSent ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':details', $details);
        $stmt->execute();

        $this->writeLogLine($type, $target, $attemptCount, $details);
    }

    private function writeLogLine($type, $target, $attemptCount, $details)
    {
        if (!is_dir(BMIS_LOG_DIR)) {
            @mkdir(BMIS_LOG_DIR, 0755, true);
        }

        $file = BMIS_LOG_DIR . '/brute_force_alerts.log';
        $line = sprintf(
            "[%s] %s target=%s attempts=%d details=%s\n",
            $this->clock->now()->format('Y-m-d H:i:s'),
            strtoupper($type),
            $target,
            $attemptCount,
            $details
        );
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    private function sendAlertEmail($alertType, $target, $attemptCount, $details)
    {
        $to = SECURITY_ALERT_EMAIL ?: SENDER_EMAIL;
        if ($to === '') {
            return false;
        }

        if (defined('APP_ENV') && APP_ENV === 'testing') {
            return false;
        }

        $subject = '[SECURITY ALERT] Brute-force threshold reached';
        $body = '<h3>Brute-force Alert</h3>'
            . '<p><strong>Type:</strong> ' . htmlspecialchars($alertType, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Target:</strong> ' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Attempts:</strong> ' . (int) $attemptCount . '</p>'
            . '<p><strong>Time:</strong> ' . htmlspecialchars($this->clock->now()->format('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><strong>Details:</strong> ' . htmlspecialchars($details, ENT_QUOTES, 'UTF-8') . '</p>';

        return $this->sendEmail($to, $subject, $body);
    }

    private function sendEmail($to, $subject, $body)
    {
        if (EMAIL_METHOD === 'smtp') {
            return $this->sendEmailViaSMTP($to, $subject, $body);
        }

        return $this->sendEmailViaPHPMail($to, $subject, $body);
    }

    private function sendEmailViaPHPMail($to, $subject, $body)
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= 'From: ' . SENDER_NAME . ' <' . SENDER_EMAIL . ">\r\n";

        return @mail($to, $subject, $body, $headers);
    }

    private function sendEmailViaSMTP($to, $subject, $body)
    {
        try {
            $smtp = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
            if (!$smtp) {
                return $this->sendEmailViaPHPMail($to, $subject, $body);
            }

            fgets($smtp, 1024);
            fputs($smtp, "HELO localhost\r\n");
            fgets($smtp, 1024);

            if (SMTP_SECURE === 'tls') {
                fputs($smtp, "STARTTLS\r\n");
                fgets($smtp, 1024);
                if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($smtp);
                    return $this->sendEmailViaPHPMail($to, $subject, $body);
                }
                fputs($smtp, "HELO localhost\r\n");
                fgets($smtp, 1024);
            }

            fputs($smtp, "AUTH LOGIN\r\n");
            fgets($smtp, 1024);
            fputs($smtp, base64_encode(SMTP_USERNAME) . "\r\n");
            fgets($smtp, 1024);
            fputs($smtp, base64_encode(SMTP_PASSWORD) . "\r\n");
            $response = fgets($smtp, 1024);

            if (strpos((string) $response, '235') === false) {
                fclose($smtp);
                return $this->sendEmailViaPHPMail($to, $subject, $body);
            }

            fputs($smtp, 'MAIL FROM: <' . SENDER_EMAIL . ">\r\n");
            fgets($smtp, 1024);
            fputs($smtp, 'RCPT TO: <' . $to . ">\r\n");
            fgets($smtp, 1024);
            fputs($smtp, "DATA\r\n");
            fgets($smtp, 1024);

            $headers = 'From: ' . SENDER_NAME . ' <' . SENDER_EMAIL . ">\r\n";
            $headers .= 'To: ' . $to . "\r\n";
            $headers .= 'Subject: ' . $subject . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

            fputs($smtp, $headers . $body . "\r\n.\r\n");
            fgets($smtp, 1024);
            fputs($smtp, "QUIT\r\n");
            fclose($smtp);

            return true;
        } catch (Exception $exception) {
            return $this->sendEmailViaPHPMail($to, $subject, $body);
        }
    }
}

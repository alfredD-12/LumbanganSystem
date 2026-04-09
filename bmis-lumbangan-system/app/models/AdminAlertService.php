<?php

class AdminAlertService
{
        private $conn;

        public function __construct($db)
        {
                $this->conn = $db;
        }

        public function evaluateAndSend($ipAddress, $username, LoginAttemptLogger $logger, $justLocked = false)
        {
                $alertThreshold = defined('ADMIN_ALERT_THRESHOLD_IP') ? (int) ADMIN_ALERT_THRESHOLD_IP : 10;
                $distributedIpThreshold = defined('DISTRIBUTED_ATTACK_DISTINCT_IP_THRESHOLD') ? (int) DISTRIBUTED_ATTACK_DISTINCT_IP_THRESHOLD : 5;
                $ipFailures = $logger->countRecentFailuresByIp($ipAddress, 5);

                if ($ipFailures >= $alertThreshold) {
                        $alertType = 'ip_threshold';
                        if ($this->shouldSendAlert($alertType, $ipAddress, 60)) {
                                $details = sprintf('IP %s reached %d failures in 5 minutes.', $ipAddress, $ipFailures);
                                $emailSent = $this->sendAlertEmail($alertType, $ipAddress, $ipFailures, $details);
                                $this->logAlert($alertType, $ipAddress, $ipFailures, $emailSent, $details);
                        }
                }

                if ($justLocked) {
                        $alertType = 'account_lockout';
                        if ($this->shouldSendAlert($alertType, $username, 30)) {
                                $details = sprintf('Username %s was temporarily locked after repeated failures from IP %s.', $username, $ipAddress);
                                $emailSent = $this->sendAlertEmail($alertType, $username, $ipFailures, $details);
                                $this->logAlert($alertType, $username, $ipFailures, $emailSent, $details);
                        }
                }

                $distinctIpCount = $logger->countDistinctFailedIpByUsername($username, 10);
                if ($distinctIpCount >= $distributedIpThreshold) {
                        $alertType = 'distributed_attack';
                        if ($this->shouldSendAlert($alertType, $username, 60)) {
                                $details = sprintf('Username %s received failures from %d distinct IPs within 10 minutes.', $username, $distinctIpCount);
                                $emailSent = $this->sendAlertEmail($alertType, $username, $distinctIpCount, $details);
                                $this->logAlert($alertType, $username, $distinctIpCount, $emailSent, $details);
                        }
                }
        }

        private function shouldSendAlert($alertType, $target, $cooldownMinutes)
        {
                $stmt = $this->conn->prepare(
                        "SELECT id FROM brute_force_alerts
             WHERE alert_type = :alert_type
               AND target = :target
               AND alert_sent_at >= (NOW() - INTERVAL :minutes MINUTE)
             ORDER BY id DESC
             LIMIT 1"
                );
                $stmt->bindValue(':alert_type', $alertType);
                $stmt->bindValue(':target', $target);
                $stmt->bindValue(':minutes', (int) $cooldownMinutes, PDO::PARAM_INT);
                $stmt->execute();

                return !$stmt->fetch(PDO::FETCH_ASSOC);
        }

        private function logAlert($type, $target, $attemptCount, $emailSent, $details)
        {
                $stmt = $this->conn->prepare(
                        "INSERT INTO brute_force_alerts (alert_type, target, attempt_count, alert_sent_at, email_sent, details)
             VALUES (:alert_type, :target, :attempt_count, NOW(), :email_sent, :details)"
                );
                $stmt->bindValue(':alert_type', $type);
                $stmt->bindValue(':target', $target);
                $stmt->bindValue(':attempt_count', (int) $attemptCount, PDO::PARAM_INT);
                $stmt->bindValue(':email_sent', $emailSent ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':details', $details);
                $stmt->execute();

                $this->writeLogLine($type, $target, $attemptCount, $details);
        }

        private function writeLogLine($type, $target, $attemptCount, $details)
        {
                $logDir = dirname(__DIR__) . '/logs';
                if (!is_dir($logDir)) {
                        @mkdir($logDir, 0755, true);
                }

                $file = $logDir . '/brute_force_alerts.log';
                $line = sprintf(
                        "[%s] %s target=%s attempts=%d details=%s\n",
                        date('Y-m-d H:i:s'),
                        strtoupper($type),
                        $target,
                        $attemptCount,
                        $details
                );
                @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        }

        private function sendAlertEmail($alertType, $target, $attemptCount, $details)
        {
                @require_once dirname(__DIR__) . '/config/email_config.php';

                $to = defined('SECURITY_ALERT_EMAIL') ? SECURITY_ALERT_EMAIL : (defined('SENDER_EMAIL') ? SENDER_EMAIL : null);
                if (empty($to)) {
                        return false;
                }

                $subject = '[SECURITY ALERT] Brute-force threshold reached';
                $body = '<h3>Brute-force Alert</h3>'
                        . '<p><strong>Type:</strong> ' . htmlspecialchars($alertType, ENT_QUOTES, 'UTF-8') . '</p>'
                        . '<p><strong>Target:</strong> ' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '</p>'
                        . '<p><strong>Attempts:</strong> ' . (int) $attemptCount . '</p>'
                        . '<p><strong>Time:</strong> ' . date('Y-m-d H:i:s') . '</p>'
                        . '<p><strong>Details:</strong> ' . htmlspecialchars($details, ENT_QUOTES, 'UTF-8') . '</p>';

                return $this->sendEmail($to, $subject, $body);
        }

        private function sendEmail($to, $subject, $body)
        {
                if (defined('EMAIL_METHOD') && EMAIL_METHOD === 'smtp') {
                        return $this->sendEmailViaSMTP($to, $subject, $body);
                }

                return $this->sendEmailViaPHPMail($to, $subject, $body);
        }

        private function sendEmailViaPHPMail($to, $subject, $body)
        {
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: noreply@barangaylumbangan.gov.ph\r\n";

                return @mail($to, $subject, $body, $headers);
        }

        private function sendEmailViaSMTP($to, $subject, $body)
        {
                try {
                        $host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
                        $port = defined('SMTP_PORT') ? SMTP_PORT : 587;
                        $username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
                        $password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
                        $secure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
                        $senderEmail = defined('SENDER_EMAIL') ? SENDER_EMAIL : 'noreply@barangaylumbangan.gov.ph';
                        $senderName = defined('SENDER_NAME') ? SENDER_NAME : 'Barangay Lumbangan';

                        $smtp = fsockopen($host, $port, $errno, $errstr, 10);
                        if (!$smtp) {
                                return $this->sendEmailViaPHPMail($to, $subject, $body);
                        }

                        fgets($smtp, 1024);
                        fputs($smtp, "HELO localhost\r\n");
                        fgets($smtp, 1024);

                        if ($secure === 'tls') {
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
                        fputs($smtp, base64_encode($username) . "\r\n");
                        fgets($smtp, 1024);
                        fputs($smtp, base64_encode($password) . "\r\n");
                        $response = fgets($smtp, 1024);

                        if (strpos($response, '235') === false) {
                                fclose($smtp);
                                return $this->sendEmailViaPHPMail($to, $subject, $body);
                        }

                        fputs($smtp, "MAIL FROM: <{$senderEmail}>\r\n");
                        fgets($smtp, 1024);
                        fputs($smtp, "RCPT TO: <{$to}>\r\n");
                        fgets($smtp, 1024);
                        fputs($smtp, "DATA\r\n");
                        fgets($smtp, 1024);

                        $headers = "From: {$senderName} <{$senderEmail}>\r\n";
                        $headers .= "To: {$to}\r\n";
                        $headers .= "Subject: {$subject}\r\n";
                        $headers .= "MIME-Version: 1.0\r\n";
                        $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

                        fputs($smtp, $headers . $body . "\r\n.\r\n");
                        fgets($smtp, 1024);
                        fputs($smtp, "QUIT\r\n");
                        fclose($smtp);
                        return true;
                } catch (Exception $e) {
                        return $this->sendEmailViaPHPMail($to, $subject, $body);
                }
        }
}

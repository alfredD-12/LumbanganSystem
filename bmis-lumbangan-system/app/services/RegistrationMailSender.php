<?php

require_once __DIR__ . '/../config/email_config.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/RegistrationMailSenderInterface.php';

class RegistrationMailSender implements RegistrationMailSenderInterface
{
    public function sendVerificationCode($email, $firstName, $code)
    {
        if ($email === '') {
            return false;
        }

        $subject = 'Email Verification Code - Barangay Lumbangan';
        $body = $this->getEmailTemplate($firstName, $code);

        if (defined('EMAIL_METHOD') && EMAIL_METHOD === 'smtp') {
            return $this->sendEmailViaSMTP($email, $subject, $body);
        }

        return $this->sendEmailViaPHPMail($email, $subject, $body);
    }

    private function getEmailTemplate($firstName, $code)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: white; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 8px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Email Verification</h1>
                    <p>Barangay Lumbangan Management System</p>
                </div>
                <div class="content">
                    <p>Hello ' . htmlspecialchars((string) $firstName, ENT_QUOTES, 'UTF-8') . ',</p>
                    <p>Thank you for registering! Please use the verification code below to complete your registration:</p>
                    
                    <div class="code-box">
                        <div class="code">' . htmlspecialchars((string) $code, ENT_QUOTES, 'UTF-8') . '</div>
                    </div>
                    
                    <p><strong>This code will expire in 1 hour.</strong></p>
                    <p>If you did not request this registration, please ignore this email.</p>
                    
                    <div class="footer">
                        <p>Barangay Lumbangan, Nasugbu, Batangas</p>
                        <p>This is an automated message, please do not reply.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ';
    }

    private function sendEmailViaPHPMail($to, $subject, $body)
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . (defined('SENDER_EMAIL') ? SENDER_EMAIL : 'noreply@barangaylumbangan.gov.ph') . "\r\n";

        return mail($to, $subject, $body, $headers);
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

            if (strpos((string) $response, '235') === false) {
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
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "\r\n";

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

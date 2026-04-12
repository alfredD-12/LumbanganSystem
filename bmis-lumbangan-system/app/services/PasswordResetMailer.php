<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/PasswordResetMailerInterface.php';

class PasswordResetMailer implements PasswordResetMailerInterface
{
    public function sendResetCode(array $user, $code)
    {
        $email = trim((string) ($user['email'] ?? ''));
        if ($email === '') {
            return false;
        }

        $firstName = $user['first_name'] ?? 'Resident';
        $subject = 'Password Reset Code - Barangay Lumbangan';
        $body = $this->buildEmailTemplate($firstName, $code);

        if (EMAIL_METHOD === 'smtp') {
            return $this->sendViaSmtp($email, $subject, $body);
        }

        return $this->sendViaPhpMail($email, $subject, $body);
    }

    private function sendViaPhpMail($to, $subject, $body)
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= 'From: ' . SENDER_NAME . ' <' . SENDER_EMAIL . ">\r\n";

        return @mail($to, $subject, $body, $headers);
    }

    private function sendViaSmtp($to, $subject, $body)
    {
        try {
            $smtp = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
            if (!$smtp) {
                return $this->sendViaPhpMail($to, $subject, $body);
            }

            fgets($smtp, 1024);
            fputs($smtp, "HELO localhost\r\n");
            fgets($smtp, 1024);

            if (SMTP_SECURE === 'tls') {
                fputs($smtp, "STARTTLS\r\n");
                fgets($smtp, 1024);

                if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($smtp);
                    return $this->sendViaPhpMail($to, $subject, $body);
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
                return $this->sendViaPhpMail($to, $subject, $body);
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
            return $this->sendViaPhpMail($to, $subject, $body);
        }
    }

    private function buildEmailTemplate($firstName, $code)
    {
        $barangayName = 'Barangay Lumbangan';

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .code-box { background: white; border: 2px solid #667eea; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
                .code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 5px; }
                .footer { background: #f0f0f0; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset Request</h1>
                    <p>{$barangayName}</p>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$firstName}</strong>,</p>
                    <p>Use the code below to continue resetting your password:</p>
                    <div class='code-box'>
                        <div class='code'>{$code}</div>
                    </div>
                    <p>This code expires in " . PASSWORD_RESET_TOKEN_EXPIRY_MINUTES . " minutes.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " {$barangayName}, Nasugbu, Batangas. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

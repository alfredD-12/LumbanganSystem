<?php

class CaptchaHelper
{
        public static function shouldRequireCaptcha(LoginAttemptLogger $logger, $username, $ipAddress)
        {
                $triggerThreshold = defined('CAPTCHA_TRIGGER_THRESHOLD') ? (int) CAPTCHA_TRIGGER_THRESHOLD : 3;

                $usernameFailures = $logger->countRecentFailuresByUsername($username, 30);
                $ipFailures = $logger->countRecentFailuresByIp($ipAddress, 30);

                return $usernameFailures >= $triggerThreshold || $ipFailures >= $triggerThreshold;
        }

        public static function verifyToken($token, $ipAddress)
        {
                if (!defined('RECAPTCHA_SECRET_KEY') || RECAPTCHA_SECRET_KEY === '') {
                        return false;
                }

                if (empty($token)) {
                        return false;
                }

                $payload = http_build_query([
                        'secret' => RECAPTCHA_SECRET_KEY,
                        'response' => $token,
                        'remoteip' => $ipAddress
                ]);

                $context = stream_context_create([
                        'http' => [
                                'method' => 'POST',
                                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                                'content' => $payload,
                                'timeout' => 5
                        ]
                ]);

                $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
                if ($result === false) {
                        return false;
                }

                $decoded = json_decode($result, true);
                if (!is_array($decoded) || empty($decoded['success'])) {
                        return false;
                }

                $scoreThreshold = defined('RECAPTCHA_SCORE_THRESHOLD') ? (float) RECAPTCHA_SCORE_THRESHOLD : 0.5;
                $score = isset($decoded['score']) ? (float) $decoded['score'] : 0.0;
                return $score >= $scoreThreshold;
        }
}

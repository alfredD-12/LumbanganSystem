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

        private static function postVerificationRequest($payload)
        {
                // Prefer cURL when available because some PHP environments disable URL fopen wrappers.
                if (function_exists('curl_init')) {
                        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
                        if ($ch === false) {
                                return '';
                        }

                        curl_setopt_array($ch, [
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => $payload,
                                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                                CURLOPT_TIMEOUT => 5,
                                CURLOPT_CONNECTTIMEOUT => 5,
                        ]);

                        $response = curl_exec($ch);
                        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        // Newer PHP versions deprecate explicit curl_close(); releasing the handle is enough.
                        $ch = null;

                        if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                                return $response;
                        }
                }

                $context = stream_context_create([
                        'http' => [
                                'method' => 'POST',
                                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                                'content' => $payload,
                                'timeout' => 5
                        ]
                ]);

                $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
                return $result === false ? '' : $result;
        }

        public static function verifyToken($token, $ipAddress, $expectedAction = 'login')
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

                $result = self::postVerificationRequest($payload);
                if ($result === '') {
                        return false;
                }

                $decoded = json_decode($result, true);
                if (!is_array($decoded) || empty($decoded['success'])) {
                        return false;
                }

                if (!empty($expectedAction) && isset($decoded['action']) && $decoded['action'] !== $expectedAction) {
                        return false;
                }

                $scoreThreshold = defined('RECAPTCHA_SCORE_THRESHOLD') ? (float) RECAPTCHA_SCORE_THRESHOLD : 0.5;
                if (isset($decoded['score'])) {
                        $score = (float) $decoded['score'];
                        return $score >= $scoreThreshold;
                }

                // If no score exists (e.g. checkbox mode), success is enough.
                return true;
        }
}

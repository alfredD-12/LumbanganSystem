<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/CaptchaVerifierInterface.php';

class GoogleCaptchaVerifier implements CaptchaVerifierInterface
{
    public function verify($token, $ipAddress, $expectedAction = 'login')
    {
        if (RECAPTCHA_SECRET_KEY === '' || empty($token)) {
            return false;
        }

        $payload = http_build_query([
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $ipAddress,
        ]);

        $result = $this->postVerificationRequest($payload);
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

        if (isset($decoded['score'])) {
            return (float) $decoded['score'] >= RECAPTCHA_SCORE_THRESHOLD;
        }

        return true;
    }

    private function postVerificationRequest($payload)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
            if ($ch !== false) {
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
                curl_close($ch);

                if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                    return $response;
                }
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 5,
            ],
        ]);

        $result = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        return $result === false ? '' : $result;
    }
}

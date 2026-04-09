<?php

if (PHP_SAPI !== 'cli') {
        exit("Run this script in CLI mode.\n");
}

$options = getopt('', ['url:', 'username::', 'password::', 'attempts::', 'sleep-ms::']);

$url = $options['url'] ?? 'http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/controllers/AuthController.php?action=login';
$username = $options['username'] ?? 'testuser';
$password = $options['password'] ?? 'wrong-password';
$attempts = isset($options['attempts']) ? max(1, (int) $options['attempts']) : 15;
$sleepMs = isset($options['sleep-ms']) ? max(0, (int) $options['sleep-ms']) : 100;

function postLogin($url, $username, $password)
{
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'username' => $username,
                'password' => $password
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
                return [
                        'ok' => false,
                        'http_code' => $httpCode,
                        'error' => $error,
                        'body' => null
                ];
        }

        $json = json_decode($response, true);
        return [
                'ok' => true,
                'http_code' => $httpCode,
                'error' => null,
                'body' => $json
        ];
}

echo "Brute-force simulator\n";
echo "URL: {$url}\n";
echo "Username: {$username}\n";
echo "Attempts: {$attempts}\n\n";

$summary = [
        'success' => 0,
        'invalid_credentials' => 0,
        'captcha_required' => 0,
        'account_locked' => 0,
        'account_rate_limited' => 0,
        'rate_limit_exceeded' => 0,
        'other_error' => 0
];

for ($i = 1; $i <= $attempts; $i++) {
        $result = postLogin($url, $username, $password);
        if (!$result['ok']) {
                $summary['other_error']++;
                echo sprintf("[%02d] request_error=%s\n", $i, $result['error']);
                usleep($sleepMs * 1000);
                continue;
        }

        $body = is_array($result['body']) ? $result['body'] : [];
        $code = $body['code'] ?? '';
        $success = !empty($body['success']);

        if ($success) {
                $summary['success']++;
                echo sprintf("[%02d] success redirect=%s\n", $i, $body['redirect'] ?? 'n/a');
        } else {
                if (isset($summary[$code])) {
                        $summary[$code]++;
                } elseif ($code === '') {
                        $summary['invalid_credentials']++;
                } else {
                        $summary['other_error']++;
                }

                echo sprintf(
                        "[%02d] fail code=%s retry_after=%s message=%s\n",
                        $i,
                        $code !== '' ? $code : 'none',
                        isset($body['retry_after']) ? (int) $body['retry_after'] : 0,
                        $body['message'] ?? 'n/a'
                );
        }

        usleep($sleepMs * 1000);
}

echo "\nSummary:\n";
foreach ($summary as $k => $v) {
        echo sprintf("- %s: %d\n", $k, $v);
}

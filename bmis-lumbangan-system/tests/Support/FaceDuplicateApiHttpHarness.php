<?php

class FaceDuplicateApiHttpHarness
{
    private static $shared = null;

    private $projectRoot;
    private $host = '127.0.0.1';
    private $port;
    private $process = null;
    private $sessionDir;
    private $stdoutPath;
    private $stderrPath;

    public static function shared()
    {
        if (self::$shared === null) {
            self::$shared = new self(dirname(__DIR__, 2));
        }

        return self::$shared;
    }

    private function __construct($projectRoot)
    {
        $this->projectRoot = $projectRoot;
        $this->port = $this->findAvailablePort();
        $this->sessionDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bmis-face-api-sessions-' . bin2hex(random_bytes(6));
        $this->stdoutPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bmis-face-api-stdout-' . bin2hex(random_bytes(4)) . '.log';
        $this->stderrPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bmis-face-api-stderr-' . bin2hex(random_bytes(4)) . '.log';

        if (!is_dir($this->sessionDir) && !mkdir($this->sessionDir, 0777, true) && !is_dir($this->sessionDir)) {
            throw new RuntimeException('Unable to create temporary session directory for face API harness.');
        }

        $this->startServer();
        register_shutdown_function([$this, 'stop']);
    }

    public function baseUrl()
    {
        return 'http://' . $this->host . ':' . $this->port;
    }

    public function stop()
    {
        if (is_resource($this->process)) {
            $status = proc_get_status($this->process);
            $pid = (int) ($status['pid'] ?? 0);

            if (DIRECTORY_SEPARATOR === '\\' && $pid > 0) {
                @exec('taskkill /F /T /PID ' . $pid . ' >NUL 2>&1');
                usleep(250000);
            } else {
                @proc_terminate($this->process);
                usleep(250000);
            }

            if (DIRECTORY_SEPARATOR !== '\\') {
                @proc_close($this->process);
            }

            $this->process = null;
        }

        $this->deletePath($this->sessionDir);
        $this->deletePath($this->stdoutPath);
        $this->deletePath($this->stderrPath);

        if (self::$shared === $this) {
            self::$shared = null;
        }
    }

    public function createCsrfSession($token = 'test-csrf-token', $sessionId = null)
    {
        $sessionId = $sessionId ?: 'faceapi' . bin2hex(random_bytes(8));
        $payload = '';

        foreach (['csrf_token' => $token, csrf_field_name() => $token] as $key => $value) {
            $payload .= $key . '|' . serialize($value);
        }

        $sessionFile = $this->sessionDir . DIRECTORY_SEPARATOR . 'sess_' . $sessionId;
        file_put_contents($sessionFile, $payload);

        return [
            'session_id' => $sessionId,
            'token' => $token,
            'cookie' => $this->sessionCookieName() . '=' . $sessionId,
        ];
    }

    public function request($method, $path, array $options = [])
    {
        $method = strtoupper((string) $method);
        $headers = [];

        foreach (($options['headers'] ?? []) as $name => $value) {
            if (is_int($name)) {
                $headers[] = (string) $value;
            } else {
                $headers[] = $name . ': ' . $value;
            }
        }

        if (!empty($options['cookie'])) {
            $headers[] = 'Cookie: ' . $options['cookie'];
        }

        $content = '';
        if (array_key_exists('json', $options)) {
            $content = json_encode($options['json']);
            $headers[] = 'Content-Type: application/json';
        } elseif (array_key_exists('body', $options)) {
            $content = (string) $options['body'];
        }

        if ($content !== '') {
            $headers[] = 'Content-Length: ' . strlen($content);
        }

        $headers[] = 'Connection: close';

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'ignore_errors' => true,
                'timeout' => 10,
                'protocol_version' => 1.1,
                'header' => implode("\r\n", $headers),
                'content' => $content,
            ],
        ]);

        $raw = @file_get_contents($this->baseUrl() . $path, false, $context);
        $responseHeaders = isset($http_response_header) && is_array($http_response_header)
            ? $http_response_header
            : [];

        if ($raw === false && $responseHeaders === []) {
            throw new RuntimeException('Failed to call face duplicate API harness URL: ' . $this->baseUrl() . $path);
        }

        $status = 200;
        if (!empty($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', $responseHeaders[0], $matches)) {
            $status = (int) $matches[1];
        }

        $raw = $raw === false ? '' : (string) $raw;

        return [
            'status' => $status,
            'headers' => $responseHeaders,
            'raw' => $raw,
            'json' => $raw !== '' ? json_decode($raw, true) : null,
        ];
    }

    private function startServer()
    {
        $command = implode(' ', [
            escapeshellarg(PHP_BINARY),
            '-d',
            'session.save_path=' . escapeshellarg($this->sessionDir),
            '-S',
            $this->host . ':' . $this->port,
        ]);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['file', $this->stdoutPath, 'a'],
            2 => ['file', $this->stderrPath, 'a'],
        ];

        $this->process = proc_open(
            $command,
            $descriptors,
            $pipes,
            $this->projectRoot,
            $this->serverEnvironment()
        );

        if (!is_resource($this->process)) {
            throw new RuntimeException('Unable to start the PHP built-in server for face duplicate API tests.');
        }

        if (isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }

        $started = false;
        $deadline = microtime(true) + 8;

        while (microtime(true) < $deadline) {
            $socket = $this->probeSocket();
            if ($socket !== false) {
                fclose($socket);
                $started = true;
                break;
            }

            $status = proc_get_status($this->process);
            if (empty($status['running'])) {
                break;
            }

            usleep(100000);
        }

        if ($started) {
            return;
        }

        $stdout = is_file($this->stdoutPath) ? trim((string) file_get_contents($this->stdoutPath)) : '';
        $stderr = is_file($this->stderrPath) ? trim((string) file_get_contents($this->stderrPath)) : '';

        $this->stop();

        throw new RuntimeException(
            "Face duplicate API HTTP harness failed to start.\nSTDOUT: {$stdout}\nSTDERR: {$stderr}"
        );
    }

    private function serverEnvironment()
    {
        $environment = function_exists('getenv') && is_array(getenv()) ? getenv() : [];

        return array_merge($environment, [
            'APP_ENV' => (string) ($_ENV['APP_ENV'] ?? 'testing'),
            'BMIS_USE_TEST_DB' => (string) ($_ENV['BMIS_USE_TEST_DB'] ?? '1'),
            'BMIS_TEST_DB_HOST' => (string) ($_ENV['BMIS_TEST_DB_HOST'] ?? 'localhost'),
            'BMIS_TEST_DB_PORT' => (string) ($_ENV['BMIS_TEST_DB_PORT'] ?? '3306'),
            'BMIS_TEST_DB_NAME' => (string) ($_ENV['BMIS_TEST_DB_NAME'] ?? 'lumbangansystem_test'),
            'BMIS_TEST_DB_USERNAME' => (string) ($_ENV['BMIS_TEST_DB_USERNAME'] ?? 'root'),
            'BMIS_TEST_DB_PASSWORD' => (string) ($_ENV['BMIS_TEST_DB_PASSWORD'] ?? ''),
            'RECAPTCHA_SITE_KEY' => (string) ($_ENV['RECAPTCHA_SITE_KEY'] ?? 'test-site-key'),
            'RECAPTCHA_SECRET_KEY' => (string) ($_ENV['RECAPTCHA_SECRET_KEY'] ?? 'test-secret-key'),
        ]);
    }

    private function sessionCookieName()
    {
        $name = ini_get('session.name');

        return $name !== false && $name !== '' ? (string) $name : 'PHPSESSID';
    }

    private function findAvailablePort()
    {
        $socket = @stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            throw new RuntimeException('Unable to allocate a free TCP port for face duplicate API tests.');
        }

        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        if (!is_string($name) || strpos($name, ':') === false) {
            throw new RuntimeException('Unable to determine a free TCP port for face duplicate API tests.');
        }

        return (int) substr(strrchr($name, ':'), 1);
    }

    private function probeSocket()
    {
        set_error_handler(static function () {
            return true;
        });

        try {
            return fsockopen($this->host, $this->port, $errno, $errstr, 0.2);
        } finally {
            restore_error_handler();
        }
    }

    private function deletePath($path)
    {
        if (!$path) {
            return;
        }

        if (is_file($path)) {
            @unlink($path);
            return;
        }

        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if (!is_array($items)) {
            @rmdir($path);
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $this->deletePath($path . DIRECTORY_SEPARATOR . $item);
        }

        @rmdir($path);
    }
}

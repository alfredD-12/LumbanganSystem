<?php

require_once __DIR__ . '/config.php';

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct(array $overrides = [])
    {
        $config = $this->resolveConfig($overrides);
        $this->host = $config['host'];
        $this->db_name = $config['db_name'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->port = $config['port'];
    }

    public function getConnection()
    {
        $this->conn = null;

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $this->host,
                $this->port,
                $this->db_name
            );

            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            echo 'Connection error: ' . $exception->getMessage();
        }

        return $this->conn;
    }

    private function resolveConfig(array $overrides)
    {
        $useTestDatabase = $this->shouldUseTestDatabase();

        $primary = [
            'host' => (string) config_env('BMIS_DB_HOST', 'localhost'),
            'db_name' => (string) config_env('BMIS_DB_NAME', 'lumbangansystem'),
            'username' => (string) config_env('BMIS_DB_USERNAME', 'root'),
            'password' => (string) config_env('BMIS_DB_PASSWORD', ''),
            'port' => config_env_int('BMIS_DB_PORT', 3306),
        ];

        $test = [
            'host' => (string) config_env('BMIS_TEST_DB_HOST', $primary['host']),
            'db_name' => (string) config_env('BMIS_TEST_DB_NAME', $primary['db_name'] . '_test'),
            'username' => (string) config_env('BMIS_TEST_DB_USERNAME', $primary['username']),
            'password' => (string) config_env('BMIS_TEST_DB_PASSWORD', $primary['password']),
            'port' => config_env_int('BMIS_TEST_DB_PORT', $primary['port']),
        ];

        return array_merge($useTestDatabase ? $test : $primary, $overrides);
    }

    private function shouldUseTestDatabase()
    {
        return APP_ENV === 'testing' || config_env_bool('BMIS_USE_TEST_DB', false);
    }
}

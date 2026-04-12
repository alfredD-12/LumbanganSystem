<?php

class MysqlAuthTestDatabase
{
    public static function recreate()
    {
        $host = (string) ($_ENV['BMIS_TEST_DB_HOST'] ?? 'localhost');
        $port = (int) ($_ENV['BMIS_TEST_DB_PORT'] ?? 3306);
        $dbName = (string) ($_ENV['BMIS_TEST_DB_NAME'] ?? 'lumbangansystem_test');
        $username = (string) ($_ENV['BMIS_TEST_DB_USERNAME'] ?? 'root');
        $password = (string) ($_ENV['BMIS_TEST_DB_PASSWORD'] ?? '');

        $admin = new PDO(
            sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port),
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        $admin->exec('DROP DATABASE IF EXISTS `' . $dbName . '`');
        $admin->exec('CREATE DATABASE `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbName),
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        self::runSqlFile($pdo, __DIR__ . '/../../db/test_auth_schema.sql');
        return $pdo;
    }

    public static function seedResidentUser(PDO $pdo, array $overrides = [])
    {
        $person = array_merge([
            'first_name' => 'Mysql',
            'middle_name' => null,
            'last_name' => 'Resident',
            'suffix' => null,
        ], $overrides['person'] ?? []);

        $personStmt = $pdo->prepare(
            "INSERT INTO persons (first_name, middle_name, last_name, suffix)
             VALUES (:first_name, :middle_name, :last_name, :suffix)"
        );
        $personStmt->execute([
            ':first_name' => $person['first_name'],
            ':middle_name' => $person['middle_name'],
            ':last_name' => $person['last_name'],
            ':suffix' => $person['suffix'],
        ]);
        $personId = (int) $pdo->lastInsertId();

        $user = array_merge([
            'username' => 'resident',
            'email' => 'resident@example.com',
            'mobile' => '09171234567',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'status' => 'active',
        ], $overrides['user'] ?? $overrides);

        $userStmt = $pdo->prepare(
            "INSERT INTO users (person_id, username, email, mobile, password_hash, status)
             VALUES (:person_id, :username, :email, :mobile, :password_hash, :status)"
        );
        $userStmt->execute([
            ':person_id' => $personId,
            ':username' => $user['username'],
            ':email' => $user['email'],
            ':mobile' => $user['mobile'],
            ':password_hash' => $user['password_hash'],
            ':status' => $user['status'],
        ]);

        return [
            'person_id' => $personId,
            'user_id' => (int) $pdo->lastInsertId(),
            'username' => $user['username'],
            'email' => $user['email'],
            'password' => $overrides['plain_password'] ?? 'secret123',
        ];
    }

    private static function runSqlFile(PDO $pdo, $path)
    {
        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('Unable to read SQL file: ' . $path);
        }

        $statement = '';
        foreach (preg_split("/\R/", $sql) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $statement .= $line . "\n";
            if (substr(rtrim($trimmed), -1) === ';') {
                $pdo->exec($statement);
                $statement = '';
            }
        }

        if (trim($statement) !== '') {
            $pdo->exec($statement);
        }
    }
}

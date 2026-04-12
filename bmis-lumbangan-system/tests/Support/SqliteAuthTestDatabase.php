<?php

class SqliteAuthTestDatabase
{
    public static function createPdo()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $schema = <<<'SQL'
CREATE TABLE persons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    family_id INTEGER DEFAULT NULL,
    household_id INTEGER DEFAULT NULL,
    last_name TEXT NOT NULL,
    first_name TEXT NOT NULL,
    middle_name TEXT DEFAULT NULL,
    suffix TEXT DEFAULT NULL,
    is_head INTEGER NOT NULL DEFAULT 0,
    sex TEXT DEFAULT NULL,
    birthdate TEXT DEFAULT NULL,
    marital_status TEXT DEFAULT NULL,
    created_at TEXT DEFAULT NULL,
    updated_at TEXT DEFAULT NULL
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    person_id INTEGER NOT NULL,
    username TEXT NOT NULL UNIQUE,
    email TEXT DEFAULT NULL UNIQUE,
    mobile TEXT DEFAULT NULL,
    password_hash TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    last_login_at TEXT DEFAULT NULL,
    created_at TEXT DEFAULT NULL,
    updated_at TEXT DEFAULT NULL,
    face_embedding TEXT DEFAULT NULL,
    face_image_path TEXT DEFAULT NULL,
    face_verified_at TEXT DEFAULT NULL,
    face_enrolled INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE officials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    username TEXT DEFAULT NULL UNIQUE,
    password_hash TEXT DEFAULT NULL,
    last_login_at TEXT DEFAULT NULL,
    role TEXT NOT NULL,
    contact_no TEXT DEFAULT NULL,
    email TEXT DEFAULT NULL,
    active INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    email TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL,
    token TEXT NOT NULL UNIQUE,
    expires_at TEXT NOT NULL,
    used_at TEXT DEFAULT NULL,
    created_at TEXT DEFAULT NULL
);

CREATE TABLE email_verifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL,
    token TEXT NOT NULL UNIQUE,
    person_data TEXT NOT NULL,
    user_data TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TEXT NOT NULL,
    verified_at TEXT DEFAULT NULL
);

CREATE TABLE ip_rate_limits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip_address TEXT NOT NULL UNIQUE,
    attempt_count INTEGER NOT NULL DEFAULT 0,
    window_start TEXT NOT NULL,
    last_attempt_at TEXT NOT NULL
);

CREATE TABLE account_lockouts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    consecutive_failures INTEGER NOT NULL DEFAULT 0,
    lockout_count INTEGER NOT NULL DEFAULT 0,
    locked_until TEXT DEFAULT NULL,
    last_failure_at TEXT DEFAULT NULL,
    last_success_at TEXT DEFAULT NULL,
    created_at TEXT DEFAULT NULL,
    updated_at TEXT DEFAULT NULL
);

CREATE TABLE login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    ip_address TEXT NOT NULL,
    user_agent TEXT NOT NULL,
    attempt_result TEXT NOT NULL,
    failure_reason TEXT DEFAULT NULL,
    geolocation_hint TEXT DEFAULT NULL,
    attempted_at TEXT NOT NULL
);

CREATE TABLE brute_force_alerts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    alert_type TEXT NOT NULL,
    target TEXT NOT NULL,
    attempt_count INTEGER NOT NULL DEFAULT 0,
    alert_sent_at TEXT NOT NULL,
    email_sent INTEGER NOT NULL DEFAULT 0,
    details TEXT DEFAULT NULL
);
SQL;

        $pdo->exec($schema);
        return $pdo;
    }

    public static function seedResidentUser(PDO $pdo, array $overrides = [])
    {
        $person = array_merge([
            'first_name' => 'Test',
            'middle_name' => null,
            'last_name' => 'Resident',
            'suffix' => null,
        ], $overrides['person'] ?? []);

        $personStmt = $pdo->prepare(
            "INSERT INTO persons (first_name, middle_name, last_name, suffix, created_at, updated_at)
             VALUES (:first_name, :middle_name, :last_name, :suffix, :created_at, :updated_at)"
        );
        $personStmt->execute([
            ':first_name' => $person['first_name'],
            ':middle_name' => $person['middle_name'],
            ':last_name' => $person['last_name'],
            ':suffix' => $person['suffix'],
            ':created_at' => '2026-04-12 09:00:00',
            ':updated_at' => '2026-04-12 09:00:00',
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
            "INSERT INTO users (person_id, username, email, mobile, password_hash, status, created_at, updated_at)
             VALUES (:person_id, :username, :email, :mobile, :password_hash, :status, :created_at, :updated_at)"
        );
        $userStmt->execute([
            ':person_id' => $personId,
            ':username' => $user['username'],
            ':email' => $user['email'],
            ':mobile' => $user['mobile'],
            ':password_hash' => $user['password_hash'],
            ':status' => $user['status'],
            ':created_at' => '2026-04-12 09:00:00',
            ':updated_at' => '2026-04-12 09:00:00',
        ]);

        return [
            'person_id' => $personId,
            'user_id' => (int) $pdo->lastInsertId(),
            'username' => $user['username'],
            'email' => $user['email'],
            'password' => $overrides['plain_password'] ?? 'secret123',
        ];
    }

    public static function seedOfficial(PDO $pdo, array $overrides = [])
    {
        $official = array_merge([
            'full_name' => 'Test Official',
            'username' => 'official',
            'password_hash' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'Captain',
            'email' => 'official@example.com',
            'contact_no' => '09179876543',
            'active' => 1,
        ], $overrides);

        $stmt = $pdo->prepare(
            "INSERT INTO officials (full_name, username, password_hash, role, email, contact_no, active)
             VALUES (:full_name, :username, :password_hash, :role, :email, :contact_no, :active)"
        );
        $stmt->execute([
            ':full_name' => $official['full_name'],
            ':username' => $official['username'],
            ':password_hash' => $official['password_hash'],
            ':role' => $official['role'],
            ':email' => $official['email'],
            ':contact_no' => $official['contact_no'],
            ':active' => $official['active'],
        ]);

        return [
            'official_id' => (int) $pdo->lastInsertId(),
            'username' => $official['username'],
            'password' => $overrides['plain_password'] ?? 'secret123',
        ];
    }
}

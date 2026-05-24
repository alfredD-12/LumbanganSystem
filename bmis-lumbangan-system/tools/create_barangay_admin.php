<?php

// CLI tool: Create/Update an initial Barangay Admin (official) account.
// Usage examples:
//   php tools/create_barangay_admin.php --username=admin --password="YourPassword" --full-name="Barangay Admin" --role=admin
//   php tools/create_barangay_admin.php --username=admin --password="YourPassword" --full-name="Barangay Admin" --role=admin --force
//
// Notes:
// - Creates an entry in the `officials` table (used for admin/official login).
// - Uses password_hash(PASSWORD_DEFAULT) to match the app.

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
require_once $root . '/app/config/config.php';
require_once $root . '/app/config/Database.php';
require_once $root . '/app/models/Official.php';

function stderr(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
}

function stdout(string $message): void
{
    fwrite(STDOUT, $message . PHP_EOL);
}

$options = getopt('', [
    'username:',
    'password:',
    'full-name:',
    'role::',
    'email::',
    'contact::',
    'force',
]);

$username = trim((string)($options['username'] ?? ''));
$password = (string)($options['password'] ?? '');
$fullName = trim((string)($options['full-name'] ?? ''));
$role = trim((string)($options['role'] ?? 'admin'));
$email = trim((string)($options['email'] ?? ''));
$contact = trim((string)($options['contact'] ?? ''));
$force = array_key_exists('force', $options);

if ($username === '' || $password === '' || $fullName === '') {
    stderr('Missing required args.');
    stderr('Required: --username=... --password=... --full-name="..."');
    stderr('Optional: --role=admin --email=... --contact=... --force');
    exit(2);
}

if (strlen($password) < 8) {
    stderr('Password must be at least 8 characters.');
    exit(2);
}

$db = (new Database())->getConnection();
if (!$db) {
    stderr('Database connection failed. Check BMIS_DB_* settings in app/config/config.php or your environment variables.');
    exit(1);
}

$officialModel = new Official($db);
$existing = $officialModel->findByUsername($username);

$hash = password_hash($password, PASSWORD_DEFAULT);

if ($existing) {
    if (!$force) {
        stdout("Official account '{$username}' already exists (id={$existing['id']}). No changes made.");
        stdout('Run again with --force to update password/details.');
        exit(0);
    }

    $update = [
        'full_name' => $fullName,
        'password_hash' => $hash,
        'role' => $role,
        'active' => 1,
    ];

    if ($email !== '') $update['email'] = $email;
    if ($contact !== '') $update['contact_no'] = $contact;

    $ok = $officialModel->updateById($existing['id'], $update);
    if (!$ok) {
        stderr('Failed to update the existing official account.');
        exit(1);
    }

    stdout("Updated official account '{$username}' (id={$existing['id']}, role={$role}).");
    exit(0);
}

$newId = $officialModel->create([
    'full_name' => $fullName,
    'username' => $username,
    'password_hash' => $hash,
    'role' => $role,
    'email' => ($email !== '' ? $email : null),
    'contact_no' => ($contact !== '' ? $contact : null),
]);

if (!$newId) {
    stderr('Failed to create the official/admin account.');
    exit(1);
}

stdout("Created official account '{$username}' (id={$newId}, role={$role}).");
stdout('Login via the main login form; officials redirect to the official dashboard after sign-in.');

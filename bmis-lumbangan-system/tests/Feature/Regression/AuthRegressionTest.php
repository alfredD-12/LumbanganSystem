<?php

it('allows login via front controller with valid csrf and sets resident session', function () {
    $pdo = MysqlRegressionTestDatabase::recreate();
    $seed = MysqlAuthTestDatabase::seedResidentUser($pdo);

    $response = test_capture_front_controller_action_with_csrf('login', [
        'username' => $seed['username'],
        'password' => $seed['password'],
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();
    expect($response['json']['user_type'])->toBe('user');

    expect($_SESSION['logged_in'])->toBeTrue();
    expect($_SESSION['user_type'])->toBe('user');
    expect((int) ($_SESSION['user_id'] ?? 0))->toBe((int) $seed['user_id']);

    $lastLoginAt = test_query_value(
        $pdo,
        'SELECT last_login_at FROM users WHERE id = :id',
        [':id' => (int) $seed['user_id']]
    );
    expect($lastLoginAt)->not->toBeNull();
});

it('allows logout via front controller with valid csrf and clears session', function () {
    $pdo = MysqlRegressionTestDatabase::recreate();
    $seed = MysqlAuthTestDatabase::seedResidentUser($pdo);

    $session = [
        'logged_in' => true,
        'user_type' => 'user',
        'user_id' => (int) $seed['user_id'],
        'person_id' => (int) $seed['person_id'],
        'username' => $seed['username'],
    ];

    $response = test_capture_front_controller_action_with_csrf('logout', [], $session);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();
    expect($response['json']['code'])->toBe('logged_out');
    expect($_SESSION)->toBeEmpty();
});


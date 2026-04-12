<?php

it('rejects missing csrf tokens on protected front-controller actions before controller work begins', function () {
    $actions = [
        'login',
        'logout',
        'request_reset',
        'verify_reset_code',
        'reset_password',
        'send_verification_code',
        'verify_registration_code',
        'complete_registration',
        'submitRequest',
        'complaint_save',
        'saveTemplate',
        'gallery_update_order',
    ];

    foreach ($actions as $action) {
        $response = test_capture_front_controller_action($action, []);

        expect($response['status'])->toBe(403);
        expect($response['json']['success'])->toBeFalse();
        expect($response['json']['code'])->toBe('invalid_csrf');
    }
});

it('rejects invalid csrf tokens on protected front-controller actions', function () {
    $response = test_capture_front_controller_action('login', [
        csrf_field_name() => 'wrong-token',
        'username' => 'resident',
        'password' => 'secret123',
    ]);

    expect($response['status'])->toBe(403);
    expect($response['json']['success'])->toBeFalse();
    expect($response['json']['code'])->toBe('invalid_csrf');
});

it('keeps page logout non-mutating when reached over get routing', function () {
    test_reset_http_state();
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = 'resident';
    $_GET = ['page' => 'logout'];

    ob_start();
    include dirname(__DIR__, 2) . '/public/index.php';
    ob_end_clean();

    expect($_SESSION['logged_in'])->toBeTrue();
    expect($_SESSION['username'])->toBe('resident');
});

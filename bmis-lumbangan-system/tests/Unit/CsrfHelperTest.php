<?php

it('extracts and validates csrf tokens from the configured header', function () {
    test_reset_http_state();
    $token = test_seed_csrf_token('header-token');
    $headerName = 'HTTP_' . str_replace('-', '_', strtoupper(csrf_header_name()));

    $_SERVER[$headerName] = $token;

    expect(csrf_request_token())->toBe('header-token');
    expect(csrf_request_is_valid())->toBeTrue();
});

it('extracts and validates csrf tokens from form payloads', function () {
    test_reset_http_state();
    $token = test_seed_csrf_token('form-token');

    $_POST = [
        csrf_field_name() => $token,
    ];

    expect(csrf_request_token())->toBe('form-token');
    expect(csrf_request_is_valid())->toBeTrue();
});

it('extracts and validates csrf tokens from json payloads', function () {
    test_reset_http_state();
    $token = test_seed_csrf_token('json-token');

    $GLOBALS['__csrf_raw_input'] = json_encode([
        csrf_field_name() => $token,
    ]);

    expect(csrf_request_token())->toBe('json-token');
    expect(csrf_request_is_valid())->toBeTrue();
});

it('returns false when the csrf token is missing or invalid', function () {
    test_reset_http_state();
    test_seed_csrf_token('valid-token');

    expect(csrf_request_is_valid())->toBeFalse();

    $_POST = [
        csrf_field_name() => 'wrong-token',
    ];

    expect(csrf_request_is_valid())->toBeFalse();
});

<?php

it('keeps the landing auth contract aligned with the backend security responses', function () {
    $landing = file_get_contents(__DIR__ . '/../../app/views/landing/landing.php');
    $loginJs = file_get_contents(__DIR__ . '/../../app/assets/js/Landing/login.js');

    expect($landing)->toContain('meta name="csrf-field"');
    expect($landing)->toContain('meta name="csrf-token"');
    expect($landing)->toContain('id="loginRecaptchaContainer"');
    expect($landing)->toContain('id="forgetRecaptchaContainer1"');
    expect($landing)->toContain('?action=request_reset');
    expect($landing)->toContain('?action=verify_code');
    expect($landing)->toContain('?action=reset_password');

    expect($loginJs)->toContain('invalid_csrf');
    expect($loginJs)->toContain('captcha_required');
    expect($loginJs)->toContain('rate_limit_exceeded');
    expect($loginJs)->toContain('account_locked');
    expect($loginJs)->toContain('window.BMISCaptcha');
});

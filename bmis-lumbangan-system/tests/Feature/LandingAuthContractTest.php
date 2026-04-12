<?php

it('keeps the landing auth contract aligned with the backend security responses', function () {
    $landing = file_get_contents(__DIR__ . '/../../app/views/landing/landing.php');
    $loginJs = file_get_contents(__DIR__ . '/../../app/assets/js/Landing/login.js');
    $emailVerificationJs = file_get_contents(__DIR__ . '/../../app/assets/js/email_verification.js');
    $captchaJs = file_get_contents(__DIR__ . '/../../app/assets/js/security/captcha.js');
    $emailVerifyModal = file_get_contents(__DIR__ . '/../../app/components/resident_components/email_verify_modal.php');

    expect($landing)->toContain('meta name="csrf-field"');
    expect($landing)->toContain('meta name="csrf-token"');
    expect($landing)->toContain('meta name="app-front-controller"');
    expect($landing)->toContain('id="loginRecaptchaContainer"');
    expect($landing)->toContain('id="forgetRecaptchaContainer1"');
    expect($landing)->toContain('assets/js/security/captcha.js');
    expect($landing)->toContain('?action=request_reset');
    expect($landing)->toContain('?action=verify_reset_code');
    expect($landing)->toContain('?action=reset_password');
    expect($landing)->not->toContain('assets/js/csrf-protection.js');

    expect($loginJs)->toContain('invalid_csrf');
    expect($loginJs)->toContain('captcha_required');
    expect($loginJs)->toContain('rate_limit_exceeded');
    expect($loginJs)->toContain('account_locked');
    expect($captchaJs)->toContain('window.BMISCaptcha');
    expect($emailVerificationJs)->toContain('registerRecaptchaContainer${step}');
    expect($emailVerificationJs)->toContain('registerRecaptchaWidget${step}');
    expect($emailVerificationJs)->toContain('captcha_required');
    expect($emailVerificationJs)->toContain('send_verification_code');
    expect($emailVerificationJs)->toContain('verify_registration_code');
    expect($emailVerificationJs)->toContain('resend_verification_code');
    expect($emailVerifyModal)->toContain('id="registerRecaptchaContainer1"');
    expect($emailVerifyModal)->toContain('id="registerRecaptchaContainer2"');
    expect($emailVerifyModal)->toContain('id="registerCaptchaTokenStep1"');
    expect($emailVerifyModal)->toContain('id="registerCaptchaTokenStep2"');
});

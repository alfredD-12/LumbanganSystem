<?php

it('loads the shared csrf bootstrap without the legacy duplicate script in shared layouts', function () {
    $landing = file_get_contents(__DIR__ . '/../../app/views/landing/landing.php');
    $residentHeader = file_get_contents(__DIR__ . '/../../app/components/resident_components/header-resident.php');
    $residentFooter = file_get_contents(__DIR__ . '/../../app/components/resident_components/footer-resident.php');
    $adminHeader = file_get_contents(__DIR__ . '/../../app/components/admin_components/header-admin.php');
    $adminFooter = file_get_contents(__DIR__ . '/../../app/components/admin_components/footer-admin.php');

    expect($landing)->toContain('assets/js/security/csrf.js');
    expect($residentFooter)->toContain('assets/js/security/csrf.js');
    expect($adminFooter)->toContain('assets/js/security/csrf.js');

    expect($landing)->not->toContain('assets/js/csrf-protection.js');
    expect($residentHeader)->not->toContain('assets/js/csrf-protection.js');
    expect($adminHeader)->not->toContain('assets/js/csrf-protection.js');
});

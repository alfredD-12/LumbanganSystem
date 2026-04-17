<?php

/*
 * Browser-style coverage for camera access, blink detection, head-turn detection,
 * and inline/modal interaction is intentionally deferred because this repo does
 * not currently include a browser automation stack. These tests maximize realistic
 * backend regression coverage against the existing standalone face duplicate API.
 */

function facial_api_embedding($seed, $step = 0.0025)
{
    $embedding = [];

    for ($i = 0; $i < 128; $i++) {
        $embedding[] = round(((float) $seed) + ($i * $step), 6);
    }

    return $embedding;
}

function facial_api_seed_user_with_embedding(PDO $pdo, $username, array $embedding, array $overrides = [])
{
    $seed = MysqlAuthTestDatabase::seedResidentUser($pdo, [
        'person' => array_merge([
            'first_name' => ucfirst($username),
            'last_name' => 'Face',
        ], $overrides['person'] ?? []),
        'user' => array_merge([
            'username' => $username,
            'email' => $username . '@example.com',
            'mobile' => '0917' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
            'status' => 'active',
        ], $overrides['user'] ?? []),
    ]);

    $stmt = $pdo->prepare(
        'UPDATE users
         SET face_embedding = :face_embedding,
             face_verified_at = :face_verified_at,
             face_enrolled = 1
         WHERE id = :id'
    );

    $stmt->execute([
        ':face_embedding' => json_encode($embedding),
        ':face_verified_at' => '2026-04-12 09:00:00',
        ':id' => (int) $seed['user_id'],
    ]);

    return $seed;
}

it('rejects duplicate-face API requests without csrf', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    facial_api_seed_user_with_embedding($pdo, 'faceapiuser', facial_api_embedding(0.05));

    $response = FaceDuplicateApiHttpHarness::shared()->request('POST', '/api/check_face_duplicate.php', [
        'json' => ['face_embedding' => facial_api_embedding(0.05)],
    ]);

    expect($response['status'])->toBe(403);
    expect($response['json'])->toMatchArray([
        'success' => false,
        'message' => 'Invalid or missing CSRF token',
    ]);
});

it('rejects duplicate-face API requests with invalid csrf', function () {
    $pdo = MysqlAuthTestDatabase::recreate();
    facial_api_seed_user_with_embedding($pdo, 'faceapiuser', facial_api_embedding(0.05));

    $session = FaceDuplicateApiHttpHarness::shared()->createCsrfSession('valid-face-api-token');
    $response = FaceDuplicateApiHttpHarness::shared()->request('POST', '/api/check_face_duplicate.php', [
        'cookie' => $session['cookie'],
        'headers' => [
            'X-CSRF-Token' => 'wrong-face-api-token',
        ],
        'json' => ['face_embedding' => facial_api_embedding(0.05)],
    ]);

    expect($response['status'])->toBe(403);
    expect($response['json'])->toMatchArray([
        'success' => false,
        'message' => 'Invalid or missing CSRF token',
    ]);
});

it('rejects duplicate-face API requests without face embedding', function () {
    MysqlAuthTestDatabase::recreate();

    $session = FaceDuplicateApiHttpHarness::shared()->createCsrfSession();
    $response = FaceDuplicateApiHttpHarness::shared()->request('POST', '/api/check_face_duplicate.php', [
        'cookie' => $session['cookie'],
        'headers' => [
            'X-CSRF-Token' => $session['token'],
        ],
        'json' => ['sample_count' => 5],
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json'])->toMatchArray([
        'success' => false,
        'message' => 'Missing face_embedding',
    ]);
});

it('rejects duplicate-face API requests with invalid embedding length', function () {
    MysqlAuthTestDatabase::recreate();

    $session = FaceDuplicateApiHttpHarness::shared()->createCsrfSession();
    $response = FaceDuplicateApiHttpHarness::shared()->request('POST', '/api/check_face_duplicate.php', [
        'cookie' => $session['cookie'],
        'headers' => [
            'X-CSRF-Token' => $session['token'],
        ],
        'json' => ['face_embedding' => [0.1, 0.2, 0.3]],
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeFalse();
    expect($response['json']['message'])->toContain('Invalid embedding (expected 128 floats, got 3)');
});

it('rejects duplicate-face API requests when request method is not post', function () {
    MysqlAuthTestDatabase::recreate();

    $response = FaceDuplicateApiHttpHarness::shared()->request('GET', '/api/check_face_duplicate.php');

    expect($response['status'])->toBe(200);
    expect($response['json'])->toMatchArray([
        'success' => false,
        'message' => 'Method not allowed',
    ]);
});

it('accepts a valid embedding payload and returns a structured response', function () {
    $pdo = MysqlAuthTestDatabase::recreate();

    facial_api_seed_user_with_embedding($pdo, 'structurea', facial_api_embedding(0.05));
    facial_api_seed_user_with_embedding($pdo, 'structureb', facial_api_embedding(0.75));
    facial_api_seed_user_with_embedding($pdo, 'structurec', facial_api_embedding(1.45));

    $session = FaceDuplicateApiHttpHarness::shared()->createCsrfSession();
    $response = FaceDuplicateApiHttpHarness::shared()->request('POST', '/api/check_face_duplicate.php', [
        'cookie' => $session['cookie'],
        'headers' => [
            'X-CSRF-Token' => $session['token'],
        ],
        'json' => ['face_embedding' => facial_api_embedding(2.25)],
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();
    expect($response['json'])->toHaveKeys([
        'duplicate',
        'reason',
        'closest_dist',
        'closest_user',
        'dynamic_threshold',
        'hard_ceiling',
        'hard_floor',
        'total_compared',
        'distances',
        'message',
    ]);
    expect($response['json']['total_compared'])->toBe(3);
    expect($response['json']['distances'])->toHaveCount(3);
});

it('flags a duplicate when an embedding matches an existing stored embedding', function () {
    $pdo = MysqlAuthTestDatabase::recreate();

    $seed = facial_api_seed_user_with_embedding($pdo, 'duplicateface', facial_api_embedding(0.12));
    facial_api_seed_user_with_embedding($pdo, 'duplicatefiller1', facial_api_embedding(0.95));
    facial_api_seed_user_with_embedding($pdo, 'duplicatefiller2', facial_api_embedding(1.75));

    $session = FaceDuplicateApiHttpHarness::shared()->createCsrfSession();
    $response = FaceDuplicateApiHttpHarness::shared()->request('POST', '/api/check_face_duplicate.php', [
        'cookie' => $session['cookie'],
        'headers' => [
            'X-CSRF-Token' => $session['token'],
        ],
        'json' => ['face_embedding' => facial_api_embedding(0.12)],
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();
    expect($response['json']['duplicate'])->toBeTrue();
    expect($response['json']['closest_user'])->toBe((int) $seed['user_id']);
    expect((float) $response['json']['closest_dist'])->toBe(0.0);
    expect($response['json']['message'])->toBe('A matching face was found in the system.');
});

it('does not flag a duplicate when embedding is sufficiently different', function () {
    $pdo = MysqlAuthTestDatabase::recreate();

    facial_api_seed_user_with_embedding($pdo, 'newfacea', facial_api_embedding(0.10));
    facial_api_seed_user_with_embedding($pdo, 'newfaceb', facial_api_embedding(0.80));
    facial_api_seed_user_with_embedding($pdo, 'newfacec', facial_api_embedding(1.55));

    $session = FaceDuplicateApiHttpHarness::shared()->createCsrfSession();
    $response = FaceDuplicateApiHttpHarness::shared()->request('POST', '/api/check_face_duplicate.php', [
        'cookie' => $session['cookie'],
        'headers' => [
            'X-CSRF-Token' => $session['token'],
        ],
        'json' => ['face_embedding' => facial_api_embedding(2.40)],
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();
    expect($response['json']['duplicate'])->toBeFalse();
    expect($response['json']['message'])->toBe('No duplicate face found.');
    expect($response['json']['closest_dist'])->toBeGreaterThan($response['json']['hard_floor']);
});

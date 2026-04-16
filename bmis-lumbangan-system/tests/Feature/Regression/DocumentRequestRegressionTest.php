<?php

it('submits a document request with valid csrf + user session and persists it', function () {
    $pdo = MysqlRegressionTestDatabase::recreate();
    $seed = MysqlAuthTestDatabase::seedResidentUser($pdo);
    $docTypeId = MysqlRegressionTestDatabase::seedDocumentType($pdo);

    $session = [
        'logged_in' => true,
        'user_type' => 'user',
        'user_id' => (int) $seed['user_id'],
        'person_id' => (int) $seed['person_id'],
        'username' => $seed['username'],
    ];

    $purpose = 'Need clearance for employment';
    $response = test_capture_front_controller_action_with_csrf('submitRequest', [
        'document_type_id' => $docTypeId,
        'purpose' => $purpose,
    ], $session);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();

    $count = test_query_value(
        $pdo,
        'SELECT COUNT(*) FROM document_requests WHERE user_id = :user_id AND document_type_id = :document_type_id AND purpose = :purpose',
        [
            ':user_id' => (int) $seed['user_id'],
            ':document_type_id' => (int) $docTypeId,
            ':purpose' => $purpose,
        ]
    );
    expect((int) $count)->toBe(1);
});

it('rejects submitRequest when not logged in even with valid csrf', function () {
    $pdo = MysqlRegressionTestDatabase::recreate();
    $seed = MysqlAuthTestDatabase::seedResidentUser($pdo);
    $docTypeId = MysqlRegressionTestDatabase::seedDocumentType($pdo);

    $response = test_capture_front_controller_action_with_csrf('submitRequest', [
        'document_type_id' => $docTypeId,
        'purpose' => 'Test',
    ], []);

    expect($response['status'])->toBe(401);
    expect($response['json']['success'])->toBeFalse();
    expect($response['json']['message'])->toBe('Unauthorized');
});


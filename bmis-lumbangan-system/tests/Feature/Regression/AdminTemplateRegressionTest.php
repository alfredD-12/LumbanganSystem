<?php

it('saves a document template with valid csrf and persists template_html', function () {
    $pdo = MysqlRegressionTestDatabase::recreate();
    $docTypeId = MysqlRegressionTestDatabase::seedDocumentType($pdo);

    $html = '<p>Template for {{subject.full_name}}</p>';
    $response = test_capture_front_controller_action_with_csrf('saveTemplate', [
        'document_type_id' => $docTypeId,
        'template_html' => $html,
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();

    $stored = test_query_value(
        $pdo,
        'SELECT template_html FROM document_templates WHERE document_type_id = :document_type_id',
        [':document_type_id' => (int) $docTypeId]
    );
    expect($stored)->toBe($html);
});


<?php

it('creates a complaint with valid csrf and persists the incident', function () {
    $pdo = MysqlRegressionTestDatabase::recreate();

    $payload = [
        'incident_title' => 'Noise complaint',
        'blotter_type' => 'Complaint',
        'case_type_id' => 1,
        'complainant_name' => 'Test Resident',
        'complainant_type' => 'Resident',
        'complainant_gender' => 'Male',
        'date_of_incident' => '2026-04-10',
        'time_of_incident' => '12:00',
        'location' => 'Purok 1',
        'narrative' => 'Loud noise after midnight.',
    ];

    $response = test_capture_front_controller_action_with_csrf('complaint_save', $payload);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();
    expect((int) ($response['json']['id'] ?? 0))->toBeGreaterThan(0);

    $incidentId = (int) $response['json']['id'];
    expect(test_query_value($pdo, 'SELECT incident_title FROM incidents WHERE id = :id', [':id' => $incidentId]))
        ->toBe($payload['incident_title']);
    expect(test_query_value($pdo, 'SELECT narrative FROM incidents WHERE id = :id', [':id' => $incidentId]))
        ->toBe($payload['narrative']);
});


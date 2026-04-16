<?php

it('updates gallery display order with valid csrf and persists new ordering', function () {
    $pdo = MysqlRegressionTestDatabase::recreate();
    $ids = MysqlRegressionTestDatabase::seedGalleryItems($pdo, 2);

    $response = test_capture_front_controller_action_with_csrf('gallery_update_order', [
        'ordered_ids' => json_encode([$ids[1], $ids[0]]),
    ]);

    expect($response['status'])->toBe(200);
    expect($response['json']['success'])->toBeTrue();

    $firstOrder = test_query_value($pdo, 'SELECT display_order FROM gallery WHERE id = :id', [':id' => (int) $ids[0]]);
    $secondOrder = test_query_value($pdo, 'SELECT display_order FROM gallery WHERE id = :id', [':id' => (int) $ids[1]]);

    expect((int) $firstOrder)->toBe(2);
    expect((int) $secondOrder)->toBe(1);
});


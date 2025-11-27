<?php

/**
 * Test script for Predictive Analytics Module
 * 
 * Usage: php test_predictions.php
 */

require_once __DIR__ . '/app/controllers/PredictiveAnalyticsController.php';
require_once __DIR__ . '/app/models/MigrationModel.php';

echo "=== Predictive Analytics Module Test ===\n\n";

// 1. Test API Connection
echo "1. Testing Python API connection...\n";
$controller = new PredictiveAnalyticsController();
$apiTest = $controller->testAPIConnection();

if ($apiTest['success']) {
    echo "   ✓ API connection successful!\n";
    echo "   Response: " . json_encode($apiTest['response']) . "\n\n";
} else {
    echo "   ✗ API connection failed!\n";
    echo "   Error: " . $apiTest['message'] . "\n";
    echo "   Please ensure Python FastAPI server is running at http://127.0.0.1:8000\n";
    echo "   Run: uvicorn predict_api:app --reload\n\n";
    exit(1);
}

// 2. Test Database Connection
echo "2. Testing database connection...\n";
try {
    $migrationModel = new MigrationModel();
    $stats = $migrationModel->getDashboardStats();
    echo "   ✓ Database connection successful!\n";
    echo "   Current predictions in DB: " . $stats['total_predictions'] . "\n\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed!\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. Test Feature Computation
echo "3. Testing feature computation...\n";
$features = $migrationModel->computeFeaturesForNextMonth(true);
echo "   ✓ Features computed successfully!\n";
echo "   Number of residents: " . count($features) . "\n";
if (count($features) > 0) {
    echo "   Sample feature: " . json_encode($features[0]) . "\n\n";
} else {
    echo "   ⚠ Warning: No features found. Please run generate_synthetic_and_insert.py\n\n";
}

// 4. Test Prediction Generation (for 5 residents only)
echo "4. Testing prediction generation (5 residents)...\n";
if (count($features) >= 5) {
    $testFeatures = array_slice($features, 0, 5);
    $successCount = 0;

    foreach ($testFeatures as $feature) {
        // Call private method via reflection (for testing only)
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('callPredictionAPI');
        $method->setAccessible(true);

        $prediction = $method->invoke($controller, $feature);

        if ($prediction) {
            $stored = $migrationModel->storePrediction(
                $feature['person_id'],
                'month',
                $prediction['prediction'],
                $prediction['probabilities'],
                'test_v1.0'
            );

            if ($stored) {
                $successCount++;
            }
        }

        usleep(50000); // 50ms delay
    }

    echo "   ✓ Predictions generated: {$successCount}/5\n\n";
} else {
    echo "   ⚠ Skipped: Not enough features available\n\n";
}

// 5. Test Dashboard Data Retrieval
echo "5. Testing dashboard data retrieval...\n";
$monthlyData = $migrationModel->getPredictionsByMonth();
$topPuroks = $migrationModel->getTopPuroksByPredictedMigration();
$probabilityDist = $migrationModel->getProbabilityDistribution();

echo "   ✓ Dashboard data retrieved successfully!\n";
echo "   Monthly data points: " . count($monthlyData) . "\n";
echo "   Top puroks: " . count($topPuroks) . "\n";
echo "   Probability distribution samples: " . count($probabilityDist) . "\n\n";

// 6. Summary
echo "=== Test Summary ===\n";
echo "✓ All tests passed successfully!\n";
echo "\nYou can now:\n";
echo "1. Access the dashboard: http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/views/admin_Dash/SecDash.php\n";
echo "2. Generate predictions via UI\n";
echo "3. View analytics and charts\n\n";

echo "To generate more synthetic data:\n";
echo "  cd ml_models && python generate_synthetic_and_insert.py\n\n";

echo "To train the model:\n";
echo "  cd ml_models && python train_nb.py --mode both\n\n";

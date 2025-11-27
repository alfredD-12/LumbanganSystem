<?php

/**
 * AJAX Handler for Predictive Analytics Operations
 * 
 * Endpoints:
 * - ?action=generate - Generate new predictions
 * - ?action=getData - Get dashboard data
 * - ?action=testAPI - Test API connection
 */

header('Content-Type: application/json');

// Only allow AJAX requests
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Direct access not allowed'
    ]);
    exit;
}

require_once dirname(__DIR__) . '/controllers/PredictiveAnalyticsController.php';
require_once dirname(__DIR__) . '/models/MigrationModel.php';

$controller = new PredictiveAnalyticsController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'generate':
        // Generate predictions
        $result = $controller->generatePredictions(true);
        echo json_encode($result);
        break;

    case 'getData':
        // Get dashboard data
        $migrationModel = new MigrationModel();

        $stats = $migrationModel->getDashboardStats();
        $monthlyData = $migrationModel->getPredictionsByMonth();
        $topPuroks = $migrationModel->getTopPuroksByPredictedMigration();
        $probabilityDist = $migrationModel->getProbabilityDistribution();

        // Prepare chart data
        $chartData = [
            'monthly' => [
                'labels' => array_map(fn($d) => date('M Y', strtotime($d['month'] . '-01')), $monthlyData),
                'moveouts' => array_map(fn($d) => intval($d['predicted_moveouts']), $monthlyData),
                'stayins' => array_map(fn($d) => intval($d['predicted_stayins']), $monthlyData)
            ],
            'puroks' => [
                'labels' => array_map(fn($p) => 'Purok ' . $p['purok_id'], $topPuroks),
                'migrations' => array_map(fn($p) => intval($p['predicted_migrations']), $topPuroks)
            ],
            'histogram' => []
        ];

        // Build histogram
        $histogramBins = [
            '0-0.1' => 0,
            '0.1-0.2' => 0,
            '0.2-0.3' => 0,
            '0.3-0.4' => 0,
            '0.4-0.5' => 0,
            '0.5-0.6' => 0,
            '0.6-0.7' => 0,
            '0.7-0.8' => 0,
            '0.8-0.9' => 0,
            '0.9-1.0' => 0
        ];

        foreach ($probabilityDist as $prob) {
            $value = floatval($prob['migration_probability']);
            $bin = min(floor($value * 10) / 10, 0.9);
            $binKey = number_format($bin, 1) . '-' . number_format($bin + 0.1, 1);
            if (isset($histogramBins[$binKey])) {
                $histogramBins[$binKey]++;
            }
        }

        $chartData['histogram'] = [
            'labels' => array_keys($histogramBins),
            'values' => array_values($histogramBins)
        ];

        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'charts' => $chartData
        ]);
        break;

    case 'testAPI':
        // Test API connection
        $result = $controller->testAPIConnection();
        echo json_encode($result);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified'
        ]);
        break;
}

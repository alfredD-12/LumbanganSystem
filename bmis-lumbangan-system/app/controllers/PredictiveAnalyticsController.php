<?php

require_once dirname(__DIR__) . '/models/MigrationModel.php';

class PredictiveAnalyticsController
{
    private $migrationModel;
    private $apiBaseUrl = 'http://127.0.0.1:8000';

    public function __construct()
    {
        $this->migrationModel = new MigrationModel();
    }

    /**
     * Generate predictions by calling Python FastAPI endpoint
     * 
     * @param bool $syntheticOnly - Use only synthetic data
     * @return array - Result with success status and message
     */
    public function generatePredictions($syntheticOnly = true)
    {
        // Get features for residents
        $features = $this->migrationModel->computeFeaturesForNextMonth($syntheticOnly);

        if (empty($features)) {
            return [
                'success' => false,
                'message' => 'No resident data found for prediction',
                'processed' => 0
            ];
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        // Process each resident
        foreach ($features as $feature) {
            try {
                // Call Python API
                $prediction = $this->callPredictionAPI($feature);

                if ($prediction && isset($prediction['prediction'])) {
                    // Store prediction in database
                    $stored = $this->migrationModel->storePrediction(
                        $feature['person_id'],
                        'month',
                        $prediction['prediction'],
                        $prediction['probabilities'] ?? [0.5, 0.5],
                        'nb_v1.0'
                    );

                    if ($stored) {
                        $successCount++;
                    } else {
                        $failCount++;
                        $errors[] = "Failed to store prediction for person {$feature['person_id']}";
                    }
                } else {
                    $failCount++;
                    $errors[] = "Invalid prediction response for person {$feature['person_id']}";
                }

                // Small delay to prevent API overload
                usleep(10000); // 10ms delay

            } catch (Exception $e) {
                $failCount++;
                $errors[] = "Error processing person {$feature['person_id']}: " . $e->getMessage();
            }
        }

        return [
            'success' => $successCount > 0,
            'message' => "Processed {$successCount} predictions successfully, {$failCount} failed",
            'processed' => $successCount,
            'failed' => $failCount,
            'errors' => array_slice($errors, 0, 5) // Return first 5 errors only
        ];
    }

    /**
     * Call Python FastAPI prediction endpoint
     * 
     * @param array $features - Feature data for prediction
     * @return array|null - Prediction result or null on failure
     */
    private function callPredictionAPI($features)
    {
        $url = $this->apiBaseUrl . '/predict';

        $postData = [
            'age' => floatval($features['age']),
            'household_size' => intval($features['household_size']),
            'sex' => $features['sex'],
            'to_purok_id' => intval($features['to_purok_id']),
            'timeframe' => 'month'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("cURL Error: " . $error);
            return null;
        }

        if ($httpCode !== 200) {
            error_log("API returned HTTP {$httpCode}: {$response}");
            return null;
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            return null;
        }

        return $result;
    }

    /**
     * Show predictions dashboard
     */
    public function showDashboard()
    {
        // Get all dashboard data
        $stats = $this->migrationModel->getDashboardStats();
        $monthlyData = $this->migrationModel->getPredictionsByMonth();
        $topPuroks = $this->migrationModel->getTopPuroksByPredictedMigration();
        $probabilityDist = $this->migrationModel->getProbabilityDistribution();

        // Prepare data for Chart.js
        $chartData = $this->prepareChartData($monthlyData, $topPuroks, $probabilityDist);

        // Load view
        require_once dirname(__DIR__) . '/views/admins/predictions_dashboard.php';
    }

    /**
     * Prepare data for Chart.js visualization
     * 
     * @param array $monthlyData
     * @param array $topPuroks
     * @param array $probabilityDist
     * @return array
     */
    private function prepareChartData($monthlyData, $topPuroks, $probabilityDist)
    {
        // Monthly predictions data
        $months = [];
        $moveouts = [];
        $stayins = [];

        foreach ($monthlyData as $data) {
            $months[] = date('M Y', strtotime($data['month'] . '-01'));
            $moveouts[] = intval($data['predicted_moveouts']);
            $stayins[] = intval($data['predicted_stayins']);
        }

        // Top puroks data
        $purokLabels = [];
        $purokMigrations = [];
        $purokProbabilities = [];

        foreach ($topPuroks as $purok) {
            $purokLabels[] = 'Purok ' . $purok['purok_id'];
            $purokMigrations[] = intval($purok['predicted_migrations']);
            $purokProbabilities[] = round(floatval($purok['avg_migration_probability']) * 100, 2);
        }

        // Probability distribution (histogram bins)
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

        return [
            'monthly' => [
                'labels' => $months,
                'moveouts' => $moveouts,
                'stayins' => $stayins
            ],
            'puroks' => [
                'labels' => $purokLabels,
                'migrations' => $purokMigrations,
                'probabilities' => $purokProbabilities
            ],
            'histogram' => [
                'labels' => array_keys($histogramBins),
                'values' => array_values($histogramBins)
            ]
        ];
    }

    /**
     * AJAX endpoint to generate predictions
     */
    public function ajaxGeneratePredictions()
    {
        header('Content-Type: application/json');

        // Check if request is AJAX
        if (
            !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
            return;
        }

        // Generate predictions
        $result = $this->generatePredictions(true);

        echo json_encode($result);
    }

    /**
     * AJAX endpoint to get dashboard data
     */
    public function ajaxGetDashboardData()
    {
        header('Content-Type: application/json');

        $stats = $this->migrationModel->getDashboardStats();
        $monthlyData = $this->migrationModel->getPredictionsByMonth();
        $topPuroks = $this->migrationModel->getTopPuroksByPredictedMigration();
        $probabilityDist = $this->migrationModel->getProbabilityDistribution();

        $chartData = $this->prepareChartData($monthlyData, $topPuroks, $probabilityDist);

        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'charts' => $chartData
        ]);
    }

    /**
     * Test API connection
     */
    public function testAPIConnection()
    {
        $testFeature = [
            'age' => 30.0,
            'household_size' => 4,
            'sex' => 'M',
            'to_purok_id' => 1,
            'timeframe' => 'month'
        ];

        $result = $this->callPredictionAPI($testFeature);

        return [
            'success' => $result !== null,
            'message' => $result ? 'API connection successful' : 'API connection failed',
            'response' => $result
        ];
    }
}

<?php

/**
 * Quick test script to verify Python API connection
 */

echo "<h2>Testing Python API Connection</h2>";

// Test 1: Simple connection test
echo "<h3>Test 1: Basic Connection</h3>";
$ch = curl_init('http://127.0.0.1:8000/docs');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Connection Error: " . $error . "<br>";
} else {
    echo "✅ HTTP Code: " . $httpCode . "<br>";
    echo "✅ API is accessible<br>";
}

// Test 2: Prediction endpoint
echo "<h3>Test 2: Prediction Endpoint</h3>";
$url = 'http://127.0.0.1:8000/predict';
$data = [
    'age' => 30.0,
    'household_size' => 4,
    'sex' => 'M',
    'to_purok_id' => 1,
    'timeframe' => 'month'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: " . $url . "<br>";
echo "Request Data: " . json_encode($data) . "<br>";

if ($error) {
    echo "❌ cURL Error: " . $error . "<br>";
} else {
    echo "✅ HTTP Code: " . $httpCode . "<br>";
    echo "Response: <pre>" . htmlspecialchars($response) . "</pre><br>";

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if ($result) {
            echo "✅ <strong>Prediction successful!</strong><br>";
            echo "Prediction: " . $result['prediction'] . "<br>";
            echo "Probabilities: " . json_encode($result['probabilities']) . "<br>";
        } else {
            echo "❌ Failed to decode JSON response<br>";
        }
    } else {
        echo "❌ HTTP error code: " . $httpCode . "<br>";
    }
}

// Test 3: Test with controller
echo "<h3>Test 3: Using PredictiveAnalyticsController</h3>";
require_once __DIR__ . '/app/controllers/PredictiveAnalyticsController.php';

try {
    $controller = new PredictiveAnalyticsController();
    $testResult = $controller->testAPIConnection();

    if ($testResult['success']) {
        echo "✅ <strong>Controller test passed!</strong><br>";
        echo "Message: " . $testResult['message'] . "<br>";
        echo "Response: <pre>" . json_encode($testResult['response'], JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "❌ Controller test failed: " . $testResult['message'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "<p>If all tests pass, the Python API is working correctly.</p>";
echo "<p>If tests fail, check:</p>";
echo "<ul>";
echo "<li>Is the Python API server running? (http://127.0.0.1:8000/docs)</li>";
echo "<li>Is the port 8000 accessible from PHP?</li>";
echo "<li>Are there any firewall issues?</li>";
echo "</ul>";

<?php
// Authentication temporarily disabled for development
// require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
// requireOfficial(); // Only allow admin/official users to access this page

$adminName = 'Admin Secretary'; // Default for development
$adminRole = 'Barangay Administrator';

// Load dashboard data configuration
$dashboardData = require_once dirname(__DIR__, 2) . '/config/dashboard_data.php';

// Load component functions
require_once dirname(__DIR__, 2) . '/components/admin_components/stat-card.php';
require_once dirname(__DIR__, 2) . '/components/admin_components/activity-item.php';
require_once dirname(__DIR__, 2) . '/components/admin_components/modal-items.php';

// Load predictive analytics data
require_once dirname(__DIR__, 2) . '/controllers/PredictiveAnalyticsController.php';
$predictiveController = new PredictiveAnalyticsController();
require_once dirname(__DIR__, 2) . '/models/MigrationModel.php';
$migrationModel = new MigrationModel();

// Get prediction statistics and chart data
$predictionStats = $migrationModel->getDashboardStats();
$monthlyPredictions = $migrationModel->getPredictionsByMonth();
$topPuroks = $migrationModel->getTopPuroksByPredictedMigration();
$probabilityDist = $migrationModel->getProbabilityDistribution();

// Prepare chart data for JavaScript
$predictionChartData = [
    'monthly' => [
        'labels' => array_map(fn($d) => date('M Y', strtotime($d['month'] . '-01')), $monthlyPredictions),
        'moveouts' => array_map(fn($d) => intval($d['predicted_moveouts']), $monthlyPredictions),
        'stayins' => array_map(fn($d) => intval($d['predicted_stayins']), $monthlyPredictions)
    ],
    'puroks' => [
        'labels' => array_map(fn($p) => 'Purok ' . $p['purok_id'], $topPuroks),
        'migrations' => array_map(fn($p) => intval($p['predicted_migrations']), $topPuroks)
    ],
    'histogram' => []
];

// Build histogram data
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
$predictionChartData['histogram'] = [
    'labels' => array_keys($histogramBins),
    'values' => array_values($histogramBins)
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Barangay Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if (!defined('BASE_URL')) {
        require_once dirname(__DIR__, 2) . '/config/config.php';
    } ?>
    <link rel="stylesheet" href="<?php echo rtrim(BASE_URL, '/'); ?>/assets/css/SecDash/secDash.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo rtrim(BASE_URL, '/'); ?>/assets/css/predictions_dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Sidebar Component -->
    <?php
    $currentPage = 'admin_dashboard'; // Set current page for active menu highlighting
    require_once dirname(__DIR__, 2) . '/components/admin_components/sidebar-admin.php';
    ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Bar Component -->
        <?php
        $pageTitle = 'Barangay Lumbangan Analytics Dashboard';
        $pageSubtitle = 'Monitoring and managing barangay operations and resident services';
        // $adminName and $adminRole already set from session at top
        require_once dirname(__DIR__, 2) . '/components/admin_components/topbar-admin.php';
        ?>

        <!-- Content Section -->
        <div class="content-section">
            <!-- Stats Cards -->
            <div class="stats-row">
                <?php foreach ($dashboardData['stats'] as $stat): ?>
                    <?php renderStatCard($stat); ?>
                <?php endforeach; ?>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Chart Section -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Registered Residents Overview</h3>
                        <div class="chart-tabs">
                            <button class="chart-tab active">Monthly</button>
                            <button class="chart-tab">Yearly</button>
                        </div>
                    </div>
                    <div style="text-align: center; margin: 1.5rem 0;">
                        <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-blue);">
                            <i class="fas fa-arrow-up" style="color: var(--success-green); font-size: 1.8rem;"></i>
                            2,847
                        </div>
                        <div style="font-size: 0.9rem; color: #718096; margin-top: 0.5rem;">TOTAL REGISTERED RESIDENTS</div>
                    </div>
                    <div class="chart-container">
                        <div class="line-chart">
                            <svg viewBox="0 0 700 300" preserveAspectRatio="xMidYMid meet">
                                <!-- Define gradients -->
                                <defs>
                                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#1e3a5f;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#2c5282;stop-opacity:1" />
                                    </linearGradient>
                                    <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:#1e3a5f;stop-opacity:0.4" />
                                        <stop offset="100%" style="stop-color:#1e3a5f;stop-opacity:0.05" />
                                    </linearGradient>
                                </defs>

                                <!-- Grid lines -->
                                <line x1="50" y1="40" x2="550" y2="40" class="chart-grid-line" />
                                <line x1="50" y1="90" x2="550" y2="90" class="chart-grid-line" />
                                <line x1="50" y1="140" x2="550" y2="140" class="chart-grid-line" />
                                <line x1="50" y1="190" x2="550" y2="190" class="chart-grid-line" />

                                <!-- Area under the line -->
                                <path class="chart-area" d="M 70,110 L 150,90 L 230,130 L 310,70 L 390,100 L 470,50 L 550,80 L 550,200 L 70,200 Z" />

                                <!-- Line path -->
                                <path class="chart-line" d="M 70,110 L 150,90 L 230,130 L 310,70 L 390,100 L 470,50 L 550,80"
                                    style="stroke-dasharray: 1000; stroke-dashoffset: 1000; animation: drawLine 2s ease-out forwards;" />

                                <!-- Data points -->
                                <circle class="chart-point" cx="70" cy="110" r="6" />
                                <circle class="chart-point" cx="150" cy="90" r="6" />
                                <circle class="chart-point" cx="230" cy="130" r="6" />
                                <circle class="chart-point" cx="310" cy="70" r="6" />
                                <circle class="chart-point" cx="390" cy="100" r="6" />
                                <circle class="chart-point" cx="470" cy="50" r="6" />
                                <circle class="chart-point" cx="550" cy="80" r="6" />

                                <!-- Value labels -->
                                <text x="70" y="100" class="chart-value-label" text-anchor="middle">215</text>
                                <text x="150" y="80" class="chart-value-label" text-anchor="middle">248</text>
                                <text x="230" y="120" class="chart-value-label" text-anchor="middle">189</text>
                                <text x="310" y="60" class="chart-value-label" text-anchor="middle">267</text>
                                <text x="390" y="90" class="chart-value-label" text-anchor="middle">231</text>
                                <text x="470" y="40" class="chart-value-label" text-anchor="middle">298</text>
                                <text x="550" y="70" class="chart-value-label" text-anchor="middle">254</text>

                                <!-- X-axis labels -->
                                <text x="70" y="220" class="chart-label" text-anchor="middle">Mon</text>
                                <text x="150" y="220" class="chart-label" text-anchor="middle">Tue</text>
                                <text x="230" y="220" class="chart-label" text-anchor="middle">Wed</text>
                                <text x="310" y="220" class="chart-label" text-anchor="middle">Thu</text>
                                <text x="390" y="220" class="chart-label" text-anchor="middle">Fri</text>
                                <text x="470" y="220" class="chart-label" text-anchor="middle">Sat</text>
                                <text x="550" y="220" class="chart-label" text-anchor="middle">Sun</text>
                            </svg>
                        </div>
                    </div>

                    <style>
                        @keyframes drawLine {
                            to {
                                stroke-dashoffset: 0;
                            }
                        }

                        .chart-point {
                            animation: popIn 0.5s ease-out backwards;
                        }

                        .chart-point:nth-child(5) {
                            animation-delay: 0.3s;
                        }

                        .chart-point:nth-child(6) {
                            animation-delay: 0.4s;
                        }

                        .chart-point:nth-child(7) {
                            animation-delay: 0.5s;
                        }

                        .chart-point:nth-child(8) {
                            animation-delay: 0.6s;
                        }

                        .chart-point:nth-child(9) {
                            animation-delay: 0.7s;
                        }

                        .chart-point:nth-child(10) {
                            animation-delay: 0.8s;
                        }

                        .chart-point:nth-child(11) {
                            animation-delay: 0.9s;
                        }

                        @keyframes popIn {
                            0% {
                                r: 0;
                                opacity: 0;
                            }

                            50% {
                                r: 8;
                            }

                            100% {
                                r: 6;
                                opacity: 1;
                            }
                        }
                    </style>
                    <div class="chart-summary">
                        <div style="text-align: center; margin-bottom: 1rem;">
                            <div style="font-weight: 700; color: var(--primary-blue);">REGISTRATION PROGRESS</div>
                        </div>
                        <div class="chart-summary-header">
                            <span>New Registrations (This Month)</span>
                            <span>124</span>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.85rem; color: #718096; margin-bottom: 0.5rem;">Last 7 days</div>
                            <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                <div style="width: 78%; height: 100%; background: linear-gradient(90deg, var(--primary-blue), var(--secondary-blue));"></div>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #718096;">
                            <span>Growth Rate</span>
                            <span style="font-weight: 600; color: var(--success-green);">+4.5%</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Complaints Timeline -->
                <div class="activity-card">
                    <h3 class="activity-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        Recent Complaints
                    </h3>

                    <?php foreach ($dashboardData['complaints'] as $complaint): ?>
                        <?php renderActivityItem($complaint); ?>
                    <?php endforeach; ?>

                    <button class="view-all-btn">
                        <i class="fas fa-list"></i> View All Complaints
                    </button>
                </div>

                <!-- Document Management Card -->
                <div class="activity-card document-management-card">
                    <h3 class="activity-header">
                        <i class="fas fa-file-signature"></i>
                        Document Management
                    </h3>

                    <!-- Pending Approvals (Status: Pending) -->
                    <div class="doc-section">
                        <div class="doc-section-header">
                            <i class="fas fa-hourglass-half"></i>
                            <span>Pending Approvals</span>
                        </div>
                        <div class="doc-items">
                            <?php foreach ($dashboardData['documents']['pending_approvals'] as $doc): ?>
                                <div class="doc-mini-item">
                                    <span><?php echo htmlspecialchars($doc['name']); ?></span>
                                    <span class="doc-badge"><?php echo $doc['count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Today's Released Documents -->
                    <div class="doc-section">
                        <div class="doc-section-header">
                            <i class="fas fa-check-circle"></i>
                            <span>Today's Released</span>
                        </div>
                        <div class="doc-stats-grid">
                            <?php foreach ($dashboardData['documents']['today_released'] as $doc): ?>
                                <div class="doc-stat-item">
                                    <div class="doc-stat-num"><?php echo $doc['num']; ?></div>
                                    <div class="doc-stat-label"><?php echo htmlspecialchars($doc['label']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Document Request Queue (Status Breakdown) -->
                    <div class="doc-section">
                        <div class="doc-section-header">
                            <i class="fas fa-layer-group"></i>
                            <span>Request Queue</span>
                        </div>
                        <div class="doc-queue-list">
                            <?php foreach ($dashboardData['documents']['queue'] as $queue): ?>
                                <div class="queue-item">
                                    <div class="queue-bar" style="width: <?php echo $queue['width']; ?>;">
                                        <span class="queue-label"><?php echo htmlspecialchars($queue['label']); ?></span>
                                    </div>
                                    <span class="queue-count"><?php echo $queue['count']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Monthly Document Processing Progress -->
                    <div class="doc-section">
                        <div class="doc-section-header">
                            <i class="fas fa-chart-line"></i>
                            <span>Monthly Progress</span>
                            <span class="doc-count info"><?php echo $dashboardData['documents']['monthly_progress']['percentage']; ?>%</span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-label-row">
                                <span><?php echo $dashboardData['documents']['monthly_progress']['current']; ?> / <?php echo $dashboardData['documents']['monthly_progress']['target']; ?> Target</span>
                                <span><?php echo $dashboardData['documents']['monthly_progress']['remaining']; ?> to go</span>
                            </div>
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="width: <?php echo $dashboardData['documents']['monthly_progress']['percentage']; ?>%;">
                                    <span class="progress-percentage"><?php echo $dashboardData['documents']['monthly_progress']['percentage']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar/Events Widget -->
                <div class="activity-card calendar-widget">
                    <h3 class="activity-header">
                        <i class="fas fa-calendar-alt"></i>
                        Upcoming Events
                    </h3>

                    <!-- Events List -->
                    <div class="events-list">
                        <div class="event-item event-meeting">
                            <div class="event-date">
                                <div class="event-day">14</div>
                                <div class="event-month">NOV</div>
                            </div>
                            <div class="event-details">
                                <div class="event-title">Barangay Council Meeting</div>
                                <div class="event-time">
                                    <i class="fas fa-clock"></i> 2:00 PM - 5:00 PM
                                </div>
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i> Barangay Hall
                                </div>
                            </div>
                            <div class="event-badge event-badge-meeting">Meeting</div>
                        </div>

                        <div class="event-item event-activity">
                            <div class="event-date">
                                <div class="event-day">18</div>
                                <div class="event-month">NOV</div>
                            </div>
                            <div class="event-details">
                                <div class="event-title">Community Clean-up Drive</div>
                                <div class="event-time">
                                    <i class="fas fa-clock"></i> 6:00 AM - 12:00 PM
                                </div>
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i> Plaza Area
                                </div>
                            </div>
                            <div class="event-badge event-badge-activity">Activity</div>
                        </div>

                        <div class="event-item event-important">
                            <div class="event-date">
                                <div class="event-day">22</div>
                                <div class="event-month">NOV</div>
                            </div>
                            <div class="event-details">
                                <div class="event-title">Document Processing Deadline</div>
                                <div class="event-time">
                                    <i class="fas fa-clock"></i> 5:00 PM
                                </div>
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i> Secretary Office
                                </div>
                            </div>
                            <div class="event-badge event-badge-important">Important</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Migration Predictions Analytics Section -->
            <div class="dashboard-grid" style="margin-top: 2rem;">
                <div class="chart-card" style="grid-column: 1 / -1; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem; border-radius: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 2px solid rgba(255, 255, 255, 0.2);">
                        <h3 style="color: white; font-size: 1.75rem; font-weight: 700; display: flex; align-items: center; gap: 1rem; margin: 0;">
                            <i class="fas fa-brain" style="font-size: 2rem;"></i>
                            Migration Predictions Analytics
                        </h3>
                        <button class="btn" id="generatePredictionsBtn" style="background: white; color: #667eea; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">
                            <i class="fas fa-magic"></i>
                            Generate Predictions
                        </button>
                    </div>

                    <!-- Alert Container -->
                    <div id="alertContainer" style="margin-bottom: 1rem;"></div>

                    <!-- Statistics Cards -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <div style="width: 50px; height: 50px; border-radius: 10px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <i class="fas fa-chart-line" style="color: white; font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #2D3748; margin-bottom: 0.5rem;" id="totalPredictions">
                                <?php echo number_format($predictionStats['total_predictions'] ?? 0); ?>
                            </div>
                            <div style="color: #718096; font-size: 0.9rem; font-weight: 500;">Total Predictions</div>
                        </div>

                        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <div style="width: 50px; height: 50px; border-radius: 10px; background: linear-gradient(135deg, #56CCF2, #2F80ED); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <i class="fas fa-home" style="color: white; font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #2D3748; margin-bottom: 0.5rem;" id="predictedStayins">
                                <?php echo number_format($predictionStats['total_predicted_stayins'] ?? 0); ?>
                            </div>
                            <div style="color: #718096; font-size: 0.9rem; font-weight: 500;">Predicted Stay-Ins</div>
                        </div>

                        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <div style="width: 50px; height: 50px; border-radius: 10px; background: linear-gradient(135deg, #F2994A, #F2C94C); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <i class="fas fa-people-arrows" style="color: white; font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #2D3748; margin-bottom: 0.5rem;" id="predictedMoveouts">
                                <?php echo number_format($predictionStats['total_predicted_moveouts'] ?? 0); ?>
                            </div>
                            <div style="color: #718096; font-size: 0.9rem; font-weight: 500;">Predicted Move-Outs</div>
                        </div>

                        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <div style="width: 50px; height: 50px; border-radius: 10px; background: linear-gradient(135deg, #EB5757, #000000); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                <i class="fas fa-percentage" style="color: white; font-size: 1.5rem;"></i>
                            </div>
                            <div style="font-size: 2rem; font-weight: 700; color: #2D3748; margin-bottom: 0.5rem;" id="avgProbability">
                                <?php echo number_format(($predictionStats['avg_moveout_probability'] ?? 0) * 100, 1); ?>%
                            </div>
                            <div style="color: #718096; font-size: 0.9rem; font-weight: 500;">Avg. Move-Out Probability</div>
                        </div>
                    </div>

                    <!-- Charts Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.5rem;">
                        <!-- Monthly Predictions Chart -->
                        <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h4 style="font-size: 1.1rem; font-weight: 600; color: #2D3748; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-calendar-alt"></i>
                                Monthly Migration Predictions
                            </h4>
                            <canvas id="monthlyPredictionsChart" style="max-height: 300px;"></canvas>
                        </div>

                        <!-- Top Puroks Chart -->
                        <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h4 style="font-size: 1.1rem; font-weight: 600; color: #2D3748; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-map-marked-alt"></i>
                                Top Puroks by Predicted Migration
                            </h4>
                            <canvas id="topPuroksChart" style="max-height: 300px;"></canvas>
                        </div>

                        <!-- Probability Distribution -->
                        <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h4 style="font-size: 1.1rem; font-weight: 600; color: #2D3748; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-chart-bar"></i>
                                Probability Distribution
                            </h4>
                            <canvas id="probabilityHistogramChart" style="max-height: 300px;"></canvas>
                        </div>

                        <!-- Cumulative Trend -->
                        <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                            <h4 style="font-size: 1.1rem; font-weight: 600; color: #2D3748; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-chart-area"></i>
                                Cumulative Predictions Trend
                            </h4>
                            <canvas id="cumulativeTrendChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales & Income Section -->
            <div class="dashboard-grid" style="margin-top: 2rem;">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Document Requests Statistics</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-top: 1.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--primary-blue);">67</div>
                            <div style="font-size: 0.85rem; color: #718096; margin-top: 0.25rem;">Pending Requests</div>
                            <div style="margin-top: 1rem; height: 60px; display: flex; align-items: flex-end; justify-content: center; gap: 4px;">
                                <div style="width: 8px; height: 30%; background: var(--warning-yellow); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 50%; background: var(--warning-yellow); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 70%; background: var(--warning-yellow); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 40%; background: var(--warning-yellow); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 100%; background: var(--accent-red); border-radius: 2px;"></div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--primary-blue);">342</div>
                            <div style="font-size: 0.85rem; color: #718096; margin-top: 0.25rem;">Completed This Month</div>
                            <div style="margin-top: 1rem; height: 60px; display: flex; align-items: flex-end; justify-content: center; gap: 4px;">
                                <div style="width: 8px; height: 40%; background: var(--success-green); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 100%; background: var(--accent-red); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 60%; background: var(--success-green); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 80%; background: var(--success-green); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 50%; background: var(--success-green); border-radius: 2px;"></div>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; color: var(--primary-blue);">1,245</div>
                            <div style="font-size: 0.85rem; color: #718096; margin-top: 0.25rem;">Total Documents Issued</div>
                            <div style="margin-top: 1rem; height: 60px; display: flex; align-items: flex-end; justify-content: center; gap: 4px;">
                                <div style="width: 8px; height: 70%; background: var(--primary-blue); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 50%; background: var(--primary-blue); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 90%; background: var(--primary-blue); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 100%; background: var(--accent-red); border-radius: 2px;"></div>
                                <div style="width: 8px; height: 60%; background: var(--primary-blue); border-radius: 2px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Barangay Officials</h3>
                    </div>
                    <div style="padding: 1.5rem 0;">
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(30, 58, 95, 0.05); border-radius: 12px; margin-bottom: 1rem;">
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem;">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--dark-text); font-size: 0.95rem;">Punong Barangay</div>
                                <div style="font-size: 0.8rem; color: #718096;">Hon. Juan Dela Cruz</div>
                            </div>
                        </div>

                        <div style="text-align: center; padding: 2rem 1rem;">
                            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary-blue); margin-bottom: 0.5rem;">15</div>
                            <div style="font-size: 0.9rem; color: #718096; margin-bottom: 1.5rem;">Active Officials</div>
                            <div style="display: flex; justify-content: center; gap: 1.5rem; margin-top: 1.5rem;">
                                <div>
                                    <div style="width: 70px; height: 70px; border-radius: 50%; border: 6px solid var(--primary-blue); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--primary-blue); margin: 0 auto; font-size: 1.2rem;">7</div>
                                    <div style="font-size: 0.75rem; color: #718096; margin-top: 0.5rem;">Kagawad</div>
                                </div>
                                <div>
                                    <div style="width: 70px; height: 70px; border-radius: 50%; border: 6px solid var(--success-green); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--success-green); margin: 0 auto; font-size: 1.2rem;">7</div>
                                    <div style="font-size: 0.75rem; color: #718096; margin-top: 0.5rem;">SK & Staff</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-text">
                    © 2024 <strong>Barangay Lumbangan</strong> - Admin Dashboard System
                </div>
                <div class="footer-links">
                    <a href="#"><i class="fas fa-info-circle"></i> About</a>
                    <a href="#"><i class="fas fa-life-ring"></i> Support</a>
                    <a href="#"><i class="fas fa-file-contract"></i> Terms</a>
                </div>
            </div>
        </footer>
    </main>

    <!-- Admin Profile Modal -->
    <div class="modal fade" id="adminProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                <!-- Modal Header -->
                <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                    <h5 class="modal-title" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;"><i class="fas fa-id-card"></i> Admin Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 2rem;">
                    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 2rem; align-items: center;">
                        <!-- Left: Avatar -->
                        <div style="text-align: center;">
                            <div style="width: 120px; height: 120px; border-radius: 12px; background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 4rem; font-weight: 600;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <!-- Right: Profile Information -->
                        <div>
                            <div class="profile-info-item" style="margin-bottom: 1rem;">
                                <label style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.3rem;">Full Name</label>
                                <p id="adminProfileName" style="color: var(--primary-blue); font-weight: 600; font-size: 1rem; margin: 0;">Admin Secretary</p>
                            </div>

                            <div class="profile-info-item" style="margin-bottom: 1rem;">
                                <label style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.3rem;">Role</label>
                                <p id="adminProfileRole" style="color: #666; font-size: 0.95rem; margin: 0;">Barangay Administrator</p>
                            </div>

                            <div class="profile-info-item" style="margin-bottom: 1rem;">
                                <label style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.3rem;">Email Address</label>
                                <p id="adminProfileEmail" style="color: #666; font-size: 0.95rem; margin: 0;">admin.secretary@lumbangan.gov.ph</p>
                            </div>

                            <div class="profile-info-item">
                                <label style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.3rem;">Contact Number</label>
                                <p id="adminProfileContact" style="color: #666; font-size: 0.95rem; margin: 0;">+63 912-345-6789</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 2rem; background: white; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm" style="background: white; border: 1px solid #ddd; color: #666; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500;" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500;" data-bs-toggle="modal" data-bs-target="#editAdminProfileModal" data-bs-dismiss="modal">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Admin Profile Modal -->
    <div class="modal fade" id="editAdminProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                <!-- Modal Header -->
                <div class="modal-header" style="background: white; border-bottom: 1px solid #f0f0f0; border-radius: 12px 12px 0 0; padding: 1.5rem;">
                    <h5 class="modal-title" style="color: var(--primary-blue); font-weight: 600; font-size: 1.1rem;"><i class="fas fa-user-edit"></i> Edit Admin Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body" style="padding: 2rem;">
                    <form id="editAdminProfileForm">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div>
                                <label for="editAdminName" style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.5rem;">Full Name</label>
                                <input type="text" name="full_name" class="form-control" id="editAdminName" value="Admin Secretary" placeholder="Enter your full name" style="border-radius: 6px; padding: 0.6rem 0.8rem; border: 1px solid #e0e0e0; font-size: 0.95rem;">
                            </div>

                            <div>
                                <label for="editAdminEmail" style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.5rem;">Email Address</label>
                                <input type="email" name="email" class="form-control" id="editAdminEmail" value="admin.secretary@lumbangan.gov.ph" placeholder="Enter your email" style="border-radius: 6px; padding: 0.6rem 0.8rem; border: 1px solid #e0e0e0; font-size: 0.95rem;">
                            </div>

                            <div style="grid-column: 1 / -1;">
                                <label for="editAdminContact" style="color: #999; font-weight: 500; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 0.5rem;">Contact Number</label>
                                <input type="tel" name="contact_no" class="form-control" id="editAdminContact" value="+63 912-345-6789" placeholder="Enter your contact number" style="border-radius: 6px; padding: 0.6rem 0.8rem; border: 1px solid #e0e0e0; font-size: 0.95rem;">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 2rem; background: white; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-sm" style="background: white; border: 1px solid #ddd; color: #666; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500;" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm" style="background: var(--primary-blue); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 6px; font-weight: 500;" onclick="saveAdminProfileChanges()">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/SecDash/SecDash.js"></script>

    <!-- Initialize Bootstrap Dropdowns -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the dropdown toggle element
            const dropdownToggle = document.querySelector('.admin-avatar.dropdown-toggle');
            const dropdownMenu = document.querySelector('.admin-profile .dropdown-menu');

            if (dropdownToggle && dropdownMenu) {
                // Manual click handler
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Toggle show class
                    dropdownMenu.classList.toggle('show');
                    dropdownToggle.classList.toggle('show');

                    console.log('Dropdown clicked! Menu is now:', dropdownMenu.classList.contains('show') ? 'VISIBLE' : 'HIDDEN');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                        dropdownToggle.classList.remove('show');
                    }
                });
            }

            // Initialize all Bootstrap dropdowns as backup
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });

        // Global fix for Bootstrap modal backdrop cleanup after close
        document.addEventListener('DOMContentLoaded', function() {
            // Listen to all modal HIDE events (after animation completes)
            const allModals = document.querySelectorAll('.modal');
            allModals.forEach(modal => {
                modal.addEventListener('hidden.bs.modal', function() {
                    // Wait for Bootstrap's fade animation to complete
                    setTimeout(() => {
                        // Only cleanup if NO modals are currently shown
                        if (!document.querySelector('.modal.show')) {
                            // Remove any orphaned backdrops
                            const backdrops = document.querySelectorAll('.modal-backdrop');
                            backdrops.forEach(backdrop => backdrop.remove());

                            // Unlock body scroll
                            document.body.classList.remove('modal-open');
                            document.body.style.paddingRight = '';
                            document.body.style.overflow = '';
                        }
                    }, 350); // Match Bootstrap's fade transition time
                });
            });
        });
    </script>

    <!-- Prediction Charts and AJAX Script -->
    <script>
        // Prediction chart data from PHP
        const predictionChartData = <?php echo json_encode($predictionChartData); ?>;

        // Chart instances
        let monthlyPredChart, topPuroksChart, histogramChart, cumulativeChart;

        // Initialize prediction charts
        function initializePredictionCharts() {
            // Monthly Predictions Bar Chart
            const monthlyCtx = document.getElementById('monthlyPredictionsChart');
            if (monthlyCtx) {
                monthlyPredChart = new Chart(monthlyCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: predictionChartData.monthly.labels,
                        datasets: [{
                                label: 'Predicted Move-Outs',
                                data: predictionChartData.monthly.moveouts,
                                backgroundColor: 'rgba(235, 87, 87, 0.8)',
                                borderColor: 'rgba(235, 87, 87, 1)',
                                borderWidth: 2,
                                borderRadius: 6
                            },
                            {
                                label: 'Predicted Stay-Ins',
                                data: predictionChartData.monthly.stayins,
                                backgroundColor: 'rgba(86, 204, 242, 0.8)',
                                borderColor: 'rgba(86, 204, 242, 1)',
                                borderWidth: 2,
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 11,
                                        family: 'Poppins'
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Top Puroks Pie Chart
            const puroksCtx = document.getElementById('topPuroksChart');
            if (puroksCtx) {
                topPuroksChart = new Chart(puroksCtx.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: predictionChartData.puroks.labels,
                        datasets: [{
                            data: predictionChartData.puroks.migrations,
                            backgroundColor: [
                                'rgba(102, 126, 234, 0.8)',
                                'rgba(118, 75, 162, 0.8)',
                                'rgba(237, 100, 166, 0.8)',
                                'rgba(242, 153, 74, 0.8)',
                                'rgba(86, 204, 242, 0.8)'
                            ],
                            borderWidth: 2,
                            borderColor: 'white'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        size: 11,
                                        family: 'Poppins'
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Probability Histogram
            const histogramCtx = document.getElementById('probabilityHistogramChart');
            if (histogramCtx) {
                histogramChart = new Chart(histogramCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: predictionChartData.histogram.labels,
                        datasets: [{
                            label: 'Frequency',
                            data: predictionChartData.histogram.values,
                            backgroundColor: 'rgba(102, 126, 234, 0.8)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 2,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count',
                                    font: {
                                        family: 'Poppins',
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Probability Range',
                                    font: {
                                        family: 'Poppins',
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Cumulative Trend Line Chart
            const cumulativeCtx = document.getElementById('cumulativeTrendChart');
            if (cumulativeCtx) {
                const cumulativeMoveouts = predictionChartData.monthly.moveouts.reduce((acc, val, idx) => {
                    acc.push((acc[idx - 1] || 0) + val);
                    return acc;
                }, []);

                const cumulativeStayins = predictionChartData.monthly.stayins.reduce((acc, val, idx) => {
                    acc.push((acc[idx - 1] || 0) + val);
                    return acc;
                }, []);

                cumulativeChart = new Chart(cumulativeCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: predictionChartData.monthly.labels,
                        datasets: [{
                                label: 'Cumulative Move-Outs',
                                data: cumulativeMoveouts,
                                borderColor: 'rgba(235, 87, 87, 1)',
                                backgroundColor: 'rgba(235, 87, 87, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 3
                            },
                            {
                                label: 'Cumulative Stay-Ins',
                                data: cumulativeStayins,
                                borderColor: 'rgba(86, 204, 242, 1)',
                                backgroundColor: 'rgba(86, 204, 242, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 11,
                                        family: 'Poppins'
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        }

        // Show alert message
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            if (!alertContainer) return;

            const alertDiv = document.createElement('div');
            alertDiv.style.cssText = `
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                gap: 1rem;
                animation: slideIn 0.3s ease;
            `;

            if (type === 'success') {
                alertDiv.style.background = '#D4EDDA';
                alertDiv.style.color = '#155724';
                alertDiv.style.border = '1px solid #C3E6CB';
            } else if (type === 'error') {
                alertDiv.style.background = '#F8D7DA';
                alertDiv.style.color = '#721C24';
                alertDiv.style.border = '1px solid #F5C6CB';
            } else {
                alertDiv.style.background = '#D1ECF1';
                alertDiv.style.color = '#0C5460';
                alertDiv.style.border = '1px solid #BEE5EB';
            }

            const icon = type === 'success' ? 'check-circle' :
                type === 'error' ? 'exclamation-circle' : 'info-circle';

            alertDiv.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            `;

            alertContainer.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateY(-10px)';
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }

        // Generate predictions AJAX handler
        document.getElementById('generatePredictionsBtn')?.addEventListener('click', async function() {
            const btn = this;
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

            try {
                const response = await fetch('<?php echo rtrim(BASE_URL, '/'); ?>/controllers/ajax_predictions.php?action=generate', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message || 'Predictions generated successfully!', 'success');

                    // Reload page after 2 seconds to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message || 'Failed to generate predictions', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred while generating predictions. Please ensure the Python API is running.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });

        // Initialize prediction charts on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializePredictionCharts();
        });
    </script>

    <!--  Include Topbar Modals Component (Notifications & Messages) -->
    <?php require_once dirname(__DIR__, 2) . '/components/admin_components/topbar-modals.php'; ?>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration Predictions Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .predictions-dashboard {
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            margin: 2rem 0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .dashboard-title {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .dashboard-title i {
            font-size: 2.5rem;
            opacity: 0.9;
        }

        .generate-btn {
            background: white;
            color: #667eea;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .generate-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #56CCF2, #2F80ED);
            color: white;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #F2994A, #F2C94C);
            color: white;
        }

        .stat-icon.red {
            background: linear-gradient(135deg, #EB5757, #000000);
            color: white;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2D3748;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #718096;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2D3748;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-canvas {
            max-height: 350px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }

        .alert-error {
            background: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }

        .alert-info {
            background: #D1ECF1;
            color: #0C5460;
            border: 1px solid #BEE5EB;
        }
    </style>
</head>

<body>
    <div class="predictions-dashboard">
        <div class="dashboard-header">
            <h1 class="dashboard-title">
                <i class="fas fa-brain"></i>
                Migration Predictions Analytics
            </h1>
            <button class="generate-btn" id="generatePredictionsBtn">
                <i class="fas fa-magic"></i>
                Generate Predictions
            </button>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value" id="totalPredictions"><?php echo number_format($stats['total_predictions'] ?? 0); ?></div>
                <div class="stat-label">Total Predictions</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-home"></i>
                </div>
                <div class="stat-value" id="predictedStayins"><?php echo number_format($stats['total_predicted_stayins'] ?? 0); ?></div>
                <div class="stat-label">Predicted Stay-Ins</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-people-arrows"></i>
                </div>
                <div class="stat-value" id="predictedMoveouts"><?php echo number_format($stats['total_predicted_moveouts'] ?? 0); ?></div>
                <div class="stat-label">Predicted Move-Outs</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value" id="avgProbability"><?php echo number_format(($stats['avg_moveout_probability'] ?? 0) * 100, 1); ?>%</div>
                <div class="stat-label">Avg. Move-Out Probability</div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Monthly Predictions Chart -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="fas fa-calendar-alt"></i>
                    Monthly Migration Predictions
                </h3>
                <canvas id="monthlyChart" class="chart-canvas"></canvas>
            </div>

            <!-- Top Puroks Chart -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="fas fa-map-marked-alt"></i>
                    Top Puroks by Predicted Migration
                </h3>
                <canvas id="puroksChart" class="chart-canvas"></canvas>
            </div>

            <!-- Probability Distribution Chart -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="fas fa-chart-bar"></i>
                    Probability Distribution
                </h3>
                <canvas id="histogramChart" class="chart-canvas"></canvas>
            </div>

            <!-- Cumulative Predictions Chart -->
            <div class="chart-container">
                <h3 class="chart-title">
                    <i class="fas fa-chart-area"></i>
                    Cumulative Predictions Trend
                </h3>
                <canvas id="cumulativeChart" class="chart-canvas"></canvas>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Chart Data from PHP
        const chartData = <?php echo json_encode($chartData); ?>;

        // Chart instances
        let monthlyChart, puroksChart, histogramChart, cumulativeChart;

        // Initialize all charts
        function initializeCharts() {
            // Monthly Predictions Bar Chart
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: chartData.monthly.labels,
                    datasets: [{
                            label: 'Predicted Move-Outs',
                            data: chartData.monthly.moveouts,
                            backgroundColor: 'rgba(235, 87, 87, 0.8)',
                            borderColor: 'rgba(235, 87, 87, 1)',
                            borderWidth: 2,
                            borderRadius: 6
                        },
                        {
                            label: 'Predicted Stay-Ins',
                            data: chartData.monthly.stayins,
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
                                    size: 12,
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

            // Top Puroks Pie Chart
            const puroksCtx = document.getElementById('puroksChart').getContext('2d');
            puroksChart = new Chart(puroksCtx, {
                type: 'pie',
                data: {
                    labels: chartData.puroks.labels,
                    datasets: [{
                        data: chartData.puroks.migrations,
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
                                    size: 12,
                                    family: 'Poppins'
                                }
                            }
                        }
                    }
                }
            });

            // Probability Distribution Histogram
            const histogramCtx = document.getElementById('histogramChart').getContext('2d');
            histogramChart = new Chart(histogramCtx, {
                type: 'bar',
                data: {
                    labels: chartData.histogram.labels,
                    datasets: [{
                        label: 'Frequency',
                        data: chartData.histogram.values,
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
                                text: 'Number of Predictions',
                                font: {
                                    family: 'Poppins'
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
                                    family: 'Poppins'
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Cumulative Predictions Line Chart
            const cumulativeCtx = document.getElementById('cumulativeChart').getContext('2d');
            const cumulativeMoveouts = chartData.monthly.moveouts.reduce((acc, val, idx) => {
                acc.push((acc[idx - 1] || 0) + val);
                return acc;
            }, []);

            const cumulativeStayins = chartData.monthly.stayins.reduce((acc, val, idx) => {
                acc.push((acc[idx - 1] || 0) + val);
                return acc;
            }, []);

            cumulativeChart = new Chart(cumulativeCtx, {
                type: 'line',
                data: {
                    labels: chartData.monthly.labels,
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
                                    size: 12,
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

        // Show alert message
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;

            const icon = type === 'success' ? 'check-circle' :
                type === 'error' ? 'exclamation-circle' : 'info-circle';

            alert.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            `;

            alertContainer.appendChild(alert);

            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Generate predictions via AJAX
        document.getElementById('generatePredictionsBtn').addEventListener('click', async function() {
            const btn = this;
            const overlay = document.getElementById('loadingOverlay');

            btn.disabled = true;
            overlay.classList.add('active');

            try {
                const response = await fetch('<?php echo BASE_URL; ?>/app/controllers/ajax_predictions.php?action=generate', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message, 'success');

                    // Reload dashboard data
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message || 'Failed to generate predictions', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred while generating predictions', 'error');
            } finally {
                btn.disabled = false;
                overlay.classList.remove('active');
            }
        });

        // Initialize charts on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
    </script>
</body>

</html>
# Predictive Analytics Module - Migration Predictions

## Overview

This module implements a comprehensive predictive analytics system for resident migration patterns using machine learning. It integrates a Python FastAPI backend with a PHP frontend to provide real-time migration predictions and visual analytics.

## Architecture

### Components

#### 1. **Backend (Python)**

- **FastAPI Server** (`predict_api.py`): REST API endpoint for predictions
- **ML Model Training** (`train_nb.py`): Naive Bayes classifier training
- **Synthetic Data Generator** (`generate_synthetic_and_insert.py`): Creates test data
- **Database Schema** (`migration_ml.sql`): Migration tracking tables

#### 2. **Frontend (PHP MVC)**

- **MigrationModel.php**: Database operations and feature computation
- **PredictiveAnalyticsController.php**: API integration and business logic
- **SecDash.php**: Integrated dashboard with visualizations
- **ajax_predictions.php**: AJAX endpoint handler

#### 3. **Visualization (Chart.js v4+)**

- Monthly migration trends (Bar chart)
- Top puroks by migration (Pie chart)
- Probability distribution (Histogram)
- Cumulative predictions (Line chart)

## Database Schema

### Tables

#### `resident_migrations`

```sql
- id: BIGINT (PK)
- person_id: BIGINT (FK to persons)
- from_purok_id: BIGINT
- to_purok_id: BIGINT
- moved_at: DATE
- reason: VARCHAR(255)
- notes: TEXT
- is_synthetic: TINYINT(1) -- 1 for synthetic data, 0 for real
- created_at: DATETIME
```

#### `migration_predictions`

```sql
- id: BIGINT (PK)
- person_id: BIGINT
- timeframe: ENUM('day', 'month', 'year')
- prediction: TINYINT(1) -- 0 = stay, 1 = move-out
- probability: JSON -- [prob_stay, prob_moveout]
- model_version: VARCHAR(128)
- created_at: DATETIME
```

#### `ml_migration_dataset_all` (VIEW)

Aggregates person, household, and migration data for ML training.

## Setup Instructions

### Prerequisites

- PHP 7.4+
- MySQL 8.0+
- Python 3.8+
- Composer
- pip

### 1. Database Setup

```bash
# Import the migration schema
mysql -u root -p lumbangansystem < bmis-lumbangan-system/db/migration_ml.sql

# Generate synthetic data
cd bmis-lumbangan-system/ml_models
python generate_synthetic_and_insert.py
```

### 2. Python Environment Setup

```bash
# Install required packages
pip install fastapi pydantic uvicorn scikit-learn pandas numpy mysql-connector-python joblib

# Train the model
python train_nb.py --mode both

# Start the FastAPI server
uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload
```

### 3. PHP Configuration

Ensure your `config/Database.php` has correct MySQL credentials:

```php
private $host = 'localhost';
private $db_name = 'lumbangansystem';
private $username = 'root';
private $password = '';
```

### 4. Access the Dashboard

Navigate to: `http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/views/admin_Dash/SecDash.php`

## Usage

### Generating Predictions

#### Via Dashboard UI

1. Open SecDash.php
2. Scroll to "Migration Predictions Analytics" section
3. Click "Generate Predictions" button
4. Wait for processing (shows loading spinner)
5. Dashboard auto-refreshes with new data

#### Via AJAX API

```javascript
fetch("/app/controllers/ajax_predictions.php?action=generate", {
  method: "POST",
  headers: {
    "X-Requested-With": "XMLHttpRequest",
  },
})
  .then((res) => res.json())
  .then((data) => console.log(data));
```

### Viewing Analytics

The dashboard displays:

1. **Statistics Cards**

   - Total Predictions
   - Predicted Stay-Ins
   - Predicted Move-Outs
   - Average Move-Out Probability

2. **Monthly Predictions Chart**

   - Bar chart showing move-ins vs move-outs per month
   - Last 12 months of data

3. **Top Puroks Chart**

   - Pie chart showing top 5 puroks by predicted migration
   - Helps identify high-migration areas

4. **Probability Distribution**

   - Histogram showing distribution of prediction probabilities
   - Bins from 0.0 to 1.0 in 0.1 increments

5. **Cumulative Trend**
   - Line chart showing cumulative predictions over time
   - Separate lines for stay-ins and move-outs

## API Endpoints

### Python FastAPI (`http://127.0.0.1:8000`)

#### POST `/predict`

Generate migration prediction for a single resident.

**Request Body:**

```json
{
  "age": 30.0,
  "household_size": 4,
  "sex": "M",
  "to_purok_id": 1,
  "timeframe": "month"
}
```

**Response:**

```json
{
  "prediction": 1,
  "probabilities": [0.35, 0.65]
}
```

### PHP AJAX (`/app/controllers/ajax_predictions.php`)

#### POST `?action=generate`

Generate predictions for all residents (uses synthetic data).

**Response:**

```json
{
  "success": true,
  "message": "Processed 100 predictions successfully, 0 failed",
  "processed": 100,
  "failed": 0,
  "errors": []
}
```

#### GET `?action=getData`

Retrieve dashboard data and chart datasets.

**Response:**

```json
{
  "success": true,
  "stats": {
    "total_predictions": 500,
    "total_predicted_moveouts": 150,
    "total_predicted_stayins": 350,
    "avg_moveout_probability": 0.32
  },
  "charts": {
    "monthly": {...},
    "puroks": {...},
    "histogram": {...}
  }
}
```

#### GET `?action=testAPI`

Test connection to Python FastAPI server.

**Response:**

```json
{
  "success": true,
  "message": "API connection successful",
  "response": {
    "prediction": 0,
    "probabilities": [0.65, 0.35]
  }
}
```

## Switching from Synthetic to Real Data

The system is designed to easily switch between synthetic and real data:

### Method 1: Database Filter

Change the `is_synthetic` filter in queries:

**MigrationModel.php:**

```php
// Synthetic only (default)
$features = $this->computeFeaturesForNextMonth(true);

// Real data only
$features = $this->computeFeaturesForNextMonth(false);
```

### Method 2: Train Model with Real Data

```bash
# Train with real data only
python train_nb.py --mode real

# Train with both
python train_nb.py --mode both
```

### Method 3: Configuration Flag

Add to `config/config.php`:

```php
define('USE_SYNTHETIC_DATA', false); // false for real data
```

## Model Features

The ML model uses these features:

- **age**: Person's age in years
- **household_size**: Number of people in household
- **sex**: Gender (M/F, encoded as 0/1)
- **to_purok_id**: Destination purok ID

## Performance Optimization

### Caching Predictions

Add Redis/Memcached for caching:

```php
// Check cache first
$cached = $redis->get("prediction_{$person_id}");
if ($cached) {
    return json_decode($cached, true);
}
```

### Batch Processing

Process predictions in batches:

```php
$features = array_chunk($allFeatures, 50);
foreach ($features as $batch) {
    // Process batch
}
```

### Async Processing

Use queues for large datasets:

```php
// Add to queue
Queue::push(new GeneratePredictionsJob($personIds));
```

## Troubleshooting

### Python API Not Responding

```bash
# Check if server is running
curl http://127.0.0.1:8000/predict

# Restart server
uvicorn predict_api:app --reload
```

### No Predictions Generated

- Verify database has person records
- Check `is_synthetic` flag in database
- Ensure Python API is accessible
- Check PHP error logs

### Charts Not Displaying

- Verify Chart.js is loaded
- Check browser console for errors
- Ensure data format matches Chart.js spec

### Database Connection Issues

- Verify MySQL credentials in `config/Database.php`
- Check if `migration_predictions` table exists
- Ensure proper permissions

## Future Enhancements

1. **Real-time Predictions**: WebSocket integration for live updates
2. **Advanced Models**: Implement Random Forest, XGBoost
3. **Feature Engineering**: Add more features (employment, income, etc.)
4. **Explainability**: SHAP values for prediction explanations
5. **Alerts**: Email/SMS notifications for high-risk migrations
6. **Export**: PDF/Excel reports generation
7. **Multi-timeframe**: Day/week/year predictions
8. **Historical Comparison**: Compare predictions vs actual migrations

## Security Considerations

1. **AJAX Validation**: Only allow XMLHttpRequest
2. **Input Sanitization**: Validate all user inputs
3. **API Authentication**: Add token-based auth for production
4. **Rate Limiting**: Prevent API abuse
5. **SQL Injection**: Use prepared statements (already implemented)
6. **CORS**: Configure proper CORS headers for API

## Credits

- **ML Framework**: scikit-learn
- **API Framework**: FastAPI
- **Charts**: Chart.js v4
- **Frontend**: PHP MVC + Bootstrap 5
- **Database**: MySQL 8.0

## License

Copyright © 2025 Barangay Lumbangan BMIS. All rights reserved.

## Support

For issues or questions, contact the development team or file an issue in the project repository.

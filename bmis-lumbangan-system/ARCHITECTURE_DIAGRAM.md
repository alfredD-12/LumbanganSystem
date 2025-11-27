# System Architecture Diagram

## Complete System Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                          USER INTERFACE                          │
│                     (SecDash.php Dashboard)                      │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Stats Cards │  │ Monthly Chart│  │  Puroks Pie  │          │
│  │   (4 cards)  │  │  (Bar Chart) │  │    Chart     │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │  Probability │  │  Cumulative  │  │   Generate   │          │
│  │  Histogram   │  │  Line Chart  │  │    Button    │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└────────────────────────┬──────────────────────────────────────┬─┘
                         │                                      │
                         ▼                                      ▼
                   ┌─────────┐                          ┌──────────┐
                   │Chart.js │                          │  AJAX    │
                   │ v4.4.0  │                          │ Request  │
                   └─────────┘                          └─────┬────┘
                                                              │
                                                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         PHP BACKEND                              │
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │         ajax_predictions.php (AJAX Handler)              │  │
│  │                                                           │  │
│  │  ?action=generate  │  ?action=getData  │  ?action=test  │  │
│  └─────────┬────────────────────┬────────────────┬──────────┘  │
│            │                    │                │              │
│            ▼                    ▼                ▼              │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │    PredictiveAnalyticsController.php (Controller)        │  │
│  │                                                           │  │
│  │  • generatePredictions()      • prepareChartData()       │  │
│  │  • callPredictionAPI()        • ajaxGeneratePredictions()│  │
│  │  • showDashboard()            • testAPIConnection()      │  │
│  └─────────┬────────────────────────────────────┬──────────┘  │
│            │                                    │              │
│            ▼                                    ▼              │
│  ┌──────────────────┐              ┌────────────────────────┐ │
│  │  cURL to Python  │              │  MigrationModel.php    │ │
│  │  FastAPI Server  │              │      (Model)           │ │
│  │  127.0.0.1:8000  │              │                        │ │
│  └────────┬─────────┘              │ • computeFeatures()    │ │
│           │                        │ • storePrediction()     │ │
│           │                        │ • getDashboardStats()   │ │
│           │                        │ • getPredictionsByMonth()│ │
│           │                        │ • getTopPuroks()        │ │
│           │                        │ • getProbabilityDist()  │ │
│           │                        └───────────┬─────────────┘ │
│           │                                    │               │
└───────────┼────────────────────────────────────┼───────────────┘
            │                                    │
            ▼                                    ▼
┌─────────────────────┐              ┌────────────────────────┐
│   PYTHON ML API     │              │   MySQL Database       │
│   (FastAPI)         │              │   (lumbangansystem)    │
│                     │              │                        │
│  predict_api.py     │              │  Tables:               │
│                     │              │  • persons             │
│  POST /predict      │              │  • households          │
│  ├─ Input:          │              │  • puroks              │
│  │  - age           │◄─────────────┤  • resident_migrations │
│  │  - household_size│              │  • migration_predictions│
│  │  - sex           │              │                        │
│  │  - to_purok_id   │              │  View:                 │
│  └─ Output:         │──────────────►│  • ml_migration_       │
│     - prediction    │              │    dataset_all         │
│     - probabilities │              │                        │
│                     │              │                        │
│  Model:             │              └────────────────────────┘
│  nb_pipeline.joblib │
│  (Naive Bayes)      │
└─────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        TRAINING PIPELINE                         │
│                                                                   │
│  ┌──────────────────┐       ┌──────────────┐                    │
│  │ train_nb.py      │       │ MySQL View   │                    │
│  │                  │◄──────│ ml_migration_│                    │
│  │ • Load data      │       │ dataset_all  │                    │
│  │ • Preprocess     │       └──────────────┘                    │
│  │ • Train Naive    │                                            │
│  │   Bayes model    │       ┌──────────────┐                    │
│  │ • Save pipeline  │──────►│nb_pipeline.  │                    │
│  └──────────────────┘       │joblib        │                    │
│                              └──────────────┘                    │
│  ┌──────────────────────────────────────────┐                   │
│  │ generate_synthetic_and_insert.py         │                   │
│  │                                          │                   │
│  │ • Generate 500 synthetic migration       │                   │
│  │   records using Faker                    │                   │
│  │ • Insert into resident_migrations        │                   │
│  │   with is_synthetic = 1                  │                   │
│  └──────────────────────────────────────────┘                   │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow Sequence

### 1. Prediction Generation Flow

```
User clicks "Generate Predictions"
           ↓
AJAX POST to ajax_predictions.php?action=generate
           ↓
PredictiveAnalyticsController->generatePredictions()
           ↓
MigrationModel->computeFeaturesForNextMonth()
           ↓
SQL Query: SELECT features FROM persons JOIN households
           ↓
For each resident:
    ├─ Controller->callPredictionAPI()
    │       ↓
    │  cURL POST to http://127.0.0.1:8000/predict
    │       ↓
    │  Python FastAPI receives request
    │       ↓
    │  Load nb_pipeline.joblib model
    │       ↓
    │  model.predict_proba(features)
    │       ↓
    │  Return {prediction: 0/1, probabilities: [0.6, 0.4]}
    │       ↓
    └─ MigrationModel->storePrediction()
           ↓
    INSERT INTO migration_predictions
           ↓
Dashboard refreshes with new data
```

### 2. Dashboard Display Flow

```
Page Load (SecDash.php)
           ↓
Include PredictiveAnalyticsController.php
           ↓
Include MigrationModel.php
           ↓
MigrationModel->getDashboardStats()
    ├─ SQL: SELECT COUNT(*), AVG(), SUM()...
    └─ Returns: {total, moveouts, stayins, probability}
           ↓
MigrationModel->getPredictionsByMonth()
    ├─ SQL: SELECT ... GROUP BY MONTH
    └─ Returns: [{month, moveouts, stayins}, ...]
           ↓
MigrationModel->getTopPuroksByPredictedMigration()
    ├─ SQL: SELECT ... GROUP BY purok_id ORDER BY COUNT
    └─ Returns: [{purok_id, count}, ...]
           ↓
MigrationModel->getProbabilityDistribution()
    ├─ SQL: SELECT JSON_EXTRACT(probability, '$[1]')
    └─ Returns: [{probability}, ...]
           ↓
Prepare chart data arrays
           ↓
Render HTML with embedded JSON
           ↓
JavaScript initializes Chart.js charts
           ↓
Display interactive dashboard
```

## Component Responsibilities

### Frontend (JavaScript)

- **Chart.js**: Render all visualizations
- **AJAX Handler**: Async communication with backend
- **Alert System**: User feedback notifications
- **Event Handlers**: Button clicks, form submissions

### Backend (PHP)

- **Controller**: Orchestrate API calls, coordinate models
- **Model**: Database CRUD operations, feature computation
- **AJAX Handler**: Route requests, validate inputs, return JSON

### ML Backend (Python)

- **FastAPI**: REST API server for predictions
- **scikit-learn**: Machine learning model (Naive Bayes)
- **Pandas**: Data preprocessing and manipulation
- **Joblib**: Model serialization/deserialization

### Database (MySQL)

- **Storage**: Persist migrations, predictions
- **Views**: Aggregate data for ML training
- **Indexes**: Optimize query performance

## File Dependencies

```
SecDash.php
    ├─ requires: PredictiveAnalyticsController.php
    │       ├─ requires: MigrationModel.php
    │       │       └─ requires: Database.php
    │       └─ calls: http://127.0.0.1:8000/predict
    │
    ├─ includes: Chart.js (CDN)
    ├─ includes: Bootstrap 5 (CDN)
    ├─ includes: Font Awesome 6 (CDN)
    └─ AJAX calls: ajax_predictions.php
            ├─ requires: PredictiveAnalyticsController.php
            └─ requires: MigrationModel.php

predict_api.py
    ├─ imports: fastapi, pydantic, joblib
    ├─ loads: nb_pipeline.joblib
    └─ returns: JSON predictions

train_nb.py
    ├─ imports: scikit-learn, pandas, mysql-connector
    ├─ queries: ml_migration_dataset_all VIEW
    ├─ trains: GaussianNB model
    └─ saves: nb_pipeline.joblib
```

## Technology Stack Summary

### Frontend Stack

```
┌─────────────────────────────────────┐
│  Presentation Layer                 │
│  ├─ HTML5                           │
│  ├─ CSS3 (Custom + Bootstrap 5)     │
│  ├─ JavaScript (ES6+)               │
│  ├─ Chart.js v4.4.0                 │
│  ├─ Font Awesome 6                  │
│  └─ Bootstrap Icons                 │
└─────────────────────────────────────┘
```

### Backend Stack (PHP)

```
┌─────────────────────────────────────┐
│  Application Layer                  │
│  ├─ PHP 7.4+ (MVC Pattern)          │
│  ├─ PDO (Database Abstraction)      │
│  ├─ cURL (HTTP Client)              │
│  ├─ JSON (Data Exchange)            │
│  └─ Custom MVC Framework            │
└─────────────────────────────────────┘
```

### ML Backend Stack (Python)

```
┌─────────────────────────────────────┐
│  AI/ML Layer                        │
│  ├─ Python 3.8+                     │
│  ├─ FastAPI (Web Framework)         │
│  ├─ Uvicorn (ASGI Server)           │
│  ├─ scikit-learn (ML Library)       │
│  ├─ Pandas (Data Processing)        │
│  ├─ NumPy (Numerical Computing)     │
│  ├─ Joblib (Model Persistence)      │
│  └─ MySQL Connector                 │
└─────────────────────────────────────┘
```

### Database Stack

```
┌─────────────────────────────────────┐
│  Data Layer                         │
│  ├─ MySQL 8.0+                      │
│  ├─ InnoDB Storage Engine           │
│  ├─ JSON Field Type                 │
│  ├─ Indexed Queries                 │
│  └─ Views for Aggregation           │
└─────────────────────────────────────┘
```

## Performance Characteristics

### Response Times (Estimated)

- **Feature Computation**: ~100ms (100 residents)
- **Single Prediction API Call**: ~50ms
- **Batch Predictions (100)**: ~6 seconds (with 10ms delays)
- **Dashboard Data Fetch**: ~200ms
- **Chart Rendering**: ~300ms
- **Total Dashboard Load**: ~500-800ms

### Scalability

- **Concurrent Users**: 100+ (with caching)
- **Predictions/Hour**: ~10,000 (without rate limits)
- **Database Records**: Millions (with proper indexing)
- **Chart Data Points**: 1000+ per chart

## Security Layers

```
┌─────────────────────────────────────────────────┐
│  Security Layers                                │
│                                                  │
│  ┌────────────────────────────────────────────┐ │
│  │  Input Validation                          │ │
│  │  • Type checking                           │ │
│  │  • Range validation                        │ │
│  │  • Sanitization                            │ │
│  └────────────────────────────────────────────┘ │
│                                                  │
│  ┌────────────────────────────────────────────┐ │
│  │  SQL Injection Prevention                  │ │
│  │  • PDO prepared statements                 │ │
│  │  • Parameter binding                       │ │
│  │  • No dynamic SQL                          │ │
│  └────────────────────────────────────────────┘ │
│                                                  │
│  ┌────────────────────────────────────────────┐ │
│  │  AJAX Security                             │ │
│  │  • XMLHttpRequest validation               │ │
│  │  • CSRF tokens (ready)                     │ │
│  │  • Content-Type checks                     │ │
│  └────────────────────────────────────────────┘ │
│                                                  │
│  ┌────────────────────────────────────────────┐ │
│  │  API Security                              │ │
│  │  • Rate limiting                           │ │
│  │  • Timeout limits                          │ │
│  │  • Error masking                           │ │
│  └────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

---

This diagram provides a complete visual overview of the predictive analytics system architecture, data flows, and component interactions.

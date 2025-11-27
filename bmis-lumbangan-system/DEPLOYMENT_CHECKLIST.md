# Deployment Checklist

## Pre-Deployment Verification

### ✅ Phase 1: Environment Setup

- [ ] **Python 3.8+ installed**

  ```powershell
  python --version
  # Expected: Python 3.8.x or higher
  ```

- [ ] **Required Python packages installed**

  ```powershell
  pip install fastapi pydantic uvicorn scikit-learn pandas numpy mysql-connector-python joblib faker
  ```

- [ ] **PHP 7.4+ installed**

  ```powershell
  php -v
  # Expected: PHP 7.4.x or higher
  ```

- [ ] **MySQL 8.0+ running**

  ```powershell
  mysql --version
  # Expected: MySQL 8.0.x or higher
  ```

- [ ] **Web server running (XAMPP/Apache)**
  - Apache started
  - MySQL started
  - Port 80 accessible

### ✅ Phase 2: Database Setup

- [ ] **Database created**

  ```sql
  CREATE DATABASE IF NOT EXISTS lumbangansystem;
  USE lumbangansystem;
  ```

- [ ] **Migration schema imported**

  ```powershell
  mysql -u root -p lumbangansystem < bmis-lumbangan-system\db\migration_ml.sql
  ```

- [ ] **Verify tables exist**

  ```sql
  SHOW TABLES LIKE '%migration%';
  -- Expected: resident_migrations, migration_predictions
  ```

- [ ] **Verify view exists**

  ```sql
  SHOW CREATE VIEW ml_migration_dataset_all;
  -- Should return view definition
  ```

- [ ] **Base tables exist (persons, households, puroks)**
  ```sql
  SHOW TABLES LIKE 'persons';
  SHOW TABLES LIKE 'households';
  SHOW TABLES LIKE 'puroks';
  ```

### ✅ Phase 3: Data Preparation

- [ ] **Synthetic data generated**

  ```powershell
  cd bmis-lumbangan-system\ml_models
  python generate_synthetic_and_insert.py
  # Expected: "Inserted synthetic migrations"
  ```

- [ ] **Verify synthetic data**

  ```sql
  SELECT COUNT(*) FROM resident_migrations WHERE is_synthetic = 1;
  -- Expected: 500+ rows
  ```

- [ ] **Check data distribution**
  ```sql
  SELECT
      from_purok_id,
      to_purok_id,
      COUNT(*) as count
  FROM resident_migrations
  WHERE is_synthetic = 1
  GROUP BY from_purok_id, to_purok_id;
  ```

### ✅ Phase 4: ML Model Setup

- [ ] **ML model trained**

  ```powershell
  cd bmis-lumbangan-system\ml_models
  python train_nb.py --mode both
  # Expected: "Saved ml_models/nb_pipeline.joblib"
  ```

- [ ] **Model file exists**

  ```powershell
  Test-Path "bmis-lumbangan-system\ml_models\nb_pipeline.joblib"
  # Expected: True
  ```

- [ ] **Model file size reasonable**
  ```powershell
  (Get-Item "bmis-lumbangan-system\ml_models\nb_pipeline.joblib").Length / 1KB
  # Expected: > 1 KB
  ```

### ✅ Phase 5: API Server Setup

- [ ] **FastAPI server starts without errors**

  ```powershell
  cd bmis-lumbangan-system\ml_models
  uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload
  # Expected: "Uvicorn running on http://127.0.0.1:8000"
  ```

- [ ] **API endpoint accessible**

  ```powershell
  curl http://127.0.0.1:8000/predict -Method POST -ContentType "application/json" -Body '{"age":30,"household_size":4,"sex":"M","to_purok_id":1,"timeframe":"month"}'
  # Expected: {"prediction":0/1,"probabilities":[...]}
  ```

- [ ] **API docs accessible**
  - Open: http://127.0.0.1:8000/docs
  - Expected: Interactive API documentation

### ✅ Phase 6: PHP Backend Verification

- [ ] **All PHP files exist**

  ```powershell
  Test-Path "bmis-lumbangan-system\app\models\MigrationModel.php"
  Test-Path "bmis-lumbangan-system\app\controllers\PredictiveAnalyticsController.php"
  Test-Path "bmis-lumbangan-system\app\controllers\ajax_predictions.php"
  # All should return: True
  ```

- [ ] **Database connection works**

  ```powershell
  php -r "require 'bmis-lumbangan-system/app/config/Database.php'; new Database();"
  # Expected: No errors
  ```

- [ ] **Test script passes**
  ```powershell
  cd bmis-lumbangan-system
  php test_predictions.php
  # Expected: "✓ All tests passed successfully!"
  ```

### ✅ Phase 7: Frontend Integration

- [ ] **SecDash.php loads without errors**

  - Open: http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/views/admin_Dash/SecDash.php
  - Check browser console for errors

- [ ] **Chart.js library loaded**

  - Check browser console: `typeof Chart`
  - Expected: "function"

- [ ] **Bootstrap loaded**

  - Check: Elements have Bootstrap styles
  - Check console: No 404 errors

- [ ] **Custom CSS loaded**
  - Check: predictions_dashboard.css in Network tab
  - Expected: Status 200

### ✅ Phase 8: Functionality Testing

- [ ] **Statistics cards display correct data**

  - Total Predictions: Shows number
  - Predicted Stay-Ins: Shows number
  - Predicted Move-Outs: Shows number
  - Avg. Probability: Shows percentage

- [ ] **Charts render properly**

  - [ ] Monthly Predictions Chart (Bar)
  - [ ] Top Puroks Chart (Pie)
  - [ ] Probability Distribution (Histogram)
  - [ ] Cumulative Trend (Line)

- [ ] **Generate Predictions button works**

  - Click button
  - Loading spinner appears
  - Success message shows
  - Page refreshes with new data

- [ ] **AJAX endpoints respond**
  ```javascript
  fetch(
    "/Lumbangan_BMIS/bmis-lumbangan-system/app/controllers/ajax_predictions.php?action=testAPI",
    {
      headers: { "X-Requested-With": "XMLHttpRequest" },
    }
  )
    .then((r) => r.json())
    .then(console.log);
  // Expected: {success: true, ...}
  ```

### ✅ Phase 9: Performance Testing

- [ ] **Page load time < 2 seconds**

  - Use browser DevTools > Network tab
  - Check "Load" time

- [ ] **API response time < 100ms per prediction**

  - Check Network tab for /predict calls
  - Average should be < 100ms

- [ ] **Database queries optimized**

  ```sql
  EXPLAIN SELECT * FROM ml_migration_dataset_all LIMIT 100;
  -- Check for "Using index" or "Using where"
  ```

- [ ] **No memory leaks (100+ predictions)**
  - Generate 100 predictions
  - Check browser memory usage
  - Should not exceed 200MB

### ✅ Phase 10: Error Handling

- [ ] **API server down error handled**

  - Stop Python API
  - Click "Generate Predictions"
  - Expected: Error alert shown

- [ ] **Database connection error handled**

  - Change DB password in config temporarily
  - Refresh page
  - Expected: Graceful error message

- [ ] **Invalid data handled**

  - Insert NULL values in test data
  - Expected: Queries handle NULLs with COALESCE

- [ ] **AJAX validation works**
  - Direct access to ajax_predictions.php
  - Expected: "Direct access not allowed"

### ✅ Phase 11: Security Verification

- [ ] **SQL injection protected**

  ```sql
  -- Try: person_id = "1'; DROP TABLE migration_predictions; --"
  -- Expected: Query fails safely, no table dropped
  ```

- [ ] **XSS protection**

  - Try: Input `<script>alert('XSS')</script>` in forms
  - Expected: Sanitized/escaped

- [ ] **CSRF protection ready**

  - Check: Token generation mechanism available
  - (Not implemented yet, but structure ready)

- [ ] **AJAX requests validated**
  - Direct browser access to ajax_predictions.php
  - Expected: 403 Forbidden

### ✅ Phase 12: Browser Compatibility

- [ ] **Chrome/Edge (latest)**

  - Dashboard loads
  - Charts render
  - Buttons work

- [ ] **Firefox (latest)**

  - Dashboard loads
  - Charts render
  - Buttons work

- [ ] **Safari (latest)**

  - Dashboard loads
  - Charts render
  - Buttons work

- [ ] **Mobile responsive**
  - Test on mobile viewport (375px width)
  - Charts stack vertically
  - Buttons accessible

### ✅ Phase 13: Documentation

- [ ] **README files exist and accurate**

  - [ ] PREDICTIVE_ANALYTICS_README.md
  - [ ] QUICKSTART.md
  - [ ] IMPLEMENTATION_SUMMARY.md
  - [ ] ARCHITECTURE_DIAGRAM.md

- [ ] **Code comments present**

  - Check: All functions have docblocks
  - Check: Complex logic explained

- [ ] **API documentation**
  - FastAPI /docs endpoint works
  - PHP methods documented

### ✅ Phase 14: Production Readiness

- [ ] **Environment configuration**

  - [ ] Database credentials secure
  - [ ] BASE_URL configured correctly
  - [ ] Error logging enabled

- [ ] **Backup procedures**

  - [ ] Database backup script
  - [ ] Cron job for backups
  - [ ] Backup restoration tested

- [ ] **Monitoring setup**

  - [ ] Error logs location known
  - [ ] Log rotation configured
  - [ ] Alert thresholds set

- [ ] **Scaling considerations**
  - [ ] Connection pooling enabled
  - [ ] Query caching configured
  - [ ] CDN for static assets (optional)

## Deployment Commands

### Complete Setup (Fresh Installation)

```powershell
# 1. Import database
mysql -u root -p lumbangansystem < bmis-lumbangan-system\db\migration_ml.sql

# 2. Generate synthetic data
cd bmis-lumbangan-system\ml_models
python generate_synthetic_and_insert.py

# 3. Train model
python train_nb.py --mode both

# 4. Start API server
uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload

# 5. (In new terminal) Test
cd ..
php test_predictions.php

# 6. Open dashboard
start http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/views/admin_Dash/SecDash.php
```

### Quick Start (Already Set Up)

```powershell
# Start API server
.\bmis-lumbangan-system\start_prediction_server.ps1

# Open dashboard
start http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/views/admin_Dash/SecDash.php
```

## Troubleshooting Checklist

### Problem: Charts not displaying

- [ ] Check Chart.js loaded (browser console)
- [ ] Verify data in predictionChartData variable
- [ ] Check canvas elements exist in DOM
- [ ] Look for JavaScript errors in console

### Problem: API connection failed

- [ ] Verify Python server running on port 8000
- [ ] Check firewall not blocking localhost
- [ ] Test API with curl
- [ ] Check API logs for errors

### Problem: No data in dashboard

- [ ] Run synthetic data generator
- [ ] Verify database has records
- [ ] Check SQL queries return results
- [ ] Generate predictions first time

### Problem: Predictions not generating

- [ ] Check Python API connection
- [ ] Verify model file exists
- [ ] Check PHP error logs
- [ ] Test with smaller batch

## Post-Deployment Verification

After deployment, verify:

1. ✅ **System accessible** - Dashboard loads
2. ✅ **Predictions working** - Can generate new predictions
3. ✅ **Charts updating** - Data reflects after generation
4. ✅ **Performance acceptable** - Response time < 2s
5. ✅ **No errors logged** - Check error logs
6. ✅ **Mobile friendly** - Test on different devices
7. ✅ **Security measures active** - AJAX validation works
8. ✅ **Backups configured** - Database backed up

## Sign-Off

- [ ] **Developer tested** - All functionality verified
- [ ] **Stakeholder approved** - UI/UX accepted
- [ ] **Documentation complete** - All docs up to date
- [ ] **Training provided** - Users know how to use
- [ ] **Production deployed** - Live and monitored

---

**Deployment Date**: ******\_\_\_******

**Deployed By**: ******\_\_\_******

**Approved By**: ******\_\_\_******

**Notes**:

---

---

---

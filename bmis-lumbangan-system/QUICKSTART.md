# Quick Start Guide - Predictive Analytics Module

## 5-Minute Setup

### Step 1: Start Python API Server (Terminal 1)

```powershell
cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models
uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload
```

**Expected Output:**

```
INFO:     Uvicorn running on http://127.0.0.1:8000
INFO:     Application startup complete.
```

### Step 2: Import Database Schema (Terminal 2)

```powershell
cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system
mysql -u root -p lumbangansystem < db\migration_ml.sql
```

### Step 3: Generate Synthetic Data

```powershell
cd ml_models
python generate_synthetic_and_insert.py
```

**Expected Output:**

```
Inserted synthetic migrations
```

### Step 4: Train ML Model

```powershell
python train_nb.py --mode both
```

**Expected Output:**

```
Loaded rows: 500
              precision    recall  f1-score   support
           0       0.85      0.88      0.86        50
           1       0.87      0.84      0.85        50
    accuracy                           0.86       100
Saved ml_models/nb_pipeline.joblib
```

### Step 5: Test Everything

```powershell
cd ..
php test_predictions.php
```

**Expected Output:**

```
=== Predictive Analytics Module Test ===
1. Testing Python API connection...
   ✓ API connection successful!
...
✓ All tests passed successfully!
```

### Step 6: Access Dashboard

1. Open browser: `http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/views/admin_Dash/SecDash.php`
2. Scroll to "Migration Predictions Analytics" section
3. Click "Generate Predictions" button
4. View charts and analytics

## Verification Checklist

- [ ] Python API running on port 8000
- [ ] Database tables created (`resident_migrations`, `migration_predictions`)
- [ ] Synthetic data inserted (500+ rows)
- [ ] ML model trained (`nb_pipeline.joblib` exists)
- [ ] Test script passes all checks
- [ ] Dashboard loads without errors
- [ ] Charts display data correctly

## Common Issues

### Issue 1: "Connection refused" when testing API

**Solution:** Make sure Python API is running

```powershell
cd ml_models
uvicorn predict_api:app --reload
```

### Issue 2: "Table doesn't exist"

**Solution:** Import the schema

```powershell
mysql -u root -p lumbangansystem < db\migration_ml.sql
```

### Issue 3: "No module named 'fastapi'" or "No module named 'sqlalchemy'"

**Solution:** Install dependencies

```powershell
pip install fastapi pydantic uvicorn scikit-learn pandas numpy mysql-connector-python joblib sqlalchemy faker
# Or use requirements file:
pip install -r ml_models\requirements.txt
```

### Issue 4: "Not enough data to train"

**Solution:** Generate more synthetic data

```powershell
python ml_models\generate_synthetic_and_insert.py
```

### Issue 5: Charts not displaying

**Solution:**

- Check browser console for errors
- Verify Chart.js is loaded
- Ensure data exists in database

## Production Deployment

Before deploying to production:

1. **Switch to Real Data:**

   ```php
   // In MigrationModel.php
   $features = $this->computeFeaturesForNextMonth(false); // false = real data
   ```

2. **Secure API Endpoint:**

   - Add authentication tokens
   - Enable HTTPS
   - Configure firewall rules

3. **Optimize Performance:**

   - Enable caching (Redis/Memcached)
   - Use connection pooling
   - Implement rate limiting

4. **Monitor System:**
   - Set up logging
   - Configure alerts
   - Track API metrics

## File Structure

```
bmis-lumbangan-system/
├── app/
│   ├── controllers/
│   │   ├── PredictiveAnalyticsController.php  ← API integration
│   │   └── ajax_predictions.php               ← AJAX handler
│   ├── models/
│   │   └── MigrationModel.php                 ← Database operations
│   ├── views/
│   │   ├── admin_Dash/
│   │   │   └── SecDash.php                    ← Main dashboard
│   │   └── admins/
│   │       └── predictions_dashboard.php      ← Standalone view
│   └── assets/
│       └── css/
│           └── predictions_dashboard.css      ← Custom styles
├── ml_models/
│   ├── predict_api.py                         ← FastAPI server
│   ├── train_nb.py                            ← Model training
│   ├── generate_synthetic_and_insert.py       ← Data generator
│   └── nb_pipeline.joblib                     ← Trained model
├── db/
│   └── migration_ml.sql                       ← Database schema
├── test_predictions.php                       ← Test script
├── PREDICTIVE_ANALYTICS_README.md             ← Full documentation
└── QUICKSTART.md                              ← This file
```

## Next Steps

1. **Customize Features:** Add more data points (employment, income, etc.)
2. **Improve Model:** Try Random Forest, XGBoost, Neural Networks
3. **Add Alerts:** Email/SMS notifications for high-risk migrations
4. **Export Reports:** Generate PDF/Excel summaries
5. **Real-time Updates:** Implement WebSocket for live data

## Support

- **Documentation:** See `PREDICTIVE_ANALYTICS_README.md`
- **Test Issues:** Run `php test_predictions.php`
- **API Docs:** Visit `http://127.0.0.1:8000/docs` (when API is running)

## Tips

- Keep Python API running in background
- Use `--mode both` for training to include synthetic + real data
- Clear old predictions periodically: `$migrationModel->clearOldPredictions(90)`
- Monitor API response times for performance tuning
- Back up database before major changes

---

**Ready to Go!** 🚀

Your predictive analytics system is now operational. Start generating insights and helping your barangay make data-driven decisions about resident migration patterns.

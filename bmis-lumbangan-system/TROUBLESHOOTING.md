# 🔧 Troubleshooting Guide - Prediction System

## Quick Diagnostics

### ✅ Step 1: Verify Python API is Running

**Test in Browser:**

```
http://127.0.0.1:8000/docs
```

- **Expected**: Swagger UI documentation page loads
- **If fails**: API server is not running

**Test in PowerShell:**

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/docs"
```

- **Expected**: HTML content returned
- **If fails**: API server not accessible

### ✅ Step 2: Start/Restart API Server

**Option A: New PowerShell Window (Recommended)**

```powershell
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models; uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload"
```

**Option B: Batch File**

```cmd
C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\start_api_server.bat
```

**Option C: Manual**

```powershell
cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models
uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload
```

### ✅ Step 3: Test Prediction Endpoint

**PowerShell Test:**

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/predict" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"age":30,"household_size":4,"sex":"M","to_purok_id":1,"timeframe":"month"}'
```

**Expected Output:**

```
prediction probabilities
---------- -------------
         0 {0.xxx, 0.xxx}
```

### ✅ Step 4: Test PHP Connection to API

**Run Test Script:**

```
http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/test_api_connection.php
```

**Expected Result:**

- Test 1: ✅ API is accessible
- Test 2: ✅ Prediction successful
- Test 3: ✅ Controller test passed

### ✅ Step 5: Verify Dashboard AJAX Path

**Check browser console** (F12) when clicking "Generate Predictions":

- Should see request to: `/app/controllers/ajax_predictions.php?action=generate`
- Status code should be `200`
- Response should be JSON

---

## Common Errors & Solutions

### Error: "Connection refused" or "Target machine actively refused"

**Cause**: Python API server is not running

**Solution:**

```powershell
# Kill any stuck processes
Get-Process -Name python,uvicorn -ErrorAction SilentlyContinue | Stop-Process -Force

# Start API server in new window
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models; uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload"

# Wait 5 seconds
Start-Sleep -Seconds 5

# Test
Invoke-RestMethod -Uri "http://127.0.0.1:8000/docs"
```

### Error: "Could not import module 'predict_api'"

**Cause**: Uvicorn is running from wrong directory

**Solution:**

```powershell
# Ensure you're in the ml_models directory
cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models

# Verify module can load
python -c "import predict_api; print('OK')"

# Start uvicorn from this directory
uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload
```

### Error: "error while attempting to bind on address... port is normally permitted"

**Cause**: Another process is using port 8000

**Solution:**

```powershell
# Find what's using port 8000
Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue

# Kill the process
$pid = (Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue).OwningProcess
if ($pid) { Stop-Process -Id $pid -Force }

# Start API server
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models; uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload"
```

### Error: "An error occurred while generating predictions"

**Cause**: PHP cannot reach Python API

**Solutions to try:**

1. **Verify API is running:**

   ```
   http://127.0.0.1:8000/docs
   ```

2. **Test PHP can connect:**

   ```
   http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/test_api_connection.php
   ```

3. **Check AJAX URL** in browser console (F12 > Network tab)

   - Should see request to `ajax_predictions.php`
   - Check response tab for error details

4. **Check PHP error logs:**

   ```
   C:\xampp\apache\logs\error.log
   ```

5. **Test AJAX endpoint directly:**
   ```
   http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/controllers/ajax_predictions.php?action=testAPI
   ```
   - Should return JSON with `success: true`

### Error: 404 on ajax_predictions.php

**Cause**: Incorrect file path

**Solution:**
Check file exists:

```powershell
Test-Path "C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\app\controllers\ajax_predictions.php"
```

If true, verify URL in browser matches the file location.

### Error: "Direct access not allowed"

**Cause**: Accessing AJAX endpoint without XMLHttpRequest header

**Solution**: This is normal security. The endpoint should only be accessed via AJAX from the dashboard.

To test manually, add header:

```powershell
Invoke-WebRequest -Uri "http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/controllers/ajax_predictions.php?action=testAPI" -Headers @{"X-Requested-With"="XMLHttpRequest"}
```

---

## Diagnostic Commands

### Check if Python API is accessible

```powershell
Test-NetConnection -ComputerName 127.0.0.1 -Port 8000
```

### Check if model file exists

```powershell
Test-Path "C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models\nb_pipeline.joblib"
```

### Check if database tables exist

```sql
USE lumbangansystem;
SHOW TABLES LIKE '%migration%';
SELECT COUNT(*) FROM resident_migrations;
SELECT COUNT(*) FROM migration_predictions;
```

### View PHP errors

```powershell
Get-Content "C:\xampp\apache\logs\error.log" -Tail 50
```

### Test prediction via curl

```powershell
curl.exe -X POST "http://127.0.0.1:8000/predict" `
  -H "Content-Type: application/json" `
  -d "{\"age\":30,\"household_size\":4,\"sex\":\"M\",\"to_purok_id\":1,\"timeframe\":\"month\"}"
```

---

## Step-by-Step Resolution

If you get the error **"An error occurred while generating predictions. Please ensure the Python API is running"**, follow these steps:

### 1. Stop everything

```powershell
Get-Process -Name python,uvicorn -ErrorAction SilentlyContinue | Stop-Process -Force
```

### 2. Navigate to ml_models

```powershell
cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models
```

### 3. Verify model exists

```powershell
dir nb_pipeline.joblib
```

If not found, run: `python train_nb.py --mode both`

### 4. Start API in NEW window

```powershell
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models; uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload"
```

### 5. Wait and verify

```powershell
Start-Sleep -Seconds 5
Invoke-RestMethod -Uri "http://127.0.0.1:8000/docs"
```

### 6. Test prediction

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/predict" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"age":30,"household_size":4,"sex":"M","to_purok_id":1,"timeframe":"month"}'
```

### 7. Open dashboard

```
http://localhost/Lumbangan_BMIS/bmis-lumbangan-system/app/views/admin_Dash/SecDash.php
```

### 8. Click "Generate Predictions"

If it still fails, open browser console (F12) and check for error messages.

---

## Prevention Tips

### Keep API Running

Create a scheduled task or keep the PowerShell window open with the API server.

### Auto-start on Login

Create a shortcut in your Startup folder:

```
Target: powershell.exe -WindowStyle Hidden -Command "cd C:\xampp\htdocs\Lumbangan_BMIS\bmis-lumbangan-system\ml_models; uvicorn predict_api:app --host 127.0.0.1 --port 8000"
```

### Monitor API Status

Add this to your dashboard or create a status page:

```php
<?php
$ch = curl_init('http://127.0.0.1:8000/docs');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo ($httpCode === 200) ? "✅ API Online" : "❌ API Offline";
?>
```

---

## Contact & Support

- **Full Docs**: `PREDICTIVE_ANALYTICS_README.md`
- **Quick Start**: `QUICKSTART.md`
- **Test Script**: `test_api_connection.php`
- **Test Predictions**: `test_predictions.php`

---

**Last Updated**: November 27, 2025

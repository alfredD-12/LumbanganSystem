@echo off
cd /d "%~dp0ml_models"
echo Starting FastAPI Prediction Server...
echo API will be available at: http://127.0.0.1:8000
echo API docs at: http://127.0.0.1:8000/docs
echo.
echo Press Ctrl+C to stop the server
echo.
uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload

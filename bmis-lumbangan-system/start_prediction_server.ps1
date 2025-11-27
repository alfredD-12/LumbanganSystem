# Predictive Analytics System - Startup Script
# Run this script to start all required services

Write-Host "=== Predictive Analytics System Startup ===" -ForegroundColor Cyan
Write-Host ""

# Check if Python is installed
Write-Host "Checking Python installation..." -ForegroundColor Yellow
try {
    $pythonVersion = python --version 2>&1
    Write-Host "✓ $pythonVersion found" -ForegroundColor Green
} catch {
    Write-Host "✗ Python not found. Please install Python 3.8+" -ForegroundColor Red
    exit 1
}

# Check if required Python packages are installed
Write-Host "Checking Python packages..." -ForegroundColor Yellow
$packages = @("fastapi", "uvicorn", "scikit-learn", "pandas", "joblib", "sqlalchemy")
$missingPackages = @()

foreach ($package in $packages) {
    $installed = python -m pip show $package 2>&1
    if ($LASTEXITCODE -ne 0) {
        $missingPackages += $package
    }
}

if ($missingPackages.Count -gt 0) {
    Write-Host "✗ Missing packages: $($missingPackages -join ', ')" -ForegroundColor Red
    Write-Host "Installing missing packages..." -ForegroundColor Yellow
    python -m pip install $($missingPackages -join ' ')
} else {
    Write-Host "✓ All required packages installed" -ForegroundColor Green
}

Write-Host ""

# Check if model exists
$modelPath = "ml_models\nb_pipeline.joblib"
if (Test-Path $modelPath) {
    Write-Host "✓ ML model found at $modelPath" -ForegroundColor Green
} else {
    Write-Host "⚠ ML model not found. You need to train it first." -ForegroundColor Yellow
    Write-Host "  Run: python ml_models\train_nb.py --mode both" -ForegroundColor Cyan
    
    $train = Read-Host "Do you want to train the model now? (y/n)"
    if ($train -eq 'y' -or $train -eq 'Y') {
        Write-Host "Training model..." -ForegroundColor Yellow
        Set-Location ml_models
        python train_nb.py --mode both
        Set-Location ..
        Write-Host "✓ Model trained successfully" -ForegroundColor Green
    }
}

Write-Host ""

# Check if MySQL is running
Write-Host "Checking MySQL service..." -ForegroundColor Yellow
$mysqlService = Get-Service -Name "MySQL*" -ErrorAction SilentlyContinue
if ($mysqlService) {
    if ($mysqlService.Status -eq "Running") {
        Write-Host "✓ MySQL service is running" -ForegroundColor Green
    } else {
        Write-Host "⚠ MySQL service is not running" -ForegroundColor Yellow
        Write-Host "  Please start MySQL/XAMPP before continuing" -ForegroundColor Cyan
    }
} else {
    Write-Host "⚠ MySQL service not found" -ForegroundColor Yellow
    Write-Host "  Please ensure XAMPP or MySQL is installed" -ForegroundColor Cyan
}

Write-Host ""

# Start FastAPI server
Write-Host "Starting FastAPI server..." -ForegroundColor Yellow
Write-Host "API will be available at: http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "API docs will be at: http://127.0.0.1:8000/docs" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

Set-Location ml_models
try {
    uvicorn predict_api:app --host 127.0.0.1 --port 8000 --reload
} catch {
    Write-Host "✗ Failed to start FastAPI server" -ForegroundColor Red
    Write-Host "Error: $_" -ForegroundColor Red
    Set-Location ..
    exit 1
}

Set-Location ..

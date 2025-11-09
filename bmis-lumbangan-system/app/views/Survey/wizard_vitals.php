<?php
// Require user authentication
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
requireUser();

$appRoot    = dirname(__DIR__, 2); // .../app
$components = $appRoot . '/components';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Survey Wizard — Vitals</title>

  <!-- Vendor CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Survey-only CSS -->
  <link rel="stylesheet" href="../../assets/css/Survey/wizard_personal.css">

  <!-- Page tokens + tiny utilities (scoped to this page) -->
  <style>
    /* Modern Scrollbar */
    ::-webkit-scrollbar {
      width: 12px;
    }

    ::-webkit-scrollbar-track {
      background: linear-gradient(180deg, #f7fafc 0%, #edf2f7 100%);
    }

    ::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, #1e3a5f, #2c5282);
      border-radius: 6px;
      border: 2px solid #f7fafc;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: linear-gradient(180deg, #2c5282, #c53030);
    }

    /* Floating shapes for background - Enhanced visibility */
    .floating-shapes {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      pointer-events: none;
      z-index: 0;
    }
    .shape {
      position: absolute;
      opacity: 0.08;
      animation: floatMove 15s ease-in-out infinite;
      will-change: transform;
    }
    .shape:nth-child(1) {
      width: 100px;
      height: 100px;
      top: 10%;
      left: 10%;
      background: var(--primary-blue);
      border-radius: 50%;
      animation-delay: 0s;
      filter: blur(1px);
    }
    .shape:nth-child(2) {
      width: 150px;
      height: 150px;
      top: 60%;
      right: 10%;
      background: var(--accent-red);
      border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
      animation-delay: 2s;
      filter: blur(1px);
    }
    .shape:nth-child(3) {
      width: 120px;
      height: 120px;
      bottom: 20%;
      left: 15%;
      background: var(--secondary-blue);
      border-radius: 20px;
      animation-delay: 4s;
      filter: blur(1px);
    }
    @keyframes floatMove {
      0% { transform: translateY(0px) translateX(0px); }
      25% { transform: translateY(-50px) translateX(20px); }
      50% { transform: translateY(-100px) translateX(-20px); }
      75% { transform: translateY(-50px) translateX(20px); }
      100% { transform: translateY(0px) translateX(0px); }
    }

    /* Ensure content is above floating shapes */
    main, nav, footer {
      position: relative;
      z-index: 1;
    }

    /* Vital signs cards styling */
    .vital-card {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border: 2px solid #e9ecef;
      border-radius: 16px;
      padding: 1.5rem;
      transition: all 0.3s ease;
    }

    .vital-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(30, 58, 95, 0.15);
      border-color: #1e3a5f;
    }

    .vital-icon {
      width: 64px;
      height: 64px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      margin-bottom: 1rem;
    }

    .vital-icon.bp {
      background: linear-gradient(135deg, #dc3545, #e74c3c);
      color: white;
    }

    .vital-icon.pulse {
      background: linear-gradient(135deg, #fd7e14, #ff6b6b);
      color: white;
    }

    .vital-icon.respiratory {
      background: linear-gradient(135deg, #20c997, #17a2b8);
      color: white;
    }

    .vital-icon.temperature {
      background: linear-gradient(135deg, #ffc107, #ff9800);
      color: white;
    }

    .input-group-text {
      font-weight: 600;
      color: #1e3a5f;
    }
  </style>
</head>

<body class="survey-scope">
  <div class="floating-shapes" aria-hidden="true">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <?php include $components . '/headerdashboard.php'; ?>

  <main class="survey-scope">
    <!-- Page header -->
    <div class="survey-page-header border-bottom bg-white">
      <div class="container content-narrow d-flex align-items-center justify-content-between py-3">
        <h1 class="h4 m-0 survey-title">
          <span class="i18n" data-en="Assessment Survey" data-tl="Assessment Survey">Assessment Survey</span>
        </h1>
        <div class="lang-toggle">
          <div class="btn-group" role="group" aria-label="Language">
            <input type="radio" class="btn-check" name="lang" id="lang-en" autocomplete="off" checked>
            <label class="btn btn-sm btn-outline-primary" for="lang-en">EN</label>

            <input type="radio" class="btn-check" name="lang" id="lang-tl" autocomplete="off">
            <label class="btn btn-sm btn-outline-primary" for="lang-tl">TL</label>
          </div>
        </div>
      </div>
    </div>

    <div class="container my-3 content-narrow">

      <!-- Stepper -->
      <div class="wizard mb-4">
        <div class="wizard-track">
          <a class="wizard-step" href="wizard_personal.php" data-key="personal">
            <span class="step-circle"><i class="fa-solid fa-user"></i></span>
            <span class="step-label i18n" data-en="Personal" data-tl="Personal">Personal</span>
          </a>
          <span class="wizard-connector" aria-hidden="true"></span>

          <a class="wizard-step active" href="wizard_vitals.php" data-key="vitals">
            <span class="step-circle"><i class="fa-solid fa-heartbeat"></i></span>
            <span class="step-label i18n" data-en="Vitals" data-tl="Vital Signs">Vitals</span>
          </a>
          <span class="wizard-connector" aria-hidden="true"></span>

          <a class="wizard-step" href="wizard_family_history.php" data-key="history">
            <span class="step-circle"><i class="fa-solid fa-notes-medical"></i></span>
            <span class="step-label i18n" data-en="History" data-tl="Kasaysayan">History</span>
          </a>
          <span class="wizard-connector" aria-hidden="true"></span>

          <a class="wizard-step" href="wizard_family.php" data-key="family">
            <span class="step-circle"><i class="fa-solid fa-people-roof"></i></span>
            <span class="step-label i18n" data-en="Family" data-tl="Pamilya">Family</span>
          </a>
          <span class="wizard-connector" aria-hidden="true"></span>

          <a class="wizard-step" href="wizard_lifestyle.php" data-key="lifestyle">
            <span class="step-circle"><i class="fa-solid fa-heart-pulse"></i></span>
            <span class="step-label i18n" data-en="Lifestyle" data-tl="Pamumuhay">Lifestyle</span>
          </a>
          <span class="wizard-connector" aria-hidden="true"></span>

          <a class="wizard-step" href="wizard_angina.php" data-key="angina">
            <span class="step-circle"><i class="fa-solid fa-stethoscope"></i></span>
            <span class="step-label i18n" data-en="Angina" data-tl="Angina">Angina</span>
          </a>
          <span class="wizard-connector" aria-hidden="true"></span>

          <a class="wizard-step" href="wizard_diabetes.php" data-key="diabetes">
            <span class="step-circle"><i class="fa-solid fa-syringe"></i></span>
            <span class="step-label i18n" data-en="Diabetes" data-tl="Diabetes">Diabetes</span>
          </a>
          <span class="wizard-connector" aria-hidden="true"></span>

          <a class="wizard-step" href="wizard_household.php" data-key="household">
            <span class="step-circle"><i class="fa-solid fa-house"></i></span>
            <span class="step-label i18n" data-en="Household" data-tl="Sambahayan">Household</span>
          </a>
        </div>
      </div>

      <!-- Info Alert -->
      <div class="alert alert-info d-flex align-items-start gap-3 mb-4" style="border-radius: 12px; border-left: 4px solid #1e3a5f;">
        <i class="fa-solid fa-circle-info fs-4 text-primary"></i>
        <div>
          <strong class="i18n" data-en="Vital Signs Measurement" data-tl="Pagsukat ng Vital Signs">Vital Signs Measurement</strong>
          <p class="mb-0 small i18n" 
             data-en="Please provide your current vital signs measurements. These include blood pressure, pulse rate, respiratory rate, and body temperature. Accurate measurements help assess your overall health status."
             data-tl="Mangyaring magbigay ng iyong kasalukuyang vital signs. Kabilang dito ang presyon ng dugo, pulso, respiratory rate, at temperatura ng katawan. Ang tumpak na pagsukat ay tumutulong upang masuri ang iyong kabuuang kalagayan ng kalusugan.">
            Please provide your current vital signs measurements. These include blood pressure, pulse rate, respiratory rate, and body temperature. Accurate measurements help assess your overall health status.
          </p>
        </div>
      </div>

      <form id="form-vitals" class="needs-validation" novalidate>

        <div class="row g-4 mb-4">
          
          <!-- Blood Pressure -->
          <div class="col-md-6">
            <div class="vital-card">
              <div class="vital-icon bp">
                <i class="fa-solid fa-heart-pulse"></i>
              </div>
              <h5 class="fw-bold mb-3">
                <span class="i18n" data-en="Blood Pressure" data-tl="Presyon ng Dugo">Blood Pressure</span>
              </h5>
              
              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <span class="i18n" data-en="Systolic (mmHg)" data-tl="Systolic (mmHg)">Systolic (mmHg)</span>
                  <span class="text-danger">*</span>
                </label>
                <input type="number" name="bp_systolic" class="form-control form-control-lg i18n-ph" 
                       data-ph-en="e.g., 120" data-ph-tl="Hal., 120"
                       min="70" max="250" required>
                <div class="form-text i18n" data-en="Normal: 90-120 mmHg" data-tl="Normal: 90-120 mmHg">Normal: 90-120 mmHg</div>
                <div class="invalid-feedback i18n" data-en="Please enter systolic blood pressure." data-tl="Mangyaring ilagay ang systolic blood pressure.">Please enter systolic blood pressure.</div>
              </div>

              <div>
                <label class="form-label fw-semibold">
                  <span class="i18n" data-en="Diastolic (mmHg)" data-tl="Diastolic (mmHg)">Diastolic (mmHg)</span>
                  <span class="text-danger">*</span>
                </label>
                <input type="number" name="bp_diastolic" class="form-control form-control-lg i18n-ph" 
                       data-ph-en="e.g., 80" data-ph-tl="Hal., 80"
                       min="40" max="150" required>
                <div class="form-text i18n" data-en="Normal: 60-80 mmHg" data-tl="Normal: 60-80 mmHg">Normal: 60-80 mmHg</div>
                <div class="invalid-feedback i18n" data-en="Please enter diastolic blood pressure." data-tl="Mangyaring ilagay ang diastolic blood pressure.">Please enter diastolic blood pressure.</div>
              </div>
            </div>
          </div>

          <!-- Pulse Rate -->
          <div class="col-md-6">
            <div class="vital-card">
              <div class="vital-icon pulse">
                <i class="fa-solid fa-heart"></i>
              </div>
              <h5 class="fw-bold mb-3">
                <span class="i18n" data-en="Pulse Rate" data-tl="Pulso">Pulse Rate</span>
              </h5>
              
              <div class="mb-3">
                <label class="form-label fw-semibold">
                  <span class="i18n" data-en="Beats per Minute (bpm)" data-tl="Beats per Minute (bpm)">Beats per Minute (bpm)</span>
                  <span class="text-danger">*</span>
                </label>
                <input type="number" name="pulse" class="form-control form-control-lg i18n-ph" 
                       data-ph-en="e.g., 72" data-ph-tl="Hal., 72"
                       min="40" max="200" required>
                <div class="form-text i18n" data-en="Normal: 60-100 bpm" data-tl="Normal: 60-100 bpm">Normal: 60-100 bpm</div>
                <div class="invalid-feedback i18n" data-en="Please enter pulse rate." data-tl="Mangyaring ilagay ang pulso.">Please enter pulse rate.</div>
              </div>

              <div style="height: 96px;"></div>
            </div>
          </div>

          <!-- Respiratory Rate -->
          <div class="col-md-6">
            <div class="vital-card">
              <div class="vital-icon respiratory">
                <i class="fa-solid fa-lungs"></i>
              </div>
              <h5 class="fw-bold mb-3">
                <span class="i18n" data-en="Respiratory Rate" data-tl="Respiratory Rate">Respiratory Rate</span>
              </h5>
              
              <div>
                <label class="form-label fw-semibold">
                  <span class="i18n" data-en="Breaths per Minute" data-tl="Mga Paghinga bawat Minuto">Breaths per Minute</span>
                  <span class="text-danger">*</span>
                </label>
                <input type="number" name="respiratory_rate" class="form-control form-control-lg i18n-ph" 
                       data-ph-en="e.g., 16" data-ph-tl="Hal., 16"
                       min="8" max="40" required>
                <div class="form-text i18n" data-en="Normal: 12-20 breaths/min" data-tl="Normal: 12-20 paghinga/minuto">Normal: 12-20 breaths/min</div>
                <div class="invalid-feedback i18n" data-en="Please enter respiratory rate." data-tl="Mangyaring ilagay ang respiratory rate.">Please enter respiratory rate.</div>
              </div>
            </div>
          </div>

          <!-- Body Temperature -->
          <div class="col-md-6">
            <div class="vital-card">
              <div class="vital-icon temperature">
                <i class="fa-solid fa-temperature-half"></i>
              </div>
              <h5 class="fw-bold mb-3">
                <span class="i18n" data-en="Body Temperature" data-tl="Temperatura ng Katawan">Body Temperature</span>
              </h5>
              
              <div>
                <label class="form-label fw-semibold">
                  <span class="i18n" data-en="Temperature (°C)" data-tl="Temperatura (°C)">Temperature (°C)</span>
                  <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-lg">
                  <input type="number" step="0.1" name="temperature_c" class="form-control i18n-ph" 
                         data-ph-en="e.g., 36.5" data-ph-tl="Hal., 36.5"
                         min="35.0" max="42.0" required>
                  <span class="input-group-text">°C</span>
                </div>
                <div class="form-text i18n" data-en="Normal: 36.1-37.2°C" data-tl="Normal: 36.1-37.2°C">Normal: 36.1-37.2°C</div>
                <div class="invalid-feedback i18n" data-en="Please enter body temperature." data-tl="Mangyaring ilagay ang temperatura ng katawan.">Please enter body temperature.</div>
              </div>
            </div>
          </div>

        </div>

        <!-- Sticky Actions -->
        <div class="sticky-actions">
          <div class="actions-inner">
            <div>
              <span class="text-muted small i18n" data-en="Step 2 of 8" data-tl="Hakbang 2 ng 8">Step 2 of 8</span>
            </div>
            <div class="d-flex gap-2">
              <a href="wizard_personal.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>
                <span class="i18n" data-en="Back" data-tl="Bumalik">Back</span>
              </a>
              <button type="submit" class="btn btn-primary">
                <span class="i18n" data-en="Save & Continue" data-tl="I-save at Magpatuloy">Save & Continue</span>
                <i class="fa-solid fa-arrow-right ms-2"></i>
              </button>
            </div>
          </div>
        </div>

      </form>

    </div>
  </main>

  <?php include $components . '/footerdashboard.php'; ?>

  <!-- Vendor JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Page JS -->
  <script src="../../assets/js/Survey/wizard_vitals.js"></script>

</body>
</html>

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
  <title>Survey Wizard — Lifestyle</title>

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

    /* Additional styling for radio buttons and checkboxes */
    .form-check-input {
      width: 1.25rem;
      height: 1.25rem;
      cursor: pointer;
    }

    .form-check-label {
      cursor: pointer;
      margin-left: 0.5rem;
    }

    /* Range input styling */
    .form-range {
      height: 0.5rem;
    }

    .range-value {
      display: inline-block;
      min-width: 3rem;
      text-align: center;
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

          <a class="wizard-step" href="wizard_vitals.php" data-key="vitals">
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

          <a class="wizard-step active" href="wizard_lifestyle.php" data-key="lifestyle">
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
          <strong class="i18n" data-en="Lifestyle Risk Assessment" data-tl="Pagsusuri ng Panganib sa Pamumuhay">Lifestyle Risk Assessment</strong>
          <p class="mb-0 small i18n" 
             data-en="Please provide information about your lifestyle habits including smoking, alcohol consumption, diet, and physical activity. This helps assess your cardiovascular risk factors."
             data-tl="Mangyaring magbigay ng impormasyon tungkol sa iyong mga gawi sa pamumuhay kabilang ang paninigarilyo, pag-inom ng alak, diyeta, at pisikal na aktibidad. Ito ay tumutulong upang masuri ang iyong mga salik ng panganib sa cardiovascular.">
            Please provide information about your lifestyle habits including smoking, alcohol consumption, diet, and physical activity. This helps assess your cardiovascular risk factors.
          </p>
        </div>
      </div>

      <form id="form-lifestyle" class="needs-validation" novalidate>

        <!-- Smoking Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-smoking"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Smoking Status" data-tl="Katayuan sa Paninigarilyo">Smoking Status</span></h5>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Current Smoking Status" data-tl="Kasalukuyang Katayuan sa Paninigarilyo">Current Smoking Status</span>
              <span class="text-danger">*</span>
            </label>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-never" value="Never" required>
                  <label class="form-check-label i18n" for="smoke-never" data-en="Never Smoked" data-tl="Hindi Kailanman Naninigarilyo">Never Smoked</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-stopped-gt1" value="Stopped_gt_1yr">
                  <label class="form-check-label i18n" for="smoke-stopped-gt1" data-en="Stopped (>1 year)" data-tl="Tumigil (>1 taon)">Stopped (>1 year)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-current" value="Current">
                  <label class="form-check-label i18n" for="smoke-current" data-en="Current Smoker" data-tl="Kasalukuyang Naninigarilyo">Current Smoker</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-stopped-lt1" value="Stopped_lt_1yr">
                  <label class="form-check-label i18n" for="smoke-stopped-lt1" data-en="Stopped (<1 year)" data-tl="Tumigil (<1 taon)">Stopped (<1 year)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-passive" value="Passive">
                  <label class="form-check-label i18n" for="smoke-passive" data-en="Passive Smoker" data-tl="Passive na Naninigarilyo">Passive Smoker</label>
                </div>
              </div>
            </div>
            <div class="invalid-feedback i18n" data-en="Please select a smoking status." data-tl="Mangyaring pumili ng katayuan sa paninigarilyo.">Please select a smoking status.</div>
          </div>

          <div class="mt-3">
            <label class="form-label">
              <span class="i18n" data-en="Additional Comments" data-tl="Karagdagang Komento">Additional Comments</span>
            </label>
            <textarea name="smoking_comments" class="form-control i18n-ph" rows="2"
                      data-ph-en="e.g., Number of cigarettes per day, years smoked"
                      data-ph-tl="Hal., Bilang ng sigarilyo bawat araw, taon ng paninigarilyo"></textarea>
          </div>
        </div>

        <!-- Alcohol Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-wine-glass"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Alcohol Consumption" data-tl="Pag-inom ng Alak">Alcohol Consumption</span></h5>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Alcohol Use Status" data-tl="Katayuan sa Pag-inom ng Alak">Alcohol Use Status</span>
              <span class="text-danger">*</span>
            </label>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="alcohol_use" id="alcohol-never" value="Never" required>
                  <label class="form-check-label i18n" for="alcohol-never" data-en="Never" data-tl="Hindi Kailanman">Never</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="alcohol_use" id="alcohol-current" value="Current">
                  <label class="form-check-label i18n" for="alcohol-current" data-en="Current" data-tl="Kasalukuyan">Current</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="alcohol_use" id="alcohol-former" value="Former">
                  <label class="form-check-label i18n" for="alcohol-former" data-en="Former" data-tl="Dating">Former</label>
                </div>
              </div>
            </div>
            <div class="invalid-feedback i18n" data-en="Please select alcohol use status." data-tl="Mangyaring pumili ng katayuan sa pag-inom ng alak.">Please select alcohol use status.</div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Excessive Alcohol Consumption?" data-tl="Labis na Pag-inom ng Alak?">Excessive Alcohol Consumption?</span>
            </label>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="excessive_alcohol" id="excessive-yes" value="1">
                  <label class="form-check-label i18n" for="excessive-yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="excessive_alcohol" id="excessive-no" value="0">
                  <label class="form-check-label i18n" for="excessive-no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">
              <span class="i18n" data-en="Alcohol Notes" data-tl="Mga Tala sa Alak">Alcohol Notes</span>
            </label>
            <textarea name="alcohol_notes" class="form-control i18n-ph" rows="2"
                      data-ph-en="e.g., Type of alcohol, frequency, quantity"
                      data-ph-tl="Hal., Uri ng alak, dalas, dami"></textarea>
          </div>
        </div>

        <!-- Diet Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-utensils"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Dietary Habits" data-tl="Mga Gawi sa Pagkain">Dietary Habits</span></h5>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Do you eat processed foods weekly?" data-tl="Kumakain ka ba ng processed na pagkain linggo-linggo?">Do you eat processed foods weekly?</span>
            </label>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="eats_processed_weekly" id="processed-yes" value="1">
                  <label class="form-check-label i18n" for="processed-yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="eats_processed_weekly" id="processed-no" value="0">
                  <label class="form-check-label i18n" for="processed-no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Do you eat 3+ servings of fruits daily?" data-tl="Kumakain ka ba ng 3+ servings ng prutas araw-araw?">Do you eat 3+ servings of fruits daily?</span>
            </label>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="fruits_3_servings_daily" id="fruits-yes" value="1">
                  <label class="form-check-label i18n" for="fruits-yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="fruits_3_servings_daily" id="fruits-no" value="0">
                  <label class="form-check-label i18n" for="fruits-no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Do you eat 3+ servings of vegetables daily?" data-tl="Kumakain ka ba ng 3+ servings ng gulay araw-araw?">Do you eat 3+ servings of vegetables daily?</span>
            </label>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="vegetables_3_servings_daily" id="vegetables-yes" value="1">
                  <label class="form-check-label i18n" for="vegetables-yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="vegetables_3_servings_daily" id="vegetables-no" value="0">
                  <label class="form-check-label i18n" for="vegetables-no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Exercise Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-person-running"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Physical Activity" data-tl="Pisikal na Aktibidad">Physical Activity</span></h5>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Exercise Days Per Week" data-tl="Mga Araw ng Ehersisyo Bawat Linggo">Exercise Days Per Week</span>
            </label>
            <div class="d-flex align-items-center gap-3">
              <input type="range" class="form-range flex-grow-1" name="exercise_days_per_week" id="exercise-days" 
                     min="0" max="7" value="0" step="1">
              <span class="range-value" id="exercise-days-value">0</span>
              <span class="i18n" data-en="days" data-tl="araw">days</span>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Exercise Minutes Per Day" data-tl="Mga Minuto ng Ehersisyo Bawat Araw">Exercise Minutes Per Day</span>
            </label>
            <div class="d-flex align-items-center gap-3">
              <input type="range" class="form-range flex-grow-1" name="exercise_minutes_per_day" id="exercise-minutes" 
                     min="0" max="180" value="0" step="5">
              <span class="range-value" id="exercise-minutes-value">0</span>
              <span class="i18n" data-en="minutes" data-tl="minuto">minutes</span>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Exercise Intensity" data-tl="Tindi ng Ehersisyo">Exercise Intensity</span>
            </label>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exercise_intensity" id="intensity-light" value="Light">
                  <label class="form-check-label i18n" for="intensity-light" data-en="Light" data-tl="Magaan">Light</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exercise_intensity" id="intensity-moderate" value="Moderate">
                  <label class="form-check-label i18n" for="intensity-moderate" data-en="Moderate" data-tl="Katamtaman">Moderate</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exercise_intensity" id="intensity-vigorous" value="Vigorous">
                  <label class="form-check-label i18n" for="intensity-vigorous" data-en="Vigorous" data-tl="Mabigat">Vigorous</label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky Actions -->
        <div class="sticky-actions">
          <div class="actions-inner">
            <div>
              <span class="text-muted small i18n" data-en="Step 5 of 8" data-tl="Hakbang 5 ng 8">Step 5 of 8</span>
            </div>
            <div class="d-flex gap-2">
              <a href="wizard_family.php" class="btn btn-outline-secondary">
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
  <script src="../../assets/js/Survey/wizard_lifestyle.js"></script>

</body>
</html>

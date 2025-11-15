<?php
// Require user authentication
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();

$appRoot    = dirname(__DIR__, 2); // .../app
$components = $appRoot . '/components';

$surveyData = loadSurveyData();
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
            </label>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-never" value="Never" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['smoking_status'] ?? '') === 'Never') ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-current" value="Current" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['smoking_status'] ?? '') === 'Current') ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="smoke-current" data-en="Current Smoker" data-tl="Kasalukuyang Naninigarilyo">Current Smoker</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-stopped-lt1" value="Stopped_lt_1yr" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['smoking_status'] ?? '') === 'Stopped_lt_1yr') ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="smoke-stopped-lt1" data-en="Stopped (<1 year)" data-tl="Tumigil (<1 taon)">Stopped (<1 year)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="smoking_status" id="smoke-passive" value="Passive" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['smoking_status'] ?? '') === 'Passive') ? 'checked' : ''; ?>>
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
                      data-ph-tl="Hal., Bilang ng sigarilyo bawat araw, taon ng paninigarilyo"><?php echo isset($surveyData['lifestyle']) ? htmlspecialchars($surveyData['lifestyle']['smoking_comments'] ?? '') : ''; ?></textarea>
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
            </label>
            <div class="row g-3">
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="alcohol_use" id="alcohol-never" value="Never" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['alcohol_use'] ?? '') === 'Never') ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="excessive_alcohol" id="excessive-yes" value="1" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['excessive_alcohol'] ?? '') == 1) ? 'checked' : ''; ?>>
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
                      data-ph-tl="Hal., Uri ng alak, dalas, dami"><?php echo isset($surveyData['lifestyle']) ? htmlspecialchars($surveyData['lifestyle']['alcohol_notes'] ?? '') : ''; ?></textarea>
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
                  <input class="form-check-input" type="radio" name="eats_processed_weekly" id="processed-yes" value="1" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['eats_processed_weekly'] ?? '') == 1) ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="fruits_3_servings_daily" id="fruits-yes" value="1" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['fruits_3_servings_daily'] ?? '') == 1) ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="vegetables_3_servings_daily" id="vegetables-yes" value="1" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['vegetables_3_servings_daily'] ?? '') == 1) ? 'checked' : ''; ?>>
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
      min="0" max="7" value="<?php echo isset($surveyData['lifestyle']) ? (int)($surveyData['lifestyle']['exercise_days_per_week'] ?? 0) : 0; ?>" step="1">
    <span class="range-value" id="exercise-days-value"><?php echo isset($surveyData['lifestyle']) ? (int)($surveyData['lifestyle']['exercise_days_per_week'] ?? 0) : 0; ?></span>
              <span class="i18n" data-en="days" data-tl="araw">days</span>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Exercise Minutes Per Day" data-tl="Mga Minuto ng Ehersisyo Bawat Araw">Exercise Minutes Per Day</span>
            </label>
            <div class="d-flex align-items-center gap-3">
    <input type="range" class="form-range flex-grow-1" name="exercise_minutes_per_day" id="exercise-minutes" 
      min="0" max="180" value="<?php echo isset($surveyData['lifestyle']) ? (int)($surveyData['lifestyle']['exercise_minutes_per_day'] ?? 0) : 0; ?>" step="5">
    <span class="range-value" id="exercise-minutes-value"><?php echo isset($surveyData['lifestyle']) ? (int)($surveyData['lifestyle']['exercise_minutes_per_day'] ?? 0) : 0; ?></span>
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
                  <input class="form-check-input" type="radio" name="exercise_intensity" id="intensity-light" value="Light" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['exercise_intensity'] ?? '') === 'Light') ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="intensity-light" data-en="Light" data-tl="Magaan">Light</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exercise_intensity" id="intensity-moderate" value="Moderate" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['exercise_intensity'] ?? '') === 'Moderate') ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="intensity-moderate" data-en="Moderate" data-tl="Katamtaman">Moderate</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exercise_intensity" id="intensity-vigorous" value="Vigorous" <?php echo (isset($surveyData['lifestyle']) && ($surveyData['lifestyle']['exercise_intensity'] ?? '') === 'Vigorous') ? 'checked' : ''; ?>>
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
  
  <div class="modal fade" id="bhwInfoModal" tabindex="-1" aria-labelledby="bhwInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title i18n" id="bhwInfoModalLabel" data-en="Survey Assistance Notice" data-tl="Pabatid Tungkol sa Pagsusuri">Survey Assistance Notice</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="i18n" data-en="A barangay health worker will visit to complete this survey and perform measurements that require equipment (for example: blood pressure, blood glucose, and other vitals). You can answer any questions you know, but the health worker will handle any checks needing instruments." data-tl="Bibilhin ka ng isang barangay health worker para kumpletuhin ang pagsusuring ito at magsagawa ng mga pagsukat na nangangailangan ng kagamitan (hal., presyon ng dugo, blood glucose, at iba pang vital). Maaari mong sagutin ang mga tanong na alam mo, ngunit ang health worker ang gagawa ng mga pagsusuring nangangailangan ng instrumento."></p>
          <p class="i18n" data-en="This visit also helps connect you with local health services and ensures appropriate follow-up. If you have concerns or symptoms, please mention them during the visit." data-tl="Ang pagbisitang ito ay tumutulong din na ikonekta ka sa mga lokal na serbisyo pangkalusugan at matiyak ang naaangkop na follow-up. Kung may mga alalahanin o sintomas, mangyaring banggitin ang mga ito sa pagbisita."></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span class="i18n" data-en="Close" data-tl="Isara">Close</span></button>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><span class="i18n" data-en="Understood" data-tl="Naiintindihan">Understood</span></button>
        </div>
      </div>
    </div>
  </div>

  <!-- Vendor JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>




  <!-- Page JS -->
  <script src="../../assets/js/Survey/wizard_lifestyle.js"></script>
  <script src="../../assets/js/Survey/survey-persistence.js"></script>
  <script src="../../assets/js/Survey/save-survey.js"></script>

  <style>
    #bhwFloatBtn{position:fixed; bottom:24px; right:24px; z-index:1060; width:56px; height:56px; border-radius:50%; background:#1e3a5f; color:#fff; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 20px rgba(0,0,0,0.18); cursor:grab}
    #bhwFloatBtn:active{cursor:grabbing}
    #bhwFloatBtn .fa-circle-info{font-size:1.35rem}
    #bhwFloatBtn.hidden{display:none}
    .bhw-hide-btn{position:absolute; top:-8px; right:-8px; background:#fff; color:#000; width:20px; height:20px; border-radius:50%; font-size:12px; display:flex; align-items:center; justify-content:center; border:1px solid rgba(0,0,0,0.08)}
  </style>

  <div id="bhwFloatBtn" role="button" aria-label="Survey info" title="Survey info">
    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
    <button id="bhwFloatHide" class="bhw-hide-btn" aria-label="Hide info">×</button>
  </div>

  <script>
    (function(){
      var keyPos = 'bhwFloatPos';
      var btn = document.getElementById('bhwFloatBtn');
      var hideBtn = document.getElementById('bhwFloatHide');
      var modalEl = document.getElementById('bhwInfoModal');
      var modalInstance = modalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;

      if (!btn) return;

      // restore position
      try{
        var pos = localStorage.getItem(keyPos);
        if (pos){ pos = JSON.parse(pos); btn.style.left = (pos.left || '') + 'px'; btn.style.top = (pos.top || '') + 'px'; btn.style.right = 'auto'; btn.style.bottom = 'auto'; btn.style.position = 'fixed'; }
      }catch(e){}

      btn.addEventListener('click', function(e){ if (e.target === hideBtn) return; if (modalInstance) modalInstance.show(); });

      hideBtn.addEventListener('click', function(e){ e.stopPropagation(); btn.classList.add('hidden'); });

      // draggable (mouse)
      (function(){ var active=false, startX=0, startY=0, origX=0, origY=0; btn.addEventListener('mousedown', function(e){ if (e.target === hideBtn) return; active = true; startX = e.clientX; startY = e.clientY; var rect = btn.getBoundingClientRect(); origX = rect.left; origY = rect.top; document.addEventListener('mousemove', move); document.addEventListener('mouseup', up); }); function move(e){ if(!active) return; var dx = e.clientX - startX, dy = e.clientY - startY; btn.style.left = (origX + dx) + 'px'; btn.style.top = (origY + dy) + 'px'; btn.style.right = 'auto'; btn.style.bottom = 'auto'; } function up(){ if(!active) return; active=false; document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); try{ localStorage.setItem(keyPos, JSON.stringify({left: parseInt(btn.style.left,10), top: parseInt(btn.style.top,10)})); }catch(e){} } })();

      // touch events for mobile
      (function(){ var active=false, sx=0, sy=0, ox=0, oy=0; btn.addEventListener('touchstart', function(e){ if (e.target === hideBtn) return; active=true; var t=e.touches[0]; sx=t.clientX; sy=t.clientY; var r=btn.getBoundingClientRect(); ox=r.left; oy=r.top; }, {passive:false}); btn.addEventListener('touchmove', function(e){ if(!active) return; var t=e.touches[0]; var dx=t.clientX-sx, dy=t.clientY-sy; btn.style.left=(ox+dx)+'px'; btn.style.top=(oy+dy)+'px'; btn.style.right='auto'; btn.style.bottom='auto'; e.preventDefault(); }, {passive:false}); btn.addEventListener('touchend', function(e){ if(!active) return; active=false; try{ localStorage.setItem(keyPos, JSON.stringify({left: parseInt(btn.style.left,10), top: parseInt(btn.style.top,10)})); }catch(e){} }); })();
      window.bhwRestoreFloat_lifestyle = function(){ try{ localStorage.removeItem(keyPos); btn.classList.remove('hidden'); btn.style.left=''; btn.style.top=''; btn.style.right='24px'; btn.style.bottom='24px'; }catch(e){} };
    })();
  </script>

</body>
</html>

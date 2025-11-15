<?php
// Require user authentication
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();

$appRoot    = dirname(__DIR__, 2); // .../app
$components = $appRoot . '/components';

// Load existing survey data from database
$surveyData = loadSurveyData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Survey Wizard — Diabetes Screening</title>

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
      <div class="wizard mb-2">
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

          <a class="wizard-step active" href="wizard_diabetes.php" data-key="diabetes">
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
          <strong class="i18n" data-en="Diabetes Screening" data-tl="Pagsusuri ng Diabetes">Diabetes Screening</strong>
          <p class="mb-0 small i18n" 
             data-en="Please provide information about diabetes history, symptoms, and test results. This screening helps identify potential diabetes risk."
             data-tl="Mangyaring magbigay ng impormasyon tungkol sa kasaysayan ng diabetes, mga sintomas, at mga resulta ng pagsusuri. Ang pagsusuring ito ay tumutulong na makilala ang potensyal na panganib sa diabetes.">
            Please provide information about diabetes history, symptoms, and test results. This screening helps identify potential diabetes risk.
          </p>
        </div>
      </div>

      <form id="form-diabetes" class="needs-validation" novalidate>

        <!-- Medical History Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-file-medical"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Medical History" data-tl="Kasaysayan ng Kalusugan">Medical History</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Do you have known diabetes?" data-tl="Mayroon ka bang kilalang diabetes?">Do you have known diabetes?</span>
              </label>
                <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="known_diabetes" id="known_diabetes_yes" value="1" <?php echo (isset($surveyData['diabetes']) && ($surveyData['diabetes']['known_diabetes'] ?? '') == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="known_diabetes_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="known_diabetes" id="known_diabetes_no" value="0" <?php echo (isset($surveyData['diabetes']) && ($surveyData['diabetes']['known_diabetes'] ?? '') == 0 && ($surveyData['diabetes']['known_diabetes'] !== null)) ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="known_diabetes_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Are you on diabetes medications?" data-tl="Ikaw ba ay umiinom ng gamot para sa diabetes?">Are you on diabetes medications?</span>
              </label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="on_medications" id="on_medications_yes" value="1" <?php echo (isset($surveyData['diabetes']) && ($surveyData['diabetes']['on_medications'] ?? '') == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="on_medications_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="on_medications" id="on_medications_no" value="0" <?php echo (isset($surveyData['diabetes']) && ($surveyData['diabetes']['on_medications'] ?? '') == 0 && ($surveyData['diabetes']['on_medications'] !== null)) ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="on_medications_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Family history of diabetes?" data-tl="Kasaysayan ng pamilya ng diabetes?">Family history of diabetes?</span>
              </label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="family_history" id="family_history_yes" value="1" <?php echo (isset($surveyData['diabetes']) && ($surveyData['diabetes']['family_history'] ?? '') == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="family_history_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="family_history" id="family_history_no" value="0" <?php echo (isset($surveyData['diabetes']) && ($surveyData['diabetes']['family_history'] ?? '') == 0 && ($surveyData['diabetes']['family_history'] !== null)) ? 'checked' : ''; ?>>
                  <label class="form-check-label i18n" for="family_history_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>
          </div>
        </div>

        <!-- Symptoms Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-list-check"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Symptoms (3 P's)" data-tl="Mga Sintomas (3 P's)">Symptoms (3 P's)</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Polyuria (frequent urination)?" data-tl="Polyuria (madalas umihi)?">Polyuria (frequent urination)?</span>
              </label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="polyuria" id="polyuria_yes" value="1">
                  <label class="form-check-label i18n" for="polyuria_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="polyuria" id="polyuria_no" value="0">
                  <label class="form-check-label i18n" for="polyuria_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Polydipsia (excessive thirst)?" data-tl="Polydipsia (labis na uhaw)?">Polydipsia (excessive thirst)?</span>
              </label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="polydipsia" id="polydipsia_yes" value="1">
                  <label class="form-check-label i18n" for="polydipsia_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="polydipsia" id="polydipsia_no" value="0">
                  <label class="form-check-label i18n" for="polydipsia_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Polyphagia (excessive hunger)?" data-tl="Polyphagia (labis na gutom)?">Polyphagia (excessive hunger)?</span>
              </label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="polyphagia" id="polyphagia_yes" value="1">
                  <label class="form-check-label i18n" for="polyphagia_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="polyphagia" id="polyphagia_no" value="0">
                  <label class="form-check-label i18n" for="polyphagia_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Unexplained weight loss?" data-tl="Hindi maipaliwanag na pagbaba ng timbang?">Unexplained weight loss?</span>
              </label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="weight_loss" id="weight_loss_yes" value="1">
                  <label class="form-check-label i18n" for="weight_loss_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="weight_loss" id="weight_loss_no" value="0">
                  <label class="form-check-label i18n" for="weight_loss_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>
          </div>
        </div>

        <!-- Laboratory Results Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-vial"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Laboratory Results" data-tl="Resulta ng Laboratoryo">Laboratory Results</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label for="rbs_mg_dl" class="form-label fw-semibold">
                <span class="i18n" data-en="Random Blood Sugar (mg/dL)" data-tl="Random Blood Sugar (mg/dL)">Random Blood Sugar (mg/dL)</span>
              </label>
              <input type="number" class="form-control" id="rbs_mg_dl" name="rbs_mg_dl" step="0.01" min="0" max="999.99">
              <small class="text-muted i18n" data-en="Normal: 70-140 mg/dL" data-tl="Normal: 70-140 mg/dL">Normal: 70-140 mg/dL</small>
            </div>

            <div class="col-md-4">
              <label for="fbs_mg_dl" class="form-label fw-semibold">
                <span class="i18n" data-en="Fasting Blood Sugar (mg/dL)" data-tl="Fasting Blood Sugar (mg/dL)">Fasting Blood Sugar (mg/dL)</span>
              </label>
              <input type="number" class="form-control" id="fbs_mg_dl" name="fbs_mg_dl" step="0.01" min="0" max="999.99">
              <small class="text-muted i18n" data-en="Normal: 70-100 mg/dL" data-tl="Normal: 70-100 mg/dL">Normal: 70-100 mg/dL</small>
            </div>

            <div class="col-md-4">
              <label for="hba1c_percent" class="form-label fw-semibold">
                <span class="i18n" data-en="HbA1c (%)" data-tl="HbA1c (%)">HbA1c (%)</span>
              </label>
              <input type="number" class="form-control" id="hba1c_percent" name="hba1c_percent" step="0.01" min="0" max="99.99">
              <small class="text-muted i18n" data-en="Normal: <5.7%" data-tl="Normal: <5.7%">Normal: &lt;5.7%</small>
            </div>
          </div>
        </div>

        <!-- Urine Test Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-flask"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Urine Test" data-tl="Pagsusuri ng Ihi">Urine Test</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Urine Ketone present?" data-tl="May Urine Ketone?">Urine Ketone present?</span>
              </label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="urine_ketone" id="urine_ketone_yes" value="1">
                  <label class="form-check-label i18n" for="urine_ketone_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="urine_ketone" id="urine_ketone_no" value="0">
                  <label class="form-check-label i18n" for="urine_ketone_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Urine Protein present?" data-tl="May Urine Protein?">Urine Protein present?</span>
              </label>
              <div class="d-flex gap-3">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="urine_protein" id="urine_protein_yes" value="1">
                  <label class="form-check-label i18n" for="urine_protein_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="urine_protein" id="urine_protein_no" value="0">
                  <label class="form-check-label i18n" for="urine_protein_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>
          </div>
        </div>

        <!-- Sticky Actions -->
        <div class="sticky-actions">
          <div class="actions-inner">
            <div>
              <span class="text-muted small i18n" data-en="Step 7 of 8" data-tl="Hakbang 7 ng 8">Step 7 of 8</span>
            </div>
            <div class="d-flex gap-2">
              <a href="wizard_angina.php" class="btn btn-outline-secondary">
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
  
  <!-- Informational modal: barangay health worker notice -->
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
  <!-- Expose whether server already has diabetes data for this person -->
  <script>
    window.__has_server_diabetes = <?php echo (!empty($surveyData['diabetes']) ? 'true' : 'false'); ?>;
  </script>
  <script src="../../assets/js/Survey/wizard_diabetes.js"></script>
  <script src="../../assets/js/Survey/survey-persistence.js"></script>
  <script src="../../assets/js/Survey/save-survey.js"></script>

  <!-- Floating info button (draggable & persist position) -->
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
      // Use a single global key so position is consistent across pages
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

      // open modal on click (but ignore hide button clicks)
      btn.addEventListener('click', function(e){ if (e.target === hideBtn) return; if (modalInstance) modalInstance.show(); });

      // hide control - only hide for this page load (do not persist)
      hideBtn.addEventListener('click', function(e){ e.stopPropagation(); btn.classList.add('hidden'); });

      // draggable (mouse)
      (function(){ var active=false, startX=0, startY=0, origX=0, origY=0; btn.addEventListener('mousedown', function(e){ if (e.target === hideBtn) return; active = true; startX = e.clientX; startY = e.clientY; var rect = btn.getBoundingClientRect(); origX = rect.left; origY = rect.top; document.addEventListener('mousemove', move); document.addEventListener('mouseup', up); }); function move(e){ if(!active) return; var dx = e.clientX - startX, dy = e.clientY - startY; btn.style.left = (origX + dx) + 'px'; btn.style.top = (origY + dy) + 'px'; btn.style.right = 'auto'; btn.style.bottom = 'auto'; } function up(){ if(!active) return; active=false; document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); try{ localStorage.setItem(keyPos, JSON.stringify({left: parseInt(btn.style.left,10), top: parseInt(btn.style.top,10)})); }catch(e){} } })();

      // touch events for mobile
      (function(){ var active=false, sx=0, sy=0, ox=0, oy=0; btn.addEventListener('touchstart', function(e){ if (e.target === hideBtn) return; active=true; var t=e.touches[0]; sx=t.clientX; sy=t.clientY; var r=btn.getBoundingClientRect(); ox=r.left; oy=r.top; }, {passive:false}); btn.addEventListener('touchmove', function(e){ if(!active) return; var t=e.touches[0]; var dx=t.clientX-sx, dy=t.clientY-sy; btn.style.left=(ox+dx)+'px'; btn.style.top=(oy+dy)+'px'; btn.style.right='auto'; btn.style.bottom='auto'; e.preventDefault(); }, {passive:false}); btn.addEventListener('touchend', function(e){ if(!active) return; active=false; try{ localStorage.setItem(keyPos, JSON.stringify({left: parseInt(btn.style.left,10), top: parseInt(btn.style.top,10)})); }catch(e){} }); })();

      // developer helper: restore button and clear saved position for this page
      window.bhwRestoreFloat_diabetes = function(){ try{ localStorage.removeItem(keyPos); btn.classList.remove('hidden'); btn.style.left=''; btn.style.top=''; btn.style.right='24px'; btn.style.bottom='24px'; }catch(e){} };
    })();
  </script>

</body>
</html>

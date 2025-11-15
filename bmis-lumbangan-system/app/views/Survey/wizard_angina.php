<?php
// Require user authentication
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();

$appRoot    = dirname(__DIR__, 2); // .../app
$components = $appRoot . '/components';

// Load existing survey data from database
$surveyData = loadSurveyData();
// If there is no angina record in the database for this assessment, clear any
// persisted draft for the angina form from localStorage. This prevents a previous
// user's local draft from being rehydrated when the DB is empty.
$hasAnginaData = false;
if (!empty($surveyData['angina']) && is_array($surveyData['angina'])) {
  foreach ($surveyData['angina'] as $v) {
    if ($v !== null && $v !== '') { $hasAnginaData = true; break; }
  }
}
if (!$hasAnginaData) {
  // Emit a small inline script to clear the persistence key used by SurveyPersistence
  echo "\n<script>\ntry{ localStorage.removeItem('survey_angina'); }catch(e){}\ntry{ var k = JSON.parse(localStorage.getItem('survey_form_keys')||'[]').filter(x=>x!=='angina'); localStorage.setItem('survey_form_keys', JSON.stringify(k)); }catch(e){}\n// also call exposed helper if available\ntry{ if (window.SurveyPersistence && typeof window.SurveyPersistence.clear === 'function') window.SurveyPersistence.clear('angina'); }catch(e){}\n</script>\n";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Survey Wizard — Angina Screening</title>

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

    /* Question card styling */
    .question-card {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      transition: all 0.3s ease;
    }

    .question-card:hover {
      border-color: #1e3a5f;
      box-shadow: 0 4px 12px rgba(30, 58, 95, 0.1);
    }

    .question-number {
      display: inline-block;
      width: 32px;
      height: 32px;
      background: linear-gradient(135deg, #1e3a5f, #2c5282);
      color: white;
      border-radius: 50%;
      text-align: center;
      line-height: 32px;
      font-weight: 600;
      margin-right: 0.75rem;
      flex-shrink: 0;
    }

    .form-check-input:checked {
      background-color: #1e3a5f;
      border-color: #1e3a5f;
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

          <a class="wizard-step active" href="wizard_angina.php" data-key="angina">
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
          <strong class="i18n" data-en="Angina & Stroke Screening" data-tl="Pagsusuri ng Angina at Stroke">Angina & Stroke Screening</strong>
          <p class="mb-0 small i18n" 
             data-en="Please answer the following questions about chest discomfort and pain. These help identify potential heart-related issues that may require medical attention."
             data-tl="Mangyaring sagutin ang mga sumusunod na tanong tungkol sa kakulangan sa dibdib at sakit. Ito ay tumutulong na makilala ang mga potensyal na isyu sa puso na maaaring kailangan ng medikal na atensyon.">
            Please answer the following questions about chest discomfort and pain. These help identify potential heart-related issues that may require medical attention.
          </p>
        </div>
      </div>

      <form id="form-angina" class="needs-validation" novalidate>

        <!-- Question 1 -->
        <div class="question-card">
          <div class="d-flex align-items-start">
            <span class="question-number">1</span>
            <div class="flex-grow-1">
              <label class="form-label fw-semibold mb-3">
                <span class="i18n" data-en="Do you have discomfort or heaviness in your chest, chest pain, or a feeling of tightness in your chest?" 
                      data-tl="Mayroon ka bang hindi komportable o bigat sa iyong dibdib, pananakit ng dibdib, o pakiramdam ng higpit sa iyong dibdib?">
                  Do you have discomfort or heaviness in your chest, chest pain, or a feeling of tightness in your chest?
                </span>
              </label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q1_chest_discomfort" id="q1-yes" value="1" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q1_chest_discomfort'] ?? '') == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q1-yes" data-en="Yes" data-tl="Oo">Yes</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q1_chest_discomfort" id="q1-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q1_chest_discomfort'] ?? '') == 0 && ($surveyData['angina']['q1_chest_discomfort'] !== null)) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q1-no" data-en="No" data-tl="Hindi">No</label>
                  </div>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>
          </div>
        </div>

        <!-- Question 2 -->
        <div class="question-card">
          <div class="d-flex align-items-start">
            <span class="question-number">2</span>
            <div class="flex-grow-1">
              <label class="form-label fw-semibold mb-3">
                <span class="i18n" data-en="Is the discomfort or pain located in your left arm, neck, jaw, or back?" 
                      data-tl="Ang hindi komportable o sakit ba ay matatagpuan sa iyong kaliwang braso, leeg, panga, o likod?">
                  Is the discomfort or pain located in your left arm, neck, jaw, or back?
                </span>
              </label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q2_pain_location_left_arm_neck_back" id="q2-yes" value="1" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q2_pain_location_left_arm_neck_back'] ?? '') == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q2-yes" data-en="Yes" data-tl="Oo">Yes</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q2_pain_location_left_arm_neck_back" id="q2-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q2_pain_location_left_arm_neck_back'] ?? '') == 0 && ($surveyData['angina']['q2_pain_location_left_arm_neck_back'] !== null)) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q2-no" data-en="No" data-tl="Hindi">No</label>
                  </div>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>
          </div>
        </div>

        <!-- Question 3 -->
        <div class="question-card">
          <div class="d-flex align-items-start">
            <span class="question-number">3</span>
            <div class="flex-grow-1">
              <label class="form-label fw-semibold mb-3">
                <span class="i18n" data-en="Does the pain occur when you climb stairs, walk uphill, or do other physical activity?" 
                      data-tl="Nangyayari ba ang sakit kapag umakyat ka ng hagdan, naglalakad paakyat, o gumagawa ng ibang pisikal na aktibidad?">
                  Does the pain occur when you climb stairs, walk uphill, or do other physical activity?
                </span>
              </label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q3_pain_on_exertion" id="q3-yes" value="1" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q3_pain_on_exertion'] ?? '') == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q3-yes" data-en="Yes" data-tl="Oo">Yes</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q3_pain_on_exertion" id="q3-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q3_pain_on_exertion'] ?? '') == 0 && ($surveyData['angina']['q3_pain_on_exertion'] !== null)) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q3-no" data-en="No" data-tl="Hindi">No</label>
                  </div>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>
          </div>
        </div>

        <!-- Question 4 -->
        <div class="question-card">
          <div class="d-flex align-items-start">
            <span class="question-number">4</span>
            <div class="flex-grow-1">
              <label class="form-label fw-semibold mb-3">
                <span class="i18n" data-en="Does the pain go away when you rest or take nitroglycerine?" 
                      data-tl="Nawawala ba ang sakit kapag nagpapahinga ka o umiinom ng nitroglycerine?">
                  Does the pain go away when you rest or take nitroglycerine?
                </span>
              </label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q4_pain_relieved_by_rest_or_nitro" id="q4-yes" value="1" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q4_pain_relieved_by_rest_or_nitro'] ?? '') == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q4-yes" data-en="Yes" data-tl="Oo">Yes</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q4_pain_relieved_by_rest_or_nitro" id="q4-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q4_pain_relieved_by_rest_or_nitro'] ?? '') == 0 && ($surveyData['angina']['q4_pain_relieved_by_rest_or_nitro'] !== null)) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q4-no" data-en="No" data-tl="Hindi">No</label>
                  </div>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>
          </div>
        </div>

        <!-- Question 5 -->
        <div class="question-card">
          <div class="d-flex align-items-start">
            <span class="question-number">5</span>
            <div class="flex-grow-1">
              <label class="form-label fw-semibold mb-3">
                <span class="i18n" data-en="Does the pain usually last 10 minutes or longer?" 
                      data-tl="Ang sakit ba ay karaniwang tumatagal ng 10 minuto o mas matagal?">
                  Does the pain usually last 10 minutes or longer?
                </span>
              </label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q5_pain_lasting_10min_plus" id="q5-yes" value="1" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q5_pain_lasting_10min_plus'] ?? '') == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q5-yes" data-en="Yes" data-tl="Oo">Yes</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q5_pain_lasting_10min_plus" id="q5-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q5_pain_lasting_10min_plus'] ?? '') == 0 && ($surveyData['angina']['q5_pain_lasting_10min_plus'] !== null)) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q5-no" data-en="No" data-tl="Hindi">No</label>
                  </div>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin ang tanong na ito.">Please answer this question.</div>
            </div>
          </div>
        </div>

        <!-- Question 6 -->
        <div class="question-card">
          <div class="d-flex align-items-start">
            <span class="question-number">6</span>
            <div class="flex-grow-1">
              <label class="form-label fw-semibold mb-3">
                <span class="i18n" data-en="Have you had severe pain in the front of your chest lasting for half an hour or more?" 
                      data-tl="Nakaranas ka na ba ng matinding sakit sa harapan ng iyong dibdib na tumatagal ng kalahating oras o higit pa?">
                  Have you had severe pain in the front of your chest lasting for half an hour or more?
                </span>
              </label>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q6_pain_front_of_chest_half_hour" id="q6-yes" value="1" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q6_pain_front_of_chest_half_hour'] ?? '') == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q6-yes" data-en="Yes" data-tl="Oo">Yes</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="q6_pain_front_of_chest_half_hour" id="q6-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q6_pain_front_of_chest_half_hour'] ?? '') == 0 && ($surveyData['angina']['q6_pain_front_of_chest_half_hour'] !== null)) ? 'checked' : ''; ?>>
                    <label class="form-check-label i18n" for="q6-no" data-en="No" data-tl="Hindi">No</label>
                  </div>
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
              <span class="text-muted small i18n" data-en="Step 6 of 8" data-tl="Hakbang 6 ng 8">Step 6 of 8</span>
            </div>
            <div class="d-flex gap-2">
              <a href="wizard_lifestyle.php" class="btn btn-outline-secondary">
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
  <script src="../../assets/js/Survey/wizard_angina.js"></script>
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
      window.bhwRestoreFloat_angina = function(){ try{ localStorage.removeItem(keyPos); btn.classList.remove('hidden'); btn.style.left=''; btn.style.top=''; btn.style.right='24px'; btn.style.bottom='24px'; }catch(e){} };
    })();
  </script>

</body>
</html>

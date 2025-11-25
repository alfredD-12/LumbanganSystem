<?php
// app/views/Survey/wizard_angina.php
// Require user authentication and helpers
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();

$surveyData = loadSurveyData();

$hasAnginaData = false;
if (!empty($surveyData['angina']) && is_array($surveyData['angina'])) {
  foreach ($surveyData['angina'] as $v) {
    if ($v !== null && $v !== '') { $hasAnginaData = true; break; }
  }
}

// Include the resident header (this header is expected to contain the site's <head> and opening <body> markup)
include __DIR__ . '/../../components/resident_components/header-resident.php';


?>

<?php
if (!$hasAnginaData) {
  echo "<script>\ntry{ localStorage.removeItem('survey_angina'); }catch(e){}\ntry{ var k = JSON.parse(localStorage.getItem('survey_form_keys')||'[]').filter(x=>x!=='angina'); localStorage.setItem('survey_form_keys', JSON.stringify(k)); }catch(e){}\ntry{ if (window.SurveyPersistence && typeof window.SurveyPersistence.clear === 'function') window.SurveyPersistence.clear('angina'); }catch(e){}\n</script>\n";
}
?>

<main class="survey-scope">
  <div class="floating-shapes" aria-hidden="true">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>
  
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
        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_personal'); ?>" data-key="personal">
          <span class="step-circle"><i class="fa-solid fa-user"></i></span>
          <span class="step-label i18n" data-en="Personal" data-tl="Personal">Personal</span>
        </a>
        <span class="wizard-connector" aria-hidden="true"></span>

        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_vitals'); ?>" data-key="vitals">
          <span class="step-circle"><i class="fa-solid fa-heartbeat"></i></span>
          <span class="step-label i18n" data-en="Vitals" data-tl="Vital Signs">Vitals</span>
        </a>
        <span class="wizard-connector" aria-hidden="true"></span>

        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_family_history'); ?>" data-key="history">
          <span class="step-circle"><i class="fa-solid fa-notes-medical"></i></span>
          <span class="step-label i18n" data-en="History" data-tl="Kasaysayan">History</span>
        </a>
        <span class="wizard-connector" aria-hidden="true"></span>

        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_family'); ?>" data-key="family">
          <span class="step-circle"><i class="fa-solid fa-people-roof"></i></span>
          <span class="step-label i18n" data-en="Family" data-tl="Pamilya">Family</span>
        </a>
        <span class="wizard-connector" aria-hidden="true"></span>

        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_lifestyle'); ?>" data-key="lifestyle">
          <span class="step-circle"><i class="fa-solid fa-heart-pulse"></i></span>
          <span class="step-label i18n" data-en="Lifestyle" data-tl="Pamumuhay">Lifestyle</span>
        </a>
        <span class="wizard-connector" aria-hidden="true"></span>

        <a class="wizard-step active" href="" data-key="angina">
          <span class="step-circle"><i class="fa-solid fa-stethoscope"></i></span>
          <span class="step-label i18n" data-en="Angina" data-tl="Angina">Angina</span>
        </a>
        <span class="wizard-connector" aria-hidden="true"></span>

        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_diabetes'); ?>" data-key="diabetes">
          <span class="step-circle"><i class="fa-solid fa-syringe"></i></span>
          <span class="step-label i18n" data-en="Diabetes" data-tl="Diabetes">Diabetes</span>
        </a>
        <span class="wizard-connector" aria-hidden="true"></span>

        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_household'); ?>" data-key="household">
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
                  <input class="form-check-input" type="radio" name="q1_chest_discomfort" id="q1-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q1_chest_discomfort'] ?? '') === 0) ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="q2_pain_location_left_arm_neck_back" id="q2-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q2_pain_location_left_arm_neck_back'] ?? '') === 0) ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="q3_pain_on_exertion" id="q3-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q3_pain_on_exertion'] ?? '') === 0) ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="q4_pain_relieved_by_rest_or_nitro" id="q4-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q4_pain_relieved_by_rest_or_nitro'] ?? '') === 0) ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="q5_pain_lasting_10min_plus" id="q5-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q5_pain_lasting_10min_plus'] ?? '') === 0) ? 'checked' : ''; ?>>
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
                  <input class="form-check-input" type="radio" name="q6_pain_front_of_chest_half_hour" id="q6-no" value="0" <?php echo (isset($surveyData['angina']) && ($surveyData['angina']['q6_pain_front_of_chest_half_hour'] ?? '') === 0) ? 'checked' : ''; ?>>
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
            <a href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_lifestyle'); ?>" class="btn btn-outline-secondary">
              <i class="fa-solid fa-arrow-left me-2"></i>
              <span class="i18n" data-en="Back" data-tl="Bumalik">Back</span>
            </a>
            <button type="button" id="btn-save-angina" class="btn btn-primary">
              <span class="i18n" data-en="Save & Continue" data-tl="I-save at Magpatuloy">Save & Continue</span>
              <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>
          </div>
        </div>
      </div>

    </form>

  </div>
</main>

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

<!-- Floating info button (draggable & persist position) -->
<div id="bhwFloatBtn" role="button" aria-label="Survey info" title="Survey info">
  <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
  <button id="bhwFloatHide" class="bhw-hide-btn" aria-label="Hide info">×</button>
</div>

<script>
(function(){
  // ========== Question Completion Check ==========
  function initQuestionCompletion() {
    const form = document.getElementById('form-angina');
    if (!form) return;

    const questionCards = Array.from(form.querySelectorAll('.question-card'));

    function updateCardCompletion(card) {
      const radios = Array.from(card.querySelectorAll('input[type="radio"]'));
      if (radios.length === 0) return;

      const isAnswered = radios.some(radio => radio.checked);
      if (isAnswered) {
        card.classList.add('section-complete');
      } else {
        card.classList.remove('section-complete');
      }
    }

    questionCards.forEach(card => {
      const radios = card.querySelectorAll('input[type="radio"]');
      radios.forEach(radio => {
        radio.addEventListener('change', () => updateCardCompletion(card));
      });
      // Initial check on load
      updateCardCompletion(card);
    });
  }

  // Add styles for the completion checkmark
  (function addCompletionStyles() {
    const styleId = 'survey-completion-style';
    if (document.getElementById(styleId)) return;
    const style = document.createElement('style');
    style.id = styleId;
    style.textContent = `
      .section-complete {
        border-color: #10b981 !important;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
      }
      .section-complete::after {
        content: '✓';
        position: absolute;
        top: 15px;
        right: 15px;
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 18px;
        animation: scaleIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
      }
      @keyframes scaleIn {
        from { transform: scale(0) rotate(-180deg); opacity: 0; }
        to { transform: scale(1) rotate(0deg); opacity: 1; }
      }
    `;
    document.head.appendChild(style);
  })();

  // ========== Save & Continue handler (AJAX) ==========
  var btn = document.getElementById('btn-save-angina');
  var form = document.getElementById('form-angina');
  if (!btn || !form) return;

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    ev.stopImmediatePropagation();
    return false;
  }, { capture: true });

  var endpoint = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?action=save_angina'); ?>;
  var nextUrl  = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=survey_wizard_diabetes'); ?>;

  function getToastContainer() {
    var id = 'survey_toast_container';
    var c = document.getElementById(id);
    if (c) return c;
    c = document.createElement('div'); c.id = id;
    Object.assign(c.style, { position: 'fixed', top: '72px', left: '50%', transform: 'translateX(-50%)', zIndex: 20000, pointerEvents: 'none', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', width: '100%', boxSizing: 'border-box', padding: '0 12px' });
    document.body.appendChild(c);
    return c;
  }

  function showToast(type, message, timeoutMs = 2500) {
    var container = getToastContainer();
    var toast = document.createElement('div');
    toast.className = 'alert alert-' + (type || 'info');
    Object.assign(toast.style, { maxWidth: '200px', width: '100%', boxSizing: 'border-box', pointerEvents: 'auto', margin: '0 auto', borderRadius: '8px', boxShadow: '0 6px 20px rgba(0,0,0,0.08)', padding: '10px 18px', textAlign: 'center', opacity: '0', transition: 'opacity 160ms ease, transform 160ms ease', transform: 'translateY(-6px)' });
    toast.innerText = message;
    container.appendChild(toast);
    requestAnimationFrame(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; });
    setTimeout(() => {
      toast.style.opacity = '0'; toast.style.transform = 'translateY(-6px)';
      setTimeout(() => { try { toast.remove(); } catch(e){} }, 200);
    }, timeoutMs);
  }

  function startSpinner() {
    btn.disabled = true;
    btn.dataset.orig = btn.innerHTML;
    const lang = document.getElementById('lang-tl')?.checked ? 'tl' : 'en';
    const savingText = lang === 'tl' ? 'Sine-save...' : 'Saving...';
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ${savingText}`;
  }
  function stopSpinner() {
    btn.disabled = false;
    if (btn.dataset.orig) btn.innerHTML = btn.dataset.orig;
  }

  btn.addEventListener('click', async function (e) {
    e.preventDefault();
    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      var firstInvalid = form.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return;
    }

    startSpinner();
    var fd = new FormData(form);

    try {
      var resp = await fetch(endpoint, { method: 'POST', body: fd });
      var json = await resp.json();
      if (resp.ok && json.success) {
        showToast('success', json.message || 'Saved successfully', 1600);
        setTimeout(() => { window.location.href = nextUrl; }, 700);
        // On success, we DON'T call stopSpinner(). The button remains disabled
        // until the page navigates away, preventing multiple clicks.
      } else {
        showToast('danger', json.message || 'Could not save data.', 4000);
        stopSpinner(); // Re-enable button only on failure
      }
    } catch (err) {
      showToast('danger', 'Network error. Please try again.', 4000);
      stopSpinner(); // Re-enable button only on failure
    }
  });

  // Initialize all features
  initQuestionCompletion();
})();
</script>

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
  var KEY_POS = 'bhwFloatPos';
  var KEY_HIDDEN = 'bhwFloatHidden';
  var btn = document.getElementById('bhwFloatBtn');
  var hideBtn = document.getElementById('bhwFloatHide');
  var modalEl = document.getElementById('bhwInfoModal');
  var modalInstance = (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

  if (!btn) return;

  // Utility: parseInt safe helper
  function toInt(v, fallback) {
    var n = parseInt(v, 10);
    return Number.isFinite(n) ? n : (fallback === undefined ? null : fallback);
  }

  // Restore position from localStorage (if any)
  try {
    var raw = localStorage.getItem(KEY_POS);
    if (raw) {
      var pos = JSON.parse(raw);
      // only set valid numeric coordinates
      var left = toInt(pos.left);
      var top  = toInt(pos.top);
      if (left !== null && top !== null) {
        // set explicit left/top and ensure right/bottom won't override them
        btn.style.position = 'fixed';
        btn.style.left = left + 'px';
        btn.style.top  = top + 'px';
        btn.style.right = 'auto';
        btn.style.bottom = 'auto';
      } else {
        // ensure defaults if stored value invalid
        btn.style.right = '24px';
        btn.style.bottom = '24px';
      }
    } else {
      // defaults
      btn.style.right = '24px';
      btn.style.bottom = '24px';
    }
  } catch (e) {
    // non-fatal; leave button in CSS-positioned place
    btn.style.right = '24px';
    btn.style.bottom = '24px';
  }

  // Restore explicit hidden preference only if user hid it previously
  try {
    var wasHidden = localStorage.getItem(KEY_HIDDEN);
    if (wasHidden === '1') {
      btn.classList.add('hidden');
    } else {
      btn.classList.remove('hidden');
    }
  } catch (e) {
    btn.classList.remove('hidden');
  }

  // Track when hideBtn was clicked (so we can distinguish explicit hides)
  var lastExplicitHideTs = 0;

  // open modal on click (but ignore hide button clicks)
  btn.addEventListener('click', function(e){
    if (e.target === hideBtn) return;
    if (modalInstance) {
      try { modalInstance.show(); return; } catch (err) {}
    }
    // fallback: try to find modal element and show minimal fallback
    if (modalEl) {
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
      modalEl.setAttribute('aria-modal','true');
      modalEl.removeAttribute('aria-hidden');
      return;
    }
    // final fallback
    alert('Survey information: A barangay health worker may follow up to perform checks requiring equipment (e.g., blood pressure, glucose).');
  }, { passive: true });

  // hide control - explicit hide persists across pages (user intent)
  hideBtn.addEventListener('click', function(e){
    e.stopPropagation();
    btn.classList.add('hidden');
    lastExplicitHideTs = Date.now();
    try { localStorage.setItem(KEY_HIDDEN, '1'); } catch (err) {}
  }, { passive: true });

  // If user wants to unhide via UI, they can call bhwRestoreFloat_vitals() in console or we expose a helper below.

  // Draggable (mouse)
  (function(){
    var active=false, startX=0, startY=0, origX=0, origY=0;
    function onMouseDown(e){
      if (e.target === hideBtn) return;
      active = true;
      startX = e.clientX;
      startY = e.clientY;
      var rect = btn.getBoundingClientRect();
      origX = rect.left;
      origY = rect.top;
      document.addEventListener('mousemove', onMove);
      document.addEventListener('mouseup', onUp);
      // prevent text-selection while dragging
      e.preventDefault();
    }
    function onMove(e){
      if(!active) return;
      var dx = e.clientX - startX, dy = e.clientY - startY;
      btn.style.left = (origX + dx) + 'px';
      btn.style.top  = (origY + dy) + 'px';
      btn.style.right = 'auto';
      btn.style.bottom = 'auto';
    }
    function onUp(){
      if(!active) return;
      active=false;
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('mouseup', onUp);
      // persist position
      try{
        var left = toInt(btn.style.left, null);
        var top  = toInt(btn.style.top, null);
        if (left !== null && top !== null) {
          localStorage.setItem(KEY_POS, JSON.stringify({ left: left, top: top }));
        }
      }catch(e){}
    }
    btn.addEventListener('mousedown', onMouseDown, { passive: false });
  })();

  // Touch events for mobile
  (function(){
    var active=false, sx=0, sy=0, ox=0, oy=0;
    btn.addEventListener('touchstart', function(e){
      if (e.target === hideBtn) return;
      var t = e.touches ? e.touches[0] : null;
      if (!t) return;
      active = true;
      sx = t.clientX; sy = t.clientY;
      var r = btn.getBoundingClientRect();
      ox = r.left; oy = r.top;
    }, { passive: true });

    btn.addEventListener('touchmove', function(e){
      if(!active) return;
      var t = e.touches ? e.touches[0] : null;
      if (!t) return;
      var dx = t.clientX - sx, dy = t.clientY - sy;
      btn.style.left = (ox + dx) + 'px';
      btn.style.top  = (oy + dy) + 'px';
      btn.style.right = 'auto';
      btn.style.bottom = 'auto';
      // prevent page scrolling while dragging
      e.preventDefault();
    }, { passive: false });

    btn.addEventListener('touchend', function(){
      if(!active) return;
      active = false;
      try{
        var left = toInt(btn.style.left, null);
        var top  = toInt(btn.style.top, null);
        if (left !== null && top !== null) {
          localStorage.setItem(KEY_POS, JSON.stringify({ left: left, top: top }));
        }
      }catch(e){}
    }, { passive: true });
  })();

  // Defensive: if some other script adds .hidden accidentally, undo it promptly
  try {
    var mo = new MutationObserver(function(muts){
      muts.forEach(function(m){
        if (m.type === 'attributes' && m.attributeName === 'class') {
          var cls = btn.className || '';
          // If hidden was set and it was NOT an explicit hide recently, remove it
          if (cls.indexOf('hidden') !== -1) {
            var sinceHide = Date.now() - (lastExplicitHideTs || 0);
            // if explicit hide happened within last 500ms, assume it was user click and respect it
            if (sinceHide > 500) {
              // Someone else set hidden; restore visibility
              btn.classList.remove('hidden');
              try { localStorage.removeItem(KEY_HIDDEN); } catch(e){}
            }
          }
        }
      });
    });
    mo.observe(btn, { attributes: true, attributeFilter: ['class'] });
  } catch (e) {
    // MutationObserver not supported - ignore
  }

  // Developer helpers
  window.bhwRestoreFloat_vitals = function(){
    try{
      localStorage.removeItem(KEY_HIDDEN);
      localStorage.removeItem(KEY_POS);
    } catch(e){}
    btn.classList.remove('hidden');
    btn.style.left=''; btn.style.top='';
    btn.style.right='24px'; btn.style.bottom='24px';
  };

  // explicit programmatic hide (doesn't persist)
  window.bhwHideFloat_vitals = function(persist){
    btn.classList.add('hidden');
    if (persist) {
      try { localStorage.setItem(KEY_HIDDEN, '1'); } catch(e){}
    }
  };

})();
</script>

<?php
// Include the resident footer which closes the body/html
include_once __DIR__ . '/../../components/resident_components/footer-resident.php';
?>
<?php
// app/views/Survey/wizard_vitals.php
// Require user authentication and helpers
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();

// Load existing survey data from database
$surveyData = loadSurveyData();

// Ensure $surveyData is available to helper functions that use global
// (surveyValue relies on global $surveyData)
$GLOBALS['surveyData'] = $surveyData;

// Helper for backward compatibility on this page
function vitalsValue($field, $default = '') {
    return surveyValue('vitals', $field, $default);
}

include __DIR__ . '/../../components/resident_components/header-resident.php';
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
    <div class="wizard mb-4">
      <div class="wizard-track">
        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_personal'); ?>" data-key="personal">
          <span class="step-circle"><i class="fa-solid fa-user"></i></span>
          <span class="step-label i18n" data-en="Personal" data-tl="Personal">Personal</span>
        </a>
        <span class="wizard-connector" aria-hidden="true"></span>

        <a class="wizard-step active" href="" data-key="vitals">
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

        <a class="wizard-step" href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_angina'); ?>" data-key="angina">
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
        <!-- Blood Pressure Card -->
        <div class="col-md-6">
          <div class="vital-card p-4 h-100">
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="vital-icon bp rounded-square d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                <i class="fa-solid fa-heart-pulse"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">
                  <span class="i18n" data-en="Blood Pressure" data-tl="Presyon ng Dugo">Blood Pressure</span>
                </h5>
                <small class="text-muted i18n" data-en="Systolic and diastolic measurements" data-tl="Systolic at diastolic na mga sukat">Systolic and diastolic measurements</small>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Systolic (mmHg)" data-tl="Systolic (mmHg)">Systolic (mmHg)</span>
              </label>
              <input type="number" name="bp_systolic" class="form-control form-control-lg i18n-ph" data-ph-en="e.g., 120" data-ph-tl="Hal., 120" min="70" max="250" value="<?php echo vitalsValue('bp_systolic'); ?>">
              <div class="form-text i18n" data-en="Normal: 90-120 mmHg" data-tl="Normal: 90-120 mmHg">Normal: 90-120 mmHg</div>
            </div>

            <div>
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Diastolic (mmHg)" data-tl="Diastolic (mmHg)">Diastolic (mmHg)</span>
              </label>
              <input type="number" name="bp_diastolic" class="form-control form-control-lg i18n-ph" data-ph-en="e.g., 80" data-ph-tl="Hal., 80" min="40" max="150" value="<?php echo vitalsValue('bp_diastolic'); ?>">
              <div class="form-text i18n" data-en="Normal: 60-80 mmHg" data-tl="Normal: 60-80 mmHg">Normal: 60-80 mmHg</div>
            </div>
          </div>
        </div>

        <!-- Pulse Card -->
        <div class="col-md-6">
          <div class="vital-card p-4 h-100">
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="vital-icon pulse rounded-square d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                <i class="fa-solid fa-heart"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">
                  <span class="i18n" data-en="Pulse Rate" data-tl="Pulso">Pulse Rate</span>
                </h5>
                <small class="text-muted i18n" data-en="Beats per minute (bpm)" data-tl="Beats per minute (bpm)">Beats per minute (bpm)</small>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Beats per Minute (bpm)" data-tl="Beats per Minute (bpm)">Beats per Minute (bpm)</span>
              </label>
              <input type="number" name="pulse" class="form-control form-control-lg i18n-ph" data-ph-en="e.g., 72" data-ph-tl="Hal., 72" min="40" max="200" value="<?php echo vitalsValue('pulse'); ?>">
              <div class="form-text i18n" data-en="Normal: 60-100 bpm" data-tl="Normal: 60-100 bpm">Normal: 60-100 bpm</div>
            </div>

            <div style="height: 96px;" aria-hidden="true"></div>
          </div>
        </div>

        <!-- Respiratory Card -->
        <div class="col-md-6">
          <div class="vital-card p-4 h-100">
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="vital-icon respiratory rounded-square d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                <i class="fa-solid fa-lungs"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">
                  <span class="i18n" data-en="Respiratory Rate" data-tl="Respiratory Rate">Respiratory Rate</span>
                </h5>
                <small class="text-muted i18n" data-en="Breaths per minute" data-tl="Mga Paghinga bawat Minuto">Breaths per minute</small>
              </div>
            </div>

            <div>
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Breaths per Minute" data-tl="Mga Paghinga bawat Minuto">Breaths per Minute</span>
              </label>
              <input type="number" name="respiratory_rate" class="form-control form-control-lg i18n-ph" data-ph-en="e.g., 16" data-ph-tl="Hal., 16" min="8" max="40" value="<?php echo vitalsValue('respiratory_rate'); ?>">
              <div class="form-text i18n" data-en="Normal: 12-20 breaths/min" data-tl="Normal: 12-20 paghinga/minuto">Normal: 12-20 breaths/min</div>
            </div>
          </div>
        </div>

        <!-- Temperature Card -->
        <div class="col-md-6">
          <div class="vital-card p-4 h-100">
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="vital-icon temperature rounded-square d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                <i class="fa-solid fa-temperature-half"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-1">
                  <span class="i18n" data-en="Body Temperature" data-tl="Temperatura ng Katawan">Body Temperature</span>
                </h5>
                <small class="text-muted i18n" data-en="Measured in °C" data-tl="Sukatin sa °C">Measured in °C</small>
              </div>
            </div>

            <div>
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Temperature (°C)" data-tl="Temperatura (°C)">Temperature (°C)</span>
              </label>
              <div class="input-group input-group-lg">
                <input type="number" step="0.1" name="temperature_c" class="form-control i18n-ph" data-ph-en="e.g., 36.5" data-ph-tl="Hal., 36.5" min="35.0" max="42.0" value="<?php echo vitalsValue('temperature_c'); ?>">
                <span class="input-group-text">°C</span>
              </div>
              <div class="form-text i18n" data-en="Normal: 36.1-37.2°C" data-tl="Normal: 36.1-37.2°C">Normal: 36.1-37.2°C</div>
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
            <a href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_personal'); ?>" class="btn btn-outline-secondary">
              <i class="fa-solid fa-arrow-left me-2"></i>
              <span class="i18n" data-en="Back" data-tl="Bumalik">Back</span>
            </a>
            <button type="button" id="btn-save-vitals" class="btn btn-primary">
              <span class="i18n" data-en="Save & Continue" data-tl="I-save at Magpatuloy">Save & Continue</span>
              <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>
          </div>
        </div>
      </div>

    </form>

  </div>
</main>

<!-- Existing UI scripts left unchanged... (modal, float button, etc.) -->
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

    // open modal on click (but ignore hide button clicks)
    btn.addEventListener('click', function(e){ if (e.target === hideBtn) return; if (modalInstance) modalInstance.show(); });

    // hide control - only hide for this page load (do not persist)
    hideBtn.addEventListener('click', function(e){ e.stopPropagation(); btn.classList.add('hidden'); });

    // draggable (mouse)
    (function(){ var active=false, startX=0, startY=0, origX=0, origY=0; btn.addEventListener('mousedown', function(e){ if (e.target === hideBtn) return; active = true; startX = e.clientX; startY = e.clientY; var rect = btn.getBoundingClientRect(); origX = rect.left; origY = rect.top; document.addEventListener('mousemove', move); document.addEventListener('mouseup', up); }); function move(e){ if(!active) return; var dx = e.clientX - startX, dy = e.clientY - startY; btn.style.left = (origX + dx) + 'px'; btn.style.top = (origY + dy) + 'px'; btn.style.right = 'auto'; btn.style.bottom = 'auto'; } function up(){ if(!active) return; active=false; document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); try{ localStorage.setItem(keyPos, JSON.stringify({left: parseInt(btn.style.left,10), top: parseInt(btn.style.top,10)})); }catch(e){} } })();

    // touch events for mobile
    (function(){ var active=false, sx=0, sy=0, ox=0, oy=0; btn.addEventListener('touchstart', function(e){ if (e.target === hideBtn) return; active=true; var t=e.touches[0]; sx=t.clientX; sy=t.clientY; var r=btn.getBoundingClientRect(); ox=r.left; oy=r.top; }, {passive:false}); btn.addEventListener('touchmove', function(e){ if(!active) return; var t=e.touches[0]; var dx=t.clientX-sx, dy=t.clientY-sy; btn.style.left=(ox+dx)+'px'; btn.style.top=(oy+dy)+'px'; btn.style.right='auto'; btn.style.bottom='auto'; e.preventDefault(); }, {passive:false}); btn.addEventListener('touchend', function(e){ if(!active) return; active=false; try{ localStorage.setItem(keyPos, JSON.stringify({left: parseInt(btn.style.left,10), top: parseInt(btn.style.top,10)})); }catch(e){} }); })();

    window.bhwRestoreFloat_vitals = function(){ try{ localStorage.removeItem(keyPos); btn.classList.remove('hidden'); btn.style.left=''; btn.style.top=''; btn.style.right='24px'; btn.style.bottom='24px'; }catch(e){} };
  })();
</script>

<!-- Save & Continue handler (AJAX) -->
<script>
(function(){
  var btn = document.getElementById('btn-save-vitals');
  var form = document.getElementById('form-vitals');
  if (!btn || !form) return;

  // Defensive: prevent native form navigation in case anything triggers form.submit()
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    ev.stopImmediatePropagation();
    return false;
  }, { capture: true });

  var endpoint = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=survey_wizard_vitals&action=save_vitals'); ?>;
  var nextUrl  = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=survey_wizard_family_history'); ?>;

  // Top-center toast container (creates if missing)
  function getToastContainer() {
    var id = 'survey_toast_container';
    var c = document.getElementById(id);
    if (c) return c;

    // create container
    c = document.createElement('div');
    c.id = id;

    // center at top of viewport, below any fixed header (adjust top px as needed)
    c.style.position = 'fixed';
    c.style.top = '72px'; // <- adjust if your header is taller/shorter
    c.style.left = '50%';
    c.style.transform = 'translateX(-50%)';
    c.style.zIndex = 20000;
    c.style.pointerEvents = 'none';

    // ensure container won't take full width but center to viewport
    c.style.display = 'flex';
    c.style.flexDirection = 'column';
    c.style.alignItems = 'center';
    c.style.gap = '8px';
    c.style.width = '100%';
    c.style.boxSizing = 'border-box';
    c.style.padding = '0 12px'; // small horizontal padding on narrow screens

    document.body.appendChild(c);
    return c;
  }

  function showToast(type, message, timeoutMs) {
    timeoutMs = typeof timeoutMs === 'number' ? timeoutMs : 2500;
    var container = getToastContainer();

    var toast = document.createElement('div');
    // Bootstrap alert class plus custom inline styling for centering & look
    toast.className = 'alert alert-' + (type || 'info');
    toast.style.maxWidth = '200px';
    toast.style.width = '100%';
    toast.style.boxSizing = 'border-box';
    toast.style.pointerEvents = 'auto';
    toast.style.margin = '0 auto';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 6px 20px rgba(0,0,0,0.08)';
    toast.style.padding = '10px 18px';
    toast.style.textAlign = 'center';
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 160ms ease, transform 160ms ease';
    toast.style.transform = 'translateY(-6px)';

    // message
    toast.innerText = message;

    container.appendChild(toast);

    // animate in
    requestAnimationFrame(function(){
      toast.style.opacity = '1';
      toast.style.transform = 'translateY(0)';
    });

    // auto remove
    setTimeout(function(){
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-6px)';
      setTimeout(function(){ try{ toast.remove(); } catch(e){} }, 200);
    }, timeoutMs);

    return toast;
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

  function validateForm() {
    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      var firstInvalid = form.querySelector(':invalid');
      if (firstInvalid) firstInvalid.focus();
      return false;
    }
    return true;
  }

  btn.addEventListener('click', async function (e) {
    e.preventDefault();
    if (!validateForm()) return;

    startSpinner();
    var fd = new FormData(form);
    fd.append('from_ajax', '1');

    try {
      var resp = await fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: fd
      });

      var text = await resp.text();
      var json = null;
      try { json = JSON.parse(text); } catch (err) { json = null; }

      if (resp.ok && json && json.success) {
        // show top toast then navigate to next step
        showToast('success', json.message || 'Saved successfully', 1600);
        setTimeout(function() { window.location.href = nextUrl; }, 700);
        // On success, we DON'T call stopSpinner(). The button remains disabled
        // until the page navigates away, preventing multiple clicks.
      } else if (json && json.success === false) {
        showToast('warning', json.message || 'Server rejected request', 3500);
        console.warn('Save vitals — server returned success:false', json);
        stopSpinner(); // Re-enable button only on failure
      } else {
        showToast('danger', 'Invalid Server Response — see console', 4000);
        console.warn('Save vitals — unexpected response', {status: resp.status, text: text, json: json, endpoint: endpoint});
        stopSpinner(); // Re-enable button only on failure
      }
    } catch (err) {
      showToast('danger', 'Network error: ' + (err.message || err), 4000);
      console.error('Save vitals network error', err);
      stopSpinner(); // Re-enable button only on failure
    }
  });
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
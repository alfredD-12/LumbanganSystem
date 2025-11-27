<?php
// app/views/Survey/wizard_family_history.php
// Require user authentication and helpers
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();


// Load existing survey data from database
$surveyData = loadSurveyData();
// Ensure helper functions that reference the global `$surveyData` (e.g. isChecked)
// can access the data even when this view is included inside a controller method scope.
$GLOBALS['surveyData'] = $surveyData;

$fh = $surveyData['family_history'] ?? [];
$familyFields = ['hypertension','stroke','heart_attack','asthma','diabetes','cancer','kidney_disease'];
$anyFamilyCondition = false;
foreach ($familyFields as $ff) {
  if (isset($fh[$ff]) && (int)$fh[$ff] === 1) { $anyFamilyCondition = true; break; }
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

        <a class="wizard-step active" href="" data-key="history">
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

    <!-- Info Alert -->
    <div class="alert alert-info d-flex align-items-start gap-3 mb-4" style="border-radius: 12px; border-left: 4px solid #1e3a5f;">
      <i class="fa-solid fa-circle-info fs-4 text-primary"></i>
      <div>
        <strong class="i18n" data-en="Family Health History" data-tl="Kasaysayan ng Kalusugan ng Pamilya">Family Health History</strong>
        <p class="mb-0 small i18n"
           data-en="Does any 1st degree relative (parent, sibling, child) have or had any of these conditions? Check all that apply."
           data-tl="May kahit sinong 1st degree relative (magulang, kapatid, anak) na may o nagkaroon ng alinman sa mga kondisyong ito? Markahan lahat ng naaangkop.">
          Does any 1st degree relative (parent, sibling, child) have or had any of these conditions? Check all that apply.
        </p>
      </div>
    </div>

    <form id="form-family-history" class="needs-validation" novalidate>

      <!-- Health Conditions Section -->
      <div class="section-card p-4 mb-4">
        <div class="section-head">
          <div class="section-icon"><i class="fa-solid fa-heartbeat"></i></div>
          <div>
            <h5 class="section-title mb-1">
              <span class="i18n" data-en="Medical Conditions" data-tl="Mga Kondisyon Medikal">Medical Conditions</span>
            </h5>
            <p class="text-muted small mb-0 i18n"
               data-en="Select all conditions that apply to your immediate family members"
               data-tl="Piliin lahat ng kondisyon na naaangkop sa iyong malapit na mga miyembro ng pamilya">
              Select all conditions that apply to your immediate family members
            </p>
          </div>
        </div>

        <div class="row g-3 mt-3">
          <!-- Hypertension -->
          <div class="col-12 col-md-6">
            <div class="health-checkbox d-flex align-items-center gap-3">
              <div class="condition-icon">
                <i class="fa-solid fa-heart-circle-exclamation"></i>
              </div>
              <div class="flex-grow-1">
                <input type="checkbox" name="hypertension" id="hypertension" value="1" class="form-check-input me-2"
                       <?php echo isChecked('family_history', 'hypertension', 1); ?>>
                <label for="hypertension" class="i18n" data-en="Hypertension (High Blood Pressure)" data-tl="Hypertension (Mataas na Presyon ng Dugo)">
                  Hypertension (High Blood Pressure)
                </label>
              </div>
            </div>
          </div>

          <!-- Stroke -->
          <div class="col-12 col-md-6">
            <div class="health-checkbox d-flex align-items-center gap-3">
              <div class="condition-icon">
                <i class="fa-solid fa-brain"></i>
              </div>
              <div class="flex-grow-1">
                <input type="checkbox" name="stroke" id="stroke" value="1" class="form-check-input me-2"
                       <?php echo isChecked('family_history', 'stroke', 1); ?>>
                <label for="stroke" class="i18n" data-en="Stroke" data-tl="Stroke">
                  Stroke
                </label>
              </div>
            </div>
          </div>

          <!-- Heart Attack -->
          <div class="col-12 col-md-6">
            <div class="health-checkbox d-flex align-items-center gap-3">
              <div class="condition-icon">
                <i class="fa-solid fa-heart-pulse"></i>
              </div>
              <div class="flex-grow-1">
                <input type="checkbox" name="heart_attack" id="heart_attack" value="1" class="form-check-input me-2"
                       <?php echo isChecked('family_history', 'heart_attack', 1); ?>>
                <label for="heart_attack" class="i18n" data-en="Heart Attack" data-tl="Atake sa Puso">
                  Heart Attack
                </label>
              </div>
            </div>
          </div>

          <!-- Asthma -->
          <div class="col-12 col-md-6">
            <div class="health-checkbox d-flex align-items-center gap-3">
              <div class="condition-icon">
                <i class="fa-solid fa-lungs"></i>
              </div>
              <div class="flex-grow-1">
                <input type="checkbox" name="asthma" id="asthma" value="1" class="form-check-input me-2"
                       <?php echo isChecked('family_history', 'asthma', 1); ?>>
                <label for="asthma" class="i18n" data-en="Asthma" data-tl="Asthma / Hika">
                  Asthma
                </label>
              </div>
            </div>
          </div>

          <!-- Diabetes -->
          <div class="col-12 col-md-6">
            <div class="health-checkbox d-flex align-items-center gap-3">
              <div class="condition-icon">
                <i class="fa-solid fa-droplet"></i>
              </div>
              <div class="flex-grow-1">
                <input type="checkbox" name="diabetes" id="diabetes" value="1" class="form-check-input me-2"
                       <?php echo isChecked('family_history', 'diabetes', 1); ?>>
                <label for="diabetes" class="i18n" data-en="Diabetes" data-tl="Diabetes">
                  Diabetes
                </label>
              </div>
            </div>
          </div>

          <!-- Cancer -->
          <div class="col-12 col-md-6">
            <div class="health-checkbox d-flex align-items-center gap-3">
              <div class="condition-icon">
                <i class="fa-solid fa-ribbon"></i>
              </div>
              <div class="flex-grow-1">
                <input type="checkbox" name="cancer" id="cancer" value="1" class="form-check-input me-2"
                       <?php echo isChecked('family_history', 'cancer', 1); ?>>
                <label for="cancer" class="i18n" data-en="Cancer" data-tl="Kanser">
                  Cancer
                </label>
              </div>
            </div>
          </div>

          <!-- Kidney Disease -->
          <div class="col-12 col-md-6">
            <div class="health-checkbox d-flex align-items-center gap-3">
              <div class="condition-icon">
                <i class="fa-solid fa-circle-dot"></i>
              </div>
              <div class="flex-grow-1">
                <input type="checkbox" name="kidney_disease" id="kidney_disease" value="1" class="form-check-input me-2"
                       <?php echo isChecked('family_history', 'kidney_disease', 1); ?>>
                <label for="kidney_disease" class="i18n" data-en="Kidney Disease" data-tl="Sakit sa Bato">
                  Kidney Disease
                </label>
              </div>
            </div>
          </div>

            <!-- None -->
            <div class="col-12">
              <div class="health-checkbox d-flex align-items-center gap-3" style="background: #fef3c7; border-color: #f59e0b;">
                <div class="condition-icon" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(251, 191, 36, 0.2)); color: #f59e0b;">
                  <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div class="flex-grow-1">
                  <input type="checkbox" name="none" id="none" value="1" class="form-check-input me-2" style="accent-color: #f59e0b;" <?php echo $anyFamilyCondition ? '' : 'checked'; ?>>
                  <label for="none" class="i18n" data-en="None of the above" data-tl="Wala sa nabanggit">
                    None of the above
                  </label>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Sticky Actions -->
        <div class="sticky-actions">
          <div class="actions-inner">
            <div>
              <span class="text-muted small i18n" data-en="Step 3 of 8" data-tl="Hakbang 3 ng 8">Step 3 of 8</span>
            </div>
            <div class="d-flex gap-2">
              <a href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_vitals'); ?>" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>
                <span class="i18n" data-en="Back" data-tl="Bumalik">Back</span>
              </a>
              <button type="button" id="btn-dummy-save" class="btn btn-primary">
                <span class="i18n" data-en="Save & Continue" data-tl="I-save at Magpatuloy">Save & Continue</span>
                <i class="fa-solid fa-arrow-right ms-2"></i>
              </button>
            </div>
          </div>
        </div>

      </form>

    </div>
  </main>

<!-- Dashboard footer modal (BHW info) -->
<div class="modal fade" id="bhwInfoModal" tabindex="-1" aria-labelledby="bhwInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title i18n" id="bhwInfoModalLabel" data-en="Survey Assistance Notice" data-tl="Pabatid Tungkol sa Pagsusuri">Survey Assistance Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="i18n" data-en="A barangay health worker will visit to complete this survey and perform measurements that require equipment (for example, blood pressure, blood glucose, and other vitals). You can answer any questions you know, but the health worker will handle any checks needing instruments." data-tl="Bibilhin ka ng isang barangay health worker para kumpletuhin ang pagsusuring ito at magsagawa ng mga pagsukat na nangangailangan ng kagamitan (hal., presyon ng dugo, blood glucose, at iba pang vital). Maaari mong sagutin ang mga tanong na alam mo, ngunit ang health worker ang gagawa ng mga pagsusuring nangangailangan ng instrumento."></p>
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

<!-- Save handler: AJAX save + top-center toast + defensive submit prevention -->
<script>
(function(){
  var btn = document.getElementById('btn-dummy-save');
  var form = document.getElementById('form-family-history');
  if (!btn || !form) return;

  // Defensive: prevent native form navigation if something else triggers submit()
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    ev.stopImmediatePropagation();
    return false;
  }, { capture: true });

  var endpoint = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=survey_wizard_family_history&action=save_family_history'); ?>;
  var nextUrl  = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=survey_wizard_family'); ?>;

  // Top-center toast container (creates if missing)
  function getToastContainer() {
    var id = 'survey_toast_container';
    var c = document.getElementById(id);
    if (c) return c;

    c = document.createElement('div');
    c.id = id;
    c.style.position = 'fixed';
    // adjust top so it sits below your fixed header; tweak if needed
    c.style.top = '72px';
    c.style.left = '50%';
    c.style.transform = 'translateX(-50%)';
    c.style.zIndex = 20000;
    c.style.pointerEvents = 'none';
    c.style.display = 'flex';
    c.style.flexDirection = 'column';
    c.style.alignItems = 'center';
    c.style.gap = '8px';
    c.style.width = '100%';
    c.style.boxSizing = 'border-box';
    c.style.padding = '0 12px';
    document.body.appendChild(c);
    return c;
  }

  // show transient toast at top-center (centered box)
  function showToast(type, message, timeoutMs) {
    timeoutMs = typeof timeoutMs === 'number' ? timeoutMs : 2500;
    var container = getToastContainer();
    var toast = document.createElement('div');
    toast.className = 'alert alert-' + (type || 'info') + ' survey-toast';
    // Inline styles for consistent look; you can move to CSS file if preferred
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
    toast.innerText = message;
    container.appendChild(toast);
    requestAnimationFrame(function(){
      toast.style.opacity = '1';
      toast.style.transform = 'translateY(0)';
    });
    setTimeout(function(){
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-6px)';
      setTimeout(function(){ try{ toast.remove(); }catch(e){} }, 200);
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
        showToast('success', json.message || 'Saved successfully', 1600);
        setTimeout(function(){ window.location.href = nextUrl; }, 700);
        // On success, we DON'T call stopSpinner(). The button remains disabled
        // until the page navigates away, preventing multiple clicks.
      } else if (json && json.success === false) {
        showToast('warning', json.message || 'Server rejected request', 3500);
        console.warn('Save family history — server returned success:false', json);
        stopSpinner(); // Re-enable button only on failure
      } else {
        showToast('danger', 'Invalid Server Response — see console', 4000);
        console.warn('Save family history — unexpected response', {status: resp.status, text: text, json: json, endpoint: endpoint});
        stopSpinner(); // Re-enable button only on failure
      }
    } catch (err) {
      showToast('danger', 'Network error: ' + (err.message || err), 4000);
      console.error('Save family history network error', err);
      stopSpinner(); // Re-enable button only on failure
    }
  });
})();
</script>

  <script>
(function(){
  var KEY_POS = 'bhwFloatPos';
  var KEY_HIDDEN = 'bhwFloatHidden';
  var btn = document.getElementById('bhwFloatBtn');
  var hideBtn = document.getElementById('bhwFloatHide');
  var modalEl = document.getElementById('bhwInfoModal');
  var modalInstance = null;

  // Correctly initialize the modal instance once Bootstrap is ready.
  document.addEventListener('DOMContentLoaded', function() {
    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
      modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    }
  });

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
      modalInstance.show();
    }
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
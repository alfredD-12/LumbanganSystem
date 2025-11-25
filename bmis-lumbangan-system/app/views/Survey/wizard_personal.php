<?php

require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();

// Load existing survey data from database (all sections)
$surveyData = loadSurveyData();
$personal = $surveyData['personal'] ?? [];

if (!function_exists('personValue')) {
    function personValue($key, $default = '')
    {
        global $personal;
        return isset($personal[$key]) ? $personal[$key] : $default;
    }
}

include __DIR__ . '/../../components/resident_components/header-resident.php';

?>

<main class="survey-scope">
  <div class="floating-shapes" aria-hidden="true">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>
  
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

    <?php // If there is already an assessment for the current month, show an overlay asking whether to edit it ?>
    <?php if (!empty($surveyData['cvd_id'])): ?>
      <div id="existingAssessmentOverlay" aria-hidden="false" style="position:fixed;inset:0;background:rgba(0,0,0,0.48);z-index:22000;display:flex;align-items:center;justify-content:center;">
        <div role="dialog" aria-labelledby="existingAssessmentTitle" aria-modal="true" style="max-width:520px;width:calc(100% - 32px);background:#fff;border-radius:12px;padding:20px;box-shadow:0 12px 40px rgba(0,0,0,0.35);text-align:left;">
          <h4 id="existingAssessmentTitle" style="margin-top:0;margin-bottom:8px;">
            Existing Assessment Detected
          </h4>
          <p class="mb-2" style="color:#333;">
            We found an assessment already recorded for this month. Do you want to edit this month's Assessment Survey or return to your dashboard?
          </p>
          <div style="background:#f8fafc;padding:10px;border-radius:8px;margin-bottom:12px;color:#333;font-style:italic;">
            May naitalang pagsusuri na para sa buwang ito. Gusto mo bang i‑edit ang pagsusuring ito ngayong buwan o bumalik sa iyong dashboard?
          </div>
          <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button id="overlay-back" type="button" class="btn btn-outline-secondary">Back</button>
            <button id="overlay-proceed" type="button" class="btn btn-primary">Proceed</button>
          </div>
        </div>
      </div>
      <script>
        (function(){
          try {
            var overlay = document.getElementById('existingAssessmentOverlay');
            var btnProceed = document.getElementById('overlay-proceed');
            var btnBack = document.getElementById('overlay-back');
            var dashboardUrl = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=dashboard_resident'); ?>;


            // Determine if the user came from another survey step using document.referrer.
            // If they came from a survey page (e.g. survey_wizard_vitals), we should NOT show the overlay.
            try {
              var ref = document.referrer || '';
              var isFromSurveyRef = false;
              try {
                var parsed = new URL(ref);
                if (parsed.search && parsed.search.indexOf('page=survey_wizard_') !== -1) isFromSurveyRef = true;
              } catch (e) {
                // fallback: simple substring check
                if (ref.indexOf('survey_wizard_') !== -1) isFromSurveyRef = true;
              }

              if (isFromSurveyRef) {
                // hide overlay immediately if present (user navigated from another survey step)
                try { overlay.style.display = 'none'; } catch (e) { /* ignore */ }
              }
            } catch (e) { /* ignore */ }

            // Prevent interaction with the page while overlay exists
            if (overlay) {
              overlay.addEventListener('keydown', function(e){ if (e.key === 'Tab') { e.preventDefault(); } });
            }

            if (btnProceed) {
              btnProceed.addEventListener('click', function(){
                try { overlay.parentNode && overlay.parentNode.removeChild(overlay); } catch(e) { overlay.style.display = 'none'; }
              });
            }
            if (btnBack) {
              btnBack.addEventListener('click', function(){
                try { window.location.href = dashboardUrl; } catch(e) { window.location = dashboardUrl; }
              });
            }
          } catch (err) { console.warn('overlay init error', err); }
        })();
      </script>
    <?php endif; ?>

    <!-- Stepper -->
    <div class="wizard mb-4">
      <div class="wizard-track">
        <a class="wizard-step active" href="" data-key="personal">
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
        <strong class="i18n" data-en="Personal Information" data-tl="Personal na Impormasyon">Personal Information</strong>
        <p class="mb-0 small i18n"
           data-en="Please provide your personal details, demographics, contact information, and health metrics. All fields marked as required must be completed."
           data-tl="Mangyaring magbigay ng iyong personal na detalye, demograpiko, impormasyon sa pakikipag-ugnayan, at mga sukatan ng kalusugan. Lahat ng field na may required ay dapat kumpletuhin.">
          Please provide your personal details, demographics, contact information, and health metrics. All fields marked as required must be completed.
        </p>
      </div>
    </div>

    <!-- Wrap all sections in a single form so persistence and validation cover all fields -->
    <form id="form-person" class="needs-validation mt-3" novalidate>

    <!-- Identity -->
    <div class="section-card p-4 mb-4">
      <div class="section-head">
        <div class="section-icon"><i class="fa-solid fa-id-card"></i></div>
        <div>
          <h5 class="section-title mb-1"><span class="i18n" data-en="Identity" data-tl="Pagkakakilanlan">Identity</span></h5>
        </div>
      </div>

      <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="First Name" data-tl="Unang Pangalan">First Name</span></label>
          <input type="text" name="first_name" class="form-control form-control-lg i18n-ph"
                 data-ph-en="e.g., Juan" data-ph-tl="Hal., Juan"
                 value="<?php echo personValue('first_name'); ?>" required>
          <div class="invalid-feedback i18n" data-en="First name is required." data-tl="Kailangan ang unang pangalan.">First name is required.</div>
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Middle Name" data-tl="Gitnang Pangalan">Middle Name</span></label>
          <input type="text" name="middle_name" class="form-control form-control-lg i18n-ph"
                 data-ph-en="e.g., Santos" data-ph-tl="Hal., Santos"
                 value="<?php echo personValue('middle_name'); ?>">
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Last Name" data-tl="Apelyido">Last Name</span></label>
          <input type="text" name="last_name" class="form-control form-control-lg i18n-ph"
                 data-ph-en="e.g., Dela Cruz" data-ph-tl="Hal., Dela Cruz"
                 value="<?php echo personValue('last_name'); ?>" required>
          <div class="invalid-feedback i18n" data-en="Last name is required." data-tl="Kailangan ang apelyido.">Last name is required.</div>
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Suffix" data-tl="Sufiks">Suffix</span></label>
          <input type="text" name="suffix" data-optional="true" class="form-control form-control-lg i18n-ph"
                 data-ph-en="Jr / III" data-ph-tl="Jr / III"
                 value="<?php echo personValue('suffix'); ?>">
        </div>
      </div>
    </div>

    <!-- Demographics -->
    <div class="section-card p-4 mb-4">
      <div class="section-head">
        <div class="section-icon"><i class="fa-solid fa-user-group"></i></div>
        <div>
          <h5 class="section-title mb-1"><span class="i18n" data-en="Demographics" data-tl="Demograpiko">Demographics</span></h5>
        </div>
      </div>

      <div class="row g-4 mt-2">
        <div class="col-12 col-md-4">
          <label class="form-label"><span class="i18n" data-en="Sex" data-tl="Kasarian">Sex</span></label>
          <div class="btn-group btn-group-lg sex-group w-100" role="group">
            <input type="radio" class="btn-check" name="sex" id="sexM" value="M" <?php echo personValue('sex') === 'M' ? 'checked' : ''; ?>>
            <label class="btn btn-outline-primary flex-fill" for="sexM"><i class="fa-solid fa-person me-1"></i><span class="i18n" data-en="Male" data-tl="Lalaki">Male</span></label>
            <input type="radio" class="btn-check" name="sex" id="sexF" value="F" <?php echo personValue('sex') === 'F' ? 'checked' : ''; ?>>
            <label class="btn btn-outline-primary flex-fill" for="sexF"><i class="fa-solid fa-person-dress me-1"></i><span class="i18n" data-en="Female" data-tl="Babae">Female</span></label>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label"><span class="i18n" data-en="Birthdate" data-tl="Petsa ng Kapanganakan">Birthdate</span></label>
          <input type="text" name="birthdate" id="birthdate" class="form-control form-control-lg i18n-ph"
                 data-ph-en="Select date" data-ph-tl="Pumili ng petsa"
                 value="<?php echo personValue('birthdate'); ?>">
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label"><span class="i18n" data-en="Civil Status" data-tl="Katayuang Sibil">Civil Status</span></label>
          <select name="marital_status" class="form-select form-select-lg">
            <option value="" class="i18n" data-en="Select" data-tl="Pumili">Select</option>
            <option <?php echo personValue('marital_status') === 'Single' ? 'selected' : ''; ?>>Single</option>
            <option <?php echo personValue('marital_status') === 'Married' ? 'selected' : ''; ?>>Married</option>
            <option <?php echo personValue('marital_status') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
            <option <?php echo personValue('marital_status') === 'Separated' ? 'selected' : ''; ?>>Separated</option>
            <option <?php echo personValue('marital_status') === 'Common-law' ? 'selected' : ''; ?>>Common-law</option>
          </select>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label"><span class="i18n" data-en="Head of the Family?" data-tl="Ulo ng Pamilya?">Head of the Family?</span></label>
          <div class="btn-group btn-group-lg w-100" role="group">
            <input type="radio" class="btn-check" name="is_head" id="is_head_yes" value="1" <?php echo personValue('is_head', '0') === '1' ? 'checked' : ''; ?>>
            <label class="btn btn-outline-primary flex-fill" for="is_head_yes"><span class="i18n" data-en="Yes" data-tl="Oo">Yes</span></label>
            
            <input type="radio" class="btn-check" name="is_head" id="is_head_no" value="0" <?php echo personValue('is_head', '0') === '0' ? 'checked' : ''; ?>>
            <label class="btn btn-outline-primary flex-fill" for="is_head_no"><span class="i18n" data-en="No" data-tl="Hindi">No</span></label>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <label class="form-label"><span class="i18n" data-en="Age" data-tl="Edad">Age</span></label>
          <input type="text" id="age_display" name="age" class="form-control form-control-lg i18n-ph"
                 data-ph-en="Auto" data-ph-tl="Awtomatiko"
                 value="<?php echo personValue('age'); ?>" readonly>
        </div>
      </div>
    </div>

    <!-- Contact & Socio -->
    <div class="section-card p-4 mb-4">
      <div class="section-head">
        <div class="section-icon"><i class="fa-solid fa-address-card"></i></div>
        <div>
          <h5 class="section-title mb-1"><span class="i18n" data-en="Contact & Socio" data-tl="Impormasyon sa Pakikipag‑ugnayan at Sosyo‑ekonomiko">Contact & Socio</span></h5>
        </div>
      </div>

      <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-3 mt-2">
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Contact No." data-tl="Numero">Contact No.</span></label>
          <input type="text"
                 name="contact_no"
                 id="contact_no"
                 class="form-control form-control-lg i18n-ph"
                 inputmode="numeric"
                 autocomplete="tel-national"
                 data-ph-en="09xx-xxxx-xxx"
                 data-ph-tl="09xx-xxxx-xxx"
                 placeholder="09xx-xxxx-xxx"
                 pattern="^09\d{2}-\d{4}-\d{3}$"
                 maxlength="13"
                 value="<?php echo personValue('contact_no'); ?>">
          <div class="form-text i18n" data-en="Auto‑formats to 0921-3123-123. Digits only."
               data-tl="Awtomatikong nagfo‑format sa 0921-3123-123. Numero lamang.">Auto‑formats to 0921-3123-123. Digits only.</div>
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Educational Attainment" data-tl="Antas ng Edukasyon">Educational Attainment</span></label>
          <select name="highest_educ_attainment" class="form-select form-select-lg">
            <option value="" class="i18n" data-en="Select" data-tl="Pumili">Select</option>
            <option <?php echo personValue('highest_educ_attainment') === 'Elementary' ? 'selected' : ''; ?>>Elementary</option>
            <option <?php echo personValue('highest_educ_attainment') === 'High School' ? 'selected' : ''; ?>>High School</option>
            <option <?php echo personValue('highest_educ_attainment') === 'Tech/Voc' ? 'selected' : ''; ?>>Tech/Voc</option>
            <option <?php echo personValue('highest_educ_attainment') === 'College' ? 'selected' : ''; ?>>College</option>
            <option <?php echo personValue('highest_educ_attainment') === 'Masters' ? 'selected' : ''; ?>>Masters</option>
            <option <?php echo personValue('highest_educ_attainment') === 'Doctorate' ? 'selected' : ''; ?>>Doctorate</option>
          </select>
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Religion" data-tl="Relihiyon">Religion</span></label>
          <input type="text" name="religion" class="form-control form-control-lg i18n-ph"
                 data-ph-en="e.g., Roman Catholic" data-ph-tl="Hal., Roman Catholic"
                 value="<?php echo personValue('religion'); ?>">
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Occupation" data-tl="Trabaho">Occupation</span></label>
          <input type="text" name="occupation" class="form-control form-control-lg i18n-ph"
                 data-ph-en="e.g., Farmer" data-ph-tl="Hal., Magsasaka"
                 value="<?php echo personValue('occupation'); ?>">
        </div>
      </div>
    </div>

    <!-- Health & Biometrics -->
    <div class="section-card p-4 mb-4">
      <div class="section-head">
        <div class="section-icon"><i class="fa-solid fa-heart-circle-bolt"></i></div>
        <div>
          <h5 class="section-title mb-1"><span class="i18n" data-en="Health & Biometrics" data-tl="Kalusugan at Sukat ng Katawan">Health & Biometrics</span></h5>
        </div>
      </div>

      <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-4 mt-2">
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Blood Type" data-tl="Uri ng Dugo">Blood Type</span></label>
          <select name="blood_type" class="form-select form-select-lg">
            <option value="" class="i18n" data-en="Select" data-tl="Pumili">Select</option>
            <option value="A+" <?php echo personValue('blood_type') === 'A+' ? 'selected' : ''; ?>>A+</option>
            <option value="A-" <?php echo personValue('blood_type') === 'A-' ? 'selected' : ''; ?>>A-</option>
            <option value="B+" <?php echo personValue('blood_type') === 'B+' ? 'selected' : ''; ?>>B+</option>
            <option value="B-" <?php echo personValue('blood_type') === 'B-' ? 'selected' : ''; ?>>B-</option>
            <option value="AB+" <?php echo personValue('blood_type') === 'AB+' ? 'selected' : ''; ?>>AB+</option>
            <option value="AB-" <?php echo personValue('blood_type') === 'AB-' ? 'selected' : ''; ?>>AB-</option>
            <option value="O+" <?php echo personValue('blood_type') === 'O+' ? 'selected' : ''; ?>>O+</option>
            <option value="O-" <?php echo personValue('blood_type') === 'O-' ? 'selected' : ''; ?>>O-</option>
          </select>
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Disability (if any)" data-tl="Kapansanan (kung mayroon)">Disability (if any)</span></label>
          <input type="text" name="disability" data-optional="true" class="form-control form-control-lg i18n-ph"
                 data-ph-en="Describe" data-ph-tl="Ilarawan"
                 value="<?php echo personValue('disability'); ?>">
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Height" data-tl="Taas">Height</span></label>
          <div class="input-group input-group-lg">
            <input type="number" step="0.01" name="height_cm" class="form-control i18n-ph"
                   data-ph-en="0.00" data-ph-tl="0.00"
                   value="<?php echo surveyValue('vitals', 'height_cm', personValue('height_cm')); ?>">
            <span class="input-group-text">cm</span>
          </div>
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Weight" data-tl="Timbang">Weight</span></label>
          <div class="input-group input-group-lg">
            <input type="number" step="0.01" name="weight_kg" class="form-control i18n-ph"
                   data-ph-en="0.00" data-ph-tl="0.00"
                   value="<?php echo surveyValue('vitals', 'weight_kg', personValue('weight_kg')); ?>">
            <span class="input-group-text">kg</span>
          </div>
        </div>
        <div class="col">
          <label class="form-label"><span class="i18n" data-en="Waist Circumference" data-tl="Baywang">Waist Circumference</span></label>
          <div class="input-group input-group-lg">
            <input type="number" step="0.01" name="waist_circumference_cm" class="form-control i18n-ph"
                   data-ph-en="0.00" data-ph-tl="0.00"
                   value="<?php echo surveyValue('vitals', 'waist_circumference_cm', personValue('waist_circumference_cm')); ?>">
            <span class="input-group-text">cm</span>
          </div>
        </div>
      </div>
    </div>

    </form>

    <!-- Sticky Actions -->
    <div class="sticky-actions shadow">
      <div class="actions-inner content-narrow">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div class="text-muted small"><span class="i18n" data-en="Step 1 of 8" data-tl="Hakbang 1 ng 8">Step 1 of 8</span></div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" id="btn-cancel">
              <span class="i18n" data-en="Reset" data-tl="I‑reset">Reset</span>
            </button>
            <button type="button" class="btn btn-primary" id="btn-dummy-save">
              <span class="i18n" data-en="Save & Continue" data-tl="I‑save at Magpatuloy">Save & Continue</span>
              <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- (existing population + flatpickr scripts kept unchanged) -->
<script>
(function(){
  var SERVER_SURVEY = <?php echo json_encode($surveyData ?? []); ?> || {};
  function setField(name, value) {
    if (typeof value === 'undefined' || value === null) return;
    var els = document.getElementsByName(name);
    if (!els || els.length === 0) return;
    if (els.length > 1) {
      for (var i = 0; i < els.length; i++) {
        var el = els[i];
        if (el.type === 'radio') {
          if (String(el.value) === String(value)) el.checked = true;
        } else if (el.type === 'checkbox') {
          el.checked = (String(value) === '1' || String(value) === 'true' || value === true);
        } else {
          el.value = value;
        }
        el.dispatchEvent(new Event('input', {bubbles:true}));
      }
      return;
    }
    var el = els[0];
    if (!el) return;
    var tag = el.tagName.toLowerCase();
    var type = el.type;
    if (tag === 'select' || type === 'text' || tag === 'textarea' || type === 'email' || type === 'tel' || type === 'number') {
      el.value = value;
    } else if (type === 'radio') {
      if (String(el.value) === String(value)) el.checked = true;
    } else if (type === 'checkbox') {
      el.checked = (String(value) === '1' || String(value) === 'true' || value === true);
    } else {
      try { el.value = value; } catch(e){}
    }
    el.dispatchEvent(new Event('input', {bubbles:true}));
  }
  function populate(data) {
    if (!data || typeof data !== 'object') return;
    if (data.person && typeof data.person === 'object') {
      Object.keys(data.person).forEach(function(k){ setField(k, data.person[k]); });
    }
    if (data.vitals && typeof data.vitals === 'object') {
      Object.keys(data.vitals).forEach(function(k){ setField(k, data.vitals[k]); });
    }
    ['lifestyle','household','family','angina','diabetes','personal'].forEach(function(section){
      if (data[section] && typeof data[section] === 'object') {
        Object.keys(data[section]).forEach(function(k){ setField(k, data[section][k]); });
      }
    });
    Object.keys(data).forEach(function(k){
      if (['person','vitals','lifestyle','household','family','angina','diabetes'].indexOf(k) !== -1) return;
      setField(k, data[k]);
    });
    try {
      var bdEl = document.querySelector('input[name="birthdate"]');
      var ageEl = document.getElementById('age_display');
      if (bdEl && bdEl.value && ageEl) {
        var age = (function(s){
          if (!s) return '';
          var str = String(s).trim();
          var parts;
          if (str.indexOf('-') !== -1) parts = str.split('-');
          else if (str.indexOf('/') !== -1) parts = str.split('/');
          else {
            var dt = new Date(str);
            if (isNaN(dt)) return '';
            var years = new Date().getFullYear() - dt.getFullYear();
            var m = new Date().getMonth() - dt.getMonth();
            if (m < 0 || (m === 0 && new Date().getDate() < dt.getDate())) years--;
            return String(years);
          }
          var y,m,d;
          if (parts.length === 3) {
            if (parts[0].length === 4) { y = parseInt(parts[0],10); m = parseInt(parts[1],10)-1; d = parseInt(parts[2],10); }
            else { y = parseInt(parts[2],10); m = parseInt(parts[0],10)-1; d = parseInt(parts[1],10); }
          } else return '';
          var dob = new Date(y,m,d);
          if (isNaN(dob)) return '';
          var years = new Date().getFullYear() - dob.getFullYear();
          var mm = new Date().getMonth() - dob.getMonth();
          if (mm < 0 || (mm === 0 && new Date().getDate() < dob.getDate())) years--;
          return String(years);
        })(bdEl.value);
        if (age !== '') ageEl.value = age;
      }
    } catch(e){}
  }
  document.addEventListener('DOMContentLoaded', function(){ try { populate(SERVER_SURVEY || {}); } catch (e) { console.warn('Survey populate error', e); } });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var bd = document.getElementById('birthdate');
  if (!bd) return;
  function initFlatpickr() {
    try {
      flatpickr(bd, {
        altInput: true,
        altFormat: "F d, Y",
        dateFormat: "Y-m-d",
        maxDate: "today",
        allowInput: true,
        clickOpens: true,
      });
      if (bd.value) {
        try { bd._flatpickr.setDate(bd.value, true); } catch (e) { /* ignore */ }
      }
    } catch (err) {
      console.warn('flatpickr init error', err);
    }
  }
  if (window.flatpickr) { initFlatpickr(); return; }
  (function loadFlatpickrFromCDN() {
    var cssHref = "https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css";
    var jsSrc  = "https://cdn.jsdelivr.net/npm/flatpickr";
    if (!document.querySelector('link[data-fp="true"]')) {
      var l = document.createElement('link'); l.rel = "stylesheet"; l.href = cssHref; l.setAttribute('data-fp', 'true'); document.head.appendChild(l);
    }
    if (!document.querySelector('script[data-fp="true"]')) {
      var s = document.createElement('script'); s.src = jsSrc; s.defer = true; s.setAttribute('data-fp', 'true'); s.onload = initFlatpickr; s.onerror = function() { console.warn('Failed to load flatpickr from CDN'); }; document.head.appendChild(s);
    } else {
      var checkInterval = setInterval(function(){ if (window.flatpickr) { clearInterval(checkInterval); initFlatpickr(); } }, 80);
      setTimeout(function(){ clearInterval(checkInterval); }, 3000);
    }
  })();
});
</script>

<!-- Minimal, single-endpoint Save & Continue JS integrated with SurveyController::save_personal_action -->
<script>
(function(){
  var btn = document.getElementById('btn-dummy-save'); // This is the correct ID from the HTML
  var form = document.getElementById('form-person');
  if (!btn || !form) return;

  // defensive: ensure form uses POST and does not navigate by default
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    ev.stopImmediatePropagation();
    return false;
  }, { capture: true });

  var endpoint = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=survey_wizard_personal&action=save_personal'); ?>;
  var nextUrl  = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=survey_wizard_vitals'); ?>;

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
    const savingText = lang === 'tl' ? 'Nagse-save...' : 'Saving...';
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
    fd.append('from_ajax', '1'); // marker so server can detect AJAX (optional)

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
        // show top toast then navigate
        showToast('success', json.message || 'Saved successfully', 1600);
        setTimeout(function() { window.location.href = nextUrl; }, 700);
        // On success, we DON'T call stopSpinner(). The button remains disabled
        // until the page navigates away, preventing multiple clicks.
      } else if (json && json.success === false) {
        showToast('warning', json.message || 'Server rejected request', 3500);
        console.warn('Save personal — server returned success:false', json);
        stopSpinner(); // Re-enable button only on failure
      } else {
        showToast('danger', 'Invalid Server Response — see console', 4000);
        console.warn('Save personal — unexpected response', {status: resp.status, text: text, json: json, endpoint: endpoint});
        stopSpinner(); // Re-enable button only on failure
      }
    } catch (err) {
      showToast('danger', 'Network error: ' + (err.message || err), 4000);
      console.error('Save personal network error', err);
      stopSpinner(); // Re-enable button only on failure
    }
  });
})();
</script>

<!-- Informational modal: barangay health worker notice (auto-show once on Personal page) -->
  <div class="modal fade" id="bhwInfoModal" tabindex="-1" aria-labelledby="bhwInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title i18n" id="bhwInfoModalLabel" data-en="Survey Assistance Notice" data-tl="Pabatid Tungkol sa Pagsusuri">Survey Assistance Notice</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p style="font-size:1rem;color:#333;line-height:1.6;margin-bottom:1rem;">
            A barangay health worker will visit to complete this survey and perform measurements that require equipment (for example: blood pressure, blood glucose, and other vitals). You can answer any questions you know, but the health worker will handle any checks needing instruments.
          </p>

          <div style="background:#f8fafc;padding:1rem;border-radius:8px;margin-bottom:1rem;">
            <p style="font-size:1rem;color:#333;line-height:1.6;font-style:italic;margin:0;">
              Ang buwanang assessment survey ay isang mahalagang kasangkapan para sa ating barangay. Ang iyong pakikilahok ay tumutulong sa amin na makakalap ng mahahalagang datos upang masubaybayan ang kapakanan ng ating komunidad, matukoy ang mga pangangailangan, at makagawa ng mga desisyon para sa mga proyekto at serbisyo sa hinaharap.
            </p>
          </div>

          <p style="font-size:0.95rem;color:#666;margin-top:0;">Thank you for your cooperation!</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><span class="i18n" data-en="Close" data-tl="Isara">Close</span></button>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><span class="i18n" data-en="Understood" data-tl="Naiintindihan">Understood</span></button>
        </div>
      </div>
    </div>
  </div>

<script>
(function(){
  // Show bhwInfoModal once per browser session (sessionStorage) on the Personal page.
  // This means it will reappear when the user opens a new browser tab/window (a new session),
  // but will show only once while that tab/window is open.
  var KEY = 'bhwInfoShown_personal_session';

  function showOncePerSession() {
    try {
      // already shown this session?
      if (sessionStorage.getItem(KEY)) return;

      var el = document.getElementById('bhwInfoModal');
      if (!el) return;

      // Prefer bootstrap's API if available (safe and idempotent).
      if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function') {
        try {
          bootstrap.Modal.getOrCreateInstance(el).show();
        } catch (err) {
          // fallback to manual show below
        }
      } else if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
        try {
          var m = new bootstrap.Modal(el);
          m.show();
        } catch (e) { /* ignore */ }
      } else {
        // minimal fallback (non-bootstrap): reveal modal node
        el.classList.add('show');
        el.style.display = 'block';
        el.setAttribute('aria-modal', 'true');
        el.removeAttribute('aria-hidden');
      }

      // mark as shown for this browser session
      try { sessionStorage.setItem(KEY, '1'); } catch (e) { /* ignore */ }
    } catch (e) {
      // swallow any errors to avoid breaking the page
      console.warn('bhwInfoModal session show error', e);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', showOncePerSession, { once: true });
  } else {
    // DOM already ready
    showOncePerSession();
  }
})();
</script>

<?php
// Include the resident footer which closes the body/html
include_once __DIR__ . '/../../components/resident_components/footer-resident.php';
?>
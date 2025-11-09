<?php
// Require user authentication
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
requireUser(); // Only allow logged-in users to access survey

$appRoot    = dirname(__DIR__, 2); // .../app
$components = $appRoot . '/components';
$fullName = getFullName();
$username = getUsername();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Survey Wizard — Personal</title>

  <!-- Vendor CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Survey-only CSS - Single Consolidated File -->
  <link rel="stylesheet" href="../../assets/css/Survey/wizard_personal.css">

  <!-- Page tokens + tiny utilities (scoped to this page) -->
  <style>
    /* Modern Scrollbar - copied from dashboard.css */
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

    :root{
      --brand-blue:#1e3a8a;
      --brand-red:#b91c1c;
      --grad-start:#4f76e2;
      --grad-end:#b91c1c;
      --surface:#f6f8fb;
    }
    body.bg-surface{ background:var(--surface); }

    .content-narrow{ max-width: 1100px; margin: 0 auto; }

    .brand-mark{
      width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;
      background:linear-gradient(135deg,var(--brand-blue),var(--brand-red)); color:#fff;
    }

    .hero{
      background:linear-gradient(90deg,var(--grad-start),var(--grad-end));
      color:#fff;
    }

    .site-footer{
      background:linear-gradient(135deg,var(--brand-blue),var(--brand-red));
      color:#fff;margin-top:48px;
    }
    .text-white-70{ color:rgba(255,255,255,.85); }
  </style>
</head>
<body class="bg-surface">
  <!-- Floating Background Shapes -->
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <!-- Dashboard header (markup-only or namespaced; ensure it does NOT inject dashboard.css) -->
  <?php require_once $components . '/headerdashboard.php'; ?>

  <!-- Shrink header on scroll (for parity with dashboard) -->
  <script>
    (function(){
      if (window.__bdhfScrollInit) return; window.__bdhfScrollInit = true;
      var nav = document.querySelector('.bdhf.user-navbar') || document.querySelector('.user-navbar');
      function apply(){ var s=(window.scrollY||window.pageYOffset||0)>8; nav && nav.classList.toggle('scrolled', s); }
      apply(); window.addEventListener('scroll', apply, {passive:true}); window.addEventListener('resize', apply, {passive:true});
    })();
  </script>

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
          <a class="wizard-step active" href="wizard_personal.php" data-key="personal">
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
          <strong class="i18n" data-en="Personal Information" data-tl="Personal na Impormasyon">Personal Information</strong>
          <p class="mb-0 small i18n" 
             data-en="Please provide your personal details, demographics, contact information, and health metrics. All fields marked as required must be completed."
             data-tl="Mangyaring magbigay ng iyong personal na detalye, demograpiko, impormasyon sa pakikipag-ugnayan, at mga sukatan ng kalusugan. Lahat ng field na may required ay dapat kumpletuhin.">
            Please provide your personal details, demographics, contact information, and health metrics. All fields marked as required must be completed.
          </p>
        </div>
      </div>

      <!-- Identity -->
      <div class="section-card p-4 mb-4">
        <div class="section-head">
          <div class="section-icon"><i class="fa-solid fa-id-card"></i></div>
          <div>
            <h5 class="section-title mb-1"><span class="i18n" data-en="Identity" data-tl="Pagkakakilanlan">Identity</span></h5>
          </div>
        </div>

        <form id="form-person" class="needs-validation mt-3" novalidate>
          <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
              <label class="form-label"><span class="i18n" data-en="First Name" data-tl="Unang Pangalan">First Name</span></label>
              <input type="text" name="first_name" class="form-control form-control-lg i18n-ph"
                     data-ph-en="e.g., Juan" data-ph-tl="Hal., Juan" required>
              <div class="invalid-feedback i18n" data-en="First name is required." data-tl="Kailangan ang unang pangalan.">First name is required.</div>
            </div>
            <div class="col">
              <label class="form-label"><span class="i18n" data-en="Middle Name" data-tl="Gitnang Pangalan">Middle Name</span></label>
              <input type="text" name="middle_name" class="form-control form-control-lg i18n-ph"
                     data-ph-en="e.g., Santos" data-ph-tl="Hal., Santos">
            </div>
            <div class="col">
              <label class="form-label"><span class="i18n" data-en="Last Name" data-tl="Apelyido">Last Name</span></label>
              <input type="text" name="last_name" class="form-control form-control-lg i18n-ph"
                     data-ph-en="e.g., Dela Cruz" data-ph-tl="Hal., Dela Cruz" required>
              <div class="invalid-feedback i18n" data-en="Last name is required." data-tl="Kailangan ang apelyido.">Last name is required.</div>
            </div>
            <div class="col">
              <label class="form-label"><span class="i18n" data-en="Suffix" data-tl="Sufiks">Suffix</span></label>
              <input type="text" name="suffix" class="form-control form-control-lg i18n-ph"
                     data-ph-en="Jr / III" data-ph-tl="Jr / III">
            </div>
          </div>
        </form>
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
              <input type="radio" class="btn-check" name="sex" id="sexM" value="M">
              <label class="btn btn-outline-primary flex-fill" for="sexM"><i class="fa-solid fa-person me-1"></i><span class="i18n" data-en="Male" data-tl="Lalaki">Male</span></label>
              <input type="radio" class="btn-check" name="sex" id="sexF" value="F">
              <label class="btn btn-outline-primary flex-fill" for="sexF"><i class="fa-solid fa-person-dress me-1"></i><span class="i18n" data-en="Female" data-tl="Babae">Female</span></label>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label"><span class="i18n" data-en="Birthdate" data-tl="Petsa ng Kapanganakan">Birthdate</span></label>
            <input type="text" name="birthdate" id="birthdate" class="form-control form-control-lg i18n-ph"
                   data-ph-en="Select date" data-ph-tl="Pumili ng petsa">
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label"><span class="i18n" data-en="Civil Status" data-tl="Katayuang Sibil">Civil Status</span></label>
            <select name="marital_status" class="form-select form-select-lg">
              <option value="" class="i18n" data-en="Select" data-tl="Pumili">Select</option>
              <option>Single</option><option>Married</option><option>Widowed</option>
              <option>Separated</option><option>Common-law</option>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label"><span class="i18n" data-en="Family Position" data-tl="Posisyon sa Pamilya">Family Position</span></label>
            <select name="family_position" class="form-select form-select-lg">
              <option value="" class="i18n" data-en="Select" data-tl="Pumili">Select</option>
              <option>Head</option><option>Spouse</option><option>Child</option>
              <option>Relative</option><option>Member</option><option>Other</option>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label"><span class="i18n" data-en="Age" data-tl="Edad">Age</span></label>
            <input type="text" id="age_display" class="form-control form-control-lg i18n-ph"
                   data-ph-en="Auto" data-ph-tl="Awtomatiko" readonly>
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
                   pattern="^09\\d{2}-\\d{4}-\\d{3}$"
                   maxlength="13">
            <div class="form-text i18n" data-en="Auto‑formats to 0921-3123-123. Digits only."
                 data-tl="Awtomatikong nagfo‑format sa 0921-3123-123. Numero lamang.">Auto‑formats to 0921-3123-123. Digits only.</div>
          </div>
          <div class="col">
            <label class="form-label"><span class="i18n" data-en="Educational Attainment" data-tl="Antas ng Edukasyon">Educational Attainment</span></label>
            <select name="highest_educ_attainment" class="form-select form-select-lg">
              <option value="" class="i18n" data-en="Select" data-tl="Pumili">Select</option>
              <option>Elementary</option><option>High School</option><option>Tech/Voc</option>
              <option>College</option><option>Masters</option><option>Doctorate</option>
            </select>
          </div>
          <div class="col">
            <label class="form-label"><span class="i18n" data-en="Religion" data-tl="Relihiyon">Religion</span></label>
            <input type="text" name="religion" class="form-control form-control-lg i18n-ph"
                   data-ph-en="e.g., Roman Catholic" data-ph-tl="Hal., Roman Catholic">
          </div>
          <div class="col">
            <label class="form-label"><span class="i18n" data-en="Occupation" data-tl="Trabaho">Occupation</span></label>
            <input type="text" name="occupation" class="form-control form-control-lg i18n-ph"
                   data-ph-en="e.g., Farmer" data-ph-tl="Hal., Magsasaka">
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
              <option value="A+">A+</option><option value="A-">A-</option>
              <option value="B+">B+</option><option value="B-">B-</option>
              <option value="AB+">AB+</option><option value="AB-">AB-</option>
              <option value="O+">O+</option><option value="O-">O-</option>
            </select>
          </div>
          <div class="col">
            <label class="form-label"><span class="i18n" data-en="Disability (if any)" data-tl="Kapansanan (kung mayroon)">Disability (if any)</span></label>
            <input type="text" name="disability" class="form-control form-control-lg i18n-ph"
                   data-ph-en="Describe" data-ph-tl="Ilarawan">
          </div>
          <div class="col">
            <label class="form-label"><span class="i18n" data-en="Height" data-tl="Taas">Height</span></label>
            <div class="input-group input-group-lg">
              <input type="number" step="0.01" name="height_cm" class="form-control i18n-ph"
                     data-ph-en="0.00" data-ph-tl="0.00">
              <span class="input-group-text">cm</span>
            </div>
          </div>
          <div class="col">
            <label class="form-label"><span class="i18n" data-en="Weight" data-tl="Timbang">Weight</span></label>
            <div class="input-group input-group-lg">
              <input type="number" step="0.01" name="weight_kg" class="form-control i18n-ph"
                     data-ph-en="0.00" data-ph-tl="0.00">
              <span class="input-group-text">kg</span>
            </div>
          </div>
          <div class="col">
            <label class="form-label"><span class="i18n" data-en="Waist Circumference" data-tl="Baywang">Waist Circumference</span></label>
            <div class="input-group input-group-lg">
              <input type="number" step="0.01" name="waist_circumference_cm" class="form-control i18n-ph"
                     data-ph-en="0.00" data-ph-tl="0.00">
              <span class="input-group-text">cm</span>
            </div>
          </div>
        </div>
      </div>

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

  <!-- Dashboard footer -->
  <?php require_once $components . '/footerdashboard.php'; ?>

  <!-- Vendor JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <!-- Survey-only JS -->
  <script src="../../assets/js/Survey/wizard_personal.js"></script>
</body>
</html>
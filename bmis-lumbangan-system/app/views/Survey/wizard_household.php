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
  <title>Survey Wizard — Household Information</title>

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

    /* Other input styling */
    .other-input {
      margin-top: 0.5rem;
      display: none;
    }
    .other-input.show {
      display: block;
    }

    /* Small text styling */
    small.text-muted {
      display: block;
      margin-top: 0.25rem;
      font-size: 0.75rem;
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

          <a class="wizard-step" href="wizard_diabetes.php" data-key="diabetes">
            <span class="step-circle"><i class="fa-solid fa-syringe"></i></span>
            <span class="step-label i18n" data-en="Diabetes" data-tl="Diabetes">Diabetes</span>
          </a>
          <span class="wizard-connector" aria-hidden="true"></span>

          <a class="wizard-step active" href="wizard_household.php" data-key="household">
            <span class="step-circle"><i class="fa-solid fa-house"></i></span>
            <span class="step-label i18n" data-en="Household" data-tl="Sambahayan">Household</span>
          </a>
        </div>
      </div>

      <!-- Info Alert -->
      <div class="alert alert-info d-flex align-items-start gap-3 mb-4" style="border-radius: 12px; border-left: 4px solid #1e3a5f;">
        <i class="fa-solid fa-circle-info fs-4 text-primary"></i>
        <div>
          <strong class="i18n" data-en="Household & Family Information" data-tl="Impormasyon ng Sambahayan at Pamilya">Household & Family Information</strong>
          <p class="mb-0 small i18n" 
             data-en="Please provide information about your household living conditions, facilities, and family details."
             data-tl="Mangyaring magbigay ng impormasyon tungkol sa inyong kondisyon ng pamumuhay, mga pasilidad, at detalye ng pamilya.">
            Please provide information about your household living conditions, facilities, and family details.
          </p>
        </div>
      </div>

      <form id="form-household" class="needs-validation" novalidate>

        <!-- Address Information Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-location-dot"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Address Information" data-tl="Impormasyon ng Address">Address Information</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="purok_sitio" class="form-label fw-semibold">
                <span class="i18n" data-en="Purok / Sitio" data-tl="Purok / Sitio">Purok / Sitio</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="purok_sitio" name="purok_sitio" required>
                <option value="" selected disabled>Select...</option>
                <option value="SA">Sagbat (SA)</option>
                <option value="CA">Campo Avejar (CA)</option>
                <option value="RV">Roxas Village (RV)</option>
                <option value="CE">Central (CE)</option>
                <option value="CC">Camachilihan (CC)</option>
                <option value="EP">El Paso (EP)</option>
                <option value="CD">Calamundingan (CD)</option>
                <option value="RO">Role (RO)</option>
                <option value="MA">Mambugan (MA)</option>
                <option value="MN">Malangaw (MN)</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select purok/sitio." data-tl="Mangyaring pumili ng purok/sitio.">
                Please select purok/sitio.
              </div>
            </div>

            <div class="col-md-6">
              <label for="household_no" class="form-label fw-semibold">
                <span class="i18n" data-en="Household Number (Auto-generated)" data-tl="Numero ng Sambahayan (Auto-generated)">Household Number (Auto-generated)</span>
              </label>
              <input type="text" class="form-control bg-light" id="household_no" name="household_no" 
                     placeholder="Will be generated based on Purok" readonly>
              <small class="text-muted i18n" data-en="Format: [Purok Code][Number] (e.g., CA-001)" data-tl="Format: [Purok Code][Number] (hal., CA-001)">
                Format: [Purok Code][Number] (e.g., CA-001)
              </small>
            </div>

            <div class="col-md-6">
              <label for="address_house_no" class="form-label fw-semibold">
                <span class="i18n" data-en="House No. / Block & Lot" data-tl="House No. / Block & Lot">House No. / Block & Lot</span>
                <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" id="address_house_no" name="address_house_no" 
                     placeholder="e.g., 123 or Blk 5 Lot 12" required>
              <div class="invalid-feedback i18n" data-en="Please provide house number or block & lot." data-tl="Mangyaring magbigay ng house number o block & lot.">
                Please provide house number or block & lot.
              </div>
            </div>

            <div class="col-md-6">
              <label for="address_street" class="form-label fw-semibold">
                <span class="i18n" data-en="Street Name" data-tl="Pangalan ng Kalye">Street Name</span>
              </label>
              <input type="text" class="form-control" id="address_street" name="address_street" 
                     placeholder="Street name">
            </div>

            <div class="col-md-6">
              <label for="address_sitio_subdivision" class="form-label fw-semibold">
                <span class="i18n" data-en="Subdivision / Compound" data-tl="Subdivision / Compound">Subdivision / Compound</span>
              </label>
              <input type="text" class="form-control" id="address_sitio_subdivision" name="address_sitio_subdivision" 
                     placeholder="Subdivision or Compound name">
            </div>

            <div class="col-md-6">
              <label for="address_building" class="form-label fw-semibold">
                <span class="i18n" data-en="Building / Apartment Name (Unit #, if applicable)" data-tl="Pangalan ng Building / Apartment (Unit #, kung mayroon)">Building / Apartment Name (Unit #, if applicable)</span>
              </label>
              <input type="text" class="form-control" id="address_building" name="address_building" 
                     placeholder="Building or apartment name with unit number (if applicable)">
            </div>
          </div>
        </div>

        <!-- Home Ownership & Construction Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-home"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Home Ownership & Construction" data-tl="Pagmamay-ari at Konstruksyon ng Bahay">Home Ownership & Construction</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Home Ownership" data-tl="Pagmamay-ari ng Bahay">Home Ownership</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="home_ownership" name="home_ownership" required>
                <option value="" selected disabled>Select...</option>
                <option value="Owned">Owned / May-ari</option>
                <option value="Rented">Rented / Inuupahan</option>
                <option value="Others">Others / Iba pa</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select home ownership." data-tl="Mangyaring pumili ng pagmamay-ari.">
                Please select home ownership.
              </div>
              <div class="other-input" id="home_ownership_other_div">
                <input type="text" class="form-control" id="home_ownership_other" name="home_ownership_other" 
                       placeholder="Please specify">
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Construction Material" data-tl="Materyales ng Konstruksyon">Construction Material</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="construction_material" name="construction_material" required>
                <option value="" selected disabled>Select...</option>
                <option value="Light">Light / Magaan</option>
                <option value="Strong">Strong / Matigas</option>
                <option value="Mixed">Mixed / Halo</option>
                <option value="Others">Others / Iba pa</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select construction material." data-tl="Mangyaring pumili ng materyales.">
                Please select construction material.
              </div>
              <div class="other-input" id="construction_material_other_div">
                <input type="text" class="form-control" id="construction_material_other" name="construction_material_other" 
                       placeholder="Please specify">
              </div>
            </div>
          </div>
        </div>

        <!-- Facilities Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-bolt"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Facilities & Utilities" data-tl="Mga Pasilidad at Utilities">Facilities & Utilities</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Lighting Facility" data-tl="Ilaw">Lighting Facility</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="lighting_facility" name="lighting_facility" required>
                <option value="" selected disabled>Select...</option>
                <option value="Electricity">Electricity / Kuryente</option>
                <option value="Kerosene">Kerosene / Gas</option>
                <option value="Others">Others / Iba pa</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select lighting facility." data-tl="Mangyaring pumili ng ilaw.">
                Please select lighting facility.
              </div>
              <div class="other-input" id="lighting_facility_other_div">
                <input type="text" class="form-control" id="lighting_facility_other" name="lighting_facility_other" 
                       placeholder="Please specify">
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Toilet Type" data-tl="Uri ng Palikuran">Toilet Type</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="toilet_type" name="toilet_type" required>
                <option value="" selected disabled>Select...</option>
                <option value="Sanitary">Sanitary / Malinis</option>
                <option value="Unsanitary">Unsanitary / Hindi malinis</option>
                <option value="None">None / Wala</option>
                <option value="Others">Others / Iba pa</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select toilet type." data-tl="Mangyaring pumili ng uri ng palikuran.">
                Please select toilet type.
              </div>
              <div class="other-input" id="toilet_type_other_div">
                <input type="text" class="form-control" id="toilet_type_other" name="toilet_type_other" 
                       placeholder="Please specify">
              </div>
            </div>
          </div>
        </div>

        <!-- Water Source Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-droplet"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Water Source & Storage" data-tl="Pinagmulan at Imbakan ng Tubig">Water Source & Storage</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Water Level" data-tl="Lebel ng Tubig">Water Level</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="water_level" name="water_level" required>
                <option value="" selected disabled>Select...</option>
                <option value="Level I">Level I</option>
                <option value="Level II">Level II</option>
                <option value="Level III">Level III</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select water level." data-tl="Mangyaring pumili ng lebel ng tubig.">
                Please select water level.
              </div>
            </div>

            <div class="col-md-8">
              <label for="water_source" class="form-label fw-semibold">
                <span class="i18n" data-en="Water Source" data-tl="Pinagmulan ng Tubig">Water Source</span>
              </label>
              <input type="text" class="form-control" id="water_source" name="water_source" 
                     placeholder="e.g., Public tap, Deep well, Spring">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Water Storage" data-tl="Imbakan ng Tubig">Water Storage</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="water_storage" name="water_storage" required>
                <option value="" selected disabled>Select...</option>
                <option value="Covered container">Covered container / May takip</option>
                <option value="Uncovered container">Uncovered container / Walang takip</option>
                <option value="Both">Both / Pareho</option>
                <option value="None">None / Wala</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select water storage." data-tl="Mangyaring pumili ng imbakan.">
                Please select water storage.
              </div>
            </div>

            <div class="col-md-6">
              <label for="drinking_water_other_source" class="form-label fw-semibold">
                <span class="i18n" data-en="Other Drinking Water Source" data-tl="Ibang Pinagmulan ng Inuming Tubig">Other Drinking Water Source</span>
              </label>
              <input type="text" class="form-control" id="drinking_water_other_source" name="drinking_water_other_source" 
                     placeholder="If different from main source">
            </div>
          </div>
        </div>

        <!-- Garbage Disposal Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-trash"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Garbage Disposal" data-tl="Pagtatapon ng Basura">Garbage Disposal</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Garbage Container" data-tl="Lalagyan ng Basura">Garbage Container</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="garbage_container" name="garbage_container" required>
                <option value="" selected disabled>Select...</option>
                <option value="Covered">Covered / May takip</option>
                <option value="Uncovered">Uncovered / Walang takip</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select container type." data-tl="Mangyaring pumili ng uri.">
                Please select container type.
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Garbage Segregated?" data-tl="Hiwalay ang Basura?">Garbage Segregated?</span>
                <span class="text-danger">*</span>
              </label>
              <div class="d-flex gap-3 pt-2">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="garbage_segregated" id="garbage_segregated_yes" value="1" required>
                  <label class="form-check-label i18n" for="garbage_segregated_yes" data-en="Yes" data-tl="Oo">Yes</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="garbage_segregated" id="garbage_segregated_no" value="0">
                  <label class="form-check-label i18n" for="garbage_segregated_no" data-en="No" data-tl="Hindi">No</label>
                </div>
              </div>
              <div class="invalid-feedback i18n" data-en="Please answer this question." data-tl="Mangyaring sagutin.">
                Please answer this question.
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Disposal Method" data-tl="Paraan ng Pagtatapon">Disposal Method</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="garbage_disposal_method" name="garbage_disposal_method" required>
                <option value="" selected disabled>Select...</option>
                <option value="Garbage Collection">Garbage Collection</option>
                <option value="Composting">Composting</option>
                <option value="Burial Pit">Burial Pit</option>
                <option value="Open Burning">Open Burning</option>
                <option value="Hog Feeding">Hog Feeding</option>
                <option value="Open Dumping">Open Dumping</option>
                <option value="Sanitary">Sanitary</option>
                <option value="Unsanitary">Unsanitary</option>
                <option value="Others">Others / Iba pa</option>
                <option value="None">None / Wala</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select disposal method." data-tl="Mangyaring pumili ng paraan.">
                Please select disposal method.
              </div>
              <div class="other-input" id="garbage_disposal_other_div">
                <input type="text" class="form-control" id="garbage_disposal_other" name="garbage_disposal_other" 
                       placeholder="Please specify">
              </div>
            </div>
          </div>
        </div>

        <!-- Family Information Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-people-roof"></i></div>
            <div>
              <h5 class="section-title mb-1"><span class="i18n" data-en="Family Information" data-tl="Impormasyon ng Pamilya">Family Information</span></h5>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="family_number" class="form-label fw-semibold">
                <span class="i18n" data-en="Family Number" data-tl="Numero ng Pamilya">Family Number</span>
              </label>
              <input type="text" class="form-control" id="family_number" name="family_number" 
                     placeholder="Family number">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">
                <span class="i18n" data-en="Residency Status" data-tl="Katayuan ng Paninirahan">Residency Status</span>
                <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="residency_status" name="residency_status" required>
                <option value="" selected disabled>Select...</option>
                <option value="Permanent">Permanent / Permanente</option>
                <option value="Temporary">Temporary / Pansamantala</option>
              </select>
              <div class="invalid-feedback i18n" data-en="Please select residency status." data-tl="Mangyaring pumili ng katayuan.">
                Please select residency status.
              </div>
            </div>

            <div class="col-md-6">
              <label for="length_of_residency_months" class="form-label fw-semibold">
                <span class="i18n" data-en="Length of Residency (months)" data-tl="Tagal ng Paninirahan (buwan)">Length of Residency (months)</span>
              </label>
              <input type="number" class="form-control" id="length_of_residency_months" name="length_of_residency_months" 
                     min="0" placeholder="Number of months">
            </div>

            <div class="col-md-6">
              <label for="email" class="form-label fw-semibold">
                <span class="i18n" data-en="Email Address" data-tl="Email Address">Email Address</span>
              </label>
              <input type="email" class="form-control" id="email" name="email" 
                     placeholder="email@example.com">
            </div>
          </div>
        </div>

        <!-- Sticky Actions -->
        <div class="sticky-actions">
          <div class="actions-inner">
            <div>
              <span class="text-muted small i18n" data-en="Step 8 of 8" data-tl="Hakbang 8 ng 8">Step 8 of 8</span>
            </div>
            <div class="d-flex gap-2">
              <a href="wizard_diabetes.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>
                <span class="i18n" data-en="Back" data-tl="Bumalik">Back</span>
              </a>
              <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-check me-2"></i>
                <span class="i18n" data-en="Complete Survey" data-tl="Kumpletuhin ang Survey">Complete Survey</span>
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
  <script src="../../assets/js/Survey/wizard_household.js"></script>

</body>
</html>

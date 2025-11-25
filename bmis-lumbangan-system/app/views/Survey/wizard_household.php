<?php
// app/views/Survey/wizard_household.php
// Require user authentication and helpers
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();

$surveyData = loadSurveyData();

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

        <a class="wizard-step active" href="" data-key="household">
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

        <?php if (!($surveyData['is_family_head'] ?? true) && !empty($surveyData['household']['head_address_full'])) : ?>
          <div class="alert alert-secondary mt-3" role="alert">
            <div class="form-check form-switch d-flex align-items-center gap-3">
              <input class="form-check-input" type="checkbox" role="switch" id="useHeadAddressToggle" checked>
              <label class="form-check-label" for="useHeadAddressToggle">
                <strong class="i18n" data-en="Use my family head's address" data-tl="Gamitin ang address ng ulo ng aking pamilya">
                  Use my family head's address
                </strong>
                <div class="small text-muted" id="headAddressDisplay">
                  <?php echo htmlspecialchars($surveyData['household']['head_address_full']); ?>
                </div>
              </label>
            </div>
            <p class="small text-muted mt-2 mb-0">
              <span class="i18n" data-en="Toggling this off will allow you to enter a new address and create a new, separate household where you will be the head." data-tl="Ang pag-off nito ay magbibigay-daan sa iyo na maglagay ng bagong address at lumikha ng bago at hiwalay na sambahayan kung saan ikaw ang magiging ulo.">
                Toggling this off will allow you to enter a new address and create a new, separate household where you will be the head.
              </span>
            </p>
          </div>
        <?php endif; ?>

        <div class="row g-3">
          <div class="col-md-6">
            <label for="purok_sitio" class="form-label fw-semibold">
              <span class="i18n" data-en="Purok / Sitio" data-tl="Purok / Sitio">Purok / Sitio</span>
              <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="purok_id" name="purok_id" required>
              <option value="" selected disabled>Select...</option>
              <!-- Use numeric purok IDs as option values for DB compatibility. data-code is used for household_no generation. -->
              <option value="1" data-code="SA" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 1) ? 'selected' : ''; ?>>Sagbat (SA)</option>
              <option value="2" data-code="CA" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 2) ? 'selected' : ''; ?>>Campo Avejar (CA)</option>
              <option value="3" data-code="RV" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 3) ? 'selected' : ''; ?>>Roxas Village (RV)</option>
              <option value="4" data-code="CE" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 4) ? 'selected' : ''; ?>>Central (CE)</option>
              <option value="5" data-code="CC" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 5) ? 'selected' : ''; ?>>Camachilihan (CC)</option>
              <option value="6" data-code="EP" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 6) ? 'selected' : ''; ?>>El Paso (EP)</option>
              <option value="7" data-code="CD" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 7) ? 'selected' : ''; ?>>Calamundingan (CD)</option>
              <option value="8" data-code="RO" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 8) ? 'selected' : ''; ?>>Role (RO)</option>
              <option value="9" data-code="MA" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 9) ? 'selected' : ''; ?>>Mambugan (MA)</option>
              <option value="10" data-code="MN" <?php echo (isset($surveyData['household']) && (int)($surveyData['household']['purok_id'] ?? 0) === 10) ? 'selected' : ''; ?>>Malangaw (MN)</option>
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
              placeholder="Will be generated based on Purok" readonly value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['household_no'] ?? '') : ''; ?>">
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
              placeholder="e.g., 123 or Blk 5 Lot 12" required value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['address_house_no'] ?? '') : ''; ?>">
            <div class="invalid-feedback i18n" data-en="Please provide house number or block & lot." data-tl="Mangyaring magbigay ng house number o block & lot.">
              Please provide house number or block & lot.
            </div>
          </div>

          <div class="col-md-6">
            <label for="address_street" class="form-label fw-semibold">
              <span class="i18n" data-en="Street Name" data-tl="Pangalan ng Kalye">Street Name</span>
            </label>
            <input type="text" class="form-control" id="address_street" name="address_street" 
              placeholder="Street name" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['address_street'] ?? '') : ''; ?>">
          </div>

          <div class="col-md-6">
            <label for="address_sitio_subdivision" class="form-label fw-semibold">
              <span class="i18n" data-en="Subdivision / Compound" data-tl="Subdivision / Compound">Subdivision / Compound</span>
            </label>
            <input type="text" class="form-control" id="address_sitio_subdivision" name="address_sitio_subdivision" 
              placeholder="Subdivision or Compound name" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['address_sitio_subdivision'] ?? '') : ''; ?>">
          </div>

          <div class="col-md-6">
            <label for="address_building" class="form-label fw-semibold">
              <span class="i18n" data-en="Building / Apartment Name (Unit #, if applicable)" data-tl="Pangalan ng Building / Apartment (Unit #, kung mayroon)">Building / Apartment Name (Unit #, if applicable)</span>
            </label>
            <input type="text" class="form-control" id="address_building" name="address_building" 
              placeholder="Building or apartment name with unit number (if applicable)" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['address_building'] ?? '') : ''; ?>" data-optional=true>
          </div>
        </div>
      </div>

      <!-- This hidden input will be managed by the toggle switch script -->
      <input type="hidden" name="use_head_address" id="use_head_address_input" value="1">

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
              <option value="Owned" <?php echo (isset($surveyData['household']) && ($surveyData['household']['home_ownership'] ?? '') === 'Owned') ? 'selected' : ''; ?>>Owned / May-ari</option>
              <option value="Rented" <?php echo (isset($surveyData['household']) && ($surveyData['household']['home_ownership'] ?? '') === 'Rented') ? 'selected' : ''; ?>>Rented / Inuupahan</option>
              <option value="Others" <?php echo (isset($surveyData['household']) && ($surveyData['household']['home_ownership'] ?? '') === 'Others') ? 'selected' : ''; ?>>Others / Iba pa</option>
            </select>
            <div class="invalid-feedback i18n" data-en="Please select home ownership." data-tl="Mangyaring pumili ng pagmamay-ari.">
              Please select home ownership.
            </div>
            <div class="other-input" id="home_ownership_other_div" style="display: none;">
              <input type="text" class="form-control" id="home_ownership_other" name="home_ownership_other" 
                     placeholder="Please specify" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['home_ownership_other'] ?? '') : ''; ?>">
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Construction Material" data-tl="Materyales ng Konstruksyon">Construction Material</span>
              <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="construction_material" name="construction_material" required>
              <option value="" selected disabled>Select...</option>
              <option value="Light" <?php echo (isset($surveyData['household']) && ($surveyData['household']['construction_material'] ?? '') === 'Light') ? 'selected' : ''; ?>>Light / Magaan</option>
              <option value="Strong" <?php echo (isset($surveyData['household']) && ($surveyData['household']['construction_material'] ?? '') === 'Strong') ? 'selected' : ''; ?>>Strong / Matigas</option>
              <option value="Mixed" <?php echo (isset($surveyData['household']) && ($surveyData['household']['construction_material'] ?? '') === 'Mixed') ? 'selected' : ''; ?>>Mixed / Halo</option>
              <option value="Others" <?php echo (isset($surveyData['household']) && ($surveyData['household']['construction_material'] ?? '') === 'Others') ? 'selected' : ''; ?>>Others / Iba pa</option>
            </select>
            <div class="invalid-feedback i18n" data-en="Please select construction material." data-tl="Mangyaring pumili ng materyales.">
              Please select construction material.
            </div>
            <div class="other-input" id="construction_material_other_div" style="display: none;">
              <input type="text" class="form-control" id="construction_material_other" name="construction_material_other" 
                     placeholder="Please specify" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['construction_material_other'] ?? '') : ''; ?>">
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
              <option value="Electricity" <?php echo (isset($surveyData['household']) && ($surveyData['household']['lighting_facility'] ?? '') === 'Electricity') ? 'selected' : ''; ?>>Electricity / Kuryente</option>
              <option value="Kerosene" <?php echo (isset($surveyData['household']) && ($surveyData['household']['lighting_facility'] ?? '') === 'Kerosene') ? 'selected' : ''; ?>>Kerosene / Gas</option>
              <option value="Others" <?php echo (isset($surveyData['household']) && ($surveyData['household']['lighting_facility'] ?? '') === 'Others') ? 'selected' : ''; ?>>Others / Iba pa</option>
            </select>
            <div class="invalid-feedback i18n" data-en="Please select lighting facility." data-tl="Mangyaring pumili ng ilaw.">
              Please select lighting facility.
            </div>
            <div class="other-input" id="lighting_facility_other_div" style="display: none;">
              <input type="text" class="form-control" id="lighting_facility_other" name="lighting_facility_other" 
                     placeholder="Please specify" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['lighting_facility_other'] ?? '') : ''; ?>">
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Toilet Type" data-tl="Uri ng Palikuran">Toilet Type</span>
              <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="toilet_type" name="toilet_type" required>
              <option value="" selected disabled>Select...</option>
              <option value="Sanitary" <?php echo (isset($surveyData['household']) && ($surveyData['household']['toilet_type'] ?? '') === 'Sanitary') ? 'selected' : ''; ?>>Sanitary / Malinis</option>
              <option value="Unsanitary" <?php echo (isset($surveyData['household']) && ($surveyData['household']['toilet_type'] ?? '') === 'Unsanitary') ? 'selected' : ''; ?>>Unsanitary / Hindi malinis</option>
              <option value="None" <?php echo (isset($surveyData['household']) && ($surveyData['household']['toilet_type'] ?? '') === 'None') ? 'selected' : ''; ?>>None / Wala</option>
              <option value="Others" <?php echo (isset($surveyData['household']) && ($surveyData['household']['toilet_type'] ?? '') === 'Others') ? 'selected' : ''; ?>>Others / Iba pa</option>
            </select>
            <div class="invalid-feedback i18n" data-en="Please select toilet type." data-tl="Mangyaring pumili ng uri ng palikuran.">
              Please select toilet type.
            </div>
            <div class="other-input" id="toilet_type_other_div" style="display: none;">
              <input type="text" class="form-control" id="toilet_type_other" name="toilet_type_other" 
                     placeholder="Please specify" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['toilet_type_other'] ?? '') : ''; ?>">
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
              <option value="Level I" class="i18n" data-en="Level I — Point/household source (e.g., private tap, hand pump)" data-tl="Antas I — Pinagmulan sa bahay (hal., pribadong gripo, hand pump)" <?php echo (isset($surveyData['household']) && ($surveyData['household']['water_level'] ?? '') === 'Level I') ? 'selected' : ''; ?>>Level I — Point/household source (e.g., private tap, hand pump)</option>
              <option value="Level II" class="i18n" data-en="Level II — Shared/communal source (e.g., community faucet/standpipe)" data-tl="Antas II — Pinagmulan na pinaghahatian (hal., pampublikong gripo/standpipe)" <?php echo (isset($surveyData['household']) && ($surveyData['household']['water_level'] ?? '') === 'Level II') ? 'selected' : ''; ?>>Level II — Shared/communal source (e.g., community faucet/standpipe)</option>
              <option value="Level III" class="i18n" data-en="Level III — Piped network / municipal supply (household connection)" data-tl="Antas III — Piped network / suplay mula sa munisipyo (koneksyon sa bahay)" <?php echo (isset($surveyData['household']) && ($surveyData['household']['water_level'] ?? '') === 'Level III') ? 'selected' : ''; ?>>Level III — Piped network / municipal supply (household connection)</option>
            </select>
            <div class="invalid-feedback i18n" data-en="Please select water level." data-tl="Mangyaring pumili ng lebel ng tubig.">
              Please select water level.
            </div>
          </div>

          <div class="col-md-8">
            <label for="water_source" class="form-label fw-semibold">
              <span class="i18n" data-en="Water Source" data-tl="Pinagmulan ng Tubig">Water Source</span>
            </label>
            <!-- Marked data-optional so progress tracker will ignore this optional field -->
            <input type="text" class="form-control" id="water_source" name="water_source" data-optional
              placeholder="e.g., Public tap, Deep well, Spring" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['water_source'] ?? '') : ''; ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Water Storage" data-tl="Imbakan ng Tubig">Water Storage</span>
              <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="water_storage" name="water_storage" required>
              <option value="" selected disabled>Select...</option>
              <option value="Covered container" <?php echo (isset($surveyData['household']) && ($surveyData['household']['water_storage'] ?? '') === 'Covered container') ? 'selected' : ''; ?>>Covered container / May takip</option>
              <option value="Uncovered container" <?php echo (isset($surveyData['household']) && ($surveyData['household']['water_storage'] ?? '') === 'Uncovered container') ? 'selected' : ''; ?>>Uncovered container / Walang takip</option>
              <option value="Both" <?php echo (isset($surveyData['household']) && ($surveyData['household']['water_storage'] ?? '') === 'Both') ? 'selected' : ''; ?>>Both / Pareho</option>
              <option value="None" <?php echo (isset($surveyData['household']) && ($surveyData['household']['water_storage'] ?? '') === 'None') ? 'selected' : ''; ?>>None / Wala</option>
            </select>
            <div class="invalid-feedback i18n" data-en="Please select water storage." data-tl="Mangyaring pumili ng imbakan.">
              Please select water storage.
            </div>
          </div>

          <div class="col-md-8">
            <label for="drinking_water_other_source" class="form-label fw-semibold">
              <span class="i18n" data-en="Other Drinking Water Source" data-tl="Ibang Pinagmulan ng Inuming Tubig">Other Drinking Water Source</span>
            </label>
            <!-- Marked data-optional so progress tracker will ignore this optional field -->
            <input type="text" class="form-control" id="drinking_water_other_source" name="drinking_water_other_source" data-optional
                   placeholder="If different from main source" value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['drinking_water_other_source'] ?? '') : ''; ?>">
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
              <option value="Covered" <?php echo (isset($surveyData['household']) && ($surveyData['household']['garbage_container'] ?? '') === 'Covered') ? 'selected' : ''; ?>>Covered / May takip</option>
              <option value="Uncovered" <?php echo (isset($surveyData['household']) && ($surveyData['household']['garbage_container'] ?? '') === 'Uncovered') ? 'selected' : ''; ?>>Uncovered / Walang takip</option>
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
                <input class="form-check-input" type="radio" name="garbage_segregated" id="garbage_segregated_yes" value="1" required <?php echo (isset($surveyData['household']) && ($surveyData['household']['garbage_segregated'] ?? '') == 1) ? 'checked' : ''; ?>>
                <label class="form-check-label i18n" for="garbage_segregated_yes" data-en="Yes" data-tl="Oo">Yes</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="garbage_segregated" id="garbage_segregated_no" value="0" <?php echo (isset($surveyData['household']) && ($surveyData['household']['garbage_segregated'] ?? '') == 0 && ($surveyData['household']['garbage_segregated'] !== null)) ? 'checked' : ''; ?>>
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
              <?php $g = $surveyData['household']['garbage_disposal_method'] ?? null; ?>
              <option value="Garbage Collection" <?php echo $g === 'Garbage Collection' ? 'selected' : ''; ?>>Garbage Collection</option>
              <option value="Composting" <?php echo $g === 'Composting' ? 'selected' : ''; ?>>Composting</option>
              <option value="Burial Pit" <?php echo $g === 'Burial Pit' ? 'selected' : ''; ?>>Burial Pit</option>
              <option value="Open Burning" <?php echo $g === 'Open Burning' ? 'selected' : ''; ?>>Open Burning</option>
              <option value="Hog Feeding" <?php echo $g === 'Hog Feeding' ? 'selected' : ''; ?>>Hog Feeding</option>
              <option value="Open Dumping" <?php echo $g === 'Open Dumping' ? 'selected' : ''; ?>>Open Dumping</option>
              <option value="Sanitary" <?php echo $g === 'Sanitary' ? 'selected' : ''; ?>>Sanitary</option>
              <option value="Unsanitary" <?php echo $g === 'Unsanitary' ? 'selected' : ''; ?>>Unsanitary</option>
              <option value="Others" <?php echo $g === 'Others' ? 'selected' : ''; ?>>Others / Iba pa</option>
              <option value="None" <?php echo $g === 'None' ? 'selected' : ''; ?>>None / Wala</option>
            </select>
            <div class="invalid-feedback i18n" data-en="Please select disposal method." data-tl="Mangyaring pumili ng paraan.">
              Please select disposal method.
            </div>
            <div class="other-input" id="garbage_disposal_other_div" style="display: none;">
              <input type="text" class="form-control" id="garbage_disposal_other" name="garbage_disposal_other" 
                placeholder="Please specify" data-optional value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['garbage_disposal_other'] ?? '') : ''; ?>">
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
            <input type="text" class="form-control bg-light" id="family_number" name="family_number" 
              placeholder="Auto-populated" readonly value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['family_number'] ?? '') : ''; ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <span class="i18n" data-en="Residency Status" data-tl="Katayuan ng Paninirahan">Residency Status</span>
              <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="residency_status" name="residency_status" required>
              <option value="" selected disabled>Select...</option>
              <option value="Permanent" <?php echo (isset($surveyData['household']) && ($surveyData['household']['residency_status'] ?? '') === 'Permanent') ? 'selected' : ''; ?>>Permanent / Permanente</option>
              <option value="Temporary" <?php echo (isset($surveyData['household']) && ($surveyData['household']['residency_status'] ?? '') === 'Temporary') ? 'selected' : ''; ?>>Temporary / Pansamantala</option>
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
              min="0" placeholder="Number of months" value="<?php echo isset($surveyData['household']) ? (int)($surveyData['household']['length_of_residency_months'] ?? 0) : ''; ?>">
          </div>

          <div class="col-md-6">
            <label for="email" class="form-label fw-semibold">
              <span class="i18n" data-en="Email Address (from Household Head)" data-tl="Email Address (mula sa Ulo ng Sambahayan)">Email Address (from Household Head)</span>
            </label>
            <input type="email" class="form-control bg-light" id="email" name="email" 
              placeholder="Auto-populated" readonly value="<?php echo isset($surveyData['household']) ? htmlspecialchars($surveyData['household']['email'] ?? '') : ''; ?>">
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
            <a href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_diabetes'); ?>" class="btn btn-outline-secondary">
              <i class="fa-solid fa-arrow-left me-2"></i>
              <span class="i18n" data-en="Back" data-tl="Bumalik">Back</span>
            </a>
            <button type="button" class="btn btn-success" id="btn-complete-survey">
              <i class="fa-solid fa-check me-2"></i>
              <span class="i18n" data-en="Complete Survey" data-tl="Kumpletuhin ang Survey">Complete Survey</span>
            </button>
          </div>
        </div>
      </div>

    </form>

  </div>
</main>

<!-- ================================================== -->
<!--  DEBUG SCRIPT TO LOG SERVER DATA                   -->
<!-- ================================================== -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('%c--- DEBUG: Survey Data from Server ---', 'color: blue; font-weight: bold;');
    const serverData = <?php echo json_encode($surveyData ?? ['error' => 'surveyData variable not set']); ?>;
    console.log('Full $surveyData object:', serverData);
    
    if (serverData && serverData.household && Object.keys(serverData.household).length > 0) {
        console.log('%c--- Household Data Details ---', 'color: green; font-weight: bold;');
        console.table(serverData.household);
    } else {
        console.warn('WARNING: The `household` key in the server data is empty or missing. This indicates a problem with the database query in survey_data_helper.php.');
    }
    console.log('%c------------------------------------', 'color: blue; font-weight: bold;');
  });
</script>

<!-- ================================================== -->
<!--  DEBUG SCRIPT TO CHECK GARBAGE DISPOSAL FIELDS     -->
<!-- ================================================== -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('%c--- DEBUG: Garbage Disposal Fields Check ---', 'color: orange; font-weight: bold;');
    
    // 1. Check Garbage Container (select)
    const containerEl = document.getElementById('garbage_container');
    console.log('Garbage Container value:', containerEl ? `'${containerEl.value}'` : 'Element not found');

    // 2. Check Garbage Segregated (radio)
    const segregatedEl = document.querySelector('input[name="garbage_segregated"]:checked');
    console.log('Garbage Segregated value:', segregatedEl ? `'${segregatedEl.value}'` : 'No option selected');

    // 3. Check Disposal Method (select)
    const methodEl = document.getElementById('garbage_disposal_method');
    console.log('Disposal Method value:', methodEl ? `'${methodEl.value}'` : 'Element not found');
  });
</script>

<!-- Address Toggle Switch Handler -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const toggle = document.getElementById('useHeadAddressToggle');
  if (!toggle) {
    // If toggle doesn't exist, ensure the hidden input is set correctly for heads/new families.
    const useHeadAddressInput = document.getElementById('use_head_address_input');
    if (useHeadAddressInput) useHeadAddressInput.value = '1'; // Default behavior
    return;
  }

  const headAddress = <?php echo json_encode($surveyData['household']['head_address_full'] ?? null); ?>;
  const headPurokId = <?php echo json_encode($surveyData['household']['purok_id'] ?? null); ?>;
  const useHeadAddressInput = document.getElementById('use_head_address_input');

  const addressFields = [
    document.getElementById('address_house_no'),
    document.getElementById('address_street'),
    document.getElementById('address_sitio_subdivision'),
    document.getElementById('address_building')
  ];
  const purokSelect = document.getElementById('purok_id');
  const allAddressControls = [...addressFields, purokSelect];

  function applyHeadAddress(use) {
    if (use) {
      // Use head's address: fill and disable fields
      const parts = headAddress.split(',').map(p => p.trim());
      const [houseNo, street, subdivision] = parts;

      if (purokSelect) purokSelect.value = headPurokId || '';
      if (document.getElementById('address_house_no')) document.getElementById('address_house_no').value = houseNo || '';
      if (document.getElementById('address_street')) document.getElementById('address_street').value = street || '';
      if (document.getElementById('address_sitio_subdivision')) document.getElementById('address_sitio_subdivision').value = subdivision || '';
      // The 4th part of address is building, which we don't have a separate field for in the head's address string. Clear it.
      if (document.getElementById('address_building')) document.getElementById('address_building').value = '';

      if (headPurokId) {
        // Use a timeout to ensure the value is set before dispatching the change event
        setTimeout(() => purokSelect.dispatchEvent(new Event('change')), 50);
      }

      allAddressControls.forEach(field => field && (field.readOnly = true));
      // Selects need to be disabled, not just readonly
      if (purokSelect) purokSelect.disabled = true;

      useHeadAddressInput.value = '1';
    } else {
      // Use new address: clear and enable fields
      allAddressControls.forEach(field => {
        if (field) {
          field.value = '';
          field.readOnly = false;
          if (field.tagName === 'SELECT') field.disabled = false;
        }
      });
      document.getElementById('household_no').value = ''; // Also clear auto-generated field
      useHeadAddressInput.value = '0';
    }
  }

  // If user manually changes a readonly field, it means they want a new address.
  // Automatically toggle "Use head's address" to OFF.
  function handleManualAddressChange() {
    if (toggle.checked) {
      toggle.checked = false;
      applyHeadAddress(false);
      // Trigger purok change handler to generate a new household number for this new address
      try { if (purokSelect) purokSelect.dispatchEvent(new Event('change')); } catch(e) {}
    }
  }

  allAddressControls.forEach(field => {
    if (field) field.addEventListener('input', handleManualAddressChange);
  });

  // Set initial state on page load
  applyHeadAddress(toggle.checked);

  // Add event listener for changes
  toggle.addEventListener('change', () => applyHeadAddress(toggle.checked));
});
</script>

<!-- Save & Finish handler (AJAX) -->
<script>
(function(){
  var btn = document.getElementById('btn-complete-survey');
  var form = document.getElementById('form-household');
  if (!btn || !form) return;

  // Define lang in this scope and keep it updated
  let lang = localStorage.getItem('survey_language') || 'en';
  document.querySelectorAll('input[name="lang"]').forEach(radio => {
    radio.addEventListener('change', function() {
      lang = this.id === 'lang-tl' ? 'tl' : 'en';
      localStorage.setItem('survey_language', lang);
    });
  });

  // Prevent native form submission
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    ev.stopImmediatePropagation();
    return false;
  }, { capture: true });

  var endpoint = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?action=save_household'); ?>;
  var successUrl  = <?php echo json_encode(rtrim(BASE_PUBLIC, '/') . '/index.php?page=dashboard_resident'); ?>;

  function getToastContainer() {
    var id = 'survey_toast_container';
    var c = document.getElementById(id);
    if (c) return c;
    c = document.createElement('div'); c.id = id;
    Object.assign(c.style, { position: 'fixed', top: '72px', left: '50%', transform: 'translateX(-50%)', zIndex: 20000, pointerEvents: 'none', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '8px', width: '100%', boxSizing: 'border-box', padding: '0 12px' });
    document.body.appendChild(c);
    return c;
  }

  function showToast(type, message, timeoutMs = 3500) {
    var container = getToastContainer();
    var toast = document.createElement('div');
    toast.className = 'alert alert-' + (type || 'info');
    Object.assign(toast.style, { maxWidth: '300px', width: '100%', boxSizing: 'border-box', pointerEvents: 'auto', margin: '0 auto', borderRadius: '8px', boxShadow: '0 6px 20px rgba(0,0,0,0.08)', padding: '10px 18px', textAlign: 'center', opacity: '0', transition: 'opacity 160ms ease, transform 160ms ease', transform: 'translateY(-6px)' });
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
    const completingText = lang === 'tl' ? 'Kinukumpleto...' : 'Completing...';
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ${completingText}`;
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
      if (firstInvalid) {
          firstInvalid.focus();
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      showToast('warning', 'Please fill out all required fields.', 2000);
      return;
    }

    startSpinner();
    var fd = new FormData(form);

    try {
      var resp = await fetch(endpoint, { method: 'POST', body: fd });
      var json = await resp.json();

      if (resp.ok && json.success) {
        showToast('success', json.message || 'Survey completed!', 2000);
        // Clear local storage drafts upon successful completion
        if (window.SurveyPersistence && typeof window.SurveyPersistence.clearAll === 'function') {
            window.SurveyPersistence.clearAll();
        }
        setTimeout(() => { window.location.href = successUrl; }, 1200);
      } else {
        showToast('danger', json.message || 'Could not save data. Please try again.', 4000);
        stopSpinner(); // Re-enable button only on failure
      }
    } catch (err) {
      showToast('danger', 'A network error occurred. Please check your connection and try again.', 4000);
      stopSpinner(); // Re-enable button only on failure
    }
    // The spinner is NOT stopped on success, as the page will redirect.
    // This prevents the button from being clickable again.
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
<div id="bhwFloatBtn" role="button" aria-label="Survey info" title="Survey info">
  <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
  <button id="bhwFloatHide" class="bhw-hide-btn" aria-label="Hide info">×</button>
</div>

<?php
// Include the resident footer which closes the body/html
include_once __DIR__ . '/../../components/resident_components/footer-resident.php';
?>
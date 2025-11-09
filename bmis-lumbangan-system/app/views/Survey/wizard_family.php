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
  <title>Survey Wizard — Family</title>

  <!-- Vendor CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Survey CSS - Single Consolidated File -->
  <link rel="stylesheet" href="../../assets/css/Survey/wizard_personal.css">

  <!-- Page tokens + tiny utilities -->
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

    /* Family member card styling */
    .family-member-card {
      position: relative;
      padding: 1.25rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: #f8fafc;
    }
    .family-member-card:hover {
      border-color: #1e3a5f;
      background: #fff;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(30, 58, 95, 0.15);
    }
    .member-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1e3a5f, #4f76e2);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 1.2rem;
    }
    .relationship-badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
    }
    .search-box {
      position: relative;
    }
    .search-results {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      max-height: 300px;
      overflow-y: auto;
      background: white;
      border: 2px solid #1e3a5f;
      border-radius: 12px;
      margin-top: 0.5rem;
      box-shadow: 0 4px 20px rgba(30, 58, 95, 0.2);
      z-index: 1000;
      display: none;
    }
    .search-results.show {
      display: block;
    }
    .search-result-item {
      padding: 0.75rem 1rem;
      cursor: pointer;
      border-bottom: 1px solid #e2e8f0;
      transition: all 0.2s;
    }
    .search-result-item:last-child {
      border-bottom: none;
    }
    .search-result-item:hover {
      background: linear-gradient(135deg, rgba(30, 58, 95, 0.05), rgba(79, 118, 226, 0.05));
    }

    /* Family Tree Modal Styles */
    .family-tree-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
      backdrop-filter: blur(5px);
    }
    .family-tree-modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .tree-modal-content {
      background: white;
      border-radius: 20px;
      width: 90%;
      height: 85%;
      max-width: 1400px;
      display: flex;
      flex-direction: column;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      overflow: hidden;
    }
    .tree-modal-header {
      padding: 1.5rem 2rem;
      background: linear-gradient(135deg, #1e3a5f, #4f76e2);
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .tree-modal-body {
      flex: 1;
      overflow: auto;
      padding: 2rem;
      background: #f8fafc;
    }
    .tree-canvas {
      min-width: 100%;
      min-height: 100%;
      position: relative;
    }

    /* Tree node styles */
    .tree-node {
      position: absolute;
      width: 140px;
      padding: 0.75rem;
      background: white;
      border: 2px solid #1e3a5f;
      border-radius: 12px;
      text-align: center;
      cursor: move;
      transition: all 0.3s;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .tree-node:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 20px rgba(30, 58, 95, 0.3);
      z-index: 10;
    }
    .tree-node.current-user {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      border-color: #059669;
      font-weight: 600;
    }
    .tree-node-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1e3a5f, #4f76e2);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      margin: 0 auto 0.5rem;
    }
    .tree-node.current-user .tree-node-avatar {
      background: rgba(255, 255, 255, 0.3);
      color: white;
    }
    .tree-node-name {
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    .tree-node-relation {
      font-size: 0.7rem;
      opacity: 0.8;
    }

    /* SVG connections */
    #tree-svg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 0;
    }
    .tree-connection {
      stroke: #1e3a5f;
      stroke-width: 2;
      fill: none;
      opacity: 0.5;
    }

    /* Tree controls */
    .tree-controls {
      position: absolute;
      bottom: 2rem;
      right: 2rem;
      display: flex;
      gap: 0.5rem;
    }
    .tree-control-btn {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: white;
      border: 2px solid #1e3a5f;
      color: #1e3a5f;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    .tree-control-btn:hover {
      background: #1e3a5f;
      color: white;
      transform: scale(1.1);
    }
  </style>
</head>
<body class="bg-surface">
  <!-- Floating Background Shapes -->
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <!-- Dashboard header -->
  <?php require_once $components . '/headerdashboard.php'; ?>

  <!-- Shrink header on scroll -->
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

          <a class="wizard-step active" href="wizard_family.php" data-key="family">
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
          <strong class="i18n" data-en="Family Relationships" data-tl="Relasyon sa Pamilya">Family Relationships</strong>
          <p class="mb-0 small i18n" 
             data-en="Search and add your family members, then specify your relationship with them. You can also view your family tree."
             data-tl="Maghanap at idagdag ang iyong mga miyembro ng pamilya, pagkatapos ay tukuyin ang iyong relasyon sa kanila. Makikita mo rin ang iyong puno ng pamilya.">
            Search and add your family members, then specify your relationship with them. You can also view your family tree.
          </p>
        </div>
      </div>

      <!-- Form -->
      <form id="form-family" class="needs-validation" novalidate>
        
        <!-- Search & Add Section -->
        <div class="section-card p-4 mb-4">
          <div class="section-head mb-3">
            <div class="section-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            <div class="flex-grow-1">
              <h5 class="section-title mb-1">
                <span class="i18n" data-en="Add Family Member" data-tl="Magdagdag ng Miyembro ng Pamilya">Add Family Member</span>
              </h5>
              <p class="text-muted small mb-0 i18n" 
                 data-en="Search for a person by name to add them to your family"
                 data-tl="Maghanap ng tao gamit ang pangalan upang idagdag sa iyong pamilya">
                Search for a person by name to add them to your family
              </p>
            </div>
            <button type="button" class="btn btn-primary" id="btn-view-tree">
              <i class="fa-solid fa-sitemap me-2"></i>
              <span class="i18n" data-en="View Family Tree" data-tl="Tingnan ang Puno ng Pamilya">View Family Tree</span>
            </button>
          </div>

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">
                <span class="i18n" data-en="Search Person" data-tl="Maghanap ng Tao">Search Person</span>
              </label>
              <div class="search-box">
                <input type="text" 
                       id="search-person" 
                       class="form-control form-control-lg i18n-ph"
                       data-ph-en="Type name to search..."
                       data-ph-tl="I-type ang pangalan..."
                       placeholder="Type name to search..."
                       autocomplete="off">
                <div class="search-results" id="search-results"></div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">
                <span class="i18n" data-en="Relationship" data-tl="Relasyon">Relationship</span>
              </label>
              <select id="relationship-type" class="form-select form-select-lg">
                <option value="" class="i18n" data-en="Select relationship" data-tl="Pumili ng relasyon">Select relationship</option>
                <option value="parent">Parent / Magulang</option>
                <option value="child">Child / Anak</option>
                <option value="spouse">Spouse / Asawa</option>
                <option value="sibling">Sibling / Kapatid</option>
                <option value="grandparent">Grandparent / Lolo/Lola</option>
                <option value="grandchild">Grandchild / Apo</option>
                <option value="guardian">Guardian / Tagapag-alaga</option>
                <option value="ward">Ward / Alaga</option>
                <option value="step_parent">Step Parent</option>
                <option value="step_child">Step Child</option>
                <option value="adoptive_parent">Adoptive Parent</option>
                <option value="adopted_child">Adopted Child</option>
                <option value="other">Other / Iba pa</option>
              </select>
            </div>

            <div class="col-12">
              <button type="button" class="btn btn-success btn-lg" id="btn-add-member" disabled>
                <i class="fa-solid fa-plus me-2"></i>
                <span class="i18n" data-en="Add Family Member" data-tl="Idagdag ang Miyembro">Add Family Member</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Family Members List -->
        <div class="section-card p-4 mb-4">
          <div class="section-head">
            <div class="section-icon"><i class="fa-solid fa-users"></i></div>
            <div>
              <h5 class="section-title mb-1">
                <span class="i18n" data-en="My Family Members" data-tl="Aking Mga Miyembro ng Pamilya">My Family Members</span>
              </h5>
              <p class="text-muted small mb-0">
                <span id="member-count">0</span>
                <span class="i18n" data-en="members added" data-tl="miyembrong naidagdag">members added</span>
              </p>
            </div>
          </div>

          <div id="family-members-list" class="row g-3 mt-2">
            <div class="col-12 text-center text-muted py-5">
              <i class="fa-solid fa-users fs-1 mb-3 d-block" style="opacity: 0.3;"></i>
              <p class="i18n" data-en="No family members added yet. Search and add members above."
                 data-tl="Walang miyembro ng pamilya na naidagdag pa. Maghanap at magdagdag ng mga miyembro sa itaas.">
                No family members added yet. Search and add members above.
              </p>
            </div>
          </div>
        </div>

        <!-- Sticky Actions -->
        <div class="sticky-actions">
          <div class="actions-inner">
            <div>
              <span class="text-muted small i18n" data-en="Step 4 of 8" data-tl="Hakbang 4 ng 8">Step 4 of 8</span>
            </div>
            <div class="d-flex gap-2">
              <a href="wizard_family_history.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>
                <span class="i18n" data-en="Back" data-tl="Bumalik">Back</span>
              </a>
              <button type="button" id="btn-save-continue" class="btn btn-primary">
                <span class="i18n" data-en="Save & Continue" data-tl="I-save at Magpatuloy">Save & Continue</span>
                <i class="fa-solid fa-arrow-right ms-2"></i>
              </button>
            </div>
          </div>
        </div>

      </form>

    </div>
  </main>

  <!-- Family Tree Modal -->
  <div class="family-tree-modal" id="family-tree-modal">
    <div class="tree-modal-content">
      <div class="tree-modal-header">
        <div>
          <h4 class="mb-0">
            <i class="fa-solid fa-sitemap me-2"></i>
            <span class="i18n" data-en="Family Tree" data-tl="Puno ng Pamilya">Family Tree</span>
          </h4>
          <small class="i18n" data-en="Drag nodes to rearrange" data-tl="I-drag ang mga node upang ayusin">Drag nodes to rearrange</small>
        </div>
        <button type="button" class="btn btn-light btn-sm" id="btn-close-tree">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>
      <div class="tree-modal-body" id="tree-modal-body">
        <div class="tree-canvas" id="tree-canvas">
          <svg id="tree-svg"></svg>
          <!-- Tree nodes will be dynamically inserted here -->
        </div>
        <div class="tree-controls">
          <button type="button" class="tree-control-btn" id="btn-tree-zoom-in" title="Zoom In">
            <i class="fa-solid fa-plus"></i>
          </button>
          <button type="button" class="tree-control-btn" id="btn-tree-zoom-out" title="Zoom Out">
            <i class="fa-solid fa-minus"></i>
          </button>
          <button type="button" class="tree-control-btn" id="btn-tree-reset" title="Reset View">
            <i class="fa-solid fa-rotate-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Dashboard footer -->
  <?php require_once $components . '/footerdashboard.php'; ?>

  <!-- Vendor JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JS -->
  <script src="../../assets/js/Survey/wizard_family.js"></script>

</body>
</html>

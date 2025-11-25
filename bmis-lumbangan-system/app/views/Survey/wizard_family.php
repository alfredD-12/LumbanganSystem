<?php
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/helpers/survey_data_helper.php';
requireUser();

$surveyData = loadSurveyData();
$family = $surveyData['family'] ?? [];

// Include resident header
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

        <a class="wizard-step active" href="" data-key="family">
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
          <strong class="i18n" data-en="Family Relationships" data-tl="Relasyon sa Pamilya">Family Relationships</strong>
          <p class="mb-0 small i18n" 
             data-en="Search and add your family members, then specify your relationship with them. You can also view your family tree."
             data-tl="Maghanap at idagdag ang iyong mga miyembro ng pamilya, pagkatapos ay tukuyin ang iyong relasyon sa kanila. Makikita mo rin ang iyong puno ng pamilya.">
            Search and add your family members, then specify your relationship with them. You can also view your family tree.
          </p>
        </div>
      </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <h3 class="mb-1 i18n" data-en="My Family Members" data-tl="Mga Miyembro ng Aking Pamilya">My Family Members</h3>
        <p class="text-muted small mb-0 i18n" data-en="Add and manage members of your family." data-tl="Magdagdag at pamahalaan ang mga miyembro ng iyong pamilya.">Add and manage members of your family.</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" id="btn-view-tree">
          <i class="fa-solid fa-sitemap me-1"></i>
          <span class="i18n" data-en="View Family Tree" data-tl="Tingnan ang Family Tree">View Family Tree</span>
        </button>
        <button class="btn btn-primary" id="btn-open-add-modal">
          <i class="fa-solid fa-user-plus me-1"></i>
          <span class="i18n" data-en="Add Family Member" data-tl="Magdagdag ng Miyembro">Add Family Member</span>
        </button>
      </div>
    </div>

    <div class="card section-card p-4 mb-4">
      <div id="family-members-list" class="family-members-list">
        <?php if (empty($family)): ?>
          <div class="text-muted i18n" data-en="No members added" data-tl="Walang miyembrong naidagdag">No members added</div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($family as $m): ?>
              <?php
                $rel_type = strtolower($m['relationship_type'] ?? '');
                $is_editable = in_array($rel_type, ['spouse', 'child']);
                $displayName = htmlspecialchars(trim(($m['first_name'] ?? '') . ' ' . ($m['middle_name'] ?? '') . ' ' . ($m['last_name'] ?? '')));
                $relationshipLabel = htmlspecialchars(ucfirst($m['relationship_type'] ?? ''));
              ?>
              <div class="col-12 col-md-6 family-member-item" data-id="<?php echo (int)$m['id']; ?>">
                <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                  <div>
                    <strong><?php echo $displayName; ?></strong>
                    <div class="small text-muted"><?php echo $relationshipLabel; ?></div>
                  </div>

                  <!-- Right-side controls: always provide a wrapper so markup stays balanced.
                      Render editable controls only when allowed. -->
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($is_editable): ?>
                      <select class="form-select form-select-sm" data-id="<?php echo (int)$m['id']; ?>" style="max-width: 150px;">
                        <option value="spouse" <?php echo $rel_type === 'spouse' ? 'selected' : ''; ?>>Spouse</option>
                        <option value="child" <?php echo $rel_type === 'child' ? 'selected' : ''; ?>>Child</option>
                      </select>
                      <button class="btn btn-sm btn-outline-danger btn-remove-member" data-id="<?php echo (int)$m['id']; ?>">
                        <span class="i18n" data-en="Remove" data-tl="Alisin">Remove</span>
                      </button>
                    <?php else: ?>
                      <!-- Optional: placeholder to preserve layout for non-editable entries -->
                      <span class="text-muted small">—</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="sticky-actions">
      <div class="actions-inner">
        <div><span class="text-muted small i18n" data-en="Step 4 of 8">Step 4 of 8</span></div>
        <div class="d-flex gap-2">
          <a href="<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_family_history'); ?>" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>
            <span class="i18n" data-en="Back">Back</span>
          </a>
          <button id="btn-save-continue" class="btn btn-primary">
            <span class="i18n" data-en="Save & Continue">Save & Continue</span>
            <i class="fa-solid fa-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Hidden form used for compatibility (not submitted directly by browser) -->
<form id="form-family" method="post" style="display:none;">
  <input type="hidden" name="family_members" value="">
  <input type="hidden" name="person_id" value="<?php echo htmlspecialchars($_SESSION['person_id'] ?? ''); ?>">
</form>

<!-- Add Family Member Modal -->
<div class="modal fade" id="addFamilyModal" tabindex="-1" aria-labelledby="addFamilyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title i18n" id="addFamilyModalLabel" data-en="Add Family Members" data-tl="Magdagdag ng mga Miyembro ng Pamilya"><i class="fa-solid fa-user-plus me-2"></i> Add Family Members</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <!-- Search / Results -->
          <div class="col-12 col-lg-6">
            <label class="form-label i18n" data-en="Search Person" data-tl="Maghanap ng Tao">Search Person</label>
            <input type="search" id="family-search" class="form-control form-control-lg i18n-placeholder" placeholder="Search by name (e.g. Maria Cruz)" data-en-placeholder="Search by name (e.g. Maria Cruz)" data-tl-placeholder="Maghanap gamit ang pangalan (hal. Maria Cruz)">
            <div id="search-results" class="list-group mt-2" style="max-height:320px; overflow:auto;"></div>
            <div id="search-empty" class="text-center text-muted mt-2 d-none i18n" data-en="No results found" data-tl="Walang nahanap na resulta">
              <i class="fa-solid fa-magnifying-glass"></i> No results found
            </div>
          </div>

          <!-- Selected People & Relationship selection -->
          <div class="col-12 col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <label class="form-label mb-0 i18n" data-en="Selected People" data-tl="Mga Napiling Tao">Selected People</label>
              <div class="small text-muted i18n" data-en="Choose relationship per person" data-tl="Piliin ang relasyon bawat tao">Choose relationship per person</div>
            </div>

            <div id="selected-people-list" class="mb-3" style="min-height: 200px; max-height:320px; overflow:auto;"></div>

            <div id="add-member-feedback" class="mt-3 small text-muted"></div>
          </div>
        </div>

        <hr class="my-3">
        
      </div>

      <div class="modal-footer">
        <div class="me-auto" id="add-member-feedback-brief"></div>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <span class="i18n" data-en="Cancel" data-tl="Kanselahin">Cancel</span>
        </button>
        <button id="btn-add-selected-confirm" type="button" class="btn btn-success" disabled>
          <span class="i18n" data-en="Add & Save" data-tl="Idagdag at I-save">Add & Save</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Family Tree Modal (used by tree-enhance.js) -->
<div class="modal fade family-tree-modal" id="family-tree-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title i18n" data-en="Family Tree" data-tl="Puno ng Pamilya"><i class="fa-solid fa-sitemap me-2"></i> Family Tree</h5>
        <button type="button" class="btn-close" id="btn-close-tree" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body tree-modal-body" style="position:relative; height: 70vh; aspect-ratio: 1 / 1; max-height: 80vw;">

        <!-- SVG area enhanced by tree-enhance.js -->
        <svg id="tree-svg" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Family tree visualization" style="width:100%; height:100%;">
          <text class="placeholder i18n" x="50%" y="50%" text-anchor="middle" fill="#94a3b8" data-en="Family tree will render here" data-tl="Ang family tree ay ipapakita dito">Family tree will render here</text>
        </svg>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <span class="i18n" data-en="Close" data-tl="Isara">Close</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>

  // Client config

  window.CURRENT_PERSON_ID = <?php echo json_encode($_SESSION['person_id'] ?? null); ?>;

  window.CURRENT_PERSON_NAME = <?php echo json_encode(trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['middle_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''))); ?>;

  window.BASE_URL = '<?php echo rtrim(BASE_URL, "/"); ?>';

  window.SURVEY_API = window.BASE_URL + '/controllers/SurveyController.php';

</script>

<script>
(function(){
  'use strict';

  // Use window.load so this runs after footer scripts (bootstrap + wizard_family.js) execute
  function initAfterAllLoaded() {

    // Helper: top-center toast (same look as other pages)
    function getToastContainer() {
      var id = 'survey_toast_container';
      var c = document.getElementById(id);
      if (c) return c;
      c = document.createElement('div');
      c.id = id;
      c.style.position = 'fixed';
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
    function showToast(type, message, timeoutMs) {
      timeoutMs = typeof timeoutMs === 'number' ? timeoutMs : 2500;
      var container = getToastContainer();
      var toast = document.createElement('div');
      toast.className = 'alert alert-' + (type || 'info') + ' survey-toast';
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
      requestAnimationFrame(function () {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
      });
      setTimeout(function () {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-6px)';
        setTimeout(function () { try { toast.remove(); } catch (e) { } }, 200);
      }, timeoutMs);
      return toast;
    }

    // Modal show/hide helpers (prefer bootstrap; fallback accessible)
    function showModalById(id) {
      var modal = document.getElementById(id);
      if (!modal) return;
      if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function') {
        try { bootstrap.Modal.getOrCreateInstance(modal).show(); return; } catch(e) {}
      }
      // fallback
      if (!document.querySelector('.wf-fallback-backdrop')) {
        var bd = document.createElement('div');
        bd.className = 'modal-backdrop fade show wf-fallback-backdrop';
        bd.style.zIndex = 1050;
        document.body.appendChild(bd);
        bd.addEventListener('click', function(){ hideModalById(id); });
      }
      try { document.querySelector('main')?.setAttribute('inert',''); } catch(e){}
      modal.classList.add('show');
      modal.style.display = 'block';
      modal.setAttribute('aria-modal','true');
      modal.removeAttribute('aria-hidden');
      setTimeout(function(){ try{ modal.querySelector('.btn-close')?.focus(); } catch(e){} }, 10);
    }
    function hideModalById(id) {
      var modal = document.getElementById(id);
      if (!modal) return;
      if (window.bootstrap && bootstrap.Modal && typeof bootstrap.Modal.getOrCreateInstance === 'function') {
        try { bootstrap.Modal.getOrCreateInstance(modal).hide(); return; } catch(e) {}
      }
      modal.classList.remove('show');
      modal.style.display = 'none';
      modal.setAttribute('aria-hidden','true');
      try { document.querySelector('main')?.removeAttribute('inert'); } catch(e){}
      var bd = document.querySelector('.wf-fallback-backdrop'); if (bd) bd.remove();
    }

    // Save & Continue: attempt to use existing page mechanisms; fallback to save_family AJAX
    var saveBtn = document.getElementById('btn-save-continue');
    if (saveBtn && !saveBtn.__family_safe_bound) {
      saveBtn.addEventListener('click', function(ev){
        // Prevent other global handlers from swallowing the click
        try { ev.preventDefault(); ev.stopImmediatePropagation(); } catch(e){}
        
        var origHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        // Use the save function exposed by wizard_family.js
        if (typeof window.wizardFamilySave === 'function') {
          window.wizardFamilySave().then(function(success) {
            if (success) {
              showToast('success', 'Saved Successfully', 1400);
              setTimeout(function() {
                window.location.href = '<?php echo h(BASE_PUBLIC . 'index.php?page=survey_wizard_lifestyle'); ?>';
              }, 700);
            } else {
              showToast('danger', 'Failed to save family members. Please try again.', 4000);
              saveBtn.disabled = false;
              saveBtn.innerHTML = origHtml;
            }
          }).catch(function(err) {
            showToast('danger', 'An error occurred while saving.', 4000);
            saveBtn.disabled = false;
            saveBtn.innerHTML = origHtml;
          });
        } else {
          showToast('danger', 'Save function not available. Please refresh.', 4000);
          saveBtn.disabled = false;
          saveBtn.innerHTML = origHtml;
        }

      }, { capture: true, passive: false });

      saveBtn.__family_safe_bound = true;
    }

    // Bind Add Member opener
    var addBtn = document.getElementById('btn-open-add-modal');
    if (addBtn && !addBtn.__family_safe_bound) {
      addBtn.addEventListener('click', function(ev){
        try { ev.preventDefault(); ev.stopImmediatePropagation(); } catch(e){}
        // let wizard_family.js populate selections if it has bound handlers; ensure modal is visible
        showModalById('addFamilyModal');
      }, { capture: true });
      addBtn.__family_safe_bound = true;
    }

    // Bind View Family Tree opener
    var treeBtn = document.getElementById('btn-view-tree');
    if (treeBtn && !treeBtn.__family_safe_bound) {
      treeBtn.addEventListener('click', function(ev){
        try { ev.preventDefault(); ev.stopImmediatePropagation(); } catch(e){}
        showModalById('family-tree-modal');
      }, { capture: true });
      treeBtn.__family_safe_bound = true;
    }

    // Defensive: prevent native form navigation if something else triggers submit on hidden form prematurely
    var hiddenForm = document.getElementById('form-family');
    if (hiddenForm && !hiddenForm.__family_prevent_native) {
      hiddenForm.addEventListener('submit', function(ev){
        // allow handlers to run, but prevent default navigation
        try { ev.preventDefault(); ev.stopImmediatePropagation(); } catch(e){}
        return false;
      }, { capture: true });
      hiddenForm.__family_prevent_native = true;
    }

    // Done
    // console.debug('wizard_family: safe handlers bound');
  }

  if (document.readyState === 'complete') {
    // page already fully loaded
    initAfterAllLoaded();
  } else {
    window.addEventListener('load', initAfterAllLoaded, { once: true });
  }
})();
</script>

<?php
include_once __DIR__ . '/../../components/resident_components/footer-resident.php';
?>
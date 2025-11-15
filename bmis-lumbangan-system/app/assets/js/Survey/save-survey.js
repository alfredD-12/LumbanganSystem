(function(){
  function createAlert(message, type='success'){
    // Single centered alert used across all survey pages when save completes.
    const existing = document.getElementById('surveyAlert');
    if (existing) existing.remove();
    const div = document.createElement('div');
    div.id = 'surveyAlert';
  // Place the alert at the top-middle of the viewport (canonical survey save toast)
  div.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 shadow-lg`;
  div.style.cssText = 'z-index:99999; min-width:320px; max-width:80%; text-align:left;';
    div.innerHTML = `
      <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-check-circle"></i>
        <div style="flex:1">${message}</div>
        <button type="button" class="btn-close" aria-label="Close" onclick="document.getElementById('surveyAlert')?.remove()"></button>
      </div>
    `;
    document.body.appendChild(div);
    // Auto-dismiss after 2.8s
    setTimeout(()=>{ div.classList.remove('show'); setTimeout(()=> div.remove(), 300); }, 2800);
  }

  // Expose a global helper so page-level scripts can reuse the canonical alert
  // rather than creating their own duplicate notifications.
  window.surveyCreateAlert = createAlert;

  // If any page enqueued alerts before this script loaded, flush them now
  try {
    if (Array.isArray(window._pendingSurveyAlerts) && window._pendingSurveyAlerts.length) {
      window._pendingSurveyAlerts.forEach(a => {
        try { createAlert(a.message, a.type || 'success'); } catch (e) {}
      });
      // Clear the queue after flushing
      window._pendingSurveyAlerts = [];
    }
  } catch (e) { /* ignore */ }

  async function postAction(action, form){
    const url = '../../controllers/SurveyController.php?action='+encodeURIComponent(action);
    const fd = new FormData(form);
    try {
      const res = await fetch(url, { method: 'POST', body: fd });
      const js = await res.json();
      return js;
    } catch (e){
      return { success:false, message: e.message || 'Network error' };
    }
  }

  const nextMap = {
    'form-person':'wizard_vitals.php',
    'form-vitals':'wizard_family_history.php',
    'form-family-history':'wizard_family.php',
    'form-family':'wizard_lifestyle.php',
    'form-lifestyle':'wizard_angina.php',
    'form-angina':'wizard_diabetes.php',
    'form-diabetes':'wizard_household.php',
    'form-household':'../Dashboard/dashboard.php'
  };

  Object.keys(nextMap).forEach(fid => {
    const form = document.getElementById(fid);
    if (!form) return;
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      // Client-side validation: prevent POST when form invalid and show a warning
      if (!form.checkValidity()){
        try { form.classList.add('was-validated'); } catch(e){}
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid){
          firstInvalid.focus();
          try { firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch(e){}
        }
        // show a single canonical warning
        if (window.surveyCreateAlert) window.surveyCreateAlert('Please fix highlighted fields', 'warning');
        return;
      }
      const btn = form.querySelector('button[type="submit"]');
      const orig = btn ? btn.innerHTML : null;
      if (btn){ btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; }

      // Determine action from form id
      // Map form ids to controller action names (controller uses some different names)
      const explicitMap = {
        'form-person': 'save_personal',
        'form-vitals': 'save_vitals',
        'form-family-history': 'save_family_history',
        'form-family': 'save_family',
        'form-lifestyle': 'save_lifestyle',
        'form-angina': 'save_angina',
        'form-diabetes': 'save_diabetes',
        'form-household': 'save_household'
      };
      const actionName = explicitMap[fid] || ('save_' + fid.replace(/^form-/, '').replace(/-/g, '_'));
      const result = await postAction(actionName, form);
      if (btn){ btn.disabled = false; btn.innerHTML = orig; }
      if (result.success){
        createAlert('Saved successfully', 'success');
        
        // Dispatch event to clear localStorage for this form
        const formIdParts = fid.split('-');
        const formIdentifier = formIdParts[1]; // e.g., 'person', 'vitals', etc.
        window.dispatchEvent(new CustomEvent('surveyFormSaved', { detail: { formId: formIdentifier } }));
        
        const next = nextMap[fid];
        if (next) setTimeout(()=> { window.location.href = next; }, 700);
      } else {
        createAlert(result.message || 'Save failed', 'danger');
      }
    });

    // Special case: some pages use an external button to trigger save (e.g., personal, family pages)
    // Find any visible button with id containing 'save' or 'btn-save' and wire it to submit the form
    const allButtons = Array.from(document.querySelectorAll('button[id]'));
    allButtons.forEach(b => {
      const id = (b.id || '').toLowerCase();
      if (id.includes('save') || id.includes('btn-save') || id.includes('btn-dummy-save') || id.includes('btn-save-continue')){
        // only attach when button is in the same page (not global)
        b.addEventListener('click', function(ev){ ev.preventDefault(); form.dispatchEvent(new Event('submit', {cancelable: true})); });
      }
    });
  });
})();

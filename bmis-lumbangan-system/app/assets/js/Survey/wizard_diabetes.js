/**
 * wizard_diabetes.js
 * Diabetes Screening (Step 7 of 8)
 */

(function() {
  'use strict';

  // Helper functions
  const $ = (s, p = document) => p.querySelector(s);
  const $$ = (s, p = document) => Array.from(p.querySelectorAll(s));

  // ============================================
  // 0) Scroll Animations
  // ============================================
  function initScrollAnimations() {
    const revealElements = $$('.section-card, .alert');
    
    const observerOptions = {
      threshold: 0.15,
      rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          setTimeout(() => {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }, index * 100);
        }
      });
    }, observerOptions);
    
    revealElements.forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(30px)';
      el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      observer.observe(el);
    });
    
    // Wizard steps: simple fade-in only
    const wizardElements = $$('.step-circle, .wizard-step');
    wizardElements.forEach((el, index) => {
      el.style.opacity = '0';
      el.style.transition = 'opacity 0.4s ease';
      setTimeout(() => {
        el.style.opacity = '1';
      }, index * 50);
    });
  }

  // ============================================
  // 1) Language Toggle
  // ============================================
  // Language radio buttons (may be absent in some embed contexts)
  const langENRadio = document.getElementById('lang-en') || null;
  const langTLRadio = document.getElementById('lang-tl') || null;

  // On page load, read saved language from localStorage
  const savedLang = localStorage.getItem('survey_language') || 'en';
  if (savedLang === 'tl') {
    if (langTLRadio) langTLRadio.checked = true;
  } else {
    if (langENRadio) langENRadio.checked = true;
  }
  applyLanguage(savedLang);

  // Listen for language toggle (guarded)
  if (langENRadio) {
    langENRadio.addEventListener('change', function() {
      if (this.checked) {
        localStorage.setItem('survey_language', 'en');
        applyLanguage('en');
      }
    });
  }

  if (langTLRadio) {
    langTLRadio.addEventListener('change', function() {
      if (this.checked) {
        localStorage.setItem('survey_language', 'tl');
        applyLanguage('tl');
      }
    });
  }

  /**
   * Apply language to all .i18n elements
   */
  function applyLanguage(lang) {
    const allI18n = document.querySelectorAll('.i18n');
    allI18n.forEach(el => {
      const text = el.getAttribute('data-' + lang);
      if (text) {
        // If it's an input placeholder
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
          el.placeholder = text;
        } else {
          el.textContent = text;
        }
      }
    });
  }

  // Re-apply language after DOMContentLoaded as a recover step in case earlier
  // execution encountered an error before the first call.
  document.addEventListener('DOMContentLoaded', function() {
    try {
      const lang = localStorage.getItem('survey_language') || 'en';
      applyLanguage(lang);
    } catch (e) {
      // swallow - language toggle is non-fatal
      console.error('Language apply failed on DOMContentLoaded', e);
    }
  });

  // ============================================
  // 2) Blood Sugar Validation
  // ============================================
  const form = document.getElementById('form-diabetes');
  const rbsInput = document.getElementById('rbs_mg_dl');
  const fbsInput = document.getElementById('fbs_mg_dl');
  const hba1cInput = document.getElementById('hba1c_percent');

  // Real-time validation for blood sugar levels
  if (rbsInput) {
    rbsInput.addEventListener('input', function() {
      validateBloodSugar(this, 'rbs');
    });
  }

  if (fbsInput) {
    fbsInput.addEventListener('input', function() {
      validateBloodSugar(this, 'fbs');
    });
  }

  if (hba1cInput) {
    hba1cInput.addEventListener('input', function() {
      validateHbA1c(this);
    });
  }

  function validateBloodSugar(input, type) {
    const value = parseFloat(input.value);
    if (isNaN(value) || value <= 0) return;

    // Remove existing warning
    const existingWarning = input.parentElement.querySelector('.blood-sugar-warning');
    if (existingWarning) {
      existingWarning.remove();
    }

    let warningMsg = '';
    const lang = localStorage.getItem('survey_language') || 'en';

    if (type === 'rbs') {
      if (value >= 200) {
        warningMsg = lang === 'tl' 
          ? 'Napakataas! Kailangan ang agarang atensyon ng doktor.'
          : 'Very High! Requires immediate medical attention.';
      } else if (value >= 140) {
        warningMsg = lang === 'tl'
          ? 'Mataas - Posibleng diabetes.'
          : 'High - Possible diabetes.';
      }
    } else if (type === 'fbs') {
      if (value >= 126) {
        warningMsg = lang === 'tl'
          ? 'Mataas - Posibleng diabetes.'
          : 'High - Possible diabetes.';
      } else if (value >= 100) {
        warningMsg = lang === 'tl'
          ? 'Pre-diabetes range.'
          : 'Pre-diabetes range.';
      }
    }

    if (warningMsg) {
      const warningDiv = document.createElement('small');
      warningDiv.className = 'text-danger d-block mt-1 blood-sugar-warning';
      warningDiv.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i>${warningMsg}`;
      input.parentElement.appendChild(warningDiv);
    }
  }

  function validateHbA1c(input) {
    const value = parseFloat(input.value);
    if (isNaN(value) || value <= 0) return;

    // Remove existing warning
    const existingWarning = input.parentElement.querySelector('.hba1c-warning');
    if (existingWarning) {
      existingWarning.remove();
    }

    let warningMsg = '';
    const lang = localStorage.getItem('survey_language') || 'en';

    if (value >= 6.5) {
      warningMsg = lang === 'tl'
        ? 'Diabetes range.'
        : 'Diabetes range.';
    } else if (value >= 5.7) {
      warningMsg = lang === 'tl'
        ? 'Pre-diabetes range.'
        : 'Pre-diabetes range.';
    }

    if (warningMsg) {
      const warningDiv = document.createElement('small');
      warningDiv.className = 'text-warning d-block mt-1 hba1c-warning';
      warningDiv.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i>${warningMsg}`;
      input.parentElement.appendChild(warningDiv);
    }
  }

  // ============================================
  // 3) Screening Logic
  // ============================================
  function calculateScreening() {
    const formData = new FormData(form);
    
    const knownDiabetes = formData.get('known_diabetes') === '1';
    const polyuria = formData.get('polyuria') === '1';
    const polydipsia = formData.get('polydipsia') === '1';
    const polyphagia = formData.get('polyphagia') === '1';
    
    const rbs = parseFloat(formData.get('rbs_mg_dl')) || 0;
    const fbs = parseFloat(formData.get('fbs_mg_dl')) || 0;
    const hba1c = parseFloat(formData.get('hba1c_percent')) || 0;

    // Screen positive if:
    // - Known diabetes OR
    // - Two or more of the 3 P's (polyuria, polydipsia, polyphagia) OR
    // - RBS >= 200 OR FBS >= 126 OR HbA1c >= 6.5
    const symptomCount = [polyuria, polydipsia, polyphagia].filter(Boolean).length;
    const labPositive = (rbs >= 200) || (fbs >= 126) || (hba1c >= 6.5);
    
    const screenPositive = knownDiabetes || (symptomCount >= 2) || labPositive;

    return screenPositive;
  }

  // ============================================
  // 4) Form Submission
  // ============================================

  // ============================================
  // 6) Load saved data on page load
  // ========== FORM PROGRESS TRACKER ==========
  function initProgressTracker() {
    if (!form) return;

    function shouldSkipConditionalOther(el) {
      try {
        if (!el || !el.name) return false;
        if (!/_other$/.test(el.name)) return false;
        const base = el.name.replace(/_other$/, '');
        const baseEl = form.querySelector(`[name="${base}"]`) || document.querySelector(`[name="${base}"]`);
        if (!baseEl) return false;
        const v = (baseEl.value || '').toString().toLowerCase();
        return !(v === 'other' || v === 'others' || v.indexOf('other') !== -1);
      } catch (e) { return false; }
    }

    // Build a de-duplicated list of form "fields" where radio groups count as one field
    const allElements = Array.from(form.elements).filter(el =>
      (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') &&
      el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button' &&
      !el.hasAttribute('data-optional')
    );
    const seenRadioNames = new Set();
    const allInputs = [];
    allElements.forEach(el => {
      if (shouldSkipConditionalOther(el)) return;
      if (el.type === 'radio') {
        if (seenRadioNames.has(el.name)) return; // already accounted for this group
        seenRadioNames.add(el.name);
        allInputs.push(el); // push a representative radio element (group counted once)
      } else {
        allInputs.push(el);
      }
    });
    
    function updateProgress() {
      const filledInputs = allInputs.filter(el => {
        // Radio groups: we pushed a representative element earlier; consider the group filled
        // if any radio with the same name in the form/section is checked
        if (el.type === 'radio') {
          return !!form.querySelector(`input[name="${el.name}"]:checked`);
        }
        if (el.type === 'checkbox') {
          return el.checked;
        }
        // flatpickr-backed inputs: check picker state or altInput if present
        try {
          if (el._flatpickr) {
            if (Array.isArray(el._flatpickr.selectedDates) && el._flatpickr.selectedDates.length > 0) return true;
            if (el._flatpickr.altInput && el._flatpickr.altInput.value) return true;
          }
        } catch(e){}
        return el.value && el.value.trim() !== '';
      });
      
      const progress = allInputs.length > 0 ? (filledInputs.length / allInputs.length) * 100 : 0;
      
      // Update any progress bar if exists
      const progressBar = $('.form-progress-bar');
      if (progressBar) {
        progressBar.style.width = `${progress}%`;
        progressBar.style.transition = 'width 0.4s ease';
      }
      
      // Update step indicators based on section completion
      updateSectionProgress();
    }
    
    function updateSectionProgress() {
      const sections = $$('.section-card');
      sections.forEach(section => {
        // Build section-level unique inputs (radio groups counted once)
        const secElements = Array.from(section.querySelectorAll('input, select, textarea')).filter(el =>
          el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button' && !el.hasAttribute('data-optional')
        );
        const secSeenRadio = new Set();
        const sectionInputs = [];
        secElements.forEach(el => {
          if (shouldSkipConditionalOther(el)) return;
          if (el.type === 'radio') {
            if (secSeenRadio.has(el.name)) return;
            secSeenRadio.add(el.name);
            sectionInputs.push(el);
          } else {
            sectionInputs.push(el);
          }
        });

        const filledInSection = sectionInputs.filter(el => {
          if (el.type === 'radio') {
            return !!section.querySelector(`input[name="${el.name}"]:checked`);
          }
          if (el.type === 'checkbox') return el.checked;
          try {
            if (el._flatpickr) {
              if (Array.isArray(el._flatpickr.selectedDates) && el._flatpickr.selectedDates.length > 0) return true;
              if (el._flatpickr.altInput && el._flatpickr.altInput.value) return true;
            }
          } catch(e){}
          return el.value && el.value.trim() !== '';
        }).length;
        
        const sectionProgress = sectionInputs.length > 0 ? (filledInSection / sectionInputs.length) * 100 : 0;
        
        // Add visual feedback
        if (sectionProgress === 100) {
          section.classList.add('section-complete');
        } else {
          section.classList.remove('section-complete');
        }
      });
    }
    
    // Listen to all input changes
    allInputs.forEach(input => {
      input.addEventListener('input', updateProgress);
      input.addEventListener('change', updateProgress);
    });
    
    // Initial progress check
    updateProgress();
  }
  
  // Add section complete styling
  const progressStyle = document.createElement('style');
  progressStyle.textContent = `
    .section-complete {
      border-color: #10b981 !important;
      background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
    }
    .section-complete .section-icon {
      background: linear-gradient(135deg, #10b981, #059669) !important;
      color: white !important;
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
      from {
        transform: scale(0) rotate(-180deg);
        opacity: 0;
      }
      to {
        transform: scale(1) rotate(0deg);
        opacity: 1;
      }
    }
    .form-progress-bar {
      height: 4px;
      background: linear-gradient(90deg, #1e3a5f, #c53030);
      position: fixed;
      top: 0;
      left: 0;
      z-index: 9999;
      box-shadow: 0 2px 10px rgba(30, 58, 95, 0.3);
    }
  `;
  document.head.appendChild(progressStyle);
  // ============================================
  window.addEventListener('DOMContentLoaded', function() {
    // Only restore local draft when the server already has diabetes data for this person.

    // Attach form submission handler after DOM is ready
    const form = document.getElementById('form-diabetes');
    if (form) {
      form.addEventListener('submit', function(e) {
        // Only block submission when invalid; allow central handler to POST when valid
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
          form.classList.add('was-validated');
          // Scroll to first invalid field
          const firstInvalid = form.querySelector(':invalid');
          if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
          }
          return;
        }

        // Form valid: save draft locally and let central save-survey.js handle POST and navigation
        const formData = new FormData(form);
        const diabetesData = {
          known_diabetes: formData.get('known_diabetes'),
          on_medications: formData.get('on_medications'),
          family_history: formData.get('family_history'),
          polyuria: formData.get('polyuria'),
          polydipsia: formData.get('polydipsia'),
          polyphagia: formData.get('polyphagia'),
          weight_loss: formData.get('weight_loss'),
          rbs_mg_dl: formData.get('rbs_mg_dl') || null,
          fbs_mg_dl: formData.get('fbs_mg_dl') || null,
          hba1c_percent: formData.get('hba1c_percent') || null,
          urine_ketone: formData.get('urine_ketone'),
          urine_protein: formData.get('urine_protein'),
          screen_positive: calculateScreening() ? '1' : '0'
        };

        localStorage.setItem('survey_diabetes', JSON.stringify(diabetesData));
      });

      // Auto-save on visibility change
      document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
          // User is leaving the page, auto-save if form has data
          const formData = new FormData(form);
          const knownDiabetes = formData.get('known_diabetes');
          
          if (knownDiabetes) {
            // At least one field answered, save draft
            const diabetesData = {
              known_diabetes: formData.get('known_diabetes'),
              on_medications: formData.get('on_medications'),
              family_history: formData.get('family_history'),
              polyuria: formData.get('polyuria'),
              polydipsia: formData.get('polydipsia'),
              polyphagia: formData.get('polyphagia'),
              weight_loss: formData.get('weight_loss'),
              rbs_mg_dl: formData.get('rbs_mg_dl') || null,
              fbs_mg_dl: formData.get('fbs_mg_dl') || null,
              hba1c_percent: formData.get('hba1c_percent') || null,
              urine_ketone: formData.get('urine_ketone'),
              urine_protein: formData.get('urine_protein'),
              screen_positive: calculateScreening() ? '1' : '0'
            };

            localStorage.setItem('survey_diabetes', JSON.stringify(diabetesData));
          }
        }
      });
    }

    // This prevents restoring another user's saved draft (privacy/leak) when no server
    // data exists for the current person.
    const savedData = localStorage.getItem('survey_diabetes');
    if (savedData && window.__has_server_diabetes) {
      try {
        const data = JSON.parse(savedData);
        
        // Restore radio selections
        if (data.known_diabetes) {
          const radio = form.querySelector(`input[name="known_diabetes"][value="${data.known_diabetes}"]`);
          if (radio) radio.checked = true;
        }
        
        if (data.on_medications) {
          const radio = form.querySelector(`input[name="on_medications"][value="${data.on_medications}"]`);
          if (radio) radio.checked = true;
        }
        
        if (data.family_history) {
          const radio = form.querySelector(`input[name="family_history"][value="${data.family_history}"]`);
          if (radio) radio.checked = true;
        }
        
        if (data.polyuria) {
          const radio = form.querySelector(`input[name="polyuria"][value="${data.polyuria}"]`);
          if (radio) radio.checked = true;
        }
        
        if (data.polydipsia) {
          const radio = form.querySelector(`input[name="polydipsia"][value="${data.polydipsia}"]`);
          if (radio) radio.checked = true;
        }
        
        if (data.polyphagia) {
          const radio = form.querySelector(`input[name="polyphagia"][value="${data.polyphagia}"]`);
          if (radio) radio.checked = true;
        }
        
        if (data.weight_loss) {
          const radio = form.querySelector(`input[name="weight_loss"][value="${data.weight_loss}"]`);
          if (radio) radio.checked = true;
        }
        
        if (data.urine_ketone) {
          const radio = form.querySelector(`input[name="urine_ketone"][value="${data.urine_ketone}"]`);
          if (radio) radio.checked = true;
        }
        
        if (data.urine_protein) {
          const radio = form.querySelector(`input[name="urine_protein"][value="${data.urine_protein}"]`);
          if (radio) radio.checked = true;
        }

        // Restore number inputs
        if (data.rbs_mg_dl) {
          rbsInput.value = data.rbs_mg_dl;
          validateBloodSugar(rbsInput, 'rbs');
        }
        
        if (data.fbs_mg_dl) {
          fbsInput.value = data.fbs_mg_dl;
          validateBloodSugar(fbsInput, 'fbs');
        }
        
        if (data.hba1c_percent) {
          hba1cInput.value = data.hba1c_percent;
          validateHbA1c(hba1cInput);
        }

      } catch (err) {
        console.error('Error loading saved diabetes data:', err);
      }
    } else if (savedData && !window.__has_server_diabetes) {
      // If there is a local draft but no server record for this person, do NOT restore it.
      // This avoids cross-account leakage. Optionally, we can remove the draft so it doesn't
      // persist unexpectedly — but we'll leave it intact for now and rely on logout clearing.
    }

    // Ensure top progress bar exists (insert if missing)
    try {
      if (!document.querySelector('.form-progress-bar')) {
        const progressBar = document.createElement('div');
        progressBar.className = 'form-progress-bar';
        progressBar.style.width = '0%';
        document.body.insertBefore(progressBar, document.body.firstChild);
      }
    } catch (e) { console.warn('Could not insert progress bar', e); }

    // Initialize tracker after any saved data is restored (or when no draft)
    try { initProgressTracker(); } catch (e) { console.warn('Progress tracker init failed', e); }
  });

  // ============================================
  // Initialize animations
  // ============================================
  initScrollAnimations();

})();

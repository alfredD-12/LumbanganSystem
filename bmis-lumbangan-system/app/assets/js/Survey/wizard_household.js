(function () {
  'use strict';

  // Intercept native alert() on this page and route survey completion messages
  // to the modern canonical survey toast. This prevents legacy or cached scripts
  // from showing a blocking browser alert like:
  // "Survey completed successfully! Thank you for your participation."
  (function interceptAlert() {
    try {
      const _origAlert = window.alert.bind(window);
      window.alert = function(msg) {
        try {
          if (typeof msg === 'string' && msg.indexOf('Survey completed successfully') !== -1) {
            // Use global canonical alert when available, otherwise enqueue
            if (window.surveyCreateAlert) {
              window.surveyCreateAlert(msg, 'success');
            } else {
              window._pendingSurveyAlerts = window._pendingSurveyAlerts || [];
              window._pendingSurveyAlerts.push({ message: msg, type: 'success' });
            }
            return; // swallow the native alert
          }
        } catch (e) {
          // fall back to native alert if something unexpected happens
        }
        _origAlert(msg);
      };
    } catch (e) {
      // ignore if binding fails (very unlikely)
    }
  })();

  // ==============================
  // 1) HELPERS
  // ==============================
  const $ = (sel) => document.querySelector(sel);
  const $$ = (sel) => document.querySelectorAll(sel);

  // ==============================
  // 2) SCROLL ANIMATIONS
  // ==============================
  function initScrollAnimations() {
    // Cards with fadeInUp animation
    const revealElements = $$('.section-card, .alert');
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, observerOptions);

    revealElements.forEach((el) => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(30px)';
      el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      revealObserver.observe(el);
    });

    // Wizard icons with simple fade-in (no transform)
    const wizardElements = $$('.step-circle, .wizard-step');
    wizardElements.forEach((el, index) => {
      el.style.opacity = '0';
      el.style.transition = 'opacity 0.4s ease';
      setTimeout(() => {
        el.style.opacity = '1';
      }, index * 50);
    });
  }

  // ==============================
  // 3) LANGUAGE SWITCHING
  // ==============================
  function initLanguage() {
    const langEN = $('#lang-en');
    const langTL = $('#lang-tl');
    const savedLang = localStorage.getItem('survey_language') || 'en';

    if (savedLang === 'tl') {
      langTL.checked = true;
      applyLanguage('tl');
    } else {
      langEN.checked = true;
      applyLanguage('en');
    }

    langEN.addEventListener('change', () => {
      if (langEN.checked) {
        applyLanguage('en');
        localStorage.setItem('survey_language', 'en');
      }
    });

    langTL.addEventListener('change', () => {
      if (langTL.checked) {
        applyLanguage('tl');
        localStorage.setItem('survey_language', 'tl');
      }
    });
  }

  function applyLanguage(lang) {
    const i18nElements = $$('.i18n');
    i18nElements.forEach((el) => {
      const text = el.getAttribute('data-' + lang);
      if (text) {
        if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
          el.placeholder = text;
        } else {
          el.textContent = text;
        }
      }
    });
  }

  // ==============================
  // 4) HOUSEHOLD NUMBER AUTO-GENERATION
  // ==============================
  function initHouseholdNumberGeneration() {
    const purokSelect = $('#purok_id');
    const householdNoInput = $('#household_no');

    if (purokSelect && householdNoInput) {
      // helper to call server for next household number
      const getNextHouseholdNo = (purokId, purokCode) => {
        const params = new URLSearchParams();
        params.set('action', 'next_household_no');
        if (purokId) params.set('purok_id', purokId);
        if (purokCode) params.set('code', purokCode);
        return fetch(window.SURVEY_API + '?' + params.toString(), { credentials: 'same-origin' })
          .then(res => res.json())
          .catch(err => { console.warn('next_household_no fetch failed', err); return null; });
      };

      const setHouseholdNoFromResponse = (json, purokCode) => {
        if (json && json.success && json.household_no) {
          householdNoInput.value = json.household_no;
          householdNoInput.setAttribute('data-purok-code', purokCode || '');
        } else if (purokCode) {
          householdNoInput.value = purokCode + '-001';
          householdNoInput.setAttribute('data-purok-code', purokCode || '');
        } else {
          householdNoInput.value = '';
        }
      };

      const generateForCurrentPurok = async () => {
        const selectedOpt = purokSelect.options[purokSelect.selectedIndex];
        const purokCode = selectedOpt ? selectedOpt.getAttribute('data-code') || purokSelect.value : purokSelect.value;
        const purokId = selectedOpt ? selectedOpt.value : purokSelect.value;
        if (!purokId && !purokCode) {
          householdNoInput.value = '';
          return;
        }
        const json = await getNextHouseholdNo(purokId, purokCode);
        setHouseholdNoFromResponse(json, purokCode);
      };

      // trigger on purok change
      purokSelect.addEventListener('change', () => {
        generateForCurrentPurok();
      });

      // debounce helper for address edits
      const debounce = (fn, ms = 300) => { let t; return function() { clearTimeout(t); const args=arguments, ctx=this; t=setTimeout(()=>fn.apply(ctx,args), ms); }; };

      // Also trigger generation when address fields are edited (immediate fallback)
      const addressFields = ['#address_house_no', '#address_street', '#address_sitio_subdivision', '#address_building'];
      const debouncedGenerate = debounce(() => { generateForCurrentPurok(); }, 250);
      addressFields.forEach(sel => {
        const el = document.querySelector(sel);
        if (el) {
          el.addEventListener('input', debouncedGenerate);
          el.addEventListener('change', debouncedGenerate);
        }
      });
    }
  }

  // ==============================
  // 5) "OTHERS" FIELD TOGGLE
  // ==============================
  function initOthersToggle() {
    const togglePairs = [
      { select: '#home_ownership', div: '#home_ownership_other_div', input: '#home_ownership_other' },
      { select: '#construction_material', div: '#construction_material_other_div', input: '#construction_material_other' },
      { select: '#lighting_facility', div: '#lighting_facility_other_div', input: '#lighting_facility_other' },
      { select: '#toilet_type', div: '#toilet_type_other_div', input: '#toilet_type_other' },
      { select: '#garbage_disposal_method', div: '#garbage_disposal_other_div', input: '#garbage_disposal_other' }
    ];

    togglePairs.forEach(({ select, div, input }) => {
      const selectEl = $(select);
      const divEl = $(div);
      const inputEl = $(input);

      if (selectEl && divEl && inputEl) {
        selectEl.addEventListener('change', () => {
          if (selectEl.value === 'Others') {
            divEl.classList.add('show');
            inputEl.required = true;
          } else {
            divEl.classList.remove('show');
            inputEl.required = false;
            inputEl.value = '';
          }
        });

        // Initial check on page load for pre-filled data
        if (selectEl.value === 'Others') {
          divEl.classList.add('show');
          inputEl.required = true;
        }
      }
    });
  }

  // ==============================
  // 6) FORM VALIDATION & SUBMISSION
  // ==============================
  function initFormValidation() {
    const form = $('#form-household');
    if (!form) return;

    form.addEventListener('submit', (e) => {
      // Only prevent submission when invalid; allow the centralized save handler to POST when valid
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

      // Form is valid: gather household data and save draft locally. Central handler will POST and show canonical alert.
      const householdData = {
        purok_id: $('#purok_id')?.value || '',
        household_no: $('#household_no')?.value.trim() || '',
        address_house_no: $('#address_house_no')?.value.trim() || '',
        address_street: $('#address_street')?.value.trim() || '',
        address_sitio_subdivision: $('#address_sitio_subdivision')?.value.trim() || '',
        address_building: $('#address_building')?.value.trim() || '',
        home_ownership: $('#home_ownership')?.value || '',
        home_ownership_other: $('#home_ownership_other')?.value.trim() || '',
        construction_material: $('#construction_material')?.value || '',
        construction_material_other: $('#construction_material_other')?.value.trim() || '',
        lighting_facility: $('#lighting_facility')?.value || '',
        lighting_facility_other: $('#lighting_facility_other')?.value.trim() || '',
        toilet_type: $('#toilet_type')?.value || '',
        toilet_type_other: $('#toilet_type_other')?.value.trim() || '',
        water_level: $('#water_level')?.value || '',
        water_source: $('#water_source')?.value.trim() || '',
        water_storage: $('#water_storage')?.value || '',
        drinking_water_other_source: $('#drinking_water_other_source')?.value.trim() || '',
        garbage_container: $('#garbage_container')?.value || '',
        garbage_segregated: $('input[name="garbage_segregated"]:checked')?.value || '',
        garbage_disposal_method: $('#garbage_disposal_method')?.value || '',
        garbage_disposal_other: $('#garbage_disposal_other')?.value.trim() || '',
        family_number: $('#family_number')?.value.trim() || '',
        residency_status: $('#residency_status')?.value || '',
        length_of_residency_months: $('#length_of_residency_months')?.value || '',
        email: $('#email')?.value.trim() || ''
      };

      localStorage.setItem('survey_household', JSON.stringify(householdData));
      // Do not show local alert/redirect here. save-survey.js will POST and show the canonical alert and handle navigation.
    });
  }

  // ========== FORM PROGRESS TRACKER ==========
  function initProgressTracker() {
    const form = $('#form-household');
    if (!form) return;

    // Helper: treat "*_other" inputs as conditional — skip them unless their
    // corresponding base select explicitly has an "Other(s)" value selected.
    function shouldSkipConditionalOther(el) {
      try {
        if (!el || !el.name) return false;
        if (!/_other$/.test(el.name)) return false;
        const base = el.name.replace(/_other$/, '');
        const baseEl = form.querySelector(`[name="${base}"]`) || document.querySelector(`[name="${base}"]`);
        if (!baseEl) return false;
        const v = (baseEl.value || '').toString().toLowerCase();
        // If base select does NOT indicate Other, skip the "_other" field
        return !(v === 'other' || v === 'others' || v.indexOf('other') !== -1);
      } catch (e) { return false; }
    }

    const allElements = Array.from(form.elements).filter(el =>
      (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') &&
      el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button' &&
      !el.hasAttribute('data-optional')
    );

    const seenRadioNames = new Set();
    const allInputs = [];
    allElements.forEach(el => {
      // Skip conditional "other" inputs when their base select isn't set to Other
      if (shouldSkipConditionalOther(el)) return;
      if (el.type === 'radio') {
        if (seenRadioNames.has(el.name)) return;
        seenRadioNames.add(el.name);
        allInputs.push(el);
      } else {
        allInputs.push(el);
      }
    });

    function updateProgress() {
      const filledInputs = allInputs.filter(el => {
        if (el.type === 'radio') return !!form.querySelector(`input[name="${el.name}"]:checked`);
        if (el.type === 'checkbox') return el.checked;
        try {
          if (el._flatpickr) {
            if (Array.isArray(el._flatpickr.selectedDates) && el._flatpickr.selectedDates.length > 0) return true;
            if (el._flatpickr.altInput && el._flatpickr.altInput.value) return true;
          }
        } catch (e) {}
        return el.value && el.value.toString().trim() !== '';
      });

      const progress = allInputs.length > 0 ? (filledInputs.length / allInputs.length) * 100 : 0;
      const progressBar = document.querySelector('.form-progress-bar');
      if (progressBar) {
        progressBar.style.width = `${progress}%`;
        progressBar.style.transition = 'width 0.4s ease';
      }
      updateSectionProgress();
    }

    function updateSectionProgress() {
      const sections = Array.from($$('.section-card'));
      sections.forEach(section => {
        const secElements = Array.from(section.querySelectorAll('input, select, textarea')).filter(el =>
          el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button' && !el.hasAttribute('data-optional')
        );
        const secSeen = new Set();
        const sectionInputs = [];
        secElements.forEach(el => {
          // Skip conditional "other" fields when not applicable
          if (shouldSkipConditionalOther(el)) return;
          if (el.type === 'radio') {
            if (secSeen.has(el.name)) return;
            secSeen.add(el.name);
            sectionInputs.push(el);
          } else {
            sectionInputs.push(el);
          }
        });

        const filled = sectionInputs.filter(el => {
          if (el.type === 'radio') return !!section.querySelector(`input[name="${el.name}"]:checked`);
          if (el.type === 'checkbox') return el.checked;
          try {
            if (el._flatpickr) {
              if (Array.isArray(el._flatpickr.selectedDates) && el._flatpickr.selectedDates.length > 0) return true;
              if (el._flatpickr.altInput && el._flatpickr.altInput.value) return true;
            }
          } catch (e) {}
          return el.value && el.value.toString().trim() !== '';
        }).length;

        const sectionProgress = sectionInputs.length > 0 ? (filled / sectionInputs.length) * 100 : 0;
        if (sectionProgress === 100) section.classList.add('section-complete'); else section.classList.remove('section-complete');
      });
    }

    allInputs.forEach(input => {
      input.addEventListener('input', updateProgress);
      input.addEventListener('change', updateProgress);
    });

    updateProgress();
    setTimeout(updateProgress, 100); // Also run shortly after load for pre-filled values
  }

  // Add section-complete styles (id guarded)
  (function addHouseholdProgressStyle(){
    const id = 'survey-household-progress-style'; if (document.getElementById(id)) return;
    const progressStyle = document.createElement('style'); progressStyle.id = id;
    progressStyle.textContent = `
      .section-complete { border-color: #10b981 !important; background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important; }
      .section-complete .section-icon { background: linear-gradient(135deg, #10b981, #059669) !important; color: white !important; }
      .section-complete::after { content: '✓'; position: absolute; top: 15px; right: 15px; width: 32px; height: 32px; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; animation: scaleIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
      @keyframes scaleIn { from { transform: scale(0) rotate(-180deg); opacity: 0; } to { transform: scale(1) rotate(0deg); opacity: 1; } }
      .form-progress-bar { height: 4px; background: linear-gradient(90deg, #1e3a5f, #c53030); position: fixed; top: 0; left: 0; z-index: 9999; box-shadow: 0 2px 10px rgba(30, 58, 95, 0.3); }
    `;
    document.head.appendChild(progressStyle);
  })();

  // ==============================
  // 9) INIT ON LOAD
  // ==============================
  window.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initLanguage();
    initHouseholdNumberGeneration();
    initOthersToggle();

    // Ensure top progress bar exists and initialize tracker
    try {
      if (!document.querySelector('.form-progress-bar')) {
        const progressBar = document.createElement('div');
        progressBar.className = 'form-progress-bar';
        progressBar.style.width = '0%';
        document.body.insertBefore(progressBar, document.body.firstChild);
      }
    } catch (e) { console.warn('Could not insert progress bar', e); }

    try { initProgressTracker(); } catch (e) { console.warn('Progress tracker init failed', e); }
  });

})();

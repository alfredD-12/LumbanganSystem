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
      purokSelect.addEventListener('change', () => {
        // Use the option's data-code (e.g., 'CA') for human-readable household_no prefix
        const selectedOpt = purokSelect.options[purokSelect.selectedIndex];
        const purokCode = selectedOpt ? selectedOpt.getAttribute('data-code') || purokSelect.value : purokSelect.value;
        if (purokCode) {
          // Generate placeholder number - in production, this should query the backend
          // for the next available number in this purok
          const placeholderNumber = '001'; // This will be replaced by backend
          householdNoInput.value = `${purokCode}-${placeholderNumber}`;
          householdNoInput.setAttribute('data-purok-code', purokCode);
        } else {
          householdNoInput.value = '';
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
      console.log('Household draft saved, delegating to central save handler');
      // Do not show local alert/redirect here. save-survey.js will POST and show the canonical alert and handle navigation.
    });
  }

  // ==============================
  // 7) AUTO-SAVE ON PAGE LEAVE
  // ==============================
  function autoSaveOnLeave() {
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        saveFormData();
      }
    });

    window.addEventListener('beforeunload', () => {
      saveFormData();
    });
  }

  function saveFormData() {
    const form = $('#form-household');
    if (!form) return;

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
  }

  // ==============================
  // 8) LOAD SAVED DATA
  // ==============================
  function loadSavedData() {
    const saved = localStorage.getItem('survey_household');
    if (!saved) return;

    try {
      const data = JSON.parse(saved);

  // Purok and household number
  if (data.purok_id) $('#purok_id').value = data.purok_id;
      if (data.household_no) $('#household_no').value = data.household_no;

      // Address fields
      if (data.address_house_no) $('#address_house_no').value = data.address_house_no;
      if (data.address_street) $('#address_street').value = data.address_street;
      if (data.address_sitio_subdivision) $('#address_sitio_subdivision').value = data.address_sitio_subdivision;
      if (data.address_building) $('#address_building').value = data.address_building;

      // Home & construction
      if (data.home_ownership) {
        $('#home_ownership').value = data.home_ownership;
        if (data.home_ownership === 'Others' && data.home_ownership_other) {
          $('#home_ownership_other_div').classList.add('show');
          $('#home_ownership_other').required = true;
          $('#home_ownership_other').value = data.home_ownership_other;
        }
      }

      if (data.construction_material) {
        $('#construction_material').value = data.construction_material;
        if (data.construction_material === 'Others' && data.construction_material_other) {
          $('#construction_material_other_div').classList.add('show');
          $('#construction_material_other').required = true;
          $('#construction_material_other').value = data.construction_material_other;
        }
      }

      // Facilities
      if (data.lighting_facility) {
        $('#lighting_facility').value = data.lighting_facility;
        if (data.lighting_facility === 'Others' && data.lighting_facility_other) {
          $('#lighting_facility_other_div').classList.add('show');
          $('#lighting_facility_other').required = true;
          $('#lighting_facility_other').value = data.lighting_facility_other;
        }
      }

      if (data.toilet_type) {
        $('#toilet_type').value = data.toilet_type;
        if (data.toilet_type === 'Others' && data.toilet_type_other) {
          $('#toilet_type_other_div').classList.add('show');
          $('#toilet_type_other').required = true;
          $('#toilet_type_other').value = data.toilet_type_other;
        }
      }

      // Water
      if (data.water_level) $('#water_level').value = data.water_level;
      if (data.water_source) $('#water_source').value = data.water_source;
      if (data.water_storage) $('#water_storage').value = data.water_storage;
      if (data.drinking_water_other_source) $('#drinking_water_other_source').value = data.drinking_water_other_source;

      // Garbage
      if (data.garbage_container) $('#garbage_container').value = data.garbage_container;
      
      if (data.garbage_segregated) {
        const radio = $(`input[name="garbage_segregated"][value="${data.garbage_segregated}"]`);
        if (radio) radio.checked = true;
      }

      if (data.garbage_disposal_method) {
        $('#garbage_disposal_method').value = data.garbage_disposal_method;
        if (data.garbage_disposal_method === 'Others' && data.garbage_disposal_other) {
          $('#garbage_disposal_other_div').classList.add('show');
          $('#garbage_disposal_other').required = true;
          $('#garbage_disposal_other').value = data.garbage_disposal_other;
        }
      }

      // Family info
      if (data.family_number) $('#family_number').value = data.family_number;
      if (data.residency_status) $('#residency_status').value = data.residency_status;
      if (data.length_of_residency_months) $('#length_of_residency_months').value = data.length_of_residency_months;
      if (data.email) $('#email').value = data.email;

    } catch (err) {
      console.error('Error loading saved household data:', err);
    }
  }

  // ==============================
  // 9) INIT ON LOAD
  // ==============================
  window.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initLanguage();
    initHouseholdNumberGeneration();
    initOthersToggle();
    initFormValidation();
    autoSaveOnLeave();
    loadSavedData();
  });

})();

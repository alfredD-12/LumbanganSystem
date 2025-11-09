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
  const langENRadio = document.getElementById('lang-en');
  const langTLRadio = document.getElementById('lang-tl');

  // On page load, read saved language from localStorage
  const savedLang = localStorage.getItem('survey_language') || 'en';
  if (savedLang === 'tl') {
    langTLRadio.checked = true;
  } else {
    langENRadio.checked = true;
  }
  applyLanguage(savedLang);

  // Listen for language toggle
  langENRadio.addEventListener('change', function() {
    if (this.checked) {
      localStorage.setItem('survey_language', 'en');
      applyLanguage('en');
    }
  });

  langTLRadio.addEventListener('change', function() {
    if (this.checked) {
      localStorage.setItem('survey_language', 'tl');
      applyLanguage('tl');
    }
  });

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
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Bootstrap validation
    if (!form.checkValidity()) {
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

    form.classList.add('was-validated');

    // Collect form data
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

    // Save to localStorage
    localStorage.setItem('survey_diabetes', JSON.stringify(diabetesData));

    console.log('Diabetes data saved:', diabetesData);

    // Show success message
    const lang = localStorage.getItem('survey_language') || 'en';
    const successMsg = lang === 'tl' ? 'Nai-save na!' : 'Saved!';
    
    console.log(successMsg);

    // Navigate to household step
    window.location.href = 'wizard_household.php';
  });

  // ============================================
  // 5) Auto-save on visibility change
  // ============================================
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
        console.log('Auto-saved diabetes data');
      }
    }
  });

  // ============================================
  // 6) Load saved data on page load
  // ============================================
  window.addEventListener('DOMContentLoaded', function() {
    const savedData = localStorage.getItem('survey_diabetes');
    if (savedData) {
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

        console.log('Loaded saved diabetes data');
      } catch (err) {
        console.error('Error loading saved diabetes data:', err);
      }
    }
  });

  // ============================================
  // Initialize animations
  // ============================================
  initScrollAnimations();

})();

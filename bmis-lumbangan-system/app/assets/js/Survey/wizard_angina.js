/**
 * wizard_angina.js
 * Angina & Stroke Screening (Step 6 of 8)
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
    const revealElements = $$('.question-card, .alert');
    
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
  // 2) Screening Logic
  // ============================================
  const form = document.getElementById('form-angina');

  /**
   * Calculate screening results based on answers
   */
  function calculateScreening() {
    const formData = new FormData(form);
    
    const q1 = formData.get('q1_chest_discomfort');
    const q2 = formData.get('q2_pain_location_left_arm_neck_back');
    const q3 = formData.get('q3_pain_on_exertion');
    const q4 = formData.get('q4_pain_relieved_by_rest_or_nitro');
    const q5 = formData.get('q5_pain_lasting_10min_plus');
    const q6 = formData.get('q6_pain_front_of_chest_half_hour');

    // Convert to boolean
    const hasQ1 = q1 === '1';
    const hasQ2 = q2 === '1';
    const hasQ3 = q3 === '1';
    const hasQ4 = q4 === '1';
    const hasQ5 = q5 === '1';
    const hasQ6 = q6 === '1';

    // Screening logic:
    // Screen positive if:
    // - Questions 1-4 are all YES (classic angina pattern)
    // - OR Question 6 is YES (possible MI)
    const screenPositive = (hasQ1 && hasQ2 && hasQ3 && hasQ4) || hasQ6;

    // Needs doctor referral if screen positive
    const needsReferral = screenPositive;

    // Show/hide referral message
    showScreeningResult(screenPositive, needsReferral);
  }

  /**
   * Show screening result message
   */
  function showScreeningResult(screenPositive, needsReferral) {
      // No-op: explicit red alert removed per UX decision.
      // The page already includes an informational icon/alert; avoid duplicating warnings.
      return;
  }

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
        } catch(e){}
        return el.value && el.value.trim() !== '';
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
      const sections = Array.from(document.querySelectorAll('.section-card'));
      sections.forEach(section => {
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
          if (el.type === 'radio') return !!section.querySelector(`input[name="${el.name}"]:checked`);
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
        if (sectionProgress === 100) section.classList.add('section-complete'); else section.classList.remove('section-complete');
      });
    }

    allInputs.forEach(input => {
      input.addEventListener('input', updateProgress);
      input.addEventListener('change', updateProgress);
    });

    // Initial
    updateProgress();
  }

  // Add section complete styling (id-guarded)
  (function addProgressStyle(){
    const id = 'survey-angina-progress-style'; if (document.getElementById(id)) return;
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

  // ============================================
  // 3) Form Submission
  // ============================================

  // ============================================
  // 5) Load saved data on page load
  // ============================================
  window.addEventListener('DOMContentLoaded', function() {
    // Attach screening logic listeners after DOM is ready
    const form = document.getElementById('form-angina');
    if (form) {
      const questionInputs = form.querySelectorAll('input[type="radio"]');
      questionInputs.forEach(input => input.addEventListener('change', calculateScreening));

      // Attach form submission handler
      form.addEventListener('submit', function(e) {
        // Only prevent submission when invalid so central handler can run when valid
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
          form.classList.add('was-validated');
          const firstInvalid = form.querySelector(':invalid');
          if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
          }
          return;
        }

        // Form is valid: save draft to localStorage but allow central handler to POST and show the canonical alert
        const formData = new FormData(form);
        const anginaData = {
          q1_chest_discomfort: formData.get('q1_chest_discomfort'),
          q2_pain_location_left_arm_neck_back: formData.get('q2_pain_location_left_arm_neck_back'),
          q3_pain_on_exertion: formData.get('q3_pain_on_exertion'),
          q4_pain_relieved_by_rest_or_nitro: formData.get('q4_pain_relieved_by_rest_or_nitro'),
          q5_pain_lasting_10min_plus: formData.get('q5_pain_lasting_10min_plus'),
          q6_pain_front_of_chest_half_hour: formData.get('q6_pain_front_of_chest_half_hour')
        };
        anginaData.screen_positive = ((anginaData.q1_chest_discomfort === '1' && anginaData.q2_pain_location_left_arm_neck_back === '1' && anginaData.q3_pain_on_exertion === '1' && anginaData.q4_pain_relieved_by_rest_or_nitro === '1') || anginaData.q6_pain_front_of_chest_half_hour === '1') ? '1' : '0';
        anginaData.needs_doctor_referral = anginaData.screen_positive;

        localStorage.setItem('survey_angina', JSON.stringify(anginaData));
        // Do not navigate here; central handler will POST and perform redirect
      });

      // Auto-save on visibility change
      document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
          // User is leaving the page, auto-save if form has data
          const formData = new FormData(form);
          const q1 = formData.get('q1_chest_discomfort');
          
          if (q1) {
            // At least one question answered, save draft
            const q2 = formData.get('q2_pain_location_left_arm_neck_back');
            const q3 = formData.get('q3_pain_on_exertion');
            const q4 = formData.get('q4_pain_relieved_by_rest_or_nitro');
            const q5 = formData.get('q5_pain_lasting_10min_plus');
            const q6 = formData.get('q6_pain_front_of_chest_half_hour');

            const hasQ1 = q1 === '1';
            const hasQ2 = q2 === '1';
            const hasQ3 = q3 === '1';
            const hasQ4 = q4 === '1';
            const hasQ5 = q5 === '1';
            const hasQ6 = q6 === '1';

            const screenPositive = (hasQ1 && hasQ2 && hasQ3 && hasQ4) || hasQ6;

            const anginaData = {
              q1_chest_discomfort: q1, q2_pain_location_left_arm_neck_back: q2, q3_pain_on_exertion: q3,
              q4_pain_relieved_by_rest_or_nitro: q4, q5_pain_lasting_10min_plus: q5, q6_pain_front_of_chest_half_hour: q6,
              screen_positive: screenPositive ? '1' : '0',
              needs_doctor_referral: screenPositive ? '1' : '0'
            };

            localStorage.setItem('survey_angina', JSON.stringify(anginaData));
          }
        }
      });
    }

    const savedData = localStorage.getItem('survey_angina');
    if (form && savedData) {
        try {
            const data = JSON.parse(savedData);

            // Restore radio selections
            if (data.q1_chest_discomfort) {
                const q1Radio = form.querySelector(`input[name="q1_chest_discomfort"][value="${data.q1_chest_discomfort}"]`);
                if (q1Radio) q1Radio.checked = true;
            }

            if (data.q2_pain_location_left_arm_neck_back) {
                const q2Radio = form.querySelector(`input[name="q2_pain_location_left_arm_neck_back"][value="${data.q2_pain_location_left_arm_neck_back}"]`);
                if (q2Radio) q2Radio.checked = true;
            }

            if (data.q3_pain_on_exertion) {
                const q3Radio = form.querySelector(`input[name="q3_pain_on_exertion"][value="${data.q3_pain_on_exertion}"]`);
                if (q3Radio) q3Radio.checked = true;
            }

            if (data.q4_pain_relieved_by_rest_or_nitro) {
                const q4Radio = form.querySelector(`input[name="q4_pain_relieved_by_rest_or_nitro"][value="${data.q4_pain_relieved_by_rest_or_nitro}"]`);
                if (q4Radio) q4Radio.checked = true;
            }

            if (data.q5_pain_lasting_10min_plus) {
                const q5Radio = form.querySelector(`input[name="q5_pain_lasting_10min_plus"][value="${data.q5_pain_lasting_10min_plus}"]`);
                if (q5Radio) q5Radio.checked = true;
            }

            if (data.q6_pain_front_of_chest_half_hour) {
                const q6Radio = form.querySelector(`input[name="q6_pain_front_of_chest_half_hour"][value="${data.q6_pain_front_of_chest_half_hour}"]`);
                if (q6Radio) q6Radio.checked = true;
            }

            // Recalculate screening
            calculateScreening();

        } catch (err) {
            console.log('Error loading saved angina data:', err);
        }
    }
    
    // Initialize progress tracker after any saved data is restored (or when there's no draft)
    try { initProgressTracker(); } catch (e) { console.warn('Progress tracker init failed', e); }
  });

  // ============================================
  // Initialize animations
  // ============================================
  initScrollAnimations();

})();

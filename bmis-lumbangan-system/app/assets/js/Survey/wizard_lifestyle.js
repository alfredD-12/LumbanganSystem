(function(){
  const $ = (s,p=document)=>p.querySelector(s);
  const $$ = (s,p=document)=>Array.from(p.querySelectorAll(s));

  // ========== LANGUAGE SWITCHING ==========
  function setLang(lang) {
    localStorage.setItem('survey_language', lang);
    
    // Update all i18n text elements
    $$('.i18n').forEach(el => {
      const key = `data-${lang}`;
      if (el.hasAttribute(key)) {
        el.textContent = el.getAttribute(key);
      }
    });
    
    // Update all i18n placeholder elements
    $$('.i18n-ph').forEach(el => {
      const key = `data-ph-${lang}`;
      if (el.hasAttribute(key)) {
        el.placeholder = el.getAttribute(key);
      }
    });
  }

  // ========== SCROLL ANIMATIONS ==========
  function initScrollAnimations() {
    const revealElements = $$('.section-card');
    
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

  // ========== FORM ENHANCEMENTS ==========
  function enhanceFormInputs() {
    const inputs = $$('.form-control, .form-select, .form-check-input');
    
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement?.classList.add('input-focused');
      });
      
      input.addEventListener('blur', function() {
        this.parentElement?.classList.remove('input-focused');
      });
      
      input.addEventListener('invalid', function(e) {
        e.preventDefault();
        this.classList.add('is-invalid');
        
        setTimeout(() => {
          this.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
      });
      
      input.addEventListener('input', function() {
        if (this.classList.contains('is-invalid') && this.checkValidity()) {
          this.classList.remove('is-invalid');
        }
      });
    });
  }

  // ========== RANGE INPUT DISPLAY ==========
  function initRangeInputs() {
    const exerciseDays = $('#exercise-days');
    const exerciseDaysValue = $('#exercise-days-value');
    const exerciseMinutes = $('#exercise-minutes');
    const exerciseMinutesValue = $('#exercise-minutes-value');

    if (exerciseDays && exerciseDaysValue) {
      exerciseDays.addEventListener('input', function() {
        exerciseDaysValue.textContent = this.value;
      });
    }

    if (exerciseMinutes && exerciseMinutesValue) {
      exerciseMinutes.addEventListener('input', function() {
        exerciseMinutesValue.textContent = this.value;
      });
    }
  }

  // ========== FORM SUBMISSION ==========
  function handleFormSubmit() {
    const form = $('#form-lifestyle');
    if (!form) return;
    form.addEventListener('submit', function(e) {
      // Validate only. If invalid prevent submission so user can fix fields.
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.classList.add('was-validated');
        const firstInvalid = $('.form-control:invalid, .form-check-input:invalid', form);
        if (firstInvalid) {
          firstInvalid.focus();
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }
      // If valid, allow the centralized save handler to perform the POST and show the single centered alert.
    });
  }

  // ========== FORM PROGRESS TRACKER (section + overall) ==========
  function initProgressTracker() {
    const form = document.getElementById('form-lifestyle');
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
        if (el.type === 'radio') {
          return !!form.querySelector(`input[name="${el.name}"]:checked`);
        }
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

      // Update any progress bar if exists
      const progressBar = document.querySelector('.form-progress-bar');
      if (progressBar) {
        progressBar.style.width = `${progress}%`;
        progressBar.style.transition = 'width 0.4s ease';
      }

      // Update section progress indicators
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

  // Add section complete styling (if not present)
  (function addProgressStyle(){
    const id = 'survey-lifestyle-progress-style'; if (document.getElementById(id)) return;
    const progressStyle = document.createElement('style'); progressStyle.id = id;
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
    @keyframes scaleIn { from { transform: scale(0) rotate(-180deg); opacity: 0; } to { transform: scale(1) rotate(0deg); opacity: 1; } }
    .form-progress-bar { height: 4px; background: linear-gradient(90deg, #1e3a5f, #c53030); position: fixed; top: 0; left: 0; z-index: 9999; box-shadow: 0 2px 10px rgba(30, 58, 95, 0.3); }
    `;
    document.head.appendChild(progressStyle);
  })();

  // ========== SMOOTH SCROLLING ==========
  function initSmoothScrolling() {
    $$('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#') return;
        
        e.preventDefault();
        const target = $(href);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  }

  // ========== INITIALIZATION ==========
  function initialize() {
    const langEn = $('#lang-en');
    const langTl = $('#lang-tl');
    const savedLang = localStorage.getItem('survey_language');
    
    // Restore language preference
    if (savedLang === 'tl' && langTl) {
      langTl.checked = true;
      setLang('tl');
    } else if (savedLang === 'en' && langEn) {
      langEn.checked = true;
      setLang('en');
    } else {
      if (langEn) langEn.checked = true;
      setLang('en');
    }
    
    // Set up language toggle listeners
    langEn?.addEventListener('change', () => {
      if (langEn.checked) setLang('en');
    });
    langTl?.addEventListener('change', () => {
      if (langTl.checked) setLang('tl');
    });
    
    // Initialize other features
    initScrollAnimations();
    enhanceFormInputs();
    initRangeInputs();
    handleFormSubmit();
    try { initProgressTracker(); } catch(e){}
    initSmoothScrolling();
  }

  // Run initialization when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }

})();

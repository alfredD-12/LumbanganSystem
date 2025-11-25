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
    const revealElements = $$('.vital-card, .alert');
    
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
    const inputs = $$('.form-control');
    
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

  // ========== VITAL SIGNS VALIDATION ==========
  function validateVitalSigns() {
    const bpSystolic = $('input[name="bp_systolic"]');
    const bpDiastolic = $('input[name="bp_diastolic"]');
    const pulse = $('input[name="pulse"]');
    const respiratory = $('input[name="respiratory_rate"]');
    const temperature = $('input[name="temperature_c"]');

    // Blood Pressure Warning
    if (bpSystolic && bpDiastolic) {
      [bpSystolic, bpDiastolic].forEach(input => {
        input.addEventListener('blur', function() {
          const systolic = parseFloat(bpSystolic.value);
          const diastolic = parseFloat(bpDiastolic.value);
          
          if (systolic && diastolic) {
            if (systolic >= 140 || diastolic >= 90) {
              showWarning(this, 'High blood pressure detected. Please consult a healthcare provider.', 'Mataas ang presyon ng dugo. Kumonsulta sa doktor.');
            } else if (systolic < 90 || diastolic < 60) {
              showWarning(this, 'Low blood pressure detected. Please consult a healthcare provider.', 'Mababa ang presyon ng dugo. Kumonsulta sa doktor.');
            } else {
              clearWarning(this);
            }
          }
        });
      });
    }

    // Pulse Warning
    if (pulse) {
      pulse.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (value) {
          if (value > 100) {
            showWarning(this, 'High pulse rate detected. Please consult a healthcare provider.', 'Mataas ang pulso. Kumonsulta sa doktor.');
          } else if (value < 60) {
            showWarning(this, 'Low pulse rate detected. Please consult a healthcare provider.', 'Mababa ang pulso. Kumonsulta sa doktor.');
          } else {
            clearWarning(this);
          }
        }
      });
    }

    // Respiratory Rate Warning
    if (respiratory) {
      respiratory.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (value) {
          if (value > 20) {
            showWarning(this, 'High respiratory rate. Please consult a healthcare provider.', 'Mataas ang respiratory rate. Kumonsulta sa doktor.');
          } else if (value < 12) {
            showWarning(this, 'Low respiratory rate. Please consult a healthcare provider.', 'Mababa ang respiratory rate. Kumonsulta sa doktor.');
          } else {
            clearWarning(this);
          }
        }
      });
    }

    // Temperature Warning
    if (temperature) {
      temperature.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (value) {
          if (value >= 37.5) {
            showWarning(this, 'Elevated temperature detected (fever). Please consult a healthcare provider.', 'May lagnat. Kumonsulta sa doktor.');
          } else if (value < 36.1) {
            showWarning(this, 'Low temperature detected. Please consult a healthcare provider.', 'Mababa ang temperatura. Kumonsulta sa doktor.');
          } else {
            clearWarning(this);
          }
        }
      });
    }
  }

  function showWarning(input, messageEn, messageTl) {
    const savedLang = localStorage.getItem('survey_language') || 'en';
    const message = savedLang === 'tl' ? messageTl : messageEn;
    
    clearWarning(input);
    
    const warning = document.createElement('div');
    warning.className = 'alert alert-warning alert-dismissible fade show mt-2 vital-warning';
    warning.style.fontSize = '0.875rem';
    warning.innerHTML = `
      <i class="fa-solid fa-triangle-exclamation me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const vitalCard = input.closest('.vital-card');
    if (vitalCard) {
      vitalCard.appendChild(warning);
    }
  }

  function clearWarning(input) {
    const vitalCard = input.closest('.vital-card');
    if (vitalCard) {
      const existingWarning = vitalCard.querySelector('.vital-warning');
      if (existingWarning) {
        existingWarning.remove();
      }
    }
  }

  // ========== FORM SUBMISSION ==========
  function handleFormSubmit() {
    const form = $('#form-vitals');
    if (!form) return;
    form.addEventListener('submit', function(e) {
      // Validate only. If invalid prevent submission so the user can fix fields.
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.classList.add('was-validated');
        const firstInvalid = $('.form-control:invalid', form);
        if (firstInvalid) {
          firstInvalid.focus();
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }
      // If valid, allow the event to continue so the centralized save handler
      // (save-survey.js) can perform the POST and show the single centered alert.
    });
  }

  // ========== WIZARD STEP PROGRESS ==========
  function getAllSteps() {
    return Array.from(document.querySelectorAll('.wizard-step'));
  }

  function getStepByKey(key) {
    return document.querySelector(`.wizard-step[data-key="${key}"]`);
  }

  // Step-level completion visuals are intentionally disabled.
  // Card-level completion and the top progress bar remain active.

  // Mark individual vital cards as completed when their contained inputs are valid
  function wireCardCompletion() {
    const cards = Array.from(document.querySelectorAll('.vital-card'));
    if (!cards || cards.length === 0) return;

    function checkCard(card) {
      // find form controls inside the card
      const inputs = Array.from(card.querySelectorAll('input, select, textarea')).filter(i => i.type !== 'hidden');
      if (inputs.length === 0) return false;
      // card is complete only if all controls that are required are valid (or if none required then valid if any has value)
      const requiredInputs = inputs.filter(i => i.hasAttribute('required'));
      if (requiredInputs.length > 0) {
        return requiredInputs.every(i => i.checkValidity());
      }
      // if no required fields, consider it complete if at least one input has a non-empty value
      return inputs.some(i => String(i.value || '').trim() !== '');
    }

    function updateAll() {
      cards.forEach(card => {
        try {
          const ok = checkCard(card);
          const changed = card.classList.toggle('completed', ok);
          // add/remove badge
          let badge = card.querySelector('.complete-badge');
          if (ok && !badge) {
            badge = document.createElement('div'); badge.className = 'complete-badge'; badge.innerHTML = '<i class="fa-solid fa-check"></i>';
            card.appendChild(badge);
          } else if (!ok && badge) {
            badge.remove();
          }
        } catch (e) { /* ignore per-card */ }
      });
    }

    // initial
    updateAll();
    // watch inputs within the form (debounced)
    let tt;
    const form = document.getElementById('form-vitals');
    if (!form) return;
    form.addEventListener('input', function(){ clearTimeout(tt); tt = setTimeout(updateAll, 200); }, { capture: true });
    form.addEventListener('change', function(){ clearTimeout(tt); tt = setTimeout(updateAll, 200); }, { capture: true });
  }

  // ========== FORM PROGRESS TRACKER (section + overall) ==========
  function initProgressTracker() {
    const form = document.getElementById('form-vitals');
    if (!form) return;

    // Build a de-duplicated list of form "fields" where radio groups count as one field
    const allElements = Array.from(form.elements).filter(el =>
      (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') &&
      el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button' &&
      !el.hasAttribute('data-optional')
    );
    const seenRadioNames = new Set();
    const allInputs = [];
    allElements.forEach(el => {
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
        if (el.type === 'checkbox') {
          return el.checked;
        }
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

  // Add section complete styling (if not already present)
  (function addProgressStyle(){
    const id = 'survey-vitals-progress-style'; if (document.getElementById(id)) return;
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
    @keyframes scaleIn {
      from { transform: scale(0) rotate(-180deg); opacity: 0; }
      to { transform: scale(1) rotate(0deg); opacity: 1; }
    }
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
    validateVitalSigns();
    handleFormSubmit();
    // wizard progress helpers: step-level completed checks disabled — keep card-level only
    // mark per-card completion
    try { wireCardCompletion(); } catch(e){}

    // init progress tracker and top progress bar (if not present)
    try { initProgressTracker(); } catch(e){}
    try {
      if (!document.querySelector('.form-progress-bar')) {
        const progressBar = document.createElement('div');
        progressBar.className = 'form-progress-bar';
        progressBar.style.width = '0%';
        document.body.insertBefore(progressBar, document.body.firstChild);
      }
    } catch(e) {}
    initSmoothScrolling();
  }

  // Run initialization when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }

  

})();

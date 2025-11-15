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
    initSmoothScrolling();
  }

  // Run initialization when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }

})();

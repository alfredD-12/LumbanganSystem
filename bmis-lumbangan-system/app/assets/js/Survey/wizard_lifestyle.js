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
    initSmoothScrolling();
  }

  // Run initialization when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
  } else {
    initialize();
  }

})();

(function(){
  const $ = (s,p=document)=>p.querySelector(s);
  const $$ = (s,p=document)=>Array.from(p.querySelectorAll(s));

  // ========== SCROLL ANIMATIONS ==========
  function initScrollAnimations() {
    const revealElements = $$('.section-card, .health-checkbox');
    
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
          }, index * 50); // Faster stagger for checkboxes
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

  // ========== CHECKBOX INTERACTIONS ==========
  function enhanceCheckboxes() {
    const checkboxes = $$('.health-checkbox input[type="checkbox"]');
    const noneCheckbox = $('#none');
    const regularCheckboxes = checkboxes.filter(cb => cb !== noneCheckbox);
    
    checkboxes.forEach(checkbox => {
      // Update parent styling on change
      checkbox.addEventListener('change', function() {
        const parent = this.closest('.health-checkbox');
        if (this.checked) {
          parent.classList.add('checked');
          parent.style.animation = 'checkPulse 0.4s ease';
        } else {
          parent.classList.remove('checked');
        }
        
        // Handle "None" logic
        if (this === noneCheckbox && this.checked) {
          // Uncheck all others
          regularCheckboxes.forEach(cb => {
            cb.checked = false;
            cb.closest('.health-checkbox').classList.remove('checked');
          });
        } else if (this !== noneCheckbox && this.checked && noneCheckbox) {
          // Uncheck "None" when any condition is selected
          noneCheckbox.checked = false;
          noneCheckbox.closest('.health-checkbox').classList.remove('checked');
        }
        
        updateProgress();
      });
      
      // Initialize state
      if (checkbox.checked) {
        checkbox.closest('.health-checkbox').classList.add('checked');
      }
    });
  }
  
  // Add checkbox animation
  const checkboxStyle = document.createElement('style');
  checkboxStyle.textContent = `
    @keyframes checkPulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.02); }
      100% { transform: scale(1); }
    }
  `;
  document.head.appendChild(checkboxStyle);

  // ========== SMOOTH CHECKBOX FOCUS ==========
  function enhanceCheckboxFocus() {
    const checkboxes = $$('.health-checkbox input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('focus', function() {
        this.closest('.health-checkbox').style.boxShadow = '0 0 0 4px rgba(30, 58, 95, 0.1)';
      });
      
      checkbox.addEventListener('blur', function() {
        this.closest('.health-checkbox').style.boxShadow = '';
      });
    });
  }

  // ========== BUTTON RIPPLE EFFECT ==========
  function addRippleEffect() {
    const buttons = $$('.btn');
    
    buttons.forEach(button => {
      button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
          position: absolute;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.6);
          width: ${size}px;
          height: ${size}px;
          left: ${x}px;
          top: ${y}px;
          pointer-events: none;
          transform: scale(0);
          animation: ripple 0.6s ease-out;
        `;
        
        this.style.position = 'relative';
        this.style.overflow = 'hidden';
        this.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
      });
    });
  }

  const rippleStyle = document.createElement('style');
  rippleStyle.textContent = `
    @keyframes ripple {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(rippleStyle);

  // ========== LANGUAGE TOGGLE ==========
  function setLang(lang){
    // Save language preference to localStorage
    localStorage.setItem('survey_language', lang);
    
    $$('.i18n').forEach(el=>{
      const txt = el.dataset[lang];
      if (typeof txt !== 'undefined') {
        el.style.opacity = '0';
        setTimeout(() => {
          el.textContent = txt;
          el.style.opacity = '1';
        }, 150);
      }
    });
  }
  
  const langEn = $('#lang-en');
  const langTl = $('#lang-tl');
  
  // Restore saved language preference on page load
  const savedLang = localStorage.getItem('survey_language');
  if (savedLang === 'tl' && langTl) {
    langTl.checked = true;
    setLang('tl');
  } else if (savedLang === 'en' && langEn) {
    langEn.checked = true;
    setLang('en');
  } else {
    // Default to English if no saved preference
    if (langEn) langEn.checked = true;
    setLang('en');
  }
  
  if (langEn) {
    langEn.addEventListener('change', () => {
      setLang('en');
      langEn.parentElement?.classList.add('lang-switch-animation');
      setTimeout(() => langEn.parentElement?.classList.remove('lang-switch-animation'), 300);
    });
  }
  
  if (langTl) {
    langTl.addEventListener('change', () => {
      setLang('tl');
      langTl.parentElement?.classList.add('lang-switch-animation');
      setTimeout(() => langTl.parentElement?.classList.remove('lang-switch-animation'), 300);
    });
  }

  // ========== FORM VALIDATION ==========
  const form = $('#form-family-history');
  
  function validate(){
    if (!form) return true; // No required fields, so always valid
    
    // Check if at least one checkbox is selected
    const anyChecked = Array.from(form.querySelectorAll('input[type="checkbox"]')).some(cb => cb.checked);
    
    if (!anyChecked) {
      const lang = langTl?.checked ? 'tl' : 'en';
      toast(
        lang === 'tl' 
          ? 'Mangyaring pumili ng kahit isang kondisyon o "Wala sa nabanggit".' 
          : 'Please select at least one condition or "None of the above".',
        'warning'
      );
      return false;
    }
    
    return true;
  }

  // ========== MODERN TOAST NOTIFICATIONS ==========
  function toast(msg, type='success'){
    // Prefer global alert when available to avoid duplicates
    if (window.surveyCreateAlert) {
      window.surveyCreateAlert(msg, type);
      return;
    }

    const icons = {
      success: '✓',
      warning: '⚠',
      danger: '✕',
      info: 'ℹ'
    };
    
    const el = document.createElement('div');
    el.className = `alert alert-${type} position-fixed top-0 start-50 translate-middle-x mt-3 shadow-lg`;
    el.style.cssText = `
      z-index: 1080;
      min-width: 300px;
      backdrop-filter: blur(20px);
      border: none;
      border-radius: 12px;
      animation: slideDown 0.4s ease, fadeOut 0.3s ease 2s forwards;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 500;
    `;
    
    const icon = document.createElement('span');
    icon.style.cssText = `
      font-size: 1.5rem;
      font-weight: bold;
    `;
    icon.textContent = icons[type] || icons.info;
    
    const text = document.createElement('span');
    text.textContent = msg;
    
    el.appendChild(icon);
    el.appendChild(text);
    document.body.appendChild(el);
    setTimeout(()=> el.remove(), 2400);
  }
  
  const toastStyle = document.createElement('style');
  toastStyle.textContent = `
    @keyframes slideDown {
      from {
        transform: translate(-50%, -100%);
        opacity: 0;
      }
      to {
        transform: translate(-50%, 0);
        opacity: 1;
      }
    }
    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translate(-50%, -20px);
      }
    }
  `;
  document.head.appendChild(toastStyle);

  // ========== SAVE BUTTON WITH LOADING STATE ==========
  const btnSave = $('#btn-dummy-save');
  if (btnSave) {
    btnSave.addEventListener('click', (e)=>{
      e.preventDefault();

      if (!validate()){
        return;
      }

      // Show loading state and then submit form so central handler takes over
      const originalText = btnSave.innerHTML;
      btnSave.disabled = true;
      const lang = langTl?.checked ? 'tl' : 'en';
      btnSave.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${lang === 'tl' ? 'Sine-save...' : 'Saving...'}`;

      const f = form || document.getElementById('form-family-history');
      try {
        if (f.requestSubmit) {
          f.requestSubmit();
        } else {
          f.dispatchEvent(new Event('submit', { cancelable: true }));
        }
      } catch (err) {
        console.error('Error submitting family history form programmatically', err);
        btnSave.disabled = false;
        btnSave.innerHTML = originalText;
      }
    });
  }

  // ========== FORM PROGRESS TRACKER ==========
  function updateProgress() {
    const checkboxes = Array.from(form?.querySelectorAll('input[type="checkbox"]') || []);
    const checkedCount = checkboxes.filter(cb => cb.checked).length;
    const progress = checkedCount > 0 ? 100 : 0; // Binary: either completed or not
    
    const progressBar = $('.form-progress-bar');
    if (progressBar) {
      progressBar.style.width = `${progress}%`;
      progressBar.style.transition = 'width 0.4s ease';
    }
    
    // Update section card styling
    const sectionCard = $('.section-card');
    if (checkedCount > 0 && sectionCard) {
      sectionCard.classList.add('section-complete');
    } else if (sectionCard) {
      sectionCard.classList.remove('section-complete');
    }
  }
  
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
      background: linear-gradient(90deg, #1e3a5f, #10b981);
      position: fixed;
      top: 0;
      left: 0;
      z-index: 9999;
      box-shadow: 0 2px 10px rgba(30, 58, 95, 0.3);
    }
  `;
  document.head.appendChild(progressStyle);

  // ========== KEYBOARD NAVIGATION ==========
  function enhanceKeyboardNav() {
    const checkboxWrappers = $$('.health-checkbox');
    
    checkboxWrappers.forEach(wrapper => {
      wrapper.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const checkbox = this.querySelector('input[type="checkbox"]');
          if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
          }
        }
      });
      
      // Make clickable area larger
      wrapper.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT') {
          const checkbox = this.querySelector('input[type="checkbox"]');
          if (checkbox) {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
          }
        }
      });
    });
  }

  // ========== INITIALIZE ALL ENHANCEMENTS ==========
  function initialize() {
    initScrollAnimations();
    enhanceCheckboxes();
    enhanceCheckboxFocus();
    addRippleEffect();
    enhanceKeyboardNav();
    updateProgress();
    
    // Add progress bar to body
    const progressBar = document.createElement('div');
    progressBar.className = 'form-progress-bar';
    progressBar.style.width = '0%';
    document.body.insertBefore(progressBar, document.body.firstChild);
    
    // Add entrance animation to form
    if (form) {
      form.style.opacity = '0';
      form.style.transform = 'translateY(20px)';
      setTimeout(() => {
        form.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
        form.style.opacity = '1';
        form.style.transform = 'translateY(0)';
      }, 100);
    }
  }
  
  document.addEventListener('DOMContentLoaded', initialize);
  
  // Initialize immediately if DOM already loaded
  if (document.readyState !== 'loading') {
    initialize();
  }
})();

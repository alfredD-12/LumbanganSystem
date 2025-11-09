(function(){
  console.log('wizard_personal.js loaded');
  const $ = (s,p=document)=>p.querySelector(s);
  const $$ = (s,p=document)=>Array.from(p.querySelectorAll(s));

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
          }, index * 100); // Stagger animation
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

  // ========== SMOOTH FORM INTERACTIONS ==========
  function enhanceFormInputs() {
    const inputs = $$('.form-control, .form-select');
    
    inputs.forEach(input => {
      // Add focus ripple effect
      input.addEventListener('focus', function() {
        this.parentElement?.classList.add('input-focused');
      });
      
      input.addEventListener('blur', function() {
        this.parentElement?.classList.remove('input-focused');
      });
      
      // Add smooth validation feedback
      input.addEventListener('invalid', function(e) {
        e.preventDefault();
        this.classList.add('is-invalid');
        setTimeout(() => {
          this.classList.remove('is-invalid');
        }, 2000);
      });
      
      // Real-time validation styling
      input.addEventListener('input', function() {
        if (this.value && this.checkValidity()) {
          this.classList.add('is-valid');
          this.classList.remove('is-invalid');
        } else if (this.value) {
          this.classList.remove('is-valid');
        }
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

  // Add ripple animation keyframes
  const style = document.createElement('style');
  style.textContent = `
    @keyframes ripple {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }
    .input-focused {
      transform: translateY(-2px);
      transition: transform 0.3s ease;
    }
  `;
  document.head.appendChild(style);

  // ========== LANGUAGE TOGGLE ==========
  function setLang(lang){
    // Save language preference to localStorage
    console.log('setLang called with:', lang);
    localStorage.setItem('survey_language', lang);
    console.log('Saved to localStorage:', localStorage.getItem('survey_language'));
    
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
    $$('.i18n-ph').forEach(el=>{
      const attr = el.getAttribute('data-'+(lang==='en'?'ph-en':'ph-tl'));
      if (attr) el.placeholder = attr;
    });
  }
  
  // ========== DATE PICKER + AGE CALCULATOR ==========
  const ageDisplay = $('#age_display');
  const birthdateInput = $('#birthdate');
  
  if (birthdateInput) {
    flatpickr('#birthdate', {
      altInput: true,
      altFormat: 'F j, Y',
      dateFormat: 'Y-m-d',
      maxDate: 'today',
      allowInput: true,
      onChange: calcAge,
      onReady: calcAge,
      onOpen: function() {
        birthdateInput.parentElement?.classList.add('flatpickr-open');
      },
      onClose: function() {
        birthdateInput.parentElement?.classList.remove('flatpickr-open');
      }
    });
  }
  
  function calcAge(){
    const v = birthdateInput?.value || '';
    if (!v){ 
      if (ageDisplay) {
        ageDisplay.value = '';
        ageDisplay.classList.remove('age-calculated');
      }
      return; 
    }
    const dob = new Date(v);
    if (isNaN(dob.getTime())) { 
      if (ageDisplay) ageDisplay.value = '';
      return; 
    }
    const t = new Date();
    let a = t.getFullYear() - dob.getFullYear();
    const m = t.getMonth() - dob.getMonth();
    if (m < 0 || (m===0 && t.getDate() < dob.getDate())) a--;
    
    if (ageDisplay && isFinite(a)) {
      ageDisplay.value = a;
      ageDisplay.classList.add('age-calculated');
      // Add pulse animation
      ageDisplay.style.animation = 'none';
      setTimeout(() => {
        ageDisplay.style.animation = 'pulse 0.5s ease';
      }, 10);
    }
  }
  
  // Add flatpickr styles
  const flatpickrStyle = document.createElement('style');
  flatpickrStyle.textContent = `
    .flatpickr-open {
      transform: translateY(-2px);
      transition: transform 0.3s ease;
    }
    .age-calculated {
      background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
      font-weight: 600;
      color: #166534;
    }
  `;
  document.head.appendChild(flatpickrStyle);

  // Contact number mask: 4-4-3 => 0921-3123-123
  const contact = $('#contact_no');
  function format443(d){
    d = d.replace(/\D/g,'').slice(0,11); // keep 11 digits max
    if (!d) return '';
    const a = d.slice(0,4);
    const b = d.slice(4,8);
    const c = d.slice(8,11);
    return [a,b,c].filter(Boolean).join('-');
  }
  function onContactInput(e){
    const formatted = format443(e.target.value);
    e.target.value = formatted;
    const len = formatted.length;
    e.target.setSelectionRange(len, len);
  }
  function onContactPaste(e){
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text') || '';
    contact.value = format443(text);
  }
  if (contact){
    contact.addEventListener('input', onContactInput);
    contact.addEventListener('paste', onContactPaste);
    contact.addEventListener('keypress', (e)=> { if (!/[0-9]/.test(e.key)) e.preventDefault(); });
  }

  // ========== FORM VALIDATION ==========
  const form = $('#form-person');
  
  function validate(){
    if (!form) return false;
    
    // Smooth scroll to first invalid field
    const firstInvalid = form.querySelector(':invalid');
    if (firstInvalid) {
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      setTimeout(() => {
        firstInvalid.focus();
        firstInvalid.classList.add('shake-animation');
        setTimeout(() => firstInvalid.classList.remove('shake-animation'), 500);
      }, 300);
    }
    
    if (!form.checkValidity()){
      form.classList.add('was-validated');
      return false;
    }
    if (contact && contact.value && !/^09\d{2}-\d{4}-\d{3}$/.test(contact.value)){
      contact.setCustomValidity('Invalid');
      form.classList.add('was-validated');
      return false;
    } else if (contact){ contact.setCustomValidity(''); }
    return true;
  }

  // Add shake animation
  const shakeStyle = document.createElement('style');
  shakeStyle.textContent = `
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
      20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    .shake-animation {
      animation: shake 0.5s;
    }
  `;
  document.head.appendChild(shakeStyle);

  // ========== MODERN TOAST NOTIFICATIONS ==========
  function toast(msg, type='success'){
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
        const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
        toast(lang==='tl' ? 'Ayusin ang mga may marka.' : 'Please fix highlighted fields.', 'warning');
        return;
      }
      
      // Show loading state
      const originalText = btnSave.innerHTML;
      btnSave.disabled = true;
      const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
      btnSave.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${lang === 'tl' ? 'Sine-save...' : 'Saving...'}`;
      
      // Simulate save delay then navigate to vitals
      setTimeout(() => {
        toast(lang==='tl' ? 'Nai-save ang personal na impormasyon!' : 'Personal information saved!');
        setTimeout(() => {
          window.location.href = 'wizard_vitals.php';
        }, 800);
      }, 1000);
    });
  }

  // ========== CANCEL BUTTON WITH CONFIRMATION ==========
  const btnCancel = $('#btn-cancel');
  if (btnCancel) {
    btnCancel.addEventListener('click', (e)=>{
      e.preventDefault();
      
      // Check if form has data
      const hasData = Array.from(form?.elements || []).some(el => 
        (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') && el.value
      );
      
      if (hasData) {
        const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
        const confirmMsg = lang === 'tl' 
          ? 'Sigurado ka bang gusto mong burahin ang lahat ng input?'
          : 'Are you sure you want to clear all inputs?';
        
        if (!confirm(confirmMsg)) return;
      }
      
      form?.reset();
      const bd = $('#birthdate'); 
      if (bd) bd.value='';
      calcAge();
      if (contact) contact.value='';
      
      // Add reset animation
      form?.classList.add('form-reset-animation');
      setTimeout(() => form?.classList.remove('form-reset-animation'), 500);
      
      const lang = $('#lang-tl')?.checked ? 'tl' : 'en';
      toast(lang === 'tl' ? 'Binura ang lahat.' : 'Form cleared.', 'info');
    });
  }
  
  const resetStyle = document.createElement('style');
  resetStyle.textContent = `
    .form-reset-animation {
      animation: fadeOut 0.3s ease, fadeIn 0.3s ease 0.3s;
    }
  `;
  document.head.appendChild(resetStyle);

  // ========== FORM PROGRESS TRACKER ==========
  function initProgressTracker() {
    if (!form) return;
    
    const allInputs = Array.from(form.elements).filter(el => 
      (el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') &&
      el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button'
    );
    
    function updateProgress() {
      const filledInputs = allInputs.filter(el => {
        if (el.type === 'checkbox' || el.type === 'radio') {
          return el.checked;
        }
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
        const sectionInputs = Array.from(section.querySelectorAll('input, select, textarea')).filter(el =>
          el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button'
        );
        
        const filledInSection = sectionInputs.filter(el => {
          if (el.type === 'checkbox' || el.type === 'radio') {
            return el.checked;
          }
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
  
  // ========== SMOOTH SECTION TRANSITIONS ==========
  function initSectionTransitions() {
    const sections = $$('.section-card');
    
    sections.forEach((section, index) => {
      section.style.animationDelay = `${index * 0.1}s`;
      
      // Add hover sound effect (visual feedback)
      section.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-4px) scale(1.01)';
      });
      
      section.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
      });
    });
  }

  // ========== INITIALIZE ALL ENHANCEMENTS ==========
  function initialize() {
    // Initialize language preference FIRST
    const langEn = $('#lang-en');
    const langTl = $('#lang-tl');
    const savedLang = localStorage.getItem('survey_language');
    
    console.log('Initialize called. Saved language:', savedLang);
    console.log('langEn:', langEn, 'langTl:', langTl);
    
    if (savedLang === 'tl' && langTl) {
      console.log('Setting to Filipino');
      langTl.checked = true;
      setLang('tl');
    } else if (savedLang === 'en' && langEn) {
      console.log('Setting to English (saved)');
      langEn.checked = true;
      setLang('en');
    } else {
      console.log('Setting to English (default)');
      // Default to English if no saved preference
      if (langEn) langEn.checked = true;
      setLang('en');
    }
    
    // Set up language toggle event listeners
    langEn?.addEventListener('change', () => {
      setLang('en');
      langEn.parentElement?.classList.add('lang-switch-animation');
      setTimeout(() => langEn.parentElement?.classList.remove('lang-switch-animation'), 300);
    });
    
    langTl?.addEventListener('change', () => {
      setLang('tl');
      langTl.parentElement?.classList.add('lang-switch-animation');
      setTimeout(() => langTl.parentElement?.classList.remove('lang-switch-animation'), 300);
    });
    
    initScrollAnimations();
    enhanceFormInputs();
    addRippleEffect();
    initProgressTracker();
    initSectionTransitions();
    
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
  
  console.log('Setting up DOMContentLoaded listener');
  document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded fired, calling initialize');
    initialize();
  });
  
  // Initialize immediately if DOM already loaded
  console.log('Document ready state:', document.readyState);
  if (document.readyState !== 'loading') {
    console.log('DOM already loaded, calling initialize immediately');
    initialize();
  }
})();
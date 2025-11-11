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
  const questionInputs = form.querySelectorAll('input[type="radio"]');

  // Listen for changes to calculate screening results
  questionInputs.forEach(input => {
    input.addEventListener('change', calculateScreening);
  });

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
    // Remove any existing message
    const existingMessage = document.querySelector('.screening-result-message');
    if (existingMessage) {
      existingMessage.remove();
    }

    if (screenPositive && needsReferral) {
      const lang = localStorage.getItem('survey_language') || 'en';
      
      const messageDiv = document.createElement('div');
      messageDiv.className = 'alert alert-danger d-flex align-items-start gap-3 mb-4 screening-result-message';
      messageDiv.style.cssText = 'border-radius: 12px; border-left: 4px solid #dc3545;';
      
      const icon = document.createElement('i');
      icon.className = 'fa-solid fa-exclamation-triangle fs-4 text-danger';
      
      const contentDiv = document.createElement('div');
      
      const title = document.createElement('strong');
      title.textContent = lang === 'tl' ? 'Kinakailangan ang Konsultasyon sa Doktor' : 'Doctor Consultation Required';
      
      const message = document.createElement('p');
      message.className = 'mb-0 small';
      message.textContent = lang === 'tl' 
        ? 'Batay sa iyong mga sagot, inirerekomenda namin na kumonsulta sa isang healthcare professional sa lalong madaling panahon. Ang mga sintomas na ito ay maaaring magpahiwatig ng isang seryosong kondisyon sa puso na nangangailangan ng medikal na atensyon.'
        : 'Based on your answers, we recommend consulting with a healthcare professional as soon as possible. These symptoms may indicate a serious heart condition that requires medical attention.';
      
      contentDiv.appendChild(title);
      contentDiv.appendChild(message);
      
      messageDiv.appendChild(icon);
      messageDiv.appendChild(contentDiv);
      
      // Insert after the info alert
      const infoAlert = document.querySelector('.alert-info');
      if (infoAlert) {
        infoAlert.parentNode.insertBefore(messageDiv, infoAlert.nextSibling);
      }
    }
  }

  // ============================================
  // 3) Form Submission
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
    
    const q1 = formData.get('q1_chest_discomfort');
    const q2 = formData.get('q2_pain_location_left_arm_neck_back');
    const q3 = formData.get('q3_pain_on_exertion');
    const q4 = formData.get('q4_pain_relieved_by_rest_or_nitro');
    const q5 = formData.get('q5_pain_lasting_10min_plus');
    const q6 = formData.get('q6_pain_front_of_chest_half_hour');

    // Calculate screening results
    const hasQ1 = q1 === '1';
    const hasQ2 = q2 === '1';
    const hasQ3 = q3 === '1';
    const hasQ4 = q4 === '1';
    const hasQ5 = q5 === '1';
    const hasQ6 = q6 === '1';

    const screenPositive = (hasQ1 && hasQ2 && hasQ3 && hasQ4) || hasQ6;
    const needsReferral = screenPositive;

    // Prepare data object
    const anginaData = {
      q1_chest_discomfort: q1,
      q2_pain_location_left_arm_neck_back: q2,
      q3_pain_on_exertion: q3,
      q4_pain_relieved_by_rest_or_nitro: q4,
      q5_pain_lasting_10min_plus: q5,
      q6_pain_front_of_chest_half_hour: q6,
      screen_positive: screenPositive ? '1' : '0',
      needs_doctor_referral: needsReferral ? '1' : '0'
    };

    // Save to localStorage
    localStorage.setItem('survey_angina', JSON.stringify(anginaData));

    console.log('Angina data saved:', anginaData);

    // Show success message
    const lang = localStorage.getItem('survey_language') || 'en';
    const successMsg = lang === 'tl' ? 'Nai-save na!' : 'Saved!';
    
    // Optional: Show a toast/alert
    // For now, just log and navigate
    console.log(successMsg);

    // Navigate to next step (Diabetes)
    window.location.href = 'wizard_diabetes.php';
  });

  // ============================================
  // 4) Auto-save on visibility change
  // ============================================
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
        const needsReferral = screenPositive;

        const anginaData = {
          q1_chest_discomfort: q1,
          q2_pain_location_left_arm_neck_back: q2,
          q3_pain_on_exertion: q3,
          q4_pain_relieved_by_rest_or_nitro: q4,
          q5_pain_lasting_10min_plus: q5,
          q6_pain_front_of_chest_half_hour: q6,
          screen_positive: screenPositive ? '1' : '0',
          needs_doctor_referral: needsReferral ? '1' : '0'
        };

        localStorage.setItem('survey_angina', JSON.stringify(anginaData));
        console.log('Auto-saved angina data');
      }
    }
  });

  // ============================================
  // 5) Load saved data on page load
  // ============================================
  window.addEventListener('DOMContentLoaded', function() {
    const savedData = localStorage.getItem('survey_angina');
    if (savedData) {
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

        console.log('Loaded saved angina data');
      } catch (err) {
        console.log('Error loading saved angina data:', err);
      }
    }
  });

  // ============================================
  // Initialize animations
  // ============================================
  initScrollAnimations();

})();

// app/assets/js/Survey/bhw-float-control.js
// Controls visibility and one-time intro behavior for the floating "i" (BHW) button.
// - Button is shown only on a small set of survey pages (vitals, history, lifestyle, angina, diabetes).
// - On the personal page, the informational modal is shown once (first time entering the survey).
// - The script is resilient to being loaded on pages that don't include the button or modal.

(function () {
  'use strict';

  // Pages where the floating info button should be visible
  var PAGES_WITH_BUTTON = [
    'wizard_vitals.php',
    'wizard_family_history.php',
    'wizard_lifestyle.php',
    'wizard_angina.php',
    'wizard_diabetes.php'
  ];

  // The personal view should show the modal at least once on first visit
  var PERSONAL_PAGE = 'wizard_personal.php';
  var INTRO_FLAG_KEY = 'survey_intro_shown';

  // Helper: last path segment (filename)
  function currentPage() {
    try {
      var parts = window.location.pathname.split('/');
      return parts[parts.length - 1] || '';
    } catch (e) {
      return '';
    }
  }

  // DOM helpers
  var btn = document.getElementById('bhwFloatBtn');
  var hideBtnInner = document.getElementById('bhwFloatHide'); // close '×' inside button (if present)
  var modalEl = document.getElementById('bhwInfoModal');
  var modalInstance = modalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;

  function showButton() {
    if (!btn) return;
    btn.classList.remove('hidden');
    btn.style.display = ''; // in case display was set to none
  }
  function hideButton() {
    if (!btn) return;
    btn.classList.add('hidden');
    // keep it in DOM but hide visually (don't remove)
    btn.style.display = 'none';
  }

  // Ensure the button opens modal (safety, in case per-view wiring is missing)
  function attachButtonHandlers() {
    if (!btn) return;
    // clicking button opens modal (ignore clicks on hide button)
    btn.addEventListener('click', function (e) {
      if (e.target === hideBtnInner) return;
      if (modalInstance) modalInstance.show();
    });

    if (hideBtnInner) {
      hideBtnInner.addEventListener('click', function (e) {
        e.stopPropagation();
        // hide just for this page load
        btn.classList.add('hidden');
        btn.style.display = 'none';
      });
    }
  }

  // One-time modal on personal page
  function maybeShowIntroOnPersonal(page) {
    if (page !== PERSONAL_PAGE) return;
    try {
      var shown = localStorage.getItem(INTRO_FLAG_KEY);
      if (!shown) {
        // Show modal when available
        if (modalInstance) {
          modalInstance.show();
        } else if (modalEl) {
          // fallback: add visible class (Bootstrap required for proper styling)
          modalEl.classList.add('show');
          modalEl.style.display = 'block';
        }
        localStorage.setItem(INTRO_FLAG_KEY, '1');
      }
    } catch (e) {
      // ignore storage errors
    }
  }

  // Decide initial visibility
  function applyVisibilityRules() {
    var page = currentPage();
    if (PAGES_WITH_BUTTON.indexOf(page) !== -1) {
      showButton();
    } else {
      hideButton();
    }

    // personal intro
    maybeShowIntroOnPersonal(page);
  }

  // Run on DOM ready
  function init() {
    applyVisibilityRules();
    attachButtonHandlers();
    // expose helper in window for debugging
    try {
      window.__bhwFloatControl = {
        showButton: showButton,
        hideButton: hideButton,
        currentPage: currentPage
      };
    } catch (e) {}
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
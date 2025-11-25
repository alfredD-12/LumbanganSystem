/*
 assets/js/Dashboard/header-resident.js
 Single-file header logic (logout -> server logout -> redirect to landing page)
 - Put exactly one <script src=".../assets/js/Dashboard/header-resident.js?v=..."></script> in your footer.
 - Behavior:
    1) On logout click, clear local survey data,
    2) call AuthController logout endpoint (same-origin) to destroy server session,
    3) then redirect browser to the landing page URL provided by data-logout on the logout element
       or to a fallback URL (if no data-logout is present).
 - Exposes window.HeaderResident API: init(), setUser(user), openProfile(), logout()
*/

(function () {
  'use strict';

  var user = { username: 'User', fullName: 'User', email: '', mobile: '' };
  var logoutEndpoint = (window && window.BASE_URL) ? (window.BASE_URL + '/index.php?page=logout') : '/index.php?page=logout'; // default; will try to read meta override
  var redirectUrl = null; // final landing page to send user to (read from DOM or init option)

  function byId(id) { return document.getElementById(id); }
  function qs(sel, root) { return (root || document).querySelector(sel); }
  function textOr(el) { return el ? (el.textContent || el.innerText || '').trim() : ''; }

  function safeShowModal(modalEl) {
    if (!modalEl) return;
    try { if (window.bootstrap && bootstrap.Modal) { bootstrap.Modal.getOrCreateInstance(modalEl).show(); return; } } catch(e){}
    modalEl.classList.add('show'); modalEl.style.display = 'block'; modalEl.removeAttribute('aria-hidden');
  }

  function clearSurveyLocalStorage() {
    try {
      if (window.SurveyPersistence && typeof window.SurveyPersistence.clearAll === 'function') {
        try { window.SurveyPersistence.clearAll(); } catch(e){ console.debug(e); }
      }
      var toRemove = [];
      for (var i = 0; i < localStorage.length; i++) {
        var k = localStorage.key(i);
        if (!k) continue;
        if (k.indexOf('survey_') === 0 || k.indexOf('survey') === 0) toRemove.push(k);
      }
      toRemove.forEach(function (k) { localStorage.removeItem(k); });
    } catch (e) {
      console.debug('header-resident: clear localStorage failed', e);
    }
  }

  // Read redirect target from logout element or meta tag
  function readRedirectTargetFromDOM() {
    var logoutBtn = byId('hdr_logout_btn');
    if (logoutBtn) {
      var dataLogout = logoutBtn.getAttribute('data-logout') || (logoutBtn.dataset && logoutBtn.dataset.logout);
      if (dataLogout) {
        redirectUrl = dataLogout;
        return;
      }
      // If anchor contains an actual href (not '#') treat it as redirect target fallback
      var href = logoutBtn.getAttribute('href');
      if (href && href !== '#' && href !== 'javascript:void(0)') {
        redirectUrl = href;
        return;
      }
    }
    // fallback: meta tag <meta name="app-landing-url" content="...">
    var metaLanding = document.querySelector('meta[name="app-landing-url"]');
    if (metaLanding && metaLanding.content) {
      redirectUrl = metaLanding.content;
      return;
    }
    // final fallback: root
    redirectUrl = '/';
  }

  // Read logout endpoint from meta tag if provided by server
  function readLogoutEndpointFromMeta() {
    try {
      var m = document.querySelector('meta[name="app-auth-logout"]');
      if (m && m.content) {
        logoutEndpoint = m.content;
      }
    } catch (e) { /* ignore */ }
  }

  // Perform server logout then redirect to landing page (redirectTarget)
  async function logoutFlow(redirectTarget) {
    // Clear client-side survey data first
    clearSurveyLocalStorage();

    // compute endpoint and target
    var endpoint = logoutEndpoint || ((window && window.BASE_URL) ? (window.BASE_URL + '/index.php?page=logout') : '/index.php?page=logout');
    var target = redirectTarget || redirectUrl || '/';

    // Send request to server logout endpoint (best-effort)
    try {
      // Use same-origin credentials so session cookie is sent, and mark as XHR
      var resp = await fetch(endpoint, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json, text/plain, */*',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      // If server returned JSON, check success; otherwise proceed anyway
      var contentType = resp.headers.get('Content-Type') || '';
      if (contentType.indexOf('application/json') !== -1) {
        var json = await resp.json();
        if (json && json.success === true) {
          // server-side session destroyed
          window.location.href = target;
          return;
        } else {
          // server responded but indicated failure - still redirect as fallback
          console.debug('Logout: server responded but success !== true', json);
          window.location.href = target;
          return;
        }
      } else {
        // Not JSON (maybe redirect HTML) - follow through and redirect client-side
        window.location.href = target;
        return;
      }
    } catch (err) {
      // network or fetch error — still proceed to redirect
      console.debug('header-resident: server logout request failed', err);
      window.location.href = target;
      return;
    }
  }

  // Attach header behaviours (profile open + logout)
  function attachHeaderBehavior() {
    // Update UI with values rendered server-side (if present)
    var full = textOr(byId('wf_profile_name'));
    var un = textOr(byId('wf_profile_username')).replace(/^@/, '');
    var em = textOr(byId('wf_profile_email'));
    var mo = textOr(byId('wf_profile_mobile'));
    if (full) user.fullName = full;
    if (un) user.username = un;
    if (em) user.email = em;
    if (mo) user.mobile = mo;

    var userBtn = qs('.user-profile-btn');
    if (userBtn) {
      var span = userBtn.querySelector('span');
      if (span && user.username) span.textContent = user.username;
    }

    readRedirectTargetFromDOM();
    // Try to read logout endpoint from meta tag (server-provided)
    readLogoutEndpointFromMeta();

    var profileBtn = byId('btn-open-profile');
    if (profileBtn) {
      profileBtn.addEventListener('click', function (ev) {
        var modal = byId('userProfileModal');
        if (modal) {
          setTimeout(function () { if (!modal.classList.contains('show')) safeShowModal(modal); }, 10);
        }
      });
    }

    var logoutBtn = byId('hdr_logout_btn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function (ev) {
        // If the anchor has a real href (not '#' or empty), let the browser navigate so
        // the server-side logout controller handles session destruction and redirect.
        var href = logoutBtn.getAttribute('href');
        if (href && href !== '#' && href !== 'javascript:void(0)') {
          // allow default navigation
          return;
        }

        // Otherwise, use XHR logout flow (fallback for JS-only buttons)
        ev.preventDefault();
        var override = logoutBtn.getAttribute('data-logout-target') || (logoutBtn.dataset && logoutBtn.dataset.logoutTarget);
        var target = override || redirectUrl;
        logoutFlow(target);
      });
    }

    // dropdown fallback if bootstrap not available
    try {
      if (!(window.bootstrap && bootstrap.Dropdown) && userBtn) {
        userBtn.addEventListener('click', function () {
          var dm = userBtn.parentElement.querySelector('.dropdown-menu');
          if (!dm) return;
          dm.classList.toggle('show');
          userBtn.setAttribute('aria-expanded', dm.classList.contains('show') ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
          if (!userBtn.parentElement.contains(e.target)) {
            var dm = userBtn.parentElement.querySelector('.dropdown-menu');
            if (dm && dm.classList.contains('show')) dm.classList.remove('show');
          }
        });
      }
    } catch (err) {
      console.debug('header-resident: dropdown attach error', err);
    }
  }

  // Public API
  function init(opts) {
    opts = opts || {};
    if (opts.logoutEndpoint) logoutEndpoint = opts.logoutEndpoint;
    if (opts.redirectUrl) redirectUrl = opts.redirectUrl;
    if (opts.user) {
      user.username = opts.user.username || user.username;
      user.fullName = opts.user.fullName || user.fullName;
      user.email = opts.user.email || user.email;
      user.mobile = opts.user.mobile || user.mobile;
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', attachHeaderBehavior);
    } else {
      attachHeaderBehavior();
    }
  }

  function setUser(u) { init({user: u}); }
  function openProfile() { var m = byId('userProfileModal'); if (m) safeShowModal(m); }
  function logout() { logoutFlow(redirectUrl); }

  // auto-init by reading DOM values (so footer only needs to include this script)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){ init(); });
  } else { init(); }

  window.HeaderResident = { init: init, setUser: setUser, openProfile: openProfile, logout: logout };
})();
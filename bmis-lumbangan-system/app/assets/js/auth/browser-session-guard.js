(function () {
  var key = "bmis_active_browser_session";
  var internalNavigationKey = "bmis_internal_navigation";
  var storage;
  var landingMeta = document.querySelector('meta[name="app-landing-url"]');
  var logoutMeta = document.querySelector('meta[name="app-auth-logout"]');
  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  var csrfFieldMeta = document.querySelector('meta[name="csrf-field"]');
  var landingUrl = landingMeta ? landingMeta.content : "index.php?page=landing";
  var logoutUrl = logoutMeta ? logoutMeta.content : "";

  try {
    storage = window.sessionStorage;
  } catch (error) {
    return;
  }

  function goLanding() {
    window.location.replace(landingUrl);
  }

  function buildLogoutPayload() {
    var formData = new FormData();
    formData.append(csrfFieldMeta ? csrfFieldMeta.content : "csrf_token", csrfMeta ? csrfMeta.content : "");
    return formData;
  }

  function logoutSilently() {
    if (!logoutUrl) {
      return Promise.resolve();
    }

    return fetch(logoutUrl, {
      method: "POST",
      body: buildLogoutPayload(),
      credentials: "same-origin",
      headers: { Accept: "application/json" },
      keepalive: true,
    }).catch(function () {});
  }

  function logoutWithBeacon() {
    if (!logoutUrl) return;

    try {
      if (navigator.sendBeacon && navigator.sendBeacon(logoutUrl, buildLogoutPayload())) {
        return;
      }
    } catch (error) {}

    logoutSilently();
  }

  function logoutAndLanding() {
    storage.removeItem(key);
    logoutSilently().finally(goLanding);
  }

  function markInternalNavigation() {
    storage.setItem(internalNavigationKey, String(Date.now()));
  }

  function isRecentInternalNavigation() {
    var startedAt = Number(storage.getItem(internalNavigationKey) || 0);
    storage.removeItem(internalNavigationKey);
    return startedAt > 0 && Date.now() - startedAt < 5000;
  }

  function installInternalNavigationTracking() {
    document.addEventListener(
      "click",
      function (event) {
        var link = event.target.closest ? event.target.closest("a[href]") : null;
        if (!link || link.target === "_blank" || link.hasAttribute("download")) return;

        try {
          var url = new URL(link.href, window.location.href);
          if (url.origin === window.location.origin) {
            markInternalNavigation();
          }
        } catch (error) {}
      },
      true
    );

    document.addEventListener(
      "submit",
      function (event) {
        var form = event.target;
        if (!form || !form.action) return;

        try {
          var url = new URL(form.action, window.location.href);
          if (url.origin === window.location.origin) {
            markInternalNavigation();
          }
        } catch (error) {}
      },
      true
    );
  }

  function installCloseLogout() {
    window.addEventListener("pagehide", function () {
      if (isRecentInternalNavigation()) return;

      storage.removeItem(key);
      logoutWithBeacon();
    });
  }

  window.BMISAuthGuard = {
    markInternalNavigation: markInternalNavigation,
    endSession: logoutAndLanding,
  };

  if (storage.getItem(key) !== "1") {
    logoutAndLanding();
    return;
  }

  installInternalNavigationTracking();
  installCloseLogout();
})();

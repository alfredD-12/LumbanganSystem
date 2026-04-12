(function () {
  if (window.BMISCaptcha) {
    return;
  }

  const captchaWidgetIds = {};
  const captchaTokens = {};

  async function ensureLoaded(siteKey) {
    if (!siteKey) {
      return false;
    }

    if (typeof grecaptcha !== "undefined" && typeof grecaptcha.ready === "function") {
      return true;
    }

    let recaptchaScript = document.querySelector('script[src*="recaptcha/api.js"]');
    if (!recaptchaScript) {
      recaptchaScript = document.createElement("script");
      recaptchaScript.src = "https://www.google.com/recaptcha/api.js";
      recaptchaScript.async = true;
      recaptchaScript.defer = true;
      document.head.appendChild(recaptchaScript);
    }

    const timeoutMs = 6000;
    const pollMs = 100;
    const start = Date.now();

    while (Date.now() - start < timeoutMs) {
      if (typeof grecaptcha !== "undefined" && typeof grecaptcha.ready === "function") {
        return true;
      }
      await new Promise((resolve) => setTimeout(resolve, pollMs));
    }

    return false;
  }

  async function ensureVisibleCaptcha(widgetKey, containerId, widgetId, siteKey) {
    const loaded = await ensureLoaded(siteKey);
    if (!loaded) {
      return false;
    }

    await new Promise((resolve) => {
      grecaptcha.ready(resolve);
    });

    const containerEl = document.getElementById(containerId);
    const widgetEl = document.getElementById(widgetId);
    if (!containerEl || !widgetEl) {
      return false;
    }

    containerEl.style.display = "block";

    if (captchaWidgetIds[widgetKey] !== undefined) {
      return true;
    }

    try {
      captchaWidgetIds[widgetKey] = grecaptcha.render(widgetEl, {
        sitekey: siteKey,
        theme: "light",
        callback(token) {
          captchaTokens[widgetKey] = token || "";
        },
        "expired-callback"() {
          captchaTokens[widgetKey] = "";
        },
        "error-callback"() {
          captchaTokens[widgetKey] = "";
        },
      });
      return true;
    } catch (err) {
      console.error("reCAPTCHA widget render error:", err);
      return false;
    }
  }

  function getToken(widgetKey) {
    const widgetId = captchaWidgetIds[widgetKey];
    if (captchaTokens[widgetKey]) {
      return captchaTokens[widgetKey];
    }

    if (widgetId === undefined || typeof grecaptcha === "undefined") {
      return "";
    }

    try {
      const token = grecaptcha.getResponse(widgetId) || "";
      captchaTokens[widgetKey] = token;
      return token;
    } catch (err) {
      console.error("reCAPTCHA getResponse error:", err);
      return "";
    }
  }

  function reset(widgetKey) {
    captchaTokens[widgetKey] = "";
    const widgetId = captchaWidgetIds[widgetKey];
    if (widgetId !== undefined && typeof grecaptcha !== "undefined") {
      try {
        grecaptcha.reset(widgetId);
      } catch (err) {
        console.error("reCAPTCHA reset error:", err);
      }
    }
  }

  function hide(containerId) {
    const containerEl = document.getElementById(containerId);
    if (containerEl) {
      containerEl.style.display = "none";
    }
  }

  window.BMISCaptcha = {
    ensureLoaded,
    ensureVisibleCaptcha,
    getToken,
    reset,
    hide,
  };
})();

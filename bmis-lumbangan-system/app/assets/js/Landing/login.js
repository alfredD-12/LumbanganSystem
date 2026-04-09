const container = document.getElementById("loginContainer");
const registerBtn = document.getElementById("modalRegisterBtn");
const loginBtn = document.getElementById("modalLoginBtn");
let loginCaptchaWidgetId = null;
let loginCaptchaRequired = false;

async function ensureRecaptchaLoaded(siteKey) {
  if (!siteKey) {
    return false;
  }

  if (typeof grecaptcha !== "undefined" && typeof grecaptcha.ready === "function") {
    return true;
  }

  const scriptSelector = 'script[src*="recaptcha/api.js"]';
  let recaptchaScript = document.querySelector(scriptSelector);

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

async function ensureVisibleLoginCaptcha(siteKey) {
  const loaded = await ensureRecaptchaLoaded(siteKey);
  if (!loaded) {
    return false;
  }

  await new Promise((resolve) => {
    grecaptcha.ready(resolve);
  });

  const containerEl = document.getElementById("loginRecaptchaContainer");
  const widgetEl = document.getElementById("loginRecaptchaWidget");
  if (!containerEl || !widgetEl) {
    return false;
  }

  containerEl.style.display = "block";

  if (loginCaptchaWidgetId !== null) {
    return true;
  }

  try {
    loginCaptchaWidgetId = grecaptcha.render(widgetEl, {
      sitekey: siteKey,
      theme: "light",
    });
    return true;
  } catch (err) {
    console.error("reCAPTCHA widget render error:", err);
    return false;
  }
}

function getVisibleLoginCaptchaToken() {
  if (loginCaptchaWidgetId === null || typeof grecaptcha === "undefined") {
    return "";
  }

  try {
    return grecaptcha.getResponse(loginCaptchaWidgetId) || "";
  } catch (err) {
    console.error("reCAPTCHA getResponse error:", err);
    return "";
  }
}

function resetVisibleLoginCaptcha() {
  if (loginCaptchaWidgetId !== null && typeof grecaptcha !== "undefined") {
    try {
      grecaptcha.reset(loginCaptchaWidgetId);
    } catch (err) {
      console.error("reCAPTCHA reset error:", err);
    }
  }
}

// Desktop toggle buttons
if (registerBtn) {
  registerBtn.addEventListener("click", () => {
    container.classList.add("active");
    hideLoginError();
  });
}

if (loginBtn) {
  loginBtn.addEventListener("click", () => {
    container.classList.remove("active");
    hideRegisterError();
  });
}

// Mobile toggle links
setTimeout(() => {
  const mobileToSignUp = document.getElementById("mobileToSignUp");
  const mobileToSignIn = document.getElementById("mobileToSignIn");

  if (mobileToSignUp) {
    mobileToSignUp.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      console.log("Switching to Sign Up");
      container.classList.add("active");
      hideLoginError();
    });
  }

  if (mobileToSignIn) {
    mobileToSignIn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      console.log("Switching to Sign In");
      container.classList.remove("active");
      hideRegisterError();
    });
  }
}, 100);

// Handle Sign In Form Submission
document
  .getElementById("signinForm")
  ?.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const captchaTokenInput = document.getElementById("loginCaptchaToken");
    const isProtectionEnabled =
      document.querySelector('meta[name="bf-protection-enabled"]')?.content ===
      "1";
    const recaptchaSiteKey =
      document.querySelector('meta[name="recaptcha-site-key"]')?.content || "";
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    if (loginCaptchaRequired) {
      const visibleToken = getVisibleLoginCaptchaToken();
      if (!visibleToken) {
        showLoginError("Please complete the reCAPTCHA challenge before signing in.");
        return;
      }
      if (captchaTokenInput) {
        captchaTokenInput.value = visibleToken;
      }
      formData.set("captcha_token", visibleToken);
    } else if (captchaTokenInput) {
      captchaTokenInput.value = "";
      formData.delete("captcha_token");
    }

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Logging in...';

    try {
        const metaAuth = document.querySelector('meta[name="app-auth-endpoint"]');
        const authUrlBase = metaAuth ? metaAuth.content : '../../controllers/AuthController.php';
        const response = await fetch(authUrlBase + '?action=login', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Hide any error messages
            hideLoginError();
            // Redirect to appropriate dashboard
            window.location.href = result.redirect;
        } else {
            // Show error message
            const retryAfter = Number(result.retry_after || 0);
            let message = result.message || "Invalid username or password";

            if (result.code === 'invalid_csrf') {
                message = 'Security validation failed (CSRF). Refresh the page and try again.';
            } else if ((result.code || "") === "captcha_required") {
                loginCaptchaRequired = true;

                if (!recaptchaSiteKey) {
                    message =
                        "CAPTCHA is required, but site key is missing in configuration. Contact administrator.";
                } else {
                    const rendered = await ensureVisibleLoginCaptcha(recaptchaSiteKey);
                    if (!rendered) {
                        message =
                            "Unable to load reCAPTCHA challenge. Please refresh and try again.";
                    } else {
                        message =
                            "Too many failed attempts. Please complete reCAPTCHA, then sign in again.";
                    }
                }
            }
        }

        showLoginError(message, result.code || "", retryAfter);

        if (loginCaptchaRequired && (result.code || "") !== "captcha_required") {
          resetVisibleLoginCaptcha();
          if (captchaTokenInput) {
            captchaTokenInput.value = "";
          }
        }

        console.error("Login failed:", result.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    } catch (error) {
      showLoginError("An error occurred. Please try again.");
      console.error("Login error:", error);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    }
  });

// Username availability checker
let usernameCheckTimeout;
const usernameInput = document.getElementById("usernameInput");
const usernameCheckContainer = document.getElementById("usernameCheck");
const usernameLoading = document.getElementById("usernameLoading");
const usernameAvailable = document.getElementById("usernameAvailable");
const usernameTaken = document.getElementById("usernameTaken");

if (usernameInput) {
  usernameInput.addEventListener("input", function () {
    const username = this.value.trim();

    // Clear previous timeout
    clearTimeout(usernameCheckTimeout);

    // Reset icons
    usernameCheckContainer.style.display = "none";
    usernameLoading.style.display = "none";
    usernameAvailable.style.display = "none";
    usernameTaken.style.display = "none";

    // Don't check if username is too short
    if (username.length < 3) {
      return;
    }

    // Show loading indicator
    usernameCheckContainer.style.display = "block";
    usernameLoading.style.display = "inline-block";

    // Debounce - wait 500ms after user stops typing
    usernameCheckTimeout = setTimeout(async () => {
      try {
        const formData = new FormData();
        formData.append("username", username);

        const metaAuth = document.querySelector(
          'meta[name="app-auth-endpoint"]',
        );
        const authUrlBase = metaAuth
          ? metaAuth.content
          : "../../controllers/AuthController.php";
        const response = await fetch(authUrlBase + "?action=checkUsername", {
          method: "POST",
          body: formData,
          credentials: "same-origin",
        });

        const result = await response.json();

        // Hide loading
        usernameLoading.style.display = "none";

        // Show appropriate icon
        if (result.available) {
          usernameAvailable.style.display = "inline-block";
          usernameInput.style.borderColor = "#28a745";
        } else {
          usernameTaken.style.display = "inline-block";
          usernameInput.style.borderColor = "#dc3545";
        }
      } catch (error) {
        console.error("Username check error:", error);
        usernameLoading.style.display = "none";
      }
    }, 500);
  });

  // Reset border color when focus is lost"
  usernameInput.addEventListener("blur", function () {
    setTimeout(() => {
      this.style.borderColor = "";
    }, 200);
  });
} else {
  console.error("Username input element not found!");
}

// Handle Sign Up Form Submission
document
  .getElementById("signupForm")
  ?.addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Check if passwords match
    const password = formData.get("password");
    const confirmPassword = formData.get("confirm_password");

    if (password !== confirmPassword) {
      showRegisterError("Passwords do not match. Please try again.");
      return;
    }

    // Keep the login modal open — face scan swaps content inside it
    const loginModal = document.getElementById("loginModal");

    // Small delay to ensure modal closes, then open face scan inline
    setTimeout(() => {
      if (typeof openFaceScanInline === "function") {
        // Face scan inside the modal → on success → email verification
        openFaceScanInline(formData, function () {
          if (typeof openEmailVerificationModal === "function") {
            openEmailVerificationModal(formData);
          } else {
            showRegisterError(
              "Email verification system not available. Please try again.",
            );
          }
        });
      } else if (typeof openEmailVerificationModal === "function") {
        // Fallback — no face scan
        openEmailVerificationModal(formData);
      } else {
        if (loginModal) {
          loginModal.style.display = "flex";
          loginModal.classList.add("show");
          document.body.style.overflow = "hidden";
        }
        showRegisterError(
          "Verification system not available. Please try again.",
        );
      }
    }, 100);
  });

(function () {
  const bouncyEls = Array.from(document.querySelectorAll(".bouncy"));
  if (!bouncyEls.length) return;

  function parseWords(el) {
    const data = el.getAttribute("data-words");
    if (data && data.trim())
      return data
        .split("|")
        .map((s) => s.trim())
        .filter(Boolean);

    const letters = Array.from(el.querySelectorAll("span"))
      .map((s) => s.textContent || "")
      .join("");
    return [letters];
  }

  const datasets = bouncyEls.map((el) => {
    const words = parseWords(el);
    el.innerHTML = "";
    const span = document.createElement("span");
    span.className = "typewriter-text";
    el.appendChild(span);
    return {
      el,
      container: span,
      words,

      typeSpeed: parseInt(el.getAttribute("data-type-speed")) || 50,
      deleteSpeed: parseInt(el.getAttribute("data-delete-speed")) || 30,
      pauseAfter: parseInt(el.getAttribute("data-pause")) || 900,
    };
  });

  datasets.forEach((d, idx) => {
    (async function loop() {
      let wi = 0;
      while (true) {
        const word = d.words[wi];
        d.container.innerHTML = "";
        const chars = Array.from(word).map((ch) => {
          const s = document.createElement("span");
          s.className = "tw-char";
          s.textContent = ch;
          d.container.appendChild(s);
          return s;
        });

        for (let i = 0; i < chars.length; i++) {
          await new Promise((r) => setTimeout(r, d.typeSpeed + i * 18));
          chars[i].classList.add("in");
        }

        await new Promise((r) => setTimeout(r, d.pauseAfter));

        for (let i = chars.length - 1; i >= 0; i--) {
          chars[i].classList.remove("in");
          chars[i].classList.add("out");
          await new Promise((r) =>
            setTimeout(r, d.deleteSpeed + (chars.length - i) * 12),
          );
        }

        await new Promise((r) => setTimeout(r, 220));
        wi = (wi + 1) % d.words.length;
      }
    })();
  });
})();

// Helper functions for showing/hiding error messages
function showLoginError(message) {
  const alertDiv = document.getElementById("loginErrorAlert");
  const messageSpan = document.getElementById("loginErrorMessage");
  const code = arguments[1] || "";
  const retryAfter = Number(arguments[2] || 0);

  if (alertDiv && messageSpan) {
    const warningCodes = [
      "rate_limit_exceeded",
      "account_locked",
      "account_rate_limited",
      "captcha_required",
    ];
    alertDiv.classList.remove("alert-danger", "alert-warning");
    alertDiv.classList.add(
      warningCodes.includes(code) ? "alert-warning" : "alert-danger",
    );

    messageSpan.textContent = message;

    if (warningCodes.includes(code) && retryAfter > 0) {
      const counter = document.createElement("small");
      counter.id = "loginRetryCountdown";
      counter.className = "d-block mt-1";
      messageSpan.appendChild(document.createElement("br"));
      messageSpan.appendChild(counter);
      startRetryCountdown(counter, retryAfter);
    }

    alertDiv.style.display = "block";
    alertDiv.classList.add("show");

    // Scroll to the alert
    alertDiv.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }
}

function hideLoginError() {
  const alertDiv = document.getElementById("loginErrorAlert");
  if (alertDiv) {
    alertDiv.classList.remove("show");
    alertDiv.style.display = "none";
  }
}

function startRetryCountdown(el, seconds) {
  let remaining = Math.max(0, Number(seconds));

  const tick = () => {
    if (remaining <= 0) {
      el.textContent = "You can try logging in again now.";
      return;
    }

    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    el.textContent = `Please wait ${mins}:${String(secs).padStart(2, "0")} before retrying.`;
    remaining -= 1;
    setTimeout(tick, 1000);
  };

  tick();
}

function showRegisterError(message) {
  const alertDiv = document.getElementById("registerErrorAlert");
  const messageSpan = document.getElementById("registerErrorMessage");

  if (alertDiv && messageSpan) {
    messageSpan.textContent = message;
    alertDiv.style.display = "block";
    alertDiv.classList.add("show");

    // Scroll to the alert
    alertDiv.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }
}

function hideRegisterError() {
  const alertDiv = document.getElementById("registerErrorAlert");
  if (alertDiv) {
    alertDiv.classList.remove("show");
    alertDiv.style.display = "none";
  }
}

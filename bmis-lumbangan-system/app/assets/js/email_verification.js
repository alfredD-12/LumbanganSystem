/**
 * Email Verification JavaScript Handler
 * Manages the registration verification modal and AJAX requests.
 */

function getBaseUrl() {
  const metaBase = document.querySelector('meta[name="base-url"]');
  if (metaBase) {
    const url = metaBase.content;
    return url.endsWith("/") ? url : `${url}/`;
  }

  const path = window.location.pathname;
  const parts = path.split("/");
  if (parts[parts.length - 1].includes(".")) {
    parts.pop();
  }

  while (parts.length > 0 && !parts[parts.length - 1].includes("bmis-lumbangan-system")) {
    parts.pop();
  }

  return `${parts.join("/")}/`;
}

const BASE_URL = getBaseUrl();
const ROOT_URL = BASE_URL.replace(/\/app\/?$/, "/");

function getFrontControllerUrl() {
  const metaFrontController = document.querySelector('meta[name="app-front-controller"]');
  if (metaFrontController?.content) {
    return metaFrontController.content;
  }

  return `${ROOT_URL}public/index.php`;
}

let registrationFormData = null;
let verificationTarget = "";
let verificationToken = "";
let countdownInterval = null;
let verifyCaptchaRequired = false;

function getRecaptchaSiteKey() {
  return document.querySelector('meta[name="recaptcha-site-key"]')?.content || "";
}

function getCsrfFieldName() {
  return document.querySelector('meta[name="csrf-field"]')?.content || "csrf_token";
}

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content || "";
}

function cloneFormData(formData) {
  const clone = new FormData();
  for (const [key, value] of formData.entries()) {
    clone.append(key, value);
  }
  return clone;
}

function appendCsrf(formData) {
  const fieldName = getCsrfFieldName();
  const token = getCsrfToken();
  if (fieldName && token && !formData.has(fieldName)) {
    formData.append(fieldName, token);
  }
}

async function ensureRegistrationCaptcha(step) {
  const siteKey = getRecaptchaSiteKey();
  if (!siteKey || !window.BMISCaptcha || typeof window.BMISCaptcha.ensureVisibleCaptcha !== "function") {
    return false;
  }

  return window.BMISCaptcha.ensureVisibleCaptcha(
    `registration-step${step}`,
    `registerRecaptchaContainer${step}`,
    `registerRecaptchaWidget${step}`,
    siteKey,
  );
}

function getRegistrationCaptchaToken(step) {
  if (!window.BMISCaptcha || typeof window.BMISCaptcha.getToken !== "function") {
    return "";
  }

  return window.BMISCaptcha.getToken(`registration-step${step}`);
}

async function waitForRegistrationCaptchaToken(step, timeoutMs = 1200) {
  const startedAt = Date.now();
  while (Date.now() - startedAt < timeoutMs) {
    const token = getRegistrationCaptchaToken(step);
    if (token) {
      return token;
    }

    await new Promise((resolve) => setTimeout(resolve, 120));
  }

  return getRegistrationCaptchaToken(step);
}

function resetRegistrationCaptcha(step) {
  const tokenInput = document.getElementById(`registerCaptchaTokenStep${step}`);
  if (tokenInput) {
    tokenInput.value = "";
  }

  if (window.BMISCaptcha && typeof window.BMISCaptcha.reset === "function") {
    window.BMISCaptcha.reset(`registration-step${step}`);
  }
}

function hideRegistrationCaptcha(step) {
  const tokenInput = document.getElementById(`registerCaptchaTokenStep${step}`);
  if (tokenInput) {
    tokenInput.value = "";
  }

  if (window.BMISCaptcha && typeof window.BMISCaptcha.hide === "function") {
    window.BMISCaptcha.hide(`registerRecaptchaContainer${step}`);
  }
}

async function requireRegistrationCaptcha(step, message) {
  const rendered = await ensureRegistrationCaptcha(step);
  if (!rendered) {
    showEmailVerifyError(step, "Unable to load reCAPTCHA challenge. Please refresh and try again.");
    return "";
  }

  const token = getRegistrationCaptchaToken(step);
  const resolvedToken = token || (await waitForRegistrationCaptchaToken(step));
  const tokenInput = document.getElementById(`registerCaptchaTokenStep${step}`);
  if (tokenInput) {
    tokenInput.value = resolvedToken;
  }

  if (!resolvedToken) {
    showEmailVerifyError(step, message || "Please complete the reCAPTCHA challenge to continue.");
    return "";
  }

  return resolvedToken;
}

function openEmailVerificationModal(formData) {
  registrationFormData = cloneFormData(formData);
  verificationTarget = String(formData.get("email") || formData.get("mobile") || "").trim();
  verificationToken = "";
  verifyCaptchaRequired = false;

  const modal = document.getElementById("emailVerifyModal");
  if (modal) {
    modal.style.display = "flex";
    modal.classList.add("show");
    document.body.style.overflow = "hidden";
    showEmailVerifyStep(1);
    clearEmailVerifyErrors();
    hideRegistrationCaptcha(2);
    setTimeout(() => {
      ensureRegistrationCaptcha(1).catch(() => {});
    }, 0);
  }
}

function closeEmailVerifyModal() {
  const modal = document.getElementById("emailVerifyModal");
  if (modal) {
    modal.classList.remove("show");
    setTimeout(() => {
      modal.style.display = "none";
    }, 300);
    document.body.style.overflow = "auto";
  }

  if (countdownInterval) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }

  const codeInput = document.getElementById("emailVerifyCode");
  if (codeInput) {
    codeInput.value = "";
  }

  resetRegistrationCaptcha(1);
  resetRegistrationCaptcha(2);
  hideRegistrationCaptcha(1);
  hideRegistrationCaptcha(2);
  clearEmailVerifyErrors();
}

function backToRegistration() {
  closeEmailVerifyModal();

  const loginModal = document.getElementById("loginModal");
  if (loginModal) {
    loginModal.style.display = "flex";
    loginModal.classList.add("show");
    document.body.style.overflow = "hidden";
  }

  const container = document.getElementById("loginContainer");
  if (container) {
    container.classList.add("active");
  }
}

function showEmailVerifyStep(step) {
  document.getElementById("emailVerifyStep1").style.display = step === 1 ? "block" : "none";
  document.getElementById("emailVerifyStep2").style.display = step === 2 ? "block" : "none";
  document.getElementById("emailVerifyStep3").style.display = step === 3 ? "block" : "none";
}

function clearEmailVerifyErrors() {
  ["emailVerifyStep1Error", "emailVerifyStep2Error"].forEach((id) => {
    const errorDiv = document.getElementById(id);
    if (!errorDiv) {
      return;
    }

    errorDiv.style.display = "none";
    errorDiv.style.background = "";
    errorDiv.style.borderLeftColor = "";
    const span = errorDiv.querySelector("span");
    const icon = errorDiv.querySelector("i");
    if (span) {
      span.textContent = "";
      span.style.color = "";
    }
    if (icon) {
      icon.className = "fas fa-exclamation-circle";
      icon.style.color = "";
    }
  });
}

function showEmailVerifyError(step, message) {
  const errorDiv = document.getElementById(`emailVerifyStep${step}Error`);
  if (!errorDiv) {
    return;
  }

  const span = errorDiv.querySelector("span");
  if (span) {
    span.textContent = message;
  }
  errorDiv.style.display = "block";
}

function showEmailVerifySuccess(step, message) {
  const errorDiv = document.getElementById(`emailVerifyStep${step}Error`);
  if (!errorDiv) {
    return;
  }

  const span = errorDiv.querySelector("span");
  const icon = errorDiv.querySelector("i");
  errorDiv.style.display = "block";
  errorDiv.style.background = "#f0fff4";
  errorDiv.style.borderLeftColor = "#48bb78";

  if (icon) {
    icon.className = "fas fa-check-circle";
    icon.style.color = "#48bb78";
  }

  if (span) {
    span.textContent = message;
    span.style.color = "#2f855a";
  }
}

async function sendVerificationCode(buttonEl) {
  clearEmailVerifyErrors();

  if (!registrationFormData) {
    showEmailVerifyError(1, "No registration data found. Please try again.");
    return;
  }

  const captchaToken = await requireRegistrationCaptcha(
    1,
    "Please complete the reCAPTCHA challenge before requesting a verification code.",
  );
  if (!captchaToken) {
    return;
  }

  const btn = buttonEl || event?.target;
  const originalText = btn ? btn.innerHTML : "";
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
  }

  try {
    const formData = cloneFormData(registrationFormData);
    appendCsrf(formData);
    formData.set("captcha_token", captchaToken);

    const response = await fetch(`${getFrontControllerUrl()}?action=send_verification_code`, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    });

    const text = await response.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (parseError) {
      console.error("[send_code] Non-JSON response:", text);
      showEmailVerifyError(1, "Server error. Please try again.");
      return;
    }

    if (data.success) {
      verificationTarget = data.target || verificationTarget;
      const emailDisplay = document.getElementById("emailVerifyEmailDisplay");
      if (emailDisplay) {
        emailDisplay.textContent = verificationTarget;
      }

      startEmailVerifyCountdown();
      verifyCaptchaRequired = false;
      showEmailVerifyStep(2);
      clearEmailVerifyErrors();
      hideRegistrationCaptcha(1);
      resetRegistrationCaptcha(1);
      hideRegistrationCaptcha(2);
      resetRegistrationCaptcha(2);
      return;
    }

    showEmailVerifyError(1, data.message || "Failed to send verification code.");
  } catch (error) {
    console.error("[send_code] fetch error:", error);
    showEmailVerifyError(1, "Network error. Please try again.");
  } finally {
    resetRegistrationCaptcha(1);
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  }
}

async function verifyEmailCode(e) {
  e.preventDefault();
  clearEmailVerifyErrors();

  const code = document.getElementById("emailVerifyCode")?.value.trim() || "";
  if (!code || code.length !== 6) {
    showEmailVerifyError(2, "Please enter a valid 6-digit code.");
    return;
  }

  const btn = e.target.querySelector('button[type="submit"]');
  const originalText = btn ? btn.innerHTML : "";
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
  }

  try {
    const formData = new FormData();
    appendCsrf(formData);
    formData.append("verification_target", verificationTarget);
    formData.append("email", verificationTarget);
    formData.append("code", code);

    if (verifyCaptchaRequired) {
      const captchaToken = await requireRegistrationCaptcha(
        2,
        "Please complete the reCAPTCHA challenge before verifying your code.",
      );
      if (!captchaToken) {
        return;
      }
      formData.set("captcha_token", captchaToken);
    } else {
      const existingToken = getRegistrationCaptchaToken(2);
      if (existingToken) {
        formData.set("captcha_token", existingToken);
      }
    }

    const response = await fetch(`${getFrontControllerUrl()}?action=verify_registration_code`, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    });

    const data = await response.json();
    if (data.success) {
      verificationToken = data.token;
      verifyCaptchaRequired = false;
      if (countdownInterval) {
        clearInterval(countdownInterval);
      }
      completeRegistration();
      return;
    }

    if ((data.code || "") === "captcha_required") {
      verifyCaptchaRequired = true;
      await ensureRegistrationCaptcha(2);
      showEmailVerifyError(2, data.message || "Complete the reCAPTCHA challenge, then verify again.");
      return;
    }

    showEmailVerifyError(2, data.message || "Invalid or expired verification code.");
  } catch (error) {
    console.error("[verify_code] error:", error);
    showEmailVerifyError(2, "An error occurred. Please try again.");
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }

    if (verifyCaptchaRequired) {
      resetRegistrationCaptcha(2);
    }
  }
}

async function completeRegistration() {
  showEmailVerifyStep(3);

  try {
    const formData = new FormData();
    appendCsrf(formData);
    formData.append("token", verificationToken);

    if (registrationFormData) {
      const faceEmbedding = registrationFormData.get("face_embedding");
      const faceImageB64 = registrationFormData.get("face_image_b64");
      if (faceEmbedding) {
        formData.append("face_embedding", faceEmbedding);
      }
      if (faceImageB64) {
        formData.append("face_image_b64", faceImageB64);
      }
    }

    const response = await fetch(`${getFrontControllerUrl()}?action=complete_registration`, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    });

    const text = await response.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (parseError) {
      showEmailVerifyStep(2);
      showEmailVerifyError(2, "Invalid server response. Please try again.");
      return;
    }

    if (data.success) {
      window.location.href = data.redirect || `${BASE_URL}public/index.php?page=dashboard_resident`;
      return;
    }

    showEmailVerifyStep(2);
    showEmailVerifyError(2, data.message || "Registration failed. Please try again.");
  } catch (error) {
    console.error("[complete_registration] error:", error);
    showEmailVerifyStep(2);
    showEmailVerifyError(2, "An error occurred during registration. Please try again.");
  }
}

async function resendVerificationCode() {
  clearEmailVerifyErrors();

  const captchaToken = await requireRegistrationCaptcha(
    2,
    "Please complete the reCAPTCHA challenge before requesting a new code.",
  );
  if (!captchaToken) {
    return;
  }

  try {
    const formData = new FormData();
    appendCsrf(formData);
    formData.append("verification_target", verificationTarget);
    formData.append("email", verificationTarget);
    formData.append("captcha_token", captchaToken);

    const response = await fetch(`${getFrontControllerUrl()}?action=resend_verification_code`, {
      method: "POST",
      body: formData,
      credentials: "same-origin",
    });

    const data = await response.json();
    if (data.success) {
      if (countdownInterval) {
        clearInterval(countdownInterval);
      }
      startEmailVerifyCountdown();
      const codeInput = document.getElementById("emailVerifyCode");
      if (codeInput) {
        codeInput.value = "";
      }
      showEmailVerifySuccess(2, data.message || "New verification code sent.");
      return;
    }

    if ((data.code || "") === "captcha_required") {
      await ensureRegistrationCaptcha(2);
    }
    showEmailVerifyError(2, data.message || "Failed to resend code.");
  } catch (error) {
    console.error("[resend_code] error:", error);
    showEmailVerifyError(2, "An error occurred. Please try again.");
  } finally {
    resetRegistrationCaptcha(2);
  }
}

function startEmailVerifyCountdown() {
  let timeLeft = 3600;
  const timerElement = document.getElementById("emailVerifyTimer");

  if (countdownInterval) {
    clearInterval(countdownInterval);
  }

  countdownInterval = setInterval(() => {
    timeLeft -= 1;

    if (timeLeft <= 0) {
      clearInterval(countdownInterval);
      if (timerElement) {
        timerElement.textContent = "Expired";
      }
      showEmailVerifyError(2, "Verification code has expired. Please request a new code.");
      return;
    }

    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    if (timerElement) {
      timerElement.textContent = `${minutes}:${String(seconds).padStart(2, "0")}`;
    }
  }, 1000);
}

window.openEmailVerificationModal = openEmailVerificationModal;
window.closeEmailVerifyModal = closeEmailVerifyModal;
window.backToRegistration = backToRegistration;
window.sendVerificationCode = sendVerificationCode;
window.verifyEmailCode = verifyEmailCode;
window.resendVerificationCode = resendVerificationCode;

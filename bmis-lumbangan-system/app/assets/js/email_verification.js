/**
 * Email Verification JavaScript Handler
 * Manages the email verification modal and AJAX requests
 */

// Get BASE_URL from a script tag or meta tag
function getBaseUrl() {
    // Try to get from a meta tag first
    const metaBase = document.querySelector('meta[name="base-url"]');
    if (metaBase) {
        return metaBase.content;
    }
    
    // Fallback: construct from current location
    const path = window.location.pathname;
    const parts = path.split('/');
    // Remove filename if present
    if (parts[parts.length - 1].includes('.')) {
        parts.pop();
    }
    // Go up to root (usually /Lumbangan_BMIS/bmis-lumbangan-system/)
    while (parts.length > 0 && !parts[parts.length - 1].includes('bmis-lumbangan-system')) {
        parts.pop();
    }
    return parts.join('/') + '/';
}

const BASE_URL = getBaseUrl();

// Store registration data and verification state
let registrationFormData = null;
let verificationEmail = '';
let verificationToken = '';
let countdownInterval = null;

/**
 * Open the email verification modal (called from registration form)
 */
function openEmailVerificationModal(formData) {
    // Store the form data
    registrationFormData = formData;
    verificationEmail = formData.get('email');
    
    // Show modal
    const modal = document.getElementById('emailVerifyModal');
    
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        showEmailVerifyStep(1);
    }
}

/**
 * Close the email verification modal
 */
function closeEmailVerifyModal() {
    const modal = document.getElementById('emailVerifyModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); // Wait for fade out animation
        document.body.style.overflow = 'auto';
    }
    
    // Clear countdown
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
    
    // Reset form
    const codeInput = document.getElementById('emailVerifyCode');
    if (codeInput) codeInput.value = '';
    
    // Clear errors
    clearEmailVerifyErrors();
}

/**
 * Back to registration (close modal and reopen login modal)
 */
function backToRegistration() {
    closeEmailVerifyModal();
    
    // Reopen login modal to registration tab
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        loginModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Switch to sign up view
        const container = document.getElementById('loginContainer');
        if (container) {
            container.classList.add('login-active');
        }
    }
}

/**
 * Show specific step in the modal
 */
function showEmailVerifyStep(step) {
    document.getElementById('emailVerifyStep1').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('emailVerifyStep2').style.display = step === 2 ? 'block' : 'none';
    document.getElementById('emailVerifyStep3').style.display = step === 3 ? 'block' : 'none';
}

/**
 * Clear all error messages
 */
function clearEmailVerifyErrors() {
    const errors = ['emailVerifyStep1Error', 'emailVerifyStep2Error'];
    errors.forEach(id => {
        const errorDiv = document.getElementById(id);
        if (errorDiv) {
            errorDiv.style.display = 'none';
            const span = errorDiv.querySelector('span');
            if (span) span.textContent = '';
        }
    });
}

/**
 * Show error message
 */
function showEmailVerifyError(step, message) {
    const errorDiv = document.getElementById(`emailVerifyStep${step}Error`);
    if (errorDiv) {
        const span = errorDiv.querySelector('span');
        if (span) span.textContent = message;
        errorDiv.style.display = 'block';
    }
}

/**
 * Step 1: Send verification code to email
 */
function sendVerificationCode() {
    clearEmailVerifyErrors();
    
    if (!registrationFormData) {
        showEmailVerifyError(1, 'No registration data found. Please try again.');
        return;
    }
    
    // Disable button and show loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    fetch(BASE_URL + 'email_verification.php?action=send_code', {
        method: 'POST',
        body: registrationFormData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            // Show email in step 2
            const emailDisplay = document.getElementById('emailVerifyEmailDisplay');
            if (emailDisplay) emailDisplay.textContent = verificationEmail;
            
            // Start countdown timer
            startEmailVerifyCountdown();
            
            // Move to step 2
            showEmailVerifyStep(2);
        } else {
            showEmailVerifyError(1, data.message || 'Failed to send verification code');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showEmailVerifyError(1, 'An error occurred. Please try again.');
    });
}

/**
 * Step 2: Verify the code entered by user
 */
function verifyEmailCode(e) {
    e.preventDefault();
    clearEmailVerifyErrors();
    
    const code = document.getElementById('emailVerifyCode').value.trim();
    
    if (!code || code.length !== 6) {
        showEmailVerifyError(2, 'Please enter a valid 6-digit code');
        return;
    }
    
    const formData = new FormData();
    formData.append('email', verificationEmail);
    formData.append('code', code);
    
    // Disable button and show loading
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    
    fetch(BASE_URL + 'email_verification.php?action=verify_code', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            verificationToken = data.token;
            
            // Clear countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            
            // Complete registration
            completeRegistration();
        } else {
            showEmailVerifyError(2, data.message || 'Invalid or expired code');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showEmailVerifyError(2, 'An error occurred. Please try again.');
    });
}

/**
 * Step 3: Complete registration and create account
 */
function completeRegistration() {
    showEmailVerifyStep(3);
    
    const formData = new FormData();
    formData.append('token', verificationToken);
    
    fetch(BASE_URL + 'email_verification.php?action=complete_registration', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            
            if (data.success) {
                // Immediate redirect
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // Force redirect
                    window.location.href = BASE_URL + 'public/index.php?page=dashboard_resident';
                }
            } else {
                // Show error and go back to step 2
                showEmailVerifyStep(2);
                showEmailVerifyError(2, data.message || 'Registration failed. Please try again.');
            }
        } catch (e) {
            showEmailVerifyStep(2);
            showEmailVerifyError(2, 'Invalid server response. Please try again.');
        }
    })
    .catch(err => {
        showEmailVerifyStep(2);
        showEmailVerifyError(2, 'An error occurred during registration. Please try again.');
    });
}

/**
 * Resend verification code
 */
function resendVerificationCode() {
    clearEmailVerifyErrors();
    
    const formData = new FormData();
    formData.append('email', verificationEmail);
    
    fetch(BASE_URL + 'email_verification.php?action=resend_code', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Clear and restart countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            startEmailVerifyCountdown();
            
            // Clear code input
            const codeInput = document.getElementById('emailVerifyCode');
            if (codeInput) codeInput.value = '';
            
            // Show success message briefly
            const errorDiv = document.getElementById('emailVerifyStep2Error');
            if (errorDiv) {
                errorDiv.style.background = '#f0fff4';
                errorDiv.style.borderLeftColor = '#48bb78';
                const span = errorDiv.querySelector('span');
                const icon = errorDiv.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-check-circle';
                    icon.style.color = '#48bb78';
                }
                if (span) {
                    span.textContent = 'New code sent to your email';
                    span.style.color = '#2f855a';
                }
                errorDiv.style.display = 'block';
                
                // Hide after 3 seconds
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                    errorDiv.style.background = '#fee';
                    errorDiv.style.borderLeftColor = '#e53e3e';
                    if (icon) {
                        icon.className = 'fas fa-exclamation-circle';
                        icon.style.color = '#e53e3e';
                    }
                    if (span) span.style.color = '#c53030';
                }, 3000);
            }
        } else {
            showEmailVerifyError(2, data.message || 'Failed to resend code');
        }
    })
    .catch(err => {
        showEmailVerifyError(2, 'An error occurred. Please try again.');
        console.error('Error:', err);
    });
}

/**
 * Start countdown timer (1 hour = 3600 seconds)
 */
function startEmailVerifyCountdown() {
    let timeLeft = 3600; // 1 hour in seconds
    
    const timerElement = document.getElementById('emailVerifyTimer');
    
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
    
    countdownInterval = setInterval(() => {
        timeLeft--;
        
        if (timeLeft <= 0) {
            clearInterval(countdownInterval);
            if (timerElement) timerElement.textContent = 'Expired';
            showEmailVerifyError(2, 'Verification code has expired. Please request a new code.');
            return;
        }
        
        // Format time as MM:SS
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        if (timerElement) {
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
    }, 1000);
}

// Make function globally accessible
window.openEmailVerificationModal = openEmailVerificationModal;
window.closeEmailVerifyModal = closeEmailVerifyModal;
window.backToRegistration = backToRegistration;
window.sendVerificationCode = sendVerificationCode;
window.verifyEmailCode = verifyEmailCode;
window.resendVerificationCode = resendVerificationCode;

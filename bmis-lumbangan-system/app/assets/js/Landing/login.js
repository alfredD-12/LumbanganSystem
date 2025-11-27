const container = document.getElementById('loginContainer');
const registerBtn = document.getElementById('modalRegisterBtn');
const loginBtn = document.getElementById('modalLoginBtn');

// Desktop toggle buttons
if (registerBtn) {
    registerBtn.addEventListener('click', () => {
        container.classList.add("active");
        hideLoginError();
    });
}

if (loginBtn) {
    loginBtn.addEventListener('click', () => {
        container.classList.remove("active");
        hideRegisterError();
    });
}

// Mobile toggle links
setTimeout(() => {
    const mobileToSignUp = document.getElementById('mobileToSignUp');
    const mobileToSignIn = document.getElementById('mobileToSignIn');
    
    if (mobileToSignUp) {
        mobileToSignUp.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Switching to Sign Up');
            container.classList.add("active");
            hideLoginError();
        });
    }
    
    if (mobileToSignIn) {
        mobileToSignIn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Switching to Sign In');
            container.classList.remove("active");
            hideRegisterError();
        });
    }
}, 100);

// Handle Sign In Form Submission
document.getElementById('signinForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
    
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
            showLoginError(result.message || 'Invalid username or password');
            console.error('Login failed:', result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        showLoginError('An error occurred. Please try again.');
        console.error('Login error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Username availability checker
let usernameCheckTimeout;
const usernameInput = document.getElementById('usernameInput');
const usernameCheckContainer = document.getElementById('usernameCheck');
const usernameLoading = document.getElementById('usernameLoading');
const usernameAvailable = document.getElementById('usernameAvailable');
const usernameTaken = document.getElementById('usernameTaken');

if (usernameInput) {
    usernameInput.addEventListener('input', function() {
        const username = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(usernameCheckTimeout);
        
        // Reset icons
        usernameCheckContainer.style.display = 'none';
        usernameLoading.style.display = 'none';
        usernameAvailable.style.display = 'none';
        usernameTaken.style.display = 'none';
        
        // Don't check if username is too short
        if (username.length < 3) {
            return;
        }
        
        // Show loading indicator
        usernameCheckContainer.style.display = 'block';
        usernameLoading.style.display = 'inline-block';
        
        // Debounce - wait 500ms after user stops typing
        usernameCheckTimeout = setTimeout(async () => {
            try {
                const formData = new FormData();
                formData.append('username', username);
                
                const metaAuth = document.querySelector('meta[name="app-auth-endpoint"]');
                const authUrlBase = metaAuth ? metaAuth.content : '../../controllers/AuthController.php';
                const response = await fetch(authUrlBase + '?action=checkUsername', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                // Hide loading
                usernameLoading.style.display = 'none';
                
                // Show appropriate icon
                if (result.available) {
                    usernameAvailable.style.display = 'inline-block';
                    usernameInput.style.borderColor = '#28a745';
                } else {
                    usernameTaken.style.display = 'inline-block';
                    usernameInput.style.borderColor = '#dc3545';
                }
            } catch (error) {
                console.error('Username check error:', error);
                usernameLoading.style.display = 'none';
            }
        }, 500);
    });
    
    // Reset border color when focus is lost
    usernameInput.addEventListener('blur', function() {
        setTimeout(() => {
            this.style.borderColor = '';
        }, 200);
    });
} else {
    console.error('Username input element not found!');
}

// Handle Sign Up Form Submission
document.getElementById('signupForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Close login modal properly
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        loginModal.style.display = 'none';
        loginModal.classList.remove('show');
    }
    
    // Reset body scroll
    document.body.style.overflow = 'auto';
    
    // Small delay to ensure modal closes, then open verification modal
    setTimeout(() => {
        // Open email verification modal with the registration data
        if (typeof openEmailVerificationModal === 'function') {
            openEmailVerificationModal(formData);
        } else {
            console.error('Email verification function not found');
            // Fallback: show login modal again
            if (loginModal) {
                loginModal.style.display = 'flex';
                loginModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
            showRegisterError('Email verification system not available. Please try again.');
        }
    }, 100);
});

(function(){
    const bouncyEls = Array.from(document.querySelectorAll('.bouncy'));
    if (!bouncyEls.length) return;

    function parseWords(el){
        const data = el.getAttribute('data-words');
        if (data && data.trim()) return data.split('|').map(s => s.trim()).filter(Boolean);
        
        
        
        
        
        const letters = Array.from(el.querySelectorAll('span')).map(s => s.textContent || '').join('');
        return [letters];
    }

    const datasets = bouncyEls.map(el => {
        const words = parseWords(el);
        el.innerHTML = '';
        const span = document.createElement('span');
        span.className = 'typewriter-text';
        el.appendChild(span);
        return {
            el,
            container: span,
            words,
            
            typeSpeed: parseInt(el.getAttribute('data-type-speed')) || 50,
            deleteSpeed: parseInt(el.getAttribute('data-delete-speed')) || 30,
            pauseAfter: parseInt(el.getAttribute('data-pause')) || 900,
        };
    });

    
    
    datasets.forEach((d, idx) => {
        (async function loop(){
            let wi = 0;
            while(true){
                const word = d.words[wi];
                d.container.innerHTML = '';
                const chars = Array.from(word).map(ch => {
                    const s = document.createElement('span');
                    s.className = 'tw-char';
                    s.textContent = ch;
                    d.container.appendChild(s);
                    return s;
                });

                for (let i=0;i<chars.length;i++){
                    await new Promise(r => setTimeout(r, d.typeSpeed + i * 18));
                    chars[i].classList.add('in');
                }

                await new Promise(r => setTimeout(r, d.pauseAfter));

                for (let i=chars.length-1;i>=0;i--){
                    chars[i].classList.remove('in');
                    chars[i].classList.add('out');
                    await new Promise(r => setTimeout(r, d.deleteSpeed + (chars.length - i) * 12));
                }

                await new Promise(r => setTimeout(r, 220));
                wi = (wi + 1) % d.words.length;
            }
        })();
    });
})();

// Helper functions for showing/hiding error messages
function showLoginError(message) {
    const alertDiv = document.getElementById('loginErrorAlert');
    const messageSpan = document.getElementById('loginErrorMessage');
    
    if (alertDiv && messageSpan) {
        messageSpan.textContent = message;
        alertDiv.style.display = 'block';
        alertDiv.classList.add('show');
        
        // Scroll to the alert
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function hideLoginError() {
    const alertDiv = document.getElementById('loginErrorAlert');
    if (alertDiv) {
        alertDiv.classList.remove('show');
        alertDiv.style.display = 'none';
    }
}

function showRegisterError(message) {
    const alertDiv = document.getElementById('registerErrorAlert');
    const messageSpan = document.getElementById('registerErrorMessage');
    
    if (alertDiv && messageSpan) {
        messageSpan.textContent = message;
        alertDiv.style.display = 'block';
        alertDiv.classList.add('show');
        
        // Scroll to the alert
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function hideRegisterError() {
    const alertDiv = document.getElementById('registerErrorAlert');
    if (alertDiv) {
        alertDiv.classList.remove('show');
        alertDiv.style.display = 'none';
    }
}
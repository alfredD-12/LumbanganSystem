const container = document.getElementById('loginContainer');
const registerBtn = document.getElementById('modalRegisterBtn');
const loginBtn = document.getElementById('modalLoginBtn');

if (registerBtn) {
    registerBtn.addEventListener('click', () => {
        container.classList.add("active");
    });
}

if (loginBtn) {
    loginBtn.addEventListener('click', () => {
        container.classList.remove("active");
    });
}

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
        const response = await fetch('../../controllers/AuthController.php?action=login', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Redirect to appropriate dashboard
            console.log('Login successful, redirecting...');
            window.location.href = result.redirect;
        } else {
            // Log error message
            console.error('Login failed:', result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
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

console.log('Username checker elements:', {
    usernameInput,
    usernameCheckContainer,
    usernameLoading,
    usernameAvailable,
    usernameTaken
});

if (usernameInput) {
    console.log('Username input found, attaching event listener');
    usernameInput.addEventListener('input', function() {
        const username = this.value.trim();
        console.log('Username input changed:', username);
        
        // Clear previous timeout
        clearTimeout(usernameCheckTimeout);
        
        // Reset icons
        usernameCheckContainer.style.display = 'none';
        usernameLoading.style.display = 'none';
        usernameAvailable.style.display = 'none';
        usernameTaken.style.display = 'none';
        
        // Don't check if username is too short
        if (username.length < 3) {
            console.log('Username too short, skipping check');
            return;
        }
        
        // Show loading indicator
        usernameCheckContainer.style.display = 'block';
        usernameLoading.style.display = 'inline-block';
        console.log('Showing loading indicator');
        
        // Debounce - wait 500ms after user stops typing
        usernameCheckTimeout = setTimeout(async () => {
            console.log('Checking username availability:', username);
            try {
                const formData = new FormData();
                formData.append('username', username);
                
                const response = await fetch('../../controllers/AuthController.php?action=checkUsername', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                console.log('Username check result:', result);
                
                // Hide loading
                usernameLoading.style.display = 'none';
                
                // Show appropriate icon
                if (result.available) {
                    usernameAvailable.style.display = 'inline-block';
                    usernameInput.style.borderColor = '#28a745';
                    console.log('Username is available');
                } else {
                    usernameTaken.style.display = 'inline-block';
                    usernameInput.style.borderColor = '#dc3545';
                    console.log('Username is taken');
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
    console.log('Registration form submitted');
    
    const formData = new FormData(this);
    
    // Log form data for debugging
    console.log('Form data:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registering...';
    
    try {
        const response = await fetch('../../controllers/AuthController.php?action=register', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        const responseText = await response.text();
        console.log('Raw response text:', responseText);
        
        // Check if response is empty
        if (!responseText || responseText.trim() === '') {
            console.error('Empty response from server');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        
        let result;
        try {
            result = JSON.parse(responseText);
            console.log('Parsed result:', result);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Could not parse response:', responseText);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        
        if (result.success) {
            // Registration successful - redirect silently
            console.log('Registration successful!');
            console.log('Redirecting to:', result.redirect);
            window.location.href = result.redirect;
        } else {
            // Log error message
            console.log('Registration failed:', result.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Registration error:', error);
        console.error('Error stack:', error.stack);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
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
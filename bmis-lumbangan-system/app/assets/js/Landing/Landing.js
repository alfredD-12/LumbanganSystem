// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Active nav link on scroll

// Scrollspy: highlight nav-link for section in view
function initScrollSpy() {
    // find sections and nav links at time of initialization (after includes)
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');

    function activateNavLink() {
        let currentSectionId = '';
        const scrollY = window.pageYOffset;
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 80; // adjust for navbar height
            const sectionHeight = section.offsetHeight;
            if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                currentSectionId = section.getAttribute('id');
            }
        });
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.startsWith('#')) {
                if (href.substring(1) === currentSectionId) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            }
        });
    }
    window.addEventListener('scroll', activateNavLink);
    activateNavLink(); // initial call
}

// Initialize scrollspy when DOM ready and when includes are loaded
document.addEventListener('DOMContentLoaded', initScrollSpy);
document.addEventListener('includes:loaded', initScrollSpy);

// News functionality removed from scripts.js to avoid conflicts with batangas-news.js
// Use `batangas-news.js` for fetching and rendering Batangas news.

// Login Modal Functionality
function initLandingUI() {
    if (window.__landingInitialized) return;
    window.__landingInitialized = true;

    const loginModal = document.getElementById('loginModal');
    const loginContainer = document.getElementById('loginContainer');
    const openLoginModalBtn = document.getElementById('openLoginModal');
    const closeLoginModalBtn = document.getElementById('closeLoginModal');
    const modalRegisterBtn = document.getElementById('modalRegisterBtn');
    const modalLoginBtn = document.getElementById('modalLoginBtn');

    // News is now handled by `batangas-news.js` (BatangasNewsFetcher)

    // Open modal
    if (openLoginModalBtn) {
        openLoginModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loginModal.classList.add('show');
            document.body.style.overflow = 'hidden';
            initTypewriter();
        });
    }

    // Close modal
    if (closeLoginModalBtn) {
        closeLoginModalBtn.addEventListener('click', function() {
            loginModal.classList.remove('show');
            document.body.style.overflow = '';
        });
    }

    // Close modal when clicking outside (guard loginModal)
    if (loginModal) {
        loginModal.addEventListener('click', function(e) {
            if (e.target === loginModal) {
                loginModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }

    // Toggle between sign in and sign up
    if (modalRegisterBtn) {
        modalRegisterBtn.addEventListener('click', function() {
            loginContainer.classList.add('active');
        });
    }

    if (modalLoginBtn) {
        modalLoginBtn.addEventListener('click', function() {
            loginContainer.classList.remove('active');
        });
    }

    // Delegated fallback: if the header/link is injected later or timing differs,
    // handle clicks on any element that matches the login trigger.
    document.addEventListener('click', function (e) {
        try {
            const clicked = e.target;
            // support clicking the <a id="openLoginModal"> or any child inside it
            const trigger = clicked.closest && clicked.closest('#openLoginModal');
            if (trigger) {
                e.preventDefault();
                if (loginModal) {
                    loginModal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                    initTypewriter();
                } else {
                    console.warn('Login modal element not found when clicking login trigger');
                }
            }
        } catch (err) {
            // ignore
        }
    });

    // Typewriter effect for modal
    function initTypewriter() {
        const containers = document.querySelectorAll('.login-bouncy .login-typewriter-text');
        
        const words = [
            ['WELCOME', 'HELLO', 'KUMUSTA'],
            ['MABUHAY', 'BARANGAY', 'LUMBANGAN']
        ];

        containers.forEach((container, idx) => {
            if (container.dataset.initialized) return;
            container.dataset.initialized = 'true';

            const wordList = words[idx];
            let wordIndex = 0;

            async function typeEffect() {
                while (true) {
                    const word = wordList[wordIndex];
                    container.innerHTML = '';

                    const chars = Array.from(word).map(ch => {
                        const span = document.createElement('span');
                        span.className = 'login-tw-char';
                        span.textContent = ch;
                        container.appendChild(span);
                        return span;
                    });

                    // Type in
                    for (let i = 0; i < chars.length; i++) {
                        await new Promise(r => setTimeout(r, 50 + i * 18));
                        chars[i].classList.add('in');
                    }

                    await new Promise(r => setTimeout(r, 900));

                    // Type out
                    for (let i = chars.length - 1; i >= 0; i--) {
                        chars[i].classList.remove('in');
                        chars[i].classList.add('out');
                        await new Promise(r => setTimeout(r, 30 + (chars.length - i) * 12));
                    }

                    await new Promise(r => setTimeout(r, 220));
                    wordIndex = (wordIndex + 1) % wordList.length;
                }
            }

            typeEffect();
        });
    }
    // Initialize additional UI animations after DOM is ready
    // 1) Staggered reveal-on-scroll for many elements
    function setupRevealOnScroll() {
        const selectors = [
            '.section-header',
            '.project-card',
            '.official-card',
            '.announcement-card',
            '.gallery-item',
            '.stat-card',
            '.hero-content',
            '.about-img-wrapper',
            '.contact-card',
            '.footer'
        ];

        const items = document.querySelectorAll(selectors.join(','));
        if (!items.length) return;

        const revObserver = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    // compute a small, deterministic delay based on position
                    const idx = Array.from(items).indexOf(el);
                    const delay = (idx % 6) * 80; // up to ~480ms stagger
                    el.classList.add('reveal');
                    el.style.transitionDelay = delay + 'ms';
                    // activate on next frame for smoother composite
                    requestAnimationFrame(() => el.classList.add('active'));
                    obs.unobserve(el);
                }
            });
        }, { threshold: 0.12 });

        items.forEach(el => {
            // make sure element starts hidden (CSS will handle .reveal)
            el.classList.add('reveal');
            revObserver.observe(el);
        });
    }


    // Batangas news handled by `batangas-news.js`.
    // Continue initializing UI behaviors below.
    setupRevealOnScroll();

    // 2) Subtle parallax for floating background shapes to make the hero feel fluid
    const floatingShapes = document.querySelector('.floating-shapes');
    if (floatingShapes) {
        let lastScroll = window.scrollY || 0;
        function onParallax() {
            const y = window.scrollY || 0;
            // small translate based on scroll position (invert for gentle upward movement)
            const translate = Math.max(-60, Math.min(60, -y * 0.03));
            // apply transform with GPU-friendly translate3d
            floatingShapes.style.transform = `translate3d(0, ${translate}px, 0)`;
            lastScroll = y;
        }
        // use rAF for performant updates
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    onParallax();
                    ticking = false;
                });
                ticking = true;
            }
        });
    }
}

// Run initialization when DOM is ready and when includes are loaded
document.addEventListener('DOMContentLoaded', initLandingUI);
document.addEventListener('includes:loaded', initLandingUI);


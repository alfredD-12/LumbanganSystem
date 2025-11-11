// Toggle 'scrolled' class on the user navbar to allow a smaller/sharper style after scrolling
(function() {
    const nav = document.querySelector('.user-navbar');
    if (!nav) return;

    let ticking = false;
    let lastScrollY = 0;

    function updateNavbar() {
        if (lastScrollY > 20) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
        ticking = false;
    }

    function onScroll() {
        lastScrollY = window.scrollY;
        if (!ticking) {
            window.requestAnimationFrame(updateNavbar);
            ticking = true;
        }
    }

    // Initialize
    updateNavbar();
    document.addEventListener('scroll', onScroll, { passive: true });
})();

// Mobile-friendly navbar behavior:
// - Close the collapsed navbar when a nav link / dropdown item is clicked
// - Turn dropdowns into accordion panels while the navbar is collapsed (mobile)
(function(){
    const collapseEl = document.getElementById('userNavbar');
    if (!collapseEl) return;

    // Close collapse when a regular nav link or dropdown item is clicked
    function setupCloseOnClick() {
        const clickable = collapseEl.querySelectorAll('.nav-link:not(.dropdown-toggle), .dropdown-item');
        clickable.forEach(el => {
            el.addEventListener('click', function(){
                // Use Bootstrap's Collapse API to hide the collapse
                try {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapseEl) || new bootstrap.Collapse(collapseEl);
                    if (collapseEl.classList.contains('show')) bsCollapse.hide();
                } catch (e) {
                    // If bootstrap isn't available or something fails, silently ignore
                }
            });
        });
    }

    // Make dropdown toggles behave as mobile accordions when viewport is narrow
    function setupAccordionDropdowns() {
        // handle dropdown toggles inside collapsed menu. Include both nav-item dropdowns
        // and standalone dropdown containers (like the user profile button).
        const toggles = collapseEl.querySelectorAll('.nav-item.dropdown > .nav-link.dropdown-toggle, .dropdown > .dropdown-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', function(e){
                if (window.innerWidth > 991) return; // keep normal dropdown behavior on desktop
                e.preventDefault();
                const parent = this.parentElement;
                const isOpen = parent.classList.contains('open');

                // Close other open dropdowns in the collapsed navbar (match any .dropdown.open)
                const others = collapseEl.querySelectorAll('.dropdown.open');
                others.forEach(o => {
                    if (o !== parent) {
                        o.classList.remove('open');
                        const t = o.querySelector('.dropdown-toggle');
                        if (t) t.setAttribute('aria-expanded','false');
                    }
                });

                if (isOpen) {
                    parent.classList.remove('open');
                    this.setAttribute('aria-expanded','false');
                } else {
                    parent.classList.add('open');
                    this.setAttribute('aria-expanded','true');
                }
            });
        });
    }

    // Re-init handlers on DOM ready
    document.addEventListener('DOMContentLoaded', function(){
        setupCloseOnClick();
        setupAccordionDropdowns();
    });

    // Also re-run accordion setup on resize (so behavior toggles at breakpoints)
    window.addEventListener('resize', function(){
        // simply no-op here; handlers respect window.innerWidth when invoked
    });
})();

// Populate the survey card from data attributes and compute status (Open / Upcoming)
(function(){
    const card = document.getElementById('survey-card');
    if (!card) return;

    const lastTitle = card.getAttribute('data-last-title') || ''; 
    const lastDateRaw = card.getAttribute('data-last-date') || '';
    const nextTitle = card.getAttribute('data-next-title') || '';
    const nextDateRaw = card.getAttribute('data-next-date') || '';

    const lastTitleEl = document.getElementById('survey-last-title');
    const lastDateEl = document.getElementById('survey-last-date');
    const nextTitleEl = document.getElementById('survey-next-title');
    const nextDateEl = document.getElementById('survey-next-date');
    const nextStatusEl = document.getElementById('survey-next-status'); // may be removed from markup
    const shortStatusEl = document.getElementById('survey-next-status-short');
    const surveyNumberEl = document.getElementById('survey-number');

    function formatDateISO(raw){
        if (!raw) return '';
        const d = new Date(raw + 'T00:00:00');
        if (isNaN(d)) return raw;
        return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    }

    // Fill texts
    if (lastTitleEl) lastTitleEl.textContent = lastTitle;
    if (lastDateEl) lastDateEl.textContent = formatDateISO(lastDateRaw);
    if (nextTitleEl) nextTitleEl.textContent = nextTitle;
    if (nextDateEl) nextDateEl.textContent = formatDateISO(nextDateRaw);

    // Determine status: if today >= nextDate -> Open, else Upcoming
    if (nextDateRaw){
        const today = new Date();
        const nextDate = new Date(nextDateRaw + 'T00:00:00');
        const isOpen = (!isNaN(nextDate) && today >= nextDate);

        // Update short badge (always present)
        if (shortStatusEl) {
            shortStatusEl.textContent = isOpen ? 'Open' : 'Upcoming';
            shortStatusEl.className = isOpen ? 'survey-badge open' : 'survey-badge upcoming';
        }

        // If a detailed badge still exists in markup, keep it in sync (optional)
        if (nextStatusEl) {
            nextStatusEl.textContent = isOpen ? 'Open' : 'Upcoming';
            nextStatusEl.className = isOpen ? 'survey-badge open' : 'survey-badge upcoming';
        }

        // Show a numeric indicator for surveys so the card reserves the same vertical
        // visual space as the other stat-cards. If there's a next survey date, show
        // "1" (one upcoming survey), otherwise show "0". This prevents an empty
        // placeholder (&nbsp;) which caused the card to visually collapse.
        if (surveyNumberEl) {
            surveyNumberEl.textContent = nextDateRaw ? '1' : '0';
        }
    }
})();
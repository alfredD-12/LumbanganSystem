// Intersection Observer for scroll-triggered animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-on-scroll');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Select all elements that need scroll-triggered animations
document.addEventListener('DOMContentLoaded', () => {
    // Stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        observer.observe(card);
    });

    // Announcement cards
    document.querySelectorAll('.announcement-card').forEach(card => {
        observer.observe(card);
    });

    // Showcase cards (card-showcase in welcome banner)
    // Skip these - they should animate on page load
    // document.querySelectorAll('.showcase-card').forEach(card => {
    //     observer.observe(card);
    // });

    // Badge items
    document.querySelectorAll('.badge-item').forEach(item => {
        observer.observe(item);
    });

    // Directory cards
    document.querySelectorAll('#directory > div > div:last-child > div').forEach(card => {
        observer.observe(card);
    });

    // Activity timeline cards
    document.querySelectorAll('#activities > div > div:last-child > div').forEach(card => {
        observer.observe(card);
    });
});

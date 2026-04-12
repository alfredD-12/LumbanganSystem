
    // Contact form Gmail handler
    document.addEventListener('DOMContentLoaded', function() {
      const contactForm = document.getElementById('contactForm');

      if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
          e.preventDefault();

          const name = document.getElementById('contactName').value;
          const email = document.getElementById('contactEmail').value;
          const subject = document.getElementById('contactSubject').value;
          const message = document.getElementById('contactMessage').value;

          // Build email body
          const bodyText = `Name: ${name}\nEmail: ${email}\n\nMessage:\n${message}`;

          // Open Gmail compose in new tab
          const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=bmis.lumbangan@gmail.com&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(bodyText)}`;
          window.open(gmailUrl, '_blank');

          // Reset form
          contactForm.reset();
        });
      }

      // Officials Carousel
      initOfficialsCarousel();
    });

    // Officials Carousel Functionality
    let currentOfficialsSlide = 0;
    let officialsPerSlide = 3;
    let officialsAutoplayInterval;

    function initOfficialsCarousel() {
      updateOfficialsPerSlide();
      createOfficialIndicators();
      updateOfficialCarousel();
      startOfficialsAutoplay();

      // Update on window resize
      window.addEventListener('resize', function() {
        updateOfficialsPerSlide();
        currentOfficialsSlide = 0;
        updateOfficialCarousel();
        createOfficialIndicators();
      });
    }

    function updateOfficialsPerSlide() {
      if (window.innerWidth <= 768) {
        officialsPerSlide = 1;
      } else if (window.innerWidth <= 992) {
        officialsPerSlide = 2;
      } else {
        officialsPerSlide = 3;
      }
    }

    function createOfficialIndicators() {
      const track = document.getElementById('officialsCarouselTrack');
      const indicatorsContainer = document.getElementById('officialsIndicators');
      const totalCards = track.children.length;
      const totalSlides = Math.ceil(totalCards / officialsPerSlide);

      indicatorsContainer.innerHTML = '';

      for (let i = 0; i < totalSlides; i++) {
        const indicator = document.createElement('button');
        indicator.className = 'carousel-indicator';
        indicator.onclick = () => goToOfficialSlide(i);
        if (i === currentOfficialsSlide) {
          indicator.classList.add('active');
        }
        indicatorsContainer.appendChild(indicator);
      }
    }

    function moveOfficialsCarousel(direction) {
      const track = document.getElementById('officialsCarouselTrack');
      const totalCards = track.children.length;
      const totalSlides = Math.ceil(totalCards / officialsPerSlide);

      currentOfficialsSlide += direction;

      if (currentOfficialsSlide >= totalSlides) {
        currentOfficialsSlide = 0;
      } else if (currentOfficialsSlide < 0) {
        currentOfficialsSlide = totalSlides - 1;
      }

      updateOfficialCarousel();
      resetOfficialsAutoplay();
    }

    function goToOfficialSlide(slideIndex) {
      currentOfficialsSlide = slideIndex;
      updateOfficialCarousel();
      resetOfficialsAutoplay();
    }

    function updateOfficialCarousel() {
      const track = document.getElementById('officialsCarouselTrack');
      const cardWidth = track.children[0].offsetWidth;
      const gap = 25;
      const offset = -(currentOfficialsSlide * (cardWidth + gap) * officialsPerSlide);

      track.style.transform = `translateX(${offset}px)`;

      // Update indicators
      const indicators = document.querySelectorAll('.carousel-indicator');
      indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentOfficialsSlide);
      });
    }

    function startOfficialsAutoplay() {
      officialsAutoplayInterval = setInterval(() => {
        moveOfficialsCarousel(1);
      }, 5000);
    }

    function resetOfficialsAutoplay() {
      clearInterval(officialsAutoplayInterval);
      startOfficialsAutoplay();
    }

    // Smooth Scroll for all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');

        // Skip bare '#' links (e.g. social icon placeholders)
        if (!targetId || targetId === '#') return;

        // If clicking home, scroll to top
        if (targetId === '#home') {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        } else {
          const target = document.querySelector(targetId);
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        }
      });
    });

    // Parallax effect for hero section
    let ticking = false;
    window.addEventListener('scroll', function() {
      if (!ticking) {
        window.requestAnimationFrame(function() {
          const scrolled = window.pageYOffset;
          const heroSection = document.querySelector('.hero-section');
          if (heroSection && scrolled < window.innerHeight) {
            heroSection.style.transform = `translateY(${scrolled * 0.5}px)`;
          }
          ticking = false;
        });
        ticking = true;
      }
    });
  

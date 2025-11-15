<?php
// Single-file Landing page that includes its own header and footer markup.
// Assets are referenced relative to app/views/landing/

// Check if user is already logged in - redirect to appropriate dashboard
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';

if (isLoggedIn()) {
    if (isUser()) {
        header('Location: ../Dashboard/dashboard.php');
        exit();
    } elseif (isOfficial()) {
        header('Location: ../Admin/admin_dashboard.php'); // Create this later
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Barangay Lumbangan | Nasugbu, Batangas</title>

  <!-- Vendor -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Landing page styles -->
  <link rel="stylesheet" href="../../assets/css/Landing/landing.css?v=2">
  <link rel="stylesheet" href="../../assets/css/Landing/news-styles.css?v=2">

  <!-- Optional news fetcher -->
  <script src="../../assets/js/Landing/batangas-news.js?v=2" defer></script>
</head>
<body>
  <!-- Floating Background Shapes -->
  <div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <!-- Header (Navbar) -->
  <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
      <!-- Brand / Logo Section -->
      <a class="navbar-brand d-flex align-items-center" href="#home">
        <div class="logo-circle">
          <i class="fas fa-landmark"></i>
        </div>
        <div class="brand-text">
          <h6 class="mb-0 fw-bold brand-title">Barangay Lumbangan</h6>
          <small class="brand-subtitle">Nasugbu, Batangas</small>
        </div>
      </a>

      <!-- Toggle for mobile -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navbar links -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto me-auto">
          <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#projects">Projects</a></li>
          <li class="nav-item"><a class="nav-link" href="#announcements">Announcements</a></li>
          <li class="nav-item"><a class="nav-link" href="#news-section">News</a></li>
          <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          <li class="nav-item"><a class="nav-link" href="#" id="openLoginModal">Login / Register</a></li>
        </ul>

        <!-- Government Seals (Right side) -->
        <div class="d-none d-lg-flex align-items-center gap-2">
          <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Bagong_Pilipinas_logo.png"
               alt="Bagong Pilipinas" title="Bagong Pilipinas"
               style="width: 38px; height: 38px; object-fit: contain;">
          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Seal_of_Nasugbu.png/599px-Seal_of_Nasugbu.png"
               alt="Nasugbu Seal" title="Municipality of Nasugbu"
               style="width: 38px; height: 38px; object-fit: contain;">
          <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Seal_of_Batangas.png"
               alt="Batangas Seal" title="Province of Batangas"
               style="width: 38px; height: 38px; object-fit: contain;">
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-section" id="home">
    <div class="container">
      <div class="hero-content">
        <div class="mb-5" style="animation: fadeInUp 1s ease; margin-top: -80px;">
          <img src="https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png"
               alt="Bagong Batangas"
               style="width: 400px; height: auto; filter: drop-shadow(0 10px 30px rgba(0,0,0,0.2));">
        </div>

        <div class="hero-badge">🏛️ Official Government Portal</div>
        <h1>BARANGAY LUMBANGAN</h1>
        <div class="hero-subtitle">Nasugbu, Batangas</div>
        <div class="hero-tagline">"Isang Barangay, Isang Puso, Para sa Lumbangan!"</div>
        <div class="hero-buttons">
          <a href="#announcements" class="btn btn-custom btn-primary-custom">View Announcements</a>
          <a href="#about" class="btn btn-custom btn-outline-custom">Discover More</a>
        </div>
      </div>
    </div>
    <div class="wave-animation">
      <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z"
              fill="rgba(30, 58, 95, 0.1)">
          <animate attributeName="d" dur="10s" repeatCount="indefinite"
            values="M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z;
                    M0,50 C150,0 350,100 600,50 C850,0 1050,100 1200,50 L1200,120 L0,120 Z;
                    M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z"/>
        </path>
      </svg>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="stats-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-6"><div class="stat-card"><div class="stat-number"><i class="fas fa-users"></i> 5,000+</div><div class="stat-label">Residents</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="stat-card"><div class="stat-number"><i class="fas fa-project-diagram"></i> 25+</div><div class="stat-label">Active Projects</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="stat-card"><div class="stat-number"><i class="fas fa-calendar-check"></i> 50+</div><div class="stat-label">Events Yearly</div></div></div>
        <div class="col-lg-3 col-md-6"><div class="stat-card"><div class="stat-number"><i class="fas fa-award"></i> 10+</div><div class="stat-label">Awards</div></div></div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section class="about-section" id="about">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">About Us</div>
        <h2 class="section-title">Welcome to Lumbangan</h2>
        <p class="section-subtitle">A progressive barangay committed to excellence in public service and community development</p>
      </div>
      <div class="row align-items-center">
        <div class="col-lg-6">
          <div class="about-img-wrapper">
            <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 800 600'><defs><linearGradient id='grad1' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' style='stop-color:%231e3a5f;stop-opacity:1'/><stop offset='100%' style='stop-color:%23c53030;stop-opacity:1'/></linearGradient></defs><rect fill='url(%23grad1)' width='800' height='600'/><circle fill='rgba(255,255,255,0.1)' cx='400' cy='300' r='200'/><circle fill='rgba(255,255,255,0.05)' cx='600' cy='150' r='100'/><rect fill='rgba(255,255,255,0.1)' x='100' y='400' width='200' height='150' rx='20'/></svg>"
                 alt="Barangay Hall" class="about-img">
          </div>
        </div>
        <div class="col-lg-6 about-content">
          <p class="about-text">Barangay Lumbangan is one of the progressive barangays of Nasugbu, Batangas, known for its peaceful community and hardworking residents. Our barangay is committed to providing excellent public service and fostering a safe, clean, and prosperous environment for all residents.</p>
          <p class="about-text">Through collaborative efforts and transparent governance, we strive to implement programs that enhance the quality of life of every family in Lumbangan.</p>
          <div class="mission-vision-grid">
            <div class="mv-card"><div class="mv-icon"><i class="fas fa-bullseye"></i></div><h5 class="mv-title">Mission</h5><p class="mv-text">To provide responsive, transparent, and inclusive governance that empowers every resident and promotes sustainable community development.</p></div>
            <div class="mv-card"><div class="mv-icon"><i class="fas fa-eye"></i></div><h5 class="mv-title">Vision</h5><p class="mv-text">A progressive, peaceful, and united Barangay Lumbangan where every resident enjoys a high quality of life.</p></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Projects Section -->
  <section class="projects-section" id="projects">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">Our Projects</div>
        <h2 class="section-title">Community Development</h2>
        <p class="section-subtitle">Building a better tomorrow through strategic initiatives and programs</p>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6 d-flex">
          <div class="project-card d-flex flex-column">
            <div class="project-img"><i class="fas fa-road"></i></div>
            <div class="project-body">
              <span class="project-status status-ongoing">Ongoing</span>
              <h5 class="project-title">Road Concreting Project</h5>
              <p style="color: #718096;">Improving road infrastructure to provide better access for residents and emergency services.</p>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="project-card d-flex flex-column">
            <div class="project-img"><i class="fas fa-basketball-ball"></i></div>
            <div class="project-body">
              <span class="project-status status-completed">Completed</span>
              <h5 class="project-title">Barangay Multi-Purpose Hall</h5>
              <p style="color: #718096;">A new community space for events, sports, and gatherings serving all residents.</p>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="project-card d-flex flex-column">
            <div class="project-img"><i class="fas fa-lightbulb"></i></div>
            <div class="project-body">
              <span class="project-status status-ongoing">Ongoing</span>
              <h5 class="project-title">Solar Street Lights</h5>
              <p style="color: #718096;">Installing eco-friendly solar-powered lighting to enhance safety and security.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Officials Section -->
  <section class="officials-section" id="officials">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">Leadership</div>
        <h2 class="section-title">Barangay Officials</h2>
        <p class="section-subtitle">Meet the dedicated leaders serving our community with integrity and passion</p>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6"><div class="official-card"><div class="official-img"><i class="fas fa-user"></i></div><div class="official-info"><div class="official-name">Hon. Juan Dela Cruz</div><div class="official-position">Punong Barangay</div></div></div></div>
        <div class="col-lg-4 col-md-6"><div class="official-card"><div class="official-img"><i class="fas fa-user"></i></div><div class="official-info"><div class="official-name">Hon. Maria Santos</div><div class="official-position">Kagawad</div></div></div></div>
        <div class="col-lg-4 col-md-6"><div class="official-card"><div class="official-img"><i class="fas fa-user"></i></div><div class="official-info"><div class="official-name">Hon. Pedro Reyes</div><div class="official-position">Kagawad</div></div></div></div>
        <div class="col-lg-4 col-md-6"><div class="official-card"><div class="official-img"><i class="fas fa-user"></i></div><div class="official-info"><div class="official-name">Hon. Ana Mercado</div><div class="official-position">Kagawad</div></div></div></div>
        <div class="col-lg-4 col-md-6"><div class="official-card"><div class="official-img"><i class="fas fa-user"></i></div><div class="official-info"><div class="official-name">Hon. Jose Garcia</div><div class="official-position">SK Chairman</div></div></div></div>
        <div class="col-lg-4 col-md-6"><div class="official-card"><div class="official-img"><i class="fas fa-user"></i></div><div class="official-info"><div class="official-name">Grace Flores</div><div class="official-position">Barangay Secretary</div></div></div></div>
      </div>
    </div>
  </section>

  <!-- Announcements Section -->
  <section class="announcements-section" id="announcements">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">Latest Updates</div>
        <h2 class="section-title">Announcements</h2>
        <p class="section-subtitle">Stay informed with the latest news, events, and advisories</p>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6"><div class="announcement-card"><div class="announcement-header"><i class="fas fa-bullhorn announcement-icon"></i><div><h5 style="margin: 0; font-size: 1rem;">Community Event</h5><div class="announcement-date">October 30, 2025</div></div></div><div class="announcement-body"><h5 class="announcement-title">Barangay Cleanup Drive</h5><p style="color: #718096; font-size: 0.95rem;">Join us for a community-wide cleanup drive. Let's work together to keep our barangay clean and beautiful. Bring your own cleaning materials.</p></div></div></div>
        <div class="col-lg-4 col-md-6"><div class="announcement-card"><div class="announcement-header"><i class="fas fa-exclamation-triangle announcement-icon"></i><div><h5 style="margin: 0; font-size: 1rem;">Advisory</h5><div class="announcement-date">October 28, 2025</div></div></div><div class="announcement-body"><h5 class="announcement-title">Scheduled Power Interruption</h5><p style="color: #718096; font-size: 0.95rem;">Please be advised of a scheduled power interruption on November 2, 2025 from 9:00 AM to 3:00 PM for maintenance work.</p></div></div></div>
        <div class="col-lg-4 col-md-6"><div class="announcement-card"><div class="announcement-header"><i class="fas fa-medkit announcement-icon"></i><div><h5 style="margin: 0; font-size: 1rem;">Health Program</h5><div class="announcement-date">October 25, 2025</div></div></div><div class="announcement-body"><h5 class="announcement-title">Free Medical Mission</h5><p style="color: #718096; font-size: 0.95rem;">Free medical consultation and medicines will be available for all residents. Bring your valid ID and barangay clearance.</p></div></div></div>
      </div>
    </div>
  </section>

  <!-- News Section -->
  <section class="news-section" id="news-section">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">Latest News</div>
        <h2 class="section-title">Batangas News & Updates</h2>
        <p class="section-subtitle">Real-time news from Batangas Provincial Government</p>
      </div>

      <div id="newsLoading" class="news-loading">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p>Fetching latest news from Batangas...</p>
      </div>

      <div id="newsError" class="news-error" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <p>Unable to load news at this time. Please try again later.</p>
      </div>

      <div id="newsContent" class="row" style="display: none;"></div>

      <div class="text-center mt-5">
        <button id="refreshNews" class="btn btn-custom btn-primary-custom">
          <i class="fas fa-sync-alt"></i> Refresh News
        </button>
      </div>
    </div>
  </section>

  <!-- Gallery Section -->
  <section class="gallery-section" id="gallery">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">Memories</div>
        <h2 class="section-title">Gallery</h2>
        <p class="section-subtitle">Capturing moments of community spirit and progress</p>
      </div>
      <div class="gallery-grid">
        <div class="gallery-item"><div class="gallery-img"><i class="fas fa-image"></i></div></div>
        <div class="gallery-item"><div class="gallery-img"><i class="fas fa-image"></i></div></div>
        <div class="gallery-item"><div class="gallery-img"><i class="fas fa-image"></i></div></div>
        <div class="gallery-item"><div class="gallery-img"><i class="fas fa-image"></i></div></div>
        <div class="gallery-item"><div class="gallery-img"><i class="fas fa-image"></i></div></div>
        <div class="gallery-item"><div class="gallery-img"><i class="fas fa-image"></i></div></div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="contact-section" id="contact">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">Get In Touch</div>
        <h2 class="section-title">Contact Us</h2>
        <p class="section-subtitle">We're here to serve you. Reach out to us for any concerns or inquiries</p>
      </div>
      <div class="row">
        <div class="col-lg-6">
          <div class="contact-card">
            <h4 style="color: var(--primary-blue); font-weight: 700; margin-bottom: 30px;">Contact Information</h4>
            <div class="contact-info-item">
              <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div>
                <h6 style="font-weight: 600; color: var(--primary-blue); margin-bottom: 5px;">Address</h6>
                <p style="color: #718096; margin: 0;">Lumbangan, Nasugbu, Batangas</p>
              </div>
            </div>
            <div class="contact-info-item">
              <div class="contact-icon"><i class="fas fa-phone"></i></div>
              <div>
                <h6 style="font-weight: 600; color: var(--primary-blue); margin-bottom: 5px;">Phone</h6>
                <p style="color: #718096; margin: 0;">(043) XXX-XXXX</p>
              </div>
            </div>
            <div class="contact-info-item">
              <div class="contact-icon"><i class="fas fa-envelope"></i></div>
              <div>
                <h6 style="font-weight: 600; color: var(--primary-blue); margin-bottom: 5px;">Email</h6>
                <p style="color: #718096; margin: 0;">barangay.lumbangan@nasugbu.gov.ph</p>
              </div>
            </div>
            <div class="contact-info-item">
              <div class="contact-icon"><i class="fas fa-clock"></i></div>
              <div>
                <h6 style="font-weight: 600; color: var(--primary-blue); margin-bottom: 5px;">Office Hours</h6>
                <p style="color: #718096; margin: 0;">Monday - Friday: 8:00 AM - 5:00 PM</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="contact-card">
            <h4 style="color: var(--primary-blue); font-weight: 700; margin-bottom: 30px;">Send Us a Message</h4>
            <form class="contact-form">
              <input type="text" class="form-control" placeholder="Your Name" required>
              <input type="email" class="form-control" placeholder="Your Email" required>
              <input type="text" class="form-control" placeholder="Subject" required>
              <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
              <button type="submit" class="btn btn-custom btn-primary-custom w-100">Send Message</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <h5 class="footer-title">Barangay Lumbangan</h5>
          <p style="color: rgba(255,255,255,0.85); line-height: 1.8;">
            Committed to serving the community with transparency, integrity, and excellence. Building a better future for all residents.
          </p>
          <div class="social-links mt-4">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
        <div class="col-lg-4 mb-4">
          <h5 class="footer-title">Quick Links</h5>
          <ul class="footer-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#projects">Projects</a></li>
            <li><a href="#announcements">Announcements</a></li>
            <li><a href="#gallery">Gallery</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div>
        <div class="col-lg-4 mb-4">
          <h5 class="footer-title">Office Hours</h5>
          <ul class="footer-links">
            <li style="color: rgba(255,255,255,0.85);">Monday - Friday</li>
            <li style="color: white; font-weight: 600;">8:00 AM - 5:00 PM</li>
            <li style="color: rgba(255,255,255,0.85); margin-top: 20px;">Saturday</li>
            <li style="color: white; font-weight: 600;">8:00 AM - 12:00 PM</li>
            <li style="color: rgba(255,255,255,0.85); margin-top: 20px;">Sunday & Holidays</li>
            <li style="color: white; font-weight: 600;">Closed</li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p style="margin: 0; color: rgba(255,255,255,0.7);">
          © 2025 Barangay Lumbangan, Nasugbu, Batangas. All rights reserved.
        </p>
      </div>
    </div>
  </footer>

  <!-- Login Modal (unchanged, used by navbar Login/Register) -->
  <div class="login-modal-overlay" id="loginModal">
    <div class="login-modal-container" id="loginModalContainer">
      <button class="login-modal-close" id="closeLoginModal"><i class="fas fa-times"></i></button>
      <img src="https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png" alt="batangas-logo" class="modal-main-logo">
      <div class="modal-sun-wrapper"><div class="modal-decorative-sun"></div></div>

      <div class="login-container" id="loginContainer">
        <!-- Sign Up -->
        <div class="login-form-container login-sign-up" style="overflow-y: auto;">
          <form id="signupForm" method="POST" action="javascript:void(0);">
            <h1>Create Account</h1>
            
            <!-- Registration Error Alert (after heading) -->
            <div id="registerErrorAlert" style="display: none; color: #d32f2f; margin: 15px 0; font-size: 13px; font-weight: 500;">
              <i class="fas fa-exclamation-circle" style="color: #d32f2f; margin-right: 6px;"></i>
              <span id="registerErrorMessage" style="color: #d32f2f;"></span>
            </div>
            <div class="login-social-icons">
              <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Seal_of_Batangas.png" alt="Batangas" style="width: 40px;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Seal_of_Nasugbu.png/599px-Seal_of_Nasugbu.png" style="width: 39px;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Bagong_Pilipinas_logo.png" style="width: 41px;">
            </div>
            <span>or use your email for registeration</span>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
              <input type="text" name="first_name" placeholder="First Name" required>
              <input type="text" name="middle_name" placeholder="Middle Name">
            </div>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <div style="position: relative; width: 100%;">
              <input type="text" name="username" id="usernameInput" placeholder="Username" required style="width: 100%; padding-right: 40px;">
              <span id="usernameCheck" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); display: none; z-index: 10;">
                <i class="fas fa-circle-notch fa-spin" id="usernameLoading" style="color: #999; font-size: 16px;"></i>
                <i class="fas fa-check-circle" id="usernameAvailable" style="color: #28a745; display: none; font-size: 16px;"></i>
                <i class="fas fa-times-circle" id="usernameTaken" style="color: #dc3545; display: none; font-size: 16px;"></i>
              </span>
            </div>
            <input type="text" name="email" placeholder="Email or Contact Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Sign Up</button>
          </form>
        </div>

        <!-- Sign In -->
        <div class="login-form-container login-sign-in">
          <form id="signinForm" method="POST" action="javascript:void(0);">
            <h1>Sign In</h1>
            <div class="login-social-icons">
              <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Seal_of_Batangas.png" alt="Batangas" style="width: 40px;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Seal_of_Nasugbu.png/599px-Seal_of_Nasugbu.png" style="width: 39px;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Bagong_Pilipinas_logo.png" style="width: 41px;">
            </div>
            <span style="color: #666; font-size: 0.9rem; margin-bottom: 15px; display: block;">Login as User or Official (auto-detected)</span>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <!-- Login Error Alert (above Forget Password) -->
            <div id="loginErrorAlert" style="display: none; color: #d32f2f; margin: 15px 0; font-size: 13px; font-weight: 500;">
              <i class="fas fa-exclamation-circle" style="color: #d32f2f; margin-right: 6px;"></i>
              <span id="loginErrorMessage" style="color: #d32f2f;"></span>
            </div>
            
            <a href="#">Forget Your Password?</a>
            <button type="submit">Sign In</button>
          </form>
        </div>

        <!-- Toggle Panels -->
        <div class="login-toggle-container">
          <div class="login-toggle">
            <div class="login-toggle-panel login-toggle-left">
              <h1 class="login-bouncy"><span class="login-typewriter-text"></span></h1>
              <p>Access your Barangay Lumbangan account to continue your tasks and services.</p>
              <button class="login-hidden" id="modalLoginBtn">Sign In</button>
            </div>
            <div class="login-toggle-panel login-toggle-right">
              <h1 class="login-bouncy"><span class="login-typewriter-text"></span></h1>
              <p>Register now to access online services of Barangay Lumbangan, Nasugbu.</p>
              <button class="login-hidden" id="modalRegisterBtn">Sign Up</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/Landing/Landing.js?v=2"></script>
  <script src="../../assets/js/Landing/login.js?v=2"></script>
</body>
</html>
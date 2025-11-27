<?php
require_once dirname(__DIR__, 2) . '/helpers/session_helper.php';
require_once dirname(__DIR__, 2) . '/config/Database.php';
require_once dirname(__DIR__, 2) . '/models/Gallery.php';
require_once dirname(__DIR__, 2) . '/models/Announcement.php';

// Fetch gallery items
$galleryModel = new Gallery();
$galleryItems = $galleryModel->getAll(true);

// Fetch latest 3 public announcements for the landing page
$announcementModel = new Announcement();
$publicAnnouncements = $announcementModel->getPublicAnnouncements('residents');
$landingAnnouncements = array_slice($publicAnnouncements, 0, 3);

if (isLoggedIn()) {
    if (isUser()) {
          $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_resident';
          header('Location: ' . $redirect);
        exit();
    } elseif (isOfficial()) {
      // Redirect officials to the routed official dashboard handled by the front controller
      $redirect = (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php?page=dashboard_official';
      header('Location: ' . $redirect);
      exit();
    }
}
?>
<?php include_once dirname(__DIR__, 2) . '/config/config.php'; ?>
<?php render_favicon(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Barangay Lumbangan | Nasugbu, Batangas</title>

  <meta name="app-auth-endpoint" content="<?php echo rtrim(BASE_URL, '/'); ?>/controllers/AuthController.php">

  <!-- Vendor -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Landing page styles -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/Landing/landing.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/Landing/news-styles.css?v=2">

  <!-- Optional news fetcher -->
  <script src="<?php echo BASE_URL; ?>/assets/js/Landing/batangas-news.js?v=2" defer></script>
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
        <div class="mb-4 matatag-logo-wrapper" style="animation: fadeInUp 1s ease;">
          <img src="https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png"
               alt="Bagong Batangas"
               class="matatag-logo"
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
      <?php
        // Filter public announcements for those with type 'project'
        $projectAnnouncementsAll = array_filter($publicAnnouncements, function($a) {
            return isset($a['type']) && strtolower(trim($a['type'])) === 'project';
        });
        // Re-index and take up to 3
        $projectAnnouncements = array_slice(array_values($projectAnnouncementsAll), 0, 3);
      ?>
      <div class="row">
        <?php if (!empty($projectAnnouncements)): ?>
          <?php foreach ($projectAnnouncements as $a): ?>
            <?php
              $id = htmlspecialchars($a['id']);
              $title = htmlspecialchars($a['title']);
              $message = htmlspecialchars($a['message']);
              $excerpt = htmlspecialchars(strlen($a['message']) > 120 ? substr($a['message'], 0, 120) . '...' : $a['message']);
              $date = htmlspecialchars(date('F j, Y', strtotime($a['created_at'])));
              $icon = !empty($a['image']) ? 'fas fa-image' : 'fas fa-bullhorn';
            ?>
            <div class="col-lg-4 col-md-6">
              <div class="announcement-card">
                <div class="announcement-header">
                  <i class="<?php echo $icon; ?> announcement-icon"></i>
                  <div>
                    <h5 style="margin: 0; font-size: 1rem;"><?php echo $title; ?></h5>
                    <div class="announcement-date"><?php echo $date; ?></div>
                  </div>
                </div>
                <div class="announcement-body">
                  <h5 class="announcement-title"><?php echo $title; ?></h5>
                  <p style="color: #718096; font-size: 0.95rem;"><?php echo $excerpt; ?></p>
                  <div style="display:flex; justify-content: flex-end;">
                    <button class="btn btn-custom btn-outline-custom" onclick="landingReadMore(this)"
                            data-id="<?php echo $id; ?>"
                            data-title="<?php echo $title; ?>"
                            data-message="<?php echo $message; ?>"
                            data-image="<?php echo htmlspecialchars($a['image']); ?>">
                      Read More
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- No Projects -->
          <div class="col-12">
              <div style="text-align: center; padding: 3rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                  <i class="fas fa-project-diagram" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                  <h4 style="color: #666; font-weight: 600;">No Projects Available</h4>
                  <p style="color: #999;">Check back later for updates</p>
              </div>
          </div>
        <?php endif; ?>
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
        <?php if (!empty($landingAnnouncements)): ?>
          <?php foreach ($landingAnnouncements as $a): ?>
            <?php
              $id = htmlspecialchars($a['id']);
              $title = htmlspecialchars($a['title']);
              $message = htmlspecialchars($a['message']);
              $excerpt = htmlspecialchars(strlen($a['message']) > 120 ? substr($a['message'], 0, 120) . '...' : $a['message']);
              $date = htmlspecialchars(date('F j, Y', strtotime($a['created_at'])));
              $icon = !empty($a['image']) ? 'fas fa-image' : 'fas fa-bullhorn';
            ?>
            <div class="col-lg-4 col-md-6">
              <div class="announcement-card">
                <div class="announcement-header">
                  <i class="<?php echo $icon; ?> announcement-icon"></i>
                  <div>
                    <h5 style="margin: 0; font-size: 1rem;"><?php echo $title; ?></h5>
                    <div class="announcement-date"><?php echo $date; ?></div>
                  </div>
                </div>
                <div class="announcement-body">
                  <h5 class="announcement-title"><?php echo $title; ?></h5>
                  <p style="color: #718096; font-size: 0.95rem;"><?php echo $excerpt; ?></p>
                  <div style="display:flex; justify-content: flex-end;">
                    <button class="btn btn-custom btn-outline-custom" onclick="landingReadMore(this)"
                            data-id="<?php echo $id; ?>"
                            data-title="<?php echo $title; ?>"
                            data-message="<?php echo $message; ?>"
                            data-image="<?php echo htmlspecialchars($a['image']); ?>">
                      Read More
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
                    <!-- No Announcements -->
                    <div class="col-12">
                        <div style="text-align: center; padding: 3rem; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                            <i class="fas fa-bullhorn" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <h4 style="color: #666; font-weight: 600;">No Announcements Available</h4>
                            <p style="color: #999;">Check back later for updates</p>
                        </div>
                    </div>
                <?php endif; ?>
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

      <div id="newsContent" class="news-carousel-wrapper" style="display: none;">
        <button class="news-carousel-nav news-carousel-prev" onclick="previousNews()">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="news-carousel-nav news-carousel-next" onclick="nextNews()">
          <i class="fas fa-chevron-right"></i>
        </button>
        <div class="news-carousel-container" id="newsCarouselContainer"></div>
        <div class="news-carousel-indicators" id="newsCarouselIndicators"></div>
      </div>

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
      
      <!-- 3D Gallery Carousel -->
      <div class="gallery-carousel-wrapper">
        
        <!-- Navigation Buttons -->
        <button class="gallery-carousel-nav gallery-carousel-prev" onclick="previousGallery()">
          <i class="fas fa-chevron-left"></i>
        </button>

        <button class="gallery-carousel-nav gallery-carousel-next" onclick="nextGallery()">
          <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Gallery Cards Container -->
        <div class="gallery-carousel-container">
          
          <?php if (empty($galleryItems)): ?>
            <!-- Default placeholder if no gallery items -->
            <div class="gallery-carousel-card active" data-index="0">
              <div class="gallery-img"><i class="fas fa-image"></i></div>
              <div class="gallery-card-overlay">
                <h4>No Gallery Items</h4>
                <p>Gallery items will appear here</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($galleryItems as $index => $item): ?>
              <?php 
                $imagePath = dirname(__DIR__, 2) . '/uploads/gallery/' . $item['image_path'];
                $imageUrl = BASE_URL . 'uploads/gallery/' . $item['image_path'];
              ?>
              <div class="gallery-carousel-card <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                <div class="gallery-img" style="cursor: pointer;" onclick="openImageLightbox('<?php echo htmlspecialchars($imageUrl); ?>')">
                  <?php if (!empty($item['image_path']) && file_exists($imagePath)): ?>
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                  <?php else: ?>
                    <i class="fas fa-image"></i>
                  <?php endif; ?>
                </div>
                <div class="gallery-card-overlay">
                  <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                  <p><?php echo htmlspecialchars($item['description']); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>

        <!-- Carousel Indicators -->
        <div class="gallery-carousel-indicators">
          <?php if (!empty($galleryItems)): ?>
            <?php foreach ($galleryItems as $index => $item): ?>
              <span class="gallery-indicator <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToGallery(<?php echo $index; ?>)"></span>
            <?php endforeach; ?>
          <?php else: ?>
            <span class="gallery-indicator active" onclick="goToGallery(0)"></span>
          <?php endif; ?>
        </div>
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
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="middle_name" placeholder="Middle Name">
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
          <div class="mobile-toggle-link" id="mobileToSignIn" style="display: none;">Already have an account? <strong>Sign In</strong></div>
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
            
            <a href="#" onclick="openForgetPasswordModal(); return false;">Forget Your Password?</a>
            <button type="submit">Sign In</button>
          </form>
          <div class="mobile-toggle-link" id="mobileToSignUp" style="display: none;">Don't have an account? <strong>Sign Up</strong></div>
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

  <!-- Email Verification Modal -->
  <?php require_once dirname(__DIR__, 2) . '/components/resident_components/email_verify_modal.php'; ?>

  <!-- Image Lightbox -->
  <div id="imageLightbox" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:99999; justify-content:center; align-items:center; cursor:pointer;" onclick="closeImageLightbox()">
    <img id="lightboxImg" style="max-width:90%; max-height:90vh; object-fit:contain; border-radius:8px; box-shadow:0 10px 50px rgba(0,0,0,0.7);" onclick="event.stopPropagation()">
  </div>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  
  <!-- Forget Password Script (Load BEFORE login.js) -->
  <script>
    let forgetPasswordEmailNew = '';
    let forgetPasswordTokenNew = '';


    function openForgetPasswordModal() {
      const modal = document.getElementById('forgetPasswordModal');
      const loginModal = document.getElementById('loginModal');
      
      // Hide login modal
      if (loginModal) {
        loginModal.style.display = 'none';
        loginModal.classList.remove('show');
        loginModal.style.pointerEvents = 'none'; // Disable login modal interaction
      }
      
      // Show forget password modal
      if (modal) {
        modal.style.display = 'flex';
        modal.style.zIndex = '99999';
        modal.style.pointerEvents = 'auto'; // Enable interaction
        showForgetStep(1);
      }

      // Prevent scrolling
      document.body.style.overflow = 'hidden';

      // Clear form
      document.getElementById('forgetEmail').value = '';
      document.getElementById('forgetCode').value = '';
      document.getElementById('forgetNewPassword').value = '';
      document.getElementById('forgetConfirmPassword').value = '';
      
      console.log('Forget modal opened');
    }

    function closeForgetModal() {
      const modal = document.getElementById('forgetPasswordModal');
      const loginModal = document.getElementById('loginModal');
      
      if (modal) {
        modal.style.display = 'none';
        modal.style.zIndex = '9999';
        modal.style.pointerEvents = 'none'; // Prevent interaction with hidden modal
      }
      
      // Don't show login modal - just close forget modal
      if (loginModal) {
        loginModal.style.pointerEvents = 'none'; // Also disable login modal
        loginModal.style.display = 'none';
      }
      
      // Allow scrolling again
      document.body.style.overflow = 'auto';
      
      // Reset all form fields
      document.getElementById('forgetEmail').value = '';
      document.getElementById('forgetCode').value = '';
      document.getElementById('forgetNewPassword').value = '';
      document.getElementById('forgetConfirmPassword').value = '';
      
      // Reset errors
      const errorDiv1 = document.getElementById('forgetStep1Error');
      const errorDiv2 = document.getElementById('forgetStep2Error');
      const errorDiv3 = document.getElementById('forgetStep3Error');
      
      if (errorDiv1) errorDiv1.style.display = 'none';
      if (errorDiv2) errorDiv2.style.display = 'none';
      if (errorDiv3) errorDiv3.style.display = 'none';
      document.getElementById('forgetStep3Success').style.display = 'none';
    }

    function backToLoginModal() {
      const forgetModal = document.getElementById('forgetPasswordModal');
      const loginModal = document.getElementById('loginModal');
      
      console.log('Back to login clicked');
      
      // Close forget password modal
      if (forgetModal) {
        forgetModal.style.display = 'none';
        forgetModal.style.pointerEvents = 'none';
        forgetModal.style.zIndex = '9999';
      }
      
      // Open login modal
      if (loginModal) {
        loginModal.style.display = 'flex';
        loginModal.style.pointerEvents = 'auto';
        loginModal.style.zIndex = '9999';
        loginModal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Keep scroll hidden while login modal is open
      }
      
      // Reset forget form
      document.getElementById('forgetEmail').value = '';
      document.getElementById('forgetCode').value = '';
      document.getElementById('forgetNewPassword').value = '';
      document.getElementById('forgetConfirmPassword').value = '';
      
      // Reset forget errors
      const errorDiv1 = document.getElementById('forgetStep1Error');
      const errorDiv2 = document.getElementById('forgetStep2Error');
      const errorDiv3 = document.getElementById('forgetStep3Error');
      
      if (errorDiv1) errorDiv1.style.display = 'none';
      if (errorDiv2) errorDiv2.style.display = 'none';
      if (errorDiv3) errorDiv3.style.display = 'none';
      document.getElementById('forgetStep3Success').style.display = 'none';
      
      // Show step 1
      showForgetStep(1);
      
      console.log('Back to login done');
    }

    function showForgetStep(step) {
      document.getElementById('forgetStep1').style.display = step === 1 ? 'block' : 'none';
      document.getElementById('forgetStep2').style.display = step === 2 ? 'block' : 'none';
      document.getElementById('forgetStep3').style.display = step === 3 ? 'block' : 'none';
    }

    function submitForgetEmailNew(e) {
      e.preventDefault();
      const email = document.getElementById('forgetEmail').value.trim();
      const errorDiv = document.getElementById('forgetStep1Error');
      const errorMsg = errorDiv ? errorDiv.querySelector('span') : null;

      if (!email) {
        if (errorMsg) errorMsg.textContent = 'Please enter your email';
        if (errorDiv) errorDiv.style.display = 'block';
        return;
      }

      const formData = new FormData();
      formData.append('email', email);

      fetch('<?php echo (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php'; ?>?action=request_reset', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          forgetPasswordEmailNew = email;
          document.getElementById('forgetEmailDisplay').textContent = email;
          showForgetStep(2);
          if (errorDiv) errorDiv.style.display = 'none';
        } else {
          if (errorMsg) errorMsg.textContent = data.message || 'Failed to send code';
          if (errorDiv) errorDiv.style.display = 'block';
        }
      })
      .catch(err => {
        if (errorMsg) errorMsg.textContent = 'An error occurred. Please try again.';
        if (errorDiv) errorDiv.style.display = 'block';
        console.error('Error:', err);
      });
    }

    function submitForgetCodeNew(e) {
      e.preventDefault();
      const code = document.getElementById('forgetCode').value.trim();
      const errorDiv = document.getElementById('forgetStep2Error');
      const errorMsg = errorDiv ? errorDiv.querySelector('span') : null;

      if (!code || code.length !== 6) {
        if (errorMsg) errorMsg.textContent = 'Please enter a valid 6-digit code';
        if (errorDiv) errorDiv.style.display = 'block';
        return;
      }

      const formData = new FormData();
      formData.append('email', forgetPasswordEmailNew);
      formData.append('code', code);

      fetch('<?php echo (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php'; ?>?action=verify_code', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          forgetPasswordTokenNew = data.token;
          showForgetStep(3);
          if (errorDiv) errorDiv.style.display = 'none';
        } else {
          if (errorMsg) errorMsg.textContent = data.message || 'Invalid or expired code';
          if (errorDiv) errorDiv.style.display = 'block';
        }
      })
      .catch(err => {
        if (errorMsg) errorMsg.textContent = 'An error occurred. Please try again.';
        if (errorDiv) errorDiv.style.display = 'block';
        console.error('Error:', err);
      });
    }

    function submitResetPasswordNew(e) {
      e.preventDefault();
      const password = document.getElementById('forgetNewPassword').value;
      const confirmPassword = document.getElementById('forgetConfirmPassword').value;
      const errorDiv = document.getElementById('forgetStep3Error');
      const successDiv = document.getElementById('forgetStep3Success');
      const errorMsg = errorDiv ? errorDiv.querySelector('span') : null;
      const successMsg = successDiv ? successDiv.querySelector('span') : null;

      if (errorDiv) errorDiv.style.display = 'none';
      if (successDiv) successDiv.style.display = 'none';

      if (!password || !confirmPassword) {
        if (errorMsg) errorMsg.textContent = 'Please enter both passwords';
        if (errorDiv) errorDiv.style.display = 'block';
        return;
      }

      if (password.length < 6) {
        if (errorMsg) errorMsg.textContent = 'Password must be at least 6 characters';
        if (errorDiv) errorDiv.style.display = 'block';
        return;
      }

      if (password !== confirmPassword) {
        if (errorMsg) errorMsg.textContent = 'Passwords do not match';
        if (errorDiv) errorDiv.style.display = 'block';
        return;
      }

      const formData = new FormData();
      formData.append('token', forgetPasswordTokenNew);
      formData.append('password', password);
      formData.append('confirm_password', confirmPassword);

      fetch('<?php echo (defined('BASE_PUBLIC') ? rtrim(BASE_PUBLIC, '/') : '') . '/index.php'; ?>?action=reset_password', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          if (successMsg) successMsg.textContent = data.message || 'Password reset successfully!';
          if (successDiv) successDiv.style.display = 'block';
          
          setTimeout(() => {
            closeForgetModal();
            document.getElementById('forgetEmail').value = '';
            document.getElementById('forgetCode').value = '';
            document.getElementById('forgetNewPassword').value = '';
            document.getElementById('forgetConfirmPassword').value = '';
          }, 2000);
        } else {
          if (errorMsg) errorMsg.textContent = data.message || 'Failed to reset password';
          if (errorDiv) errorDiv.style.display = 'block';
        }
      })
      .catch(err => {
        if (errorMsg) errorMsg.textContent = 'An error occurred. Please try again.';
        if (errorDiv) errorDiv.style.display = 'block';
        console.error('Error:', err);
      });
    }
  </script>
  
  <script src="<?php echo BASE_URL; ?>/assets/js/Landing/Landing.js?v=2"></script>
  <script src="<?php echo BASE_URL; ?>/assets/js/email_verification.js?v=<?php echo time(); ?>"></script>
  <script src="<?php echo BASE_URL; ?>/assets/js/Landing/login.js?v=2"></script>
  
  <!-- Announcement Modal for Landing -->
  <div class="modal fade" id="landingAnnouncementModal" tabindex="-1" aria-labelledby="landingAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="landingAnnouncementModalLabel"><i class="fas fa-bullhorn me-2"></i>Announcement Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h4 id="landingModalTitle" class="mb-3"></h4>
          <p class="text-muted small mb-3" id="landingModalMeta"></p>
          <div id="landingModalImageWrap" style="margin-bottom:1.5rem; display:none; text-align:center;">
            <img id="landingModalImage" src="" alt="" class="img-fluid rounded" style="width:100%; height:auto; max-height:60vh; object-fit:contain; display:block; margin:0 auto;">
          </div>
          <div id="landingModalMessageContainer">
            <div id="landingModalMessage" class="modal-message-content"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Close</button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    // Is user logged in? (set server-side)
    const isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;

    // Called by Read More buttons on landing announcements
    function landingReadMore(btn) {
      const id = btn.getAttribute('data-id');
      const title = btn.getAttribute('data-title') || '';
      const message = btn.getAttribute('data-message') || '';
      const image = btn.getAttribute('data-image') || '';

      if (!isLoggedIn) {
        // Trigger existing login modal (navbar login link has id 'openLoginModal')
        const openLogin = document.getElementById('openLoginModal');
        if (openLogin) openLogin.click();
        else {
          // Fallback: show the overlay directly
          const overlay = document.getElementById('loginModal');
          if (overlay) overlay.style.display = 'flex';
        }
        return;
      }

      // Populate modal and show (Bootstrap)
      document.getElementById('landingModalTitle').textContent = title;
      document.getElementById('landingModalMessage').textContent = message;
      if (image) {
        document.getElementById('landingModalImage').src = image;
        document.getElementById('landingModalImageWrap').style.display = 'block';
      } else {
        document.getElementById('landingModalImageWrap').style.display = 'none';
      }

      const modalEl = document.getElementById('landingAnnouncementModal');
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
    function openImageLightbox(imgSrc) {
      document.getElementById('imageLightbox').style.display = 'flex';
      document.getElementById('lightboxImg').src = imgSrc;
      document.body.style.overflow = 'hidden';
    }
    
    function closeImageLightbox() {
      document.getElementById('imageLightbox').style.display = 'none';
      document.body.style.overflow = 'auto';
    }
    
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeImageLightbox();
    });
  </script>
  
  <!-- Gallery Carousel Script -->
  <script>
    let currentGalleryIndex = 0;
    const totalGalleryItems = <?php echo !empty($galleryItems) ? count($galleryItems) : 1; ?>;

    function updateGalleryCarousel() {
        const cards = document.querySelectorAll('.gallery-carousel-card');
        const indicators = document.querySelectorAll('.gallery-indicator');
        
        cards.forEach((card, index) => {
            const dataIndex = parseInt(card.getAttribute('data-index'));
            let position = dataIndex - currentGalleryIndex;
            
            // Handle wrapping
            if (position > totalGalleryItems / 2) position -= totalGalleryItems;
            if (position < -totalGalleryItems / 2) position += totalGalleryItems;
            
            card.classList.remove('active');
            
            if (position === 0) {
                // Center card - active
                card.style.transform = 'translateX(0) scale(1) rotateY(0deg)';
                card.style.zIndex = '3';
                card.style.opacity = '1';
                card.style.filter = 'blur(0px)';
                card.style.pointerEvents = 'auto';
                card.classList.add('active');
            } else if (position === 1) {
                // Right card
                card.style.transform = 'translateX(550px) scale(0.85) rotateY(-15deg)';
                card.style.zIndex = '2';
                card.style.opacity = '0.5';
                card.style.filter = 'blur(2px)';
                card.style.pointerEvents = 'none';
            } else if (position === -1) {
                // Left card
                card.style.transform = 'translateX(-550px) scale(0.85) rotateY(15deg)';
                card.style.zIndex = '2';
                card.style.opacity = '0.5';
                card.style.filter = 'blur(2px)';
                card.style.pointerEvents = 'none';
            } else {
                // Hidden cards
                card.style.transform = position > 0 ? 'translateX(550px) scale(0.7)' : 'translateX(-550px) scale(0.7)';
                card.style.zIndex = '1';
                card.style.opacity = '0';
                card.style.filter = 'blur(3px)';
                card.style.pointerEvents = 'none';
            }
        });
        
        // Update indicators
        indicators.forEach((indicator, index) => {
            if (index === currentGalleryIndex) {
                indicator.classList.add('active');
            } else {
                indicator.classList.remove('active');
            }
        });
    }

    function nextGallery() {
        currentGalleryIndex = (currentGalleryIndex + 1) % totalGalleryItems;
        updateGalleryCarousel();
    }

    function previousGallery() {
        currentGalleryIndex = (currentGalleryIndex - 1 + totalGalleryItems) % totalGalleryItems;
        updateGalleryCarousel();
    }

    function goToGallery(index) {
        currentGalleryIndex = index;
        updateGalleryCarousel();
    }

    // Initialize carousel on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateGalleryCarousel();
        
        // Optional: Auto-advance carousel every 5 seconds
        // setInterval(nextGallery, 5000);
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            previousGallery();
        } else if (e.key === 'ArrowRight') {
            nextGallery();
        }
    });
  </script>

  <!-- Forget Password Modal (Simple Approach) -->
  <!-- Forget Password Modal (Similar design to login, no conflicts) -->
  <div id="forgetPasswordModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
    z-index: 99999;
    justify-content: center;
    align-items: center;
    padding: 20px;
  ">
    <div style="
      position: relative;
      animation: modalSlideIn 0.5s ease;
    ">
      <!-- Close Button -->
      <button onclick="closeForgetModal()" style="
        position: absolute;
        top: -50px;
        right: 0;
        background: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 20px;
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 10001;
      " onmouseover="this.style.background='#d32f2f'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='#667eea';">×</button>

      <!-- Logo -->
      <img src="https://portal.batangas.gov.ph/wp-content/uploads/2025/06/batangaslogo2025.png" alt="batangas-logo" style="
        position: absolute;
        top: -80px;
        left: 50%;
        transform: translateX(-50%);
        width: 200px;
        filter: drop-shadow(0 0 20px rgba(0,0,0,0.3));
        z-index: 10000;
      ">

      <!-- Main Container -->
      <div style="
        background-color: #fff;
        border-radius: 100px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
        max-width: 500px;
        width: 100%;
        padding: 60px 40px 40px;
        overflow-y: auto;
        max-height: 85vh;
      ">
        <!-- Step 1: Email -->
        <div id="forgetStep1" style="display: block;">
          <h1 style="color: #333; text-align: center; margin-bottom: 10px; font-size: 24px;">Reset Password</h1>
          <div style="display: flex; justify-content: center; gap: 15px; margin-bottom: 20px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Seal_of_Batangas.png" alt="Batangas" style="width: 40px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c0/Seal_of_Nasugbu.png/599px-Seal_of_Nasugbu.png" style="width: 39px;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Bagong_Pilipinas_logo.png" style="width: 41px;">
          </div>
          <p style="color: #666; text-align: center; margin-bottom: 30px; font-size: 0.95rem;">Enter your email and we'll send you a reset code</p>
          
          <form onsubmit="submitForgetEmailNew(event)" style="display: flex; flex-direction: column;">
            <input type="email" placeholder="Email address" id="forgetEmail" required style="
              padding: 12px 15px;
              margin-bottom: 15px;
              border: 1px solid #ddd;
              border-radius: 8px;
              font-size: 14px;
              width: 100%;
            ">
            <div id="forgetStep1Error" style="display: none; color: #d32f2f; margin-bottom: 15px; font-size: 13px; font-weight: 500;">
              <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>
              <span></span>
            </div>
            <button type="submit" style="
              padding: 10px 45px;
              background-color: #2600ff;
              color: white;
              border: 1px solid transparent;
              border-radius: 8px;
              font-weight: 600;
              cursor: pointer;
              font-size: 12px;
              letter-spacing: 0.5px;
              text-transform: uppercase;
              transition: all 0.3s ease-in-out;
            " onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 5px 15px rgba(0, 0, 0, 0.2)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">Send Reset Code</button>
          </form>
          <button type="button" onclick="backToLoginModal()" style="
            background: transparent;
            color: #667eea;
            margin-top: 15px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: underline;
            width: 100%;
          ">Back to Login</button>
        </div>

        <!-- Step 2: Code Verification -->
        <div id="forgetStep2" style="display: none;">
          <h1 style="color: #333; text-align: center; margin-bottom: 20px; font-size: 24px;">Verify Code</h1>
          <p style="color: #666; text-align: center; margin-bottom: 30px; font-size: 0.95rem;">Enter the 6-digit code sent to<br><strong id="forgetEmailDisplay" style="color: #333;"></strong></p>
          
          <form onsubmit="submitForgetCodeNew(event)" style="display: flex; flex-direction: column;">
            <input type="text" placeholder="000000" maxlength="6" id="forgetCode" required style="
              padding: 12px 15px;
              margin-bottom: 15px;
              border: 1px solid #ddd;
              border-radius: 8px;
              text-align: center;
              font-size: 24px;
              letter-spacing: 5px;
              font-weight: bold;
              width: 100%;
            ">
            <div id="forgetStep2Error" style="display: none; color: #d32f2f; margin-bottom: 15px; font-size: 13px; font-weight: 500;">
              <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>
              <span></span>
            </div>
            <button type="submit" style="
              padding: 10px 45px;
              background-color: #2600ff;
              color: white;
              border: 1px solid transparent;
              border-radius: 8px;
              font-weight: 600;
              cursor: pointer;
              font-size: 12px;
              letter-spacing: 0.5px;
              text-transform: uppercase;
              transition: all 0.3s ease-in-out;
            " onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 5px 15px rgba(0, 0, 0, 0.2)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">Verify Code</button>
          </form>
          <button type="button" onclick="showForgetStep(1)" style="
            background: transparent;
            color: #667eea;
            margin-top: 15px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: underline;
            width: 100%;
          ">Back</button>
        </div>

        <!-- Step 3: New Password -->
        <div id="forgetStep3" style="display: none;">
          <h1 style="color: #333; text-align: center; margin-bottom: 20px; font-size: 24px;">Create New Password</h1>
          <p style="color: #666; text-align: center; margin-bottom: 30px; font-size: 0.95rem;">Password must be at least 6 characters</p>
          
          <form onsubmit="submitResetPasswordNew(event)" style="display: flex; flex-direction: column;">
            <input type="password" placeholder="New Password" id="forgetNewPassword" required style="
              padding: 12px 15px;
              margin-bottom: 15px;
              border: 1px solid #ddd;
              border-radius: 8px;
              font-size: 14px;
              width: 100%;
            ">
            <input type="password" placeholder="Confirm Password" id="forgetConfirmPassword" required style="
              padding: 12px 15px;
              margin-bottom: 15px;
              border: 1px solid #ddd;
              border-radius: 8px;
              font-size: 14px;
              width: 100%;
            ">
            <div id="forgetStep3Error" style="display: none; color: #d32f2f; margin-bottom: 15px; font-size: 13px; font-weight: 500;">
              <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i>
              <span></span>
            </div>
            <div id="forgetStep3Success" style="display: none; color: #28a745; margin-bottom: 15px; font-size: 13px; font-weight: 500;">
              <i class="fas fa-check-circle" style="margin-right: 6px;"></i>
              <span></span>
            </div>
            <button type="submit" style="
              padding: 10px 45px;
              background-color: #2600ff;
              color: white;
              border: 1px solid transparent;
              border-radius: 8px;
              font-weight: 600;
              cursor: pointer;
              font-size: 12px;
              letter-spacing: 0.5px;
              text-transform: uppercase;
              transition: all 0.3s ease-in-out;
            " onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 5px 15px rgba(0, 0, 0, 0.2)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">Reset Password</button>
          </form>
        </div>

      </div>
    </div>
  </div>

  <style>
  </style>

  <style>
    @media (max-width: 768px) {
      #forgetPasswordModal {
        padding: 10px !important;
      }
      #forgetPasswordModal > div {
        padding: 20px !important;
      }
    }
  </style>

  <?php include dirname(__DIR__, 2) . '/components/ai_chatbot.php'; ?>

  <script src="<?php echo BASE_URL; ?>assets/js/ai_chatbot.js"></script>
  
  
</body>
</html>
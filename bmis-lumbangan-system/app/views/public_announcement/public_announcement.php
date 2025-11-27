<?php
// Prevent direct access to this view file. This view is intended to be
// included by the `PublicAnnouncementController::index()` action (via
// the front controller). Opening the view directly in the browser will
// bypass controller setup (models, helpers, constants) and commonly
// produce undefined variable/function errors.
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Location: /Lumbangan_BMIS/bmis-lumbangan-system/public/index.php?page=public_announcement');
    exit;
}
// Ensure session helper is available so header/footer and session helpers work correctly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include session helper (provides isOfficial(), getFullName(), getUsername(), getFirstName(), etc.)
require_once dirname(__DIR__) . '/../helpers/session_helper.php';

// Get user info from existing session functions (if logged in)
$fullName = isLoggedIn() ? getFullName() : 'Guest';
$username = isLoggedIn() ? getUsername() : '';
$firstName = isLoggedIn() ? getFirstName() : 'Guest';


// Provide a page title, then include the resident header which outputs DOCTYPE, <head> and the navigation.
// We avoid modifying header-resident.php per your request.
$pageTitle = 'Public Announcements - Barangay Lumbangan';
include __DIR__ . '/../../components/resident_components/header-resident.php';
?>
<link rel="stylesheet" href="<?php echo rtrim(BASE_URL, '/'); ?>/assets/css/announcement/public_announcements_modern.css?v=<?php echo time(); ?>">

<div class="public-announcements">
    
    <!-- Hero Header -->
    <header class="hero-header">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <span class="title-highlight">Community</span> Announcements
                </h1>
                <p class="hero-subtitle">
                    Welcome, <strong><?php echo htmlspecialchars($firstName); ?></strong>! Stay informed with the latest updates for <strong><?php echo ucfirst($role); ?></strong>
                </p>
            </div>
        </div>
    </header>

    <!-- Quick Filters Bar -->
    <section class="filters-bar">
        <div class="container">
            <form method="get" class="filters-compact">
                <input type="hidden" name="page" value="public_announcement">
                
                <div class="filter-group">
                    <div class="filter-item">
                        <i class="fas fa-search filter-icon"></i>
                        <input name="q" type="text" class="filter-input" placeholder="Search announcements...and types" value="<?php echo htmlspecialchars($q ?? ''); ?>">
                    </div>
                    
                    <div class="filter-item">
                        <i class="fas fa-calendar-alt filter-icon"></i>
                        <input id="start_date" name="start_date" type="date" class="filter-input" placeholder="From date" value="<?php echo htmlspecialchars($start_date ?? ''); ?>">
                    </div>
                    
                    <div class="filter-item">
                        <i class="fas fa-calendar-check filter-icon"></i>
                        <input id="end_date" name="end_date" type="date" class="filter-input" placeholder="To date" value="<?php echo htmlspecialchars($end_date ?? ''); ?>">
                    </div>
                    <!-- type filter removed per request -->
                    
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    
                    <?php if ($start_date || $end_date || $q): ?>
                        <a href="?page=public_announcement" class="btn-reset">
                            <i class="fas fa-times-circle"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </section>

    <?php if (!empty($todaysAnnouncements)): ?>
    <!-- Today's Highlights Section - 3D Carousel -->
    <section class="featured-section container">
        <div class="section-header-modern">
            <div class="header-badge">
                <i class="fas fa-star"></i>
                <span>Today's Highlights</span>
            </div>
            <h2 class="section-title-modern">Latest Updates</h2>
        </div>

        <div class="carousel-3d-container">
            <div class="carousel-3d" id="todayCarousel">
                <?php 
                $displayLimit = 6; // Show max 6 featured items
                $todayCount = count($todaysAnnouncements);
                $displayToday = array_slice($todaysAnnouncements, 0, $displayLimit);
                foreach ($displayToday as $index => $a): 
                    $data_title = htmlspecialchars($a['title'], ENT_QUOTES);
                        $data_message = htmlspecialchars($a['message'], ENT_QUOTES);
                        $data_image = $a['image'] ? htmlspecialchars(announcement_image_url($a['image']), ENT_QUOTES) : '';
                    $data_author = htmlspecialchars($a['author'], ENT_QUOTES);
                    $data_audience = htmlspecialchars($a['audience'], ENT_QUOTES);
                    $data_type = htmlspecialchars($a['type'] ?? 'general', ENT_QUOTES);
                    $data_created = htmlspecialchars(date('M d, Y h:i A', strtotime($a['created_at'])), ENT_QUOTES);
                ?>
                <article class="carousel-card-3d"
                         data-index="<?php echo $index; ?>"
                         onclick="openAnnouncementModal(this)"
                         data-title="<?php echo $data_title; ?>"
                         data-message="<?php echo $data_message; ?>"
                         data-image="<?php echo $data_image; ?>"
                         data-type="<?php echo $data_type; ?>"
                         data-author="<?php echo $data_author; ?>"
                         data-audience="<?php echo $data_audience; ?>"
                         data-created="<?php echo $data_created; ?>">
                    <div class="card-3d-inner">
                        <div class="card-3d-front">
                                    <div class="card-image-wrapper">
                                <?php if ($a['image']): ?>
                                    <img src="<?php echo htmlspecialchars(announcement_image_url($a['image'])); ?>" class="card-image" alt="<?php echo htmlspecialchars($a['title']); ?>">
                                <?php else: ?>
                                    <div class="card-image-placeholder">
                                                <i class="fas fa-bullhorn"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-badge today-badge">
                                    <i class="fas fa-clock"></i> Today
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-meta">
                                    <span class="meta-audience audience-<?php echo $a['audience']; ?>">
                                        <i class="fas fa-users"></i> <?php echo ucfirst($a['audience']); ?>
                                    </span>
                                    <span class="meta-type type-<?php echo htmlspecialchars($a['type'] ?? 'general'); ?>">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars(ucfirst($a['type'] ?? 'general')); ?>
                                    </span>
                                    <span class="meta-time">
                                        <i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($a['created_at'])); ?>
                                    </span>
                                </div>
                                <h3 class="card-title"><?php echo htmlspecialchars($a['title']); ?></h3>
                                <p class="card-excerpt"><?php echo htmlspecialchars(substr($a['message'], 0, 120)) . (strlen($a['message']) > 120 ? '...' : ''); ?></p>
                                <div class="card-footer-info">
                                    <span class="card-author">
                                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($a['author']); ?>
                                    </span>
                                    <span class="read-more-link">
                                        Read More <i class="fas fa-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Carousel Navigation -->
            <button class="carousel-nav-3d prev" id="prevBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-nav-3d next" id="nextBtn">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Carousel Indicators -->
            <div class="carousel-indicators-3d" id="carouselIndicators">
                <?php foreach ($displayToday as $index => $a): ?>
                <button class="indicator-dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                        data-slide-to="<?php echo $index; ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- All Announcements Section - Enhanced 3D Carousel -->
    <section class="main-content container">
        <div class="section-header-modern enhanced">
            <div class="header-badge secondary-badge">
                <i class="fas fa-archive"></i>
                <span>Archives</span>
            </div>
            <h2 class="section-title-modern">Earlier Announcements</h2>
            <p class="section-subtitle">Explore our comprehensive collection of past announcements</p>
        </div>

        <?php if (empty($announcements)): ?>
            <div class="empty-state-modern">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3>No Earlier Announcements</h3>
                <p><?php echo empty($todaysAnnouncements)
                    ? 'No announcements available for ' . htmlspecialchars(ucfirst($role)) . ' yet.'
                    : 'All caught up! Check back later for more updates.'; ?></p>
            </div>
        <?php else: ?>
            <!-- Enhanced 3D Carousel Container -->
            <div class="enhanced-carousel-wrapper">
                <div class="carousel-3d-container earlier-carousel enhanced">
                    <div class="carousel-3d" id="earlierCarousel">
                        <?php 
                        foreach ($announcements as $index => $a): 
                            $data_title = htmlspecialchars($a['title'], ENT_QUOTES);
                                $data_message = htmlspecialchars($a['message'], ENT_QUOTES);
                                $data_image = $a['image'] ? htmlspecialchars(announcement_image_url($a['image']), ENT_QUOTES) : '';
                            $data_author = htmlspecialchars($a['author'], ENT_QUOTES);
                            $data_audience = htmlspecialchars($a['audience'], ENT_QUOTES);
                            $data_type = htmlspecialchars($a['type'] ?? 'general', ENT_QUOTES);
                            $data_created = htmlspecialchars(date('M d, Y h:i A', strtotime($a['created_at'])), ENT_QUOTES);
                        ?>
                        <article class="carousel-card-3d earlier-card enhanced"
                                 data-index="<?php echo $index; ?>"
                                 onclick="openAnnouncementModal(this)"
                                 data-title="<?php echo $data_title; ?>"
                                 data-message="<?php echo $data_message; ?>"
                                 data-image="<?php echo $data_image; ?>"
                                 data-type="<?php echo $data_type; ?>"
                                 data-author="<?php echo $data_author; ?>"
                                 data-audience="<?php echo $data_audience; ?>"
                                 data-created="<?php echo $data_created; ?>">
                            <div class="card-3d-inner">
                                <div class="card-3d-front enhanced">
                                    <!-- Enhanced Image Section -->
                                    <div class="card-image-wrapper enhanced">
                                        <?php if ($a['image']): ?>
                                            <img src="<?php echo htmlspecialchars(announcement_image_url($a['image'])); ?>" 
                                                 class="card-image" 
                                                 alt="<?php echo htmlspecialchars($a['title']); ?>"
                                                 loading="lazy">
                                            <div class="image-overlay-gradient"></div>
                                        <?php else: ?>
                                            <div class="card-image-placeholder enhanced">
                                                <div class="placeholder-icon-wrapper">
                                                    <i class="fas fa-bullhorn"></i>
                                                </div>
                                                <span class="placeholder-text">No Image Available</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Enhanced Date Badge -->
                                        <div class="card-date-badge-enhanced">
                                            <div class="date-badge-inner">
                                                <span class="date-day"><?php echo date('d', strtotime($a['created_at'])); ?></span>
                                                <span class="date-month"><?php echo date('M', strtotime($a['created_at'])); ?></span>
                                                <span class="date-year"><?php echo date('Y', strtotime($a['created_at'])); ?></span>
                                            </div>
                                        </div>
                                        
                                        <!-- View Icon Overlay -->
                                        <div class="card-hover-overlay">
                                            <div class="hover-icon-wrapper">
                                                <i class="fas fa-eye"></i>
                                                <span class="hover-text">View Details</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Enhanced Content Section -->
                                    <div class="card-content enhanced">
                                        <div class="card-meta-enhanced">
                                            <span class="meta-badge-enhanced audience-<?php echo $a['audience']; ?>">
                                                <i class="fas fa-users"></i>
                                                <span><?php echo ucfirst($a['audience']); ?></span>
                                            </span>
                                            <span class="meta-badge-enhanced type-<?php echo htmlspecialchars($a['type'] ?? 'general'); ?>">
                                                <i class="fas fa-tag"></i>
                                                <span><?php echo htmlspecialchars(ucfirst($a['type'] ?? 'general')); ?></span>
                                            </span>
                                            <span class="meta-time-enhanced">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span><?php echo date('M d, Y', strtotime($a['created_at'])); ?></span>
                                            </span>
                                        </div>
                                        
                                        <h3 class="card-title enhanced"><?php echo htmlspecialchars($a['title']); ?></h3>
                                        
                                        <p class="card-excerpt enhanced"><?php echo htmlspecialchars(substr($a['message'], 0, 140)) . (strlen($a['message']) > 140 ? '...' : ''); ?></p>
                                        
                                        <div class="card-footer-enhanced">
                                            <div class="author-info-enhanced">
                                                <div class="author-avatar-enhanced">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="author-details-enhanced">
                                                    <span class="author-name"><?php echo htmlspecialchars($a['author']); ?></span>
                                                    <span class="author-label">Posted by</span>
                                                </div>
                                            </div>
                                            <button class="read-more-btn-enhanced">
                                                <span class="btn-text">Read</span>
                                                <i class="fas fa-arrow-circle-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Enhanced Navigation Arrows -->
                    <button class="carousel-nav-3d prev enhanced" id="earlierPrevBtn">
                        <i class="fas fa-chevron-left"></i>
                        <span class="nav-label"></span>
                    </button>
                    <button class="carousel-nav-3d next enhanced" id="earlierNextBtn">
                        <span class="nav-label"></span>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <!-- Enhanced Control Panel -->
                <div class="carousel-control-panel">
                    <div class="control-panel-inner">
                        <!-- Page Navigation -->
                        <div class="page-navigation">
                            <button class="page-nav-btn" id="prevPageBtn" title="Previous Page">
                                <i class="fas fa-angle-double-left"></i>
                                <span>Prev 10</span>
                            </button>
                            
                            <!-- Indicators -->
                            <div class="indicators-section">
                                <div class="carousel-indicators-3d enhanced" id="earlierCarouselIndicators">
                                    <?php 
                                    $maxIndicators = min(count($announcements), 10);
                                    for ($i = 0; $i < $maxIndicators; $i++): 
                                    ?>
                                    <button class="indicator-dot enhanced <?php echo $i === 0 ? 'active' : ''; ?>" 
                                            data-slide-to="<?php echo $i; ?>"
                                            title="Announcement <?php echo $i + 1; ?>"></button>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <button class="page-nav-btn" id="nextPageBtn" title="Next Page">
                                <span>Next 10</span>
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                        </div>
                        
                        <!-- Counter Display -->
                        <div class="carousel-counter-enhanced">
                            <div class="counter-content">
                                <span class="counter-label">Viewing</span>
                                <span class="counter-numbers">
                                    <span id="currentSlide" class="current">1</span>
                                    <span class="separator">/</span>
                                    <span id="totalSlides" class="total"><?php echo count($announcements); ?></span>
                                </span>
                                <span class="counter-text">announcements</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Bootstrap CSS/JS and Font Awesome are provided by resident header/footer to avoid duplicates -->


<script>
document.addEventListener('DOMContentLoaded', function () {
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');
    const form = document.querySelector('form.filters-compact');

    function updateConstraints() {
        if (!start || !end) return;

        if (start.value) {
            end.min = start.value;
        } else {
            end.removeAttribute('min');
        }

        if (end.value) {
            start.max = end.value;
        } else {
            start.removeAttribute('max');
        }

        // If both set and start > end, correct end to start
        if (start.value && end.value && start.value > end.value) {
            end.value = start.value;
        }
    }

    if (start) start.addEventListener('change', updateConstraints);
    if (end) end.addEventListener('change', updateConstraints);

    if (form) {
        form.addEventListener('submit', function (e) {
            if (start && end && start.value && end.value && start.value > end.value) {
                e.preventDefault();
                // brief accessible notification
                alert('Please ensure the start date is the same as or before the end date.');
                start.focus();
            }
        });
    }

    // Initialize constraints on load
    updateConstraints();
});
</script>

<!-- Announcement view modal (reused) -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="announcementModalLabel"><i class="fas fa-bullhorn me-2"></i>Announcement Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 id="modalTitle" class="mb-3"></h4>
                <p class="text-muted small mb-3" id="modalMeta"></p>
                <div id="modalImageWrap" style="margin-bottom:1.5rem; display:none; text-align:center;">
                    <div id="modalImageSpinner" style="display:none; margin:0 auto 0.5rem;">
                        <div class="spinner-border text-secondary" role="status" style="width:2.5rem; height:2.5rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <img id="modalImage" src="" alt="" class="img-fluid rounded" style="cursor:pointer; width:100%; height:auto; max-height:60vh; object-fit:contain; display:block; margin:0 auto;">
                </div>
                <div id="modalMessageContainer">
                    <div id="modalMessage" class="modal-message-content"></div>
                    <button id="viewMoreBtn" class="btn btn-link text-primary p-0 mt-2" style="display:none;">
                        <i class="fas fa-chevron-down me-1"></i>View More
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Image full view modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0">
                <img id="imageModalImg" src="" alt="full image" class="rounded" style="display:block; margin:0 auto; width:auto; max-width:100%; max-height:90vh; object-fit:contain;">
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../components/resident_components/footer-resident.php'; ?>


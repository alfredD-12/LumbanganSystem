<?php
// Footer partial (example). Replace the existing footer block content with this file.
// NOTE: This file uses the BASE_URL PHP constant already present in your app.
?>
<footer class="dashboard-footer footer mt-5 pt-5 pb-3 position-relative">
    <!-- Floating Shapes Background -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

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
                    <li><a href="<?php echo h(BASE_PUBLIC . 'index.php?page=dashboard_resident'); ?>">Dashboard</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#announcements">Announcements</a></li>
                    <li><a href="#profile">My Profile</a></li>
                </ul>
            </div>
            <div class="col-lg-4 mb-4">
                <h5 class="footer-title">Contact Information</h5>
                <ul class="footer-links">
                    <li style="color: rgba(255,255,255,0.85);"><i class="fas fa-map-marker-alt" style="margin-right: 10px;"></i>Lumbangan, Nasugbu, Batangas</li>
                    <li style="color: rgba(255,255,255,0.85);"><i class="fas fa-phone" style="margin-right: 10px;"></i>(043) XXX-XXXX</li>
                    <li style="color: rgba(255,255,255,0.85);"><i class="fas fa-envelope" style="margin-right: 10px;"></i>barangay.lumbangan@nasugbu.gov.ph</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p style="margin: 0; color: rgba(255,255,255,0.7);">
                © 2025 Barangay Lumbangan, Nasugbu, Batangas. All rights reserved.
            </p>
        </div>
    </div>

    <!-- Wave effect at bottom of footer -->
    <div class="footer-wave">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z"
                fill="rgba(255, 255, 255, 0.1)">
                <animate attributeName="d" dur="10s" repeatCount="indefinite"
                    values="M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z;
                        M0,50 C150,0 350,100 600,50 C850,0 1050,100 1200,50 L1200,120 L0,120 Z;
                        M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z" />
            </path>
        </svg>
    </div>
</footer>

<!-- Expose runtime base URL for static JS files and provide small shims -->
<script>
    // Make sure all static JS can build absolute URLs
    window.BASE_URL = '<?php echo rtrim(BASE_URL, "/"); ?>';

    // Provide a no-op shim for addManualDropdownListeners if not defined elsewhere.
    // This avoids ReferenceError in document_resident.js when the real implementation isn't loaded early enough.
    if (typeof window.addManualDropdownListeners !== 'function') {
        window.addManualDropdownListeners = function() {
            /* no-op fallback */
        };
    }

    // Optional: expose a small helper path for Survey controller endpoints
    window.SURVEY_API = window.BASE_URL + '/controllers/SurveyController.php';
</script>

<!-- 1) Core dependencies -->
<!-- Load Bootstrap first so scripts that use bootstrap.Modal etc can run safely -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Resident-specific JS -->
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/resident/document_resident.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/resident/document_resident.js'); ?>"></script>

<!-- Document request logic -->
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/document_request.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/document_request.js'); ?>"></script>

<!-- 2) Survey scripts (use BASE_URL absolute paths; load only once each) -->
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/wizard_angina.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/wizard_angina.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/wizard_diabetes.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/wizard_diabetes.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/wizard_family_history.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/wizard_family_history.js'); ?>"></script>
<!-- ensure wizard_family uses the corrected static JS (no PHP embedded) and included once -->
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/wizard_household.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/wizard_household.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/wizard_family.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/wizard_family.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/wizard_lifestyle.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/wizard_lifestyle.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/wizard_personal.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/wizard_personal.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/wizard_vitals.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/wizard_vitals.js'); ?>"></script>

<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/survey-persistence.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/survey-persistence.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/save-survey.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/save-survey.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/tree-enhance.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/tree-enhance.js'); ?>"></script>
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Survey/bhw-float-control.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Survey/bhw-float-control.js'); ?>"></script>

<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/Dashboard/header-resident.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/Dashboard/header-resident.js'); ?>"></script>


<!-- Announcements / other page-specific scripts -->
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/announcement/public_announcements.js?v=<?php echo time(); ?>"></script>

<!-- Residents Complaint Page JS -->
<script src="<?php echo rtrim(BASE_URL, '/'); ?>/assets/js/residents/residents.js?v=<?php echo time(); ?>"></script>
<!-- Base url from config.php -->
<script>
    const BASE_URL = "<?php echo BASE_URL; ?>";
    const BASE_PUBLIC = "<?php echo BASE_PUBLIC; ?>";
</script>
</body>

</html>
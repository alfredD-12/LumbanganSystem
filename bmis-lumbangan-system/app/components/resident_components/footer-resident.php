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
                <li><a href="#dashboard">Dashboard</a></li>
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
                        M0,0 C150,60 350,0 600,50 C850,100 1050,50 1200,0 L1200,120 L0,120 Z"/>
            </path>
            </svg>
        </div>
    </footer>
    
    <!-- JS FOR UI (NEW HEADER AND FOOTER) -->
    <script src="<?php echo BASE_URL . 'assets/js/resident/document_resident.js'?>"></script>
    <!-- JS FOR BOOTSTRAP CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS FOR DOCUMENT REQUEST -->
    <script src="<?php echo BASE_URL . 'assets/js/document_request.js'; ?>"></script>
    <!-- PAKILAGAY NALANG DIN SA BABA NG DEPENDENCIES OR CDNS -->
    
    <!-- Page JS -->
    <script src="../../assets/js/Survey/wizard_angina.js"></script>
    <script src="../../assets/js/Survey/wizard_diabetes.js"></script>
    <script src="../../assets/js/Survey/wizard_family_history.js"></script>
    <script src="../../assets/js/Survey/wizard_family.js"></script>
    <script src="../../assets/js/Survey/wizard_family.js"></script>
    <script src="../../assets/js/Survey/wizard_lifestyle.js"></script>
    <script src="../../assets/js/Survey/wizard_lifestyle.js"></script>
    <script src="../../assets/js/Survey/wizard_vitals.js"></script>

    <script src="../../assets/js/Survey/survey-persistence.js"></script>
    <script src="../../assets/js/Survey/save-survey.js"></script>
    <script src="../../assets/js/Survey/tree-enhance.js"></script>
    <script src="../../assets/js/Survey/bhw-float-control.js"></script>

    <script src="<?php echo BASE_URL; ?>assets/js/announcement/public_announcements.js?v=<?php echo time(); ?>"></script>
</body>
</html>
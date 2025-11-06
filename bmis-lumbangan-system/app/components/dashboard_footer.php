<!-- Dashboard Footer Component -->
<!-- Self-contained with inline CSS - No external CSS dependencies! -->
<!-- Required: Bootstrap 5.3.2, Font Awesome 6.4.0 -->

<style>
    /* CSS Variables */
    :root {
        --primary-blue: #1e3a5f;
        --secondary-blue: #2c5282;
        --accent-red: #c53030;
    }

    /* Footer Styles */
    .footer {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 80px 0 30px;
        position: relative;
        overflow: visible;
        width: 100%;
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--accent-red), transparent);
    }

    .footer-title {
        font-weight: 700;
        margin-bottom: 25px;
        font-size: 1.3rem;
    }

    .footer-links {
        list-style: none;
        padding: 0;
    }

    .footer-links li {
        margin-bottom: 15px;
    }

    .footer-links a {
        color: rgba(255,255,255,0.85);
        text-decoration: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .footer-links a:hover {
        color: white;
        padding-left: 10px;
    }

    .footer-links a::before {
        content: '→';
        opacity: 0;
        transition: all 0.3s;
    }

    .footer-links a:hover::before {
        opacity: 1;
    }

    .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.15);
        border-radius: 50%;
        color: white;
        margin-right: 15px;
        transition: all 0.4s;
        font-size: 1.1rem;
        backdrop-filter: blur(10px);
        text-decoration: none;
    }

    .social-links a:hover {
        background: var(--accent-red);
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 10px 25px rgba(197, 48, 48, 0.4);
    }

    .footer-bottom {
        margin-top: 60px;
        padding-top: 30px;
        border-top: 1px solid rgba(255,255,255,0.15);
        text-align: center;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .footer {
            padding: 50px 0 20px;
        }

        .footer-title {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .footer-bottom {
            margin-top: 40px;
            padding-top: 20px;
        }
    }
</style>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5 class="footer-title">Barangay Lumbangan</h5>
                <p style="color: rgba(255,255,255,0.85); line-height: 1.8;">
                    Committed to serving the community with transparency, integrity, and excellence. Building a better future for all residents.
                </p>
                <div class="social-links mt-4">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
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
                    <li style="color: rgba(255,255,255,0.85);">
                        <i class="fas fa-map-marker-alt" style="margin-right: 10px;"></i>
                        Lumbangan, Nasugbu, Batangas
                    </li>
                    <li style="color: rgba(255,255,255,0.85);">
                        <i class="fas fa-phone" style="margin-right: 10px;"></i>
                        (043) XXX-XXXX
                    </li>
                    <li style="color: rgba(255,255,255,0.85);">
                        <i class="fas fa-envelope" style="margin-right: 10px;"></i>
                        barangay.lumbangan@nasugbu.gov.ph
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p style="margin: 0; color: rgba(255,255,255,0.7);">
                © <?php echo date('Y'); ?> Barangay Lumbangan, Nasugbu, Batangas. All rights reserved.
            </p>
        </div>
    </div>
</footer>

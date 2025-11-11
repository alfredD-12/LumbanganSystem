<?php
// Footer component: Loads namespaced CSS to prevent bleeding, but mirrors dashboard.css styling exactly

$scriptDir   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$projectBase = rtrim(preg_replace('#/app/.*$#', '', $scriptDir), '/');
$assetBase   = $projectBase . '/app/assets';

if (!defined('HEADER_ASSETS_LOADED')) {
  define('HEADER_ASSETS_LOADED', true);
  echo <<<HTML
<script>(function(d){
  var h=d.head||d.getElementsByTagName('head')[0];
  function addOnce(id, html){ if(d.getElementById(id)) return; var t=d.createElement('template'); t.innerHTML=html; h.appendChild(t.content.firstChild); }
  addOnce('header-fonts','<link id="header-fonts" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">');
  addOnce('header-fa','<link id="header-fa" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">');
  addOnce('header-css','<link id="header-css" rel="stylesheet" href="{$assetBase}/css/Dashboard/headerfooter-bdhf.css">');
  if(!('bootstrap' in window) && !d.getElementById('header-bs')){
    var s=d.createElement('script'); s.id='header-bs'; s.defer=true; s.src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js'; h.appendChild(s);
  }
  addOnce('header-js','<script id="header-js" defer src="{$assetBase}/js/Dashboard/dashboard.js"><\\/script>');
})(document);</script>
HTML;
}
?>
<footer class="dashboard-footer footer">
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
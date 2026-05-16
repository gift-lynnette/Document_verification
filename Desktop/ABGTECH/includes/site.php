<?php
/**
 * ABGTECH - Shared Template Functions
 * This file contains reusable PHP functions for page headers, navigation, and footers
 */

/**
 * Output HTML head and opening body tag
 * @param {string} $title - Page title for <title> and meta
 * @param {string} $description - Meta description tag
 */
function abg_head($title, $description = 'ABGTECH professional software, products, and digital marketing services.') {
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($description) ?>">
  <!-- Bootstrap CSS for responsive layout -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons for SVG icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Custom ABGTECH styles -->
  <link href="assets/abg.css" rel="stylesheet">
</head>
<body>
<?php
}

/**
 * Output navigation bar with mobile drawer
 * @param {string} $active - Current page identifier for active link highlighting
 */
function abg_nav($active = '') {
  $is = fn($page) => $active === $page ? ' active' : '';
?>
  <!-- ========================================
       NAVIGATION BAR - Fixed Header Section
       ======================================== -->
  <nav class="site-nav" id="siteNav">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between">
        <!-- Logo and Brand Section -->
        <a class="navbar-brand d-flex align-items-center gap-3" href="index.php" aria-label="ABGTECH homepage">
          <img src="assets/logo.png" alt="ABGTECH Logo" class="logo-img" height="72" width="72">
          <div class="logo-text">
            <div class="logo-company">ABGTECH CO. LTD</div>
            <div class="logo-tagline">Solutions That Drive Success</div>
          </div>
        </a>

        <!-- Desktop Navigation Menu -->
        <div class="desktop-nav d-flex align-items-center gap-3">
          <a class="nav-link<?= $is('home') ?>" href="index.php">Homepage</a>
          
          <!-- Services Dropdown -->
          <div class="dropdown">
            <a class="nav-link dropdown-toggle<?= $is('services') ?>" href="software.php" data-bs-toggle="dropdown">Services Page</a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="software.php">Custom Software Development</a>
              <a class="dropdown-item" href="digitalmarketing.php">Digital Marketing</a>
            </div>
          </div>

          <!-- Products Dropdown -->
          <div class="dropdown">
            <a class="nav-link dropdown-toggle<?= $is('products') ?>" href="products.php" data-bs-toggle="dropdown">Products Page</a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="sacco-system.php">Sacco & Microfinance System</a>
              <a class="dropdown-item" href="school-management-system.php">School Management System</a>
              <a class="dropdown-item" href="retail-pos-system.php">Retail POS & Inventory System</a>
              <a class="dropdown-item" href="medical-clinic-pharmacy-system.php">Medical Clinic & Pharmacy System</a>
            </div>
          </div>

          <!-- Other Navigation Links -->
          <a class="nav-link<?= $is('about') ?>" href="about.php">About Us</a>
          <a class="nav-link<?= $is('contact') ?>" href="contact.php">Contact Us</a>
          
          <!-- Get a Quote CTA Button -->
          <a class="btn btn-premium" href="contact.php"><i class="bi bi-send me-2"></i>Get a Quote</a>
        </div>

        <!-- Mobile Menu Hamburger Button -->
        <button class="btn btn-premium mobile-menu-button" type="button" id="openDrawer" aria-label="Open menu">
          <i class="bi bi-list fs-4"></i>
        </button>
      </div>
    </div>
  </nav>

  <!-- Mobile Drawer Menu -->
  <div class="drawer-backdrop" id="drawerBackdrop"></div>
  <aside class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
    <!-- Mobile Drawer Header with Logo -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div class="d-flex align-items-center gap-3"><img src="assets/logo.png" alt="ABGTECH Logo" height="56" width="56" style="border-radius: 50%; object-fit: contain; box-shadow: 0 4px 12px rgba(89,215,241,.32); background: rgba(255,255,255,.04); padding: 2px;"><div><div style="font-weight: 950; color: #fff; font-size: 1rem;">ABGTECH</div><div style="font-size: .72rem; color: rgba(255,255,255,.78);">Solutions That Drive Success</div></div></div>
      <!-- Close Button -->
      <button class="btn btn-outline-light" type="button" id="closeDrawer" aria-label="Close menu"><i class="bi bi-x-lg"></i></button>
    </div>

    <!-- Mobile Menu Links -->
    <a class="drawer-link" href="index.php">Homepage <i class="bi bi-house"></i></a>
    <a class="drawer-link" href="software.php">Custom Software Development <i class="bi bi-code-slash"></i></a>
    <a class="drawer-link" href="digitalmarketing.php">Digital Marketing <i class="bi bi-graph-up-arrow"></i></a>
    <a class="drawer-link" href="products.php">Products Page <i class="bi bi-grid"></i></a>
    <a class="drawer-link" href="about.php">About Us <i class="bi bi-building"></i></a>
    <a class="drawer-link" href="contact.php">Contact Us <i class="bi bi-chat-dots"></i></a>
  </aside>
<?php
}

/**
 * Output footer with company info, links, and social media
 */
function abg_footer() {
?>
  <!-- ========================================
       FOOTER SECTION
       ======================================== -->
  <footer class="footer">
    <div class="container">
      <div class="row g-4">
        <!-- Footer Column 1 - Company Info & Social -->
        <div class="col-lg-4">
          <div class="footer-logo-wrapper">
            <img src="assets/logo.png" alt="ABGTECH Logo" class="footer-logo">
            <div class="footer-logo-text">
              <strong>ABGTECH CO LTD</strong>
              Solutions That Drive Success
            </div>
          </div>
          <p>Professional software, business products, and digital growth systems for modern organizations.</p>

          <!-- Social Media Links -->
          <div class="social-row mt-3">
            <a href="https://www.facebook.com/abgtech" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://www.linkedin.com/company/abgtech" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="https://www.instagram.com/abgtech" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="https://wa.me/256700000000" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
            <a href="https://x.com/abgtech" aria-label="X"><i class="bi bi-twitter-x"></i></a>
          </div>
        </div>

        <!-- Footer Column 2 - Quick Links -->
        <div class="col-6 col-lg-2">
          <h3>Quick Links</h3>
          <a href="index.php">Homepage</a>
          <a href="products.php">Products Page</a>
          <a href="about.php">About Us</a>
          <a href="contact.php">Contact Us</a>
        </div>

        <!-- Footer Column 3 - Services -->
        <div class="col-6 col-lg-3">
          <h3>Services</h3>
          <a href="software.php">Custom Software Development</a>
          <a href="digitalmarketing.php">Digital Marketing</a>
        </div>

        <div class="col-lg-3">
          <h3>Contact Info</h3>
          <a href="mailto:info@abgtech.co">info@abgtech.co</a>
          <a href="tel:+256700000000">+256 700 000 000</a>
          <p class="mb-0">Kampala, Uganda<br>Serving clients internationally</p>
        </div>
      </div>
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pt-4 mt-4 border-top border-secondary">
        <small>&copy; <span id="year"></span> ABGTECH. All rights reserved.</small>
        <small>Facebook / LinkedIn / Instagram / WhatsApp / X</small>
      </div>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/abg.js"></script>
</body>
</html>
<?php
}
?>

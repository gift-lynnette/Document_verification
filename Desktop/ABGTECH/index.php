<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ABGTECH | Professional Software, Products & Digital Growth</title>
  <meta name="description" content="ABGTECH builds custom software, business products, and digital marketing systems for ambitious organizations.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --ink: #07152f;
      --navy: #0d2b67;
      --royal: #1f55c7;
      --blue: #3d86e8;
      --cyan: #59d7f1;
      --hover-cyan: #47c7dc;
      --deep-teal: #17384c;
      --aqua: #3ee6c2;
      --green: #20c997;
      --soft: #eef5ff;
      --muted: #6d7d99;
      --line: rgba(31, 85, 199, .13);
      --shadow: 0 24px 70px rgba(6, 22, 58, .16);
      --radius: 22px;
      --glass: rgba(255,255,255,.74);
      --glass-strong: rgba(248,251,255,.84);
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      color: var(--ink);
      background:
        radial-gradient(circle at 12% 8%, rgba(89,215,241,.18), transparent 24rem),
        linear-gradient(135deg, #eef6ff 0%, #f7fbff 45%, #e8f4f8 100%);
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      overflow-x: hidden;
    }
    a { color: inherit; text-decoration: none; }
    .site-nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1030;
      padding: 1rem 0;
      background: transparent;
      border-bottom: 1px solid rgba(255,255,255,.14);
      transition: background .25s ease, padding .25s ease, box-shadow .25s ease;
    }
    .site-nav.is-scrolled {
      padding: .65rem 0;
      background: rgba(23, 56, 76, .96);
      box-shadow: 0 18px 50px rgba(6, 22, 58, .28);
      backdrop-filter: blur(18px);
    }
    .brand-mark {
      width: 52px;
      height: 52px;
      display: grid;
      place-items: center;
      border-radius: 50%;
      color: #fff;
      font-weight: 950;
      letter-spacing: -.05em;
      border: 2px solid rgba(255,255,255,.72);
      background: linear-gradient(135deg, #123b7f, var(--cyan));
      box-shadow: 0 0 30px rgba(89,215,241,.48);
    }
    .logo-img {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      object-fit: contain;
      object-position: center;
      display: inline-block;
      box-shadow: 0 6px 20px rgba(89,215,241,.32);
      flex-shrink: 0;
      min-width: 72px;
      min-height: 72px;
      vertical-align: middle;
      background: rgba(255,255,255,.05);
      padding: 4px;
    }
    .logo-text {
      display: flex;
      flex-direction: column;
      gap: .2rem;
    }
    .logo-company {
      color: #fff;
      font-weight: 950;
      font-size: 1.1rem;
      letter-spacing: .01em;
      line-height: 1.2;
    }
    .logo-tagline {
      color: rgba(255,255,255,.82);
      font-size: .74rem;
      font-weight: 600;
      letter-spacing: .02em;
      line-height: 1.1;
    }
    .navbar-brand {
      color: #fff;
      font-weight: 950;
      letter-spacing: .03em;
      display: flex !important;
      align-items: center;
      gap: 1.2rem !important;
      padding: 0.5rem 0;
    }
    .footer-logo-wrapper {
      display: flex;
      align-items: center;
      gap: 1.2rem;
      margin-bottom: 1.4rem;
    }
    .footer-logo {
      border-radius: 50%;
      object-fit: contain;
      object-position: center;
      display: block;
      box-shadow: 0 6px 18px rgba(89,215,241,.28);
      width: 64px;
      height: 64px;
      flex-shrink: 0;
      min-width: 64px;
      min-height: 64px;
      background: rgba(255,255,255,.04);
      padding: 3px;
    }
    .footer-logo-text strong {
      display: block;
      color: #fff;
      font-size: 1.05rem;
      font-weight: 950;
      line-height: 1.2;
      margin-bottom: .3rem;
    }
    .footer-logo-text {
      color: rgba(255,255,255,.78);
      font-size: .78rem;
      line-height: 1.4;
      letter-spacing: .01em;
    }
    .footer-logo-wrapper {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.2rem;
    }
    .footer-logo {
      border-radius: 50%;
      object-fit: cover;
      display: block;
      box-shadow: 0 6px 20px rgba(89,215,241,.36);
      width: 56px;
      height: 56px;
      flex-shrink: 0;
    }
    .footer-logo-text strong {
      display: block;
      color: #fff;
      font-size: 1rem;
      font-weight: 950;
      line-height: 1.1;
      margin-bottom: .2rem;
    }
    .footer-logo-text {
      color: rgba(255,255,255,.7);
      font-size: .75rem;
      line-height: 1.3;
    }
    .navbar-brand { color: #fff; font-weight: 950; letter-spacing: .03em; }
    .navbar-brand span { color: var(--cyan); }
    .nav-link {
      color: rgba(255,255,255,.84);
      text-transform: uppercase;
      font-weight: 850;
      letter-spacing: .05em;
      font-size: .84rem;
    }
    .nav-link:hover, .nav-link.active, .dropdown-item:hover { color: var(--hover-cyan); }
    .dropdown-menu {
      border: 1px solid rgba(89,215,241,.18);
      border-radius: 16px;
      padding: .65rem;
      background: rgba(23,56,76,.98);
      box-shadow: 0 22px 60px rgba(0,0,0,.24);
    }
    .dropdown-item {
      color: rgba(255,255,255,.78);
      border-radius: 12px;
      font-weight: 750;
      padding: .65rem .8rem;
    }
    .dropdown-item:hover, .dropdown-item:focus, .dropdown-item.active {
      color: var(--hover-cyan);
      background: transparent;
    }
    .btn-premium {
      border: 0;
      border-radius: 18px;
      padding: .92rem 1.18rem;
      color: #fff;
      font-weight: 900;
      background: linear-gradient(135deg, var(--royal), #123b94);
      box-shadow: 0 18px 38px rgba(31,85,199,.34);
      transition: transform .22s ease, box-shadow .22s ease;
    }
    .btn-premium:hover { color: #fff; transform: translateY(-3px); box-shadow: 0 24px 50px rgba(31,85,199,.46); }
    .btn-ghost {
      border: 1px solid rgba(255,255,255,.45);
      border-radius: 18px;
      padding: .88rem 1.15rem;
      color: #fff;
      font-weight: 900;
      background: rgba(255,255,255,.08);
      backdrop-filter: blur(14px);
    }
    .btn-ghost:hover { color: #fff; background: rgba(255,255,255,.15); }
    .hero {
      min-height: 100vh;
      position: relative;
      display: flex;
      align-items: center;
      padding: 8rem 0 4.5rem;
      color: #fff;
      overflow: hidden;
      background:
        radial-gradient(circle at 18% 16%, rgba(89,215,241,.32), transparent 24rem),
        radial-gradient(circle at 82% 24%, rgba(62,230,194,.16), transparent 26rem),
        linear-gradient(135deg, #06142e 0%, #0d2b67 48%, #1f55c7 100%);
    }
    #particleCanvas {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: .75;
    }
    .hero::after {
      content: "";
      position: absolute;
      inset: auto 0 0;
      height: 34%;
      background: linear-gradient(transparent, rgba(246,249,255,.08));
      pointer-events: none;
    }
    .hero-content { position: relative; z-index: 2; }
    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .55rem;
      padding: .48rem .85rem;
      color: #dff9ff;
      border: 1px solid rgba(255,255,255,.22);
      background: rgba(255,255,255,.1);
      border-radius: 999px;
      font-weight: 850;
      font-size: .78rem;
      letter-spacing: .08em;
      text-transform: uppercase;
    }
    .display-title {
      max-width: 920px;
      font-size: clamp(2.65rem, 6vw, 6.35rem);
      line-height: .98;
      font-weight: 950;
      letter-spacing: 0;
    }
    .type-cursor::after {
      content: "";
      display: inline-block;
      width: .09em;
      height: .82em;
      margin-left: .08em;
      background: var(--cyan);
      animation: blink .82s steps(2) infinite;
    }
    @keyframes blink { 50% { opacity: 0; } }
    .lead-copy {
      color: rgba(255,255,255,.82);
      font-size: clamp(1rem, 1.45vw, 1.25rem);
      line-height: 1.72;
      max-width: 690px;
    }
    .hero-mockup {
      position: relative;
      min-height: 470px;
      perspective: 1200px;
    }
    .product-console {
      position: absolute;
      inset: 0;
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 30px;
      padding: 1rem;
      background: linear-gradient(160deg, rgba(255,255,255,.18), rgba(255,255,255,.05));
      box-shadow: 0 34px 90px rgba(0,0,0,.3);
      backdrop-filter: blur(18px);
      transform: rotateY(-10deg) rotateX(6deg);
      transform-origin: center;
    }
    .mock-window {
      height: 100%;
      overflow: hidden;
      border-radius: 22px;
      color: var(--ink);
      background: var(--glass-strong);
      border: 1px solid rgba(255,255,255,.18);
    }
    .mock-top { display: flex; gap: .42rem; padding: .9rem 1rem; background: #06142e; }
    .mock-top span { width: 11px; height: 11px; border-radius: 50%; background: var(--cyan); }
    .mock-grid { display: grid; grid-template-columns: .8fr 1.2fr; gap: 1rem; padding: 1rem; }
    .mock-card {
      min-height: 108px;
      border: 1px solid rgba(31,85,199,.1);
      border-radius: 18px;
      padding: 1rem;
      background: var(--glass);
      backdrop-filter: blur(14px);
      box-shadow: 0 12px 34px rgba(6,22,58,.08);
    }
    .mock-card.large { grid-row: span 2; }
    .bar {
      width: 100%;
      height: 10px;
      border-radius: 99px;
      overflow: hidden;
      background: #e7eef9;
    }
    .bar span { display: block; width: 0; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--royal), var(--cyan)); transition: width 1.2s ease; }
    .chart-line {
      height: 125px;
      border-radius: 16px;
      background:
        linear-gradient(135deg, transparent 47%, rgba(89,215,241,.88) 48%, transparent 50%),
        linear-gradient(45deg, transparent 54%, rgba(31,85,199,.85) 55%, transparent 57%),
        linear-gradient(#edf4ff 1px, transparent 1px),
        linear-gradient(90deg, #edf4ff 1px, transparent 1px);
      background-size: 100% 100%, 100% 100%, 22px 22px, 22px 22px;
    }
    .floating-chip {
      position: absolute;
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .72rem .95rem;
      border-radius: 999px;
      color: #fff;
      background: rgba(7,21,47,.78);
      border: 1px solid rgba(89,215,241,.28);
      box-shadow: 0 20px 50px rgba(0,0,0,.22);
      animation: floaty 5.8s ease-in-out infinite;
      z-index: 3;
    }
    .floating-chip.one { top: 8%; right: -2%; }
    .floating-chip.two { left: -2%; bottom: 15%; animation-delay: -2s; }
    @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
    .section { padding: 6.25rem 0; }
    .section-dark {
      color: #fff;
      background:
        radial-gradient(circle at 84% 14%, rgba(89,215,241,.18), transparent 22rem),
        linear-gradient(135deg, #07152f, #0d2b67 68%, #102c5d);
    }
    .section-kicker {
      color: var(--royal);
      text-transform: uppercase;
      font-weight: 950;
      letter-spacing: .09em;
      font-size: .78rem;
      margin-bottom: .7rem;
    }
    .section-dark .section-kicker { color: var(--cyan); }
    .section-title {
      font-size: clamp(2rem, 3.8vw, 4.05rem);
      line-height: 1.05;
      font-weight: 950;
      letter-spacing: 0;
      margin-bottom: 1rem;
    }
    .section-copy { color: var(--muted); font-size: 1.04rem; line-height: 1.75; }
    .section-dark .section-copy { color: rgba(255,255,255,.72); }
    .stats-strip {
      position: relative;
      z-index: 4;
      margin-top: -3.5rem;
    }
    .stats-inner {
      border: 1px solid var(--line);
      border-radius: 28px;
      background: var(--glass-strong);
      backdrop-filter: blur(14px);
      box-shadow: var(--shadow);
      overflow: hidden;
    }
    .stat-box {
      padding: 1.45rem;
      text-align: center;
      border-right: 1px solid var(--line);
    }
    .stat-box:last-child { border-right: 0; }
    .stat-box strong {
      display: block;
      color: var(--royal);
      font-size: clamp(2rem, 3.6vw, 3.4rem);
      font-weight: 950;
      line-height: 1;
    }
    .premium-card, .product-card, .value-card, .lead-panel {
      border: 1px solid var(--line);
      border-radius: var(--radius);
      background: var(--glass);
      backdrop-filter: blur(14px);
      box-shadow: 0 20px 50px rgba(6,22,58,.09);
      transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .premium-card:hover, .product-card:hover, .value-card:hover {
      transform: translateY(-6px);
      border-color: rgba(89,215,241,.52);
      box-shadow: 0 28px 72px rgba(6,22,58,.16);
    }
    .service-card {
      position: relative;
      overflow: hidden;
      min-height: 300px;
    }
    .service-card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      transform: scaleX(0);
      transform-origin: left;
      background: linear-gradient(90deg, var(--royal), var(--cyan));
      transition: transform .22s ease;
    }
    .service-card:hover::before { transform: scaleX(1); }
    .icon-box {
      width: 56px;
      height: 56px;
      display: grid;
      place-items: center;
      border-radius: 18px;
      color: #fff;
      font-size: 1.48rem;
      background: linear-gradient(135deg, var(--royal), var(--cyan));
      box-shadow: 0 18px 34px rgba(31,85,199,.28);
    }
    .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    .product-card {
      min-height: 270px;
      padding: 1.25rem;
    }
    .product-card .icon-box { color: #dff9ff; background: linear-gradient(135deg, #0d2b67, #1f55c7); transition: background .22s ease, transform .22s ease; }
    .product-card:hover .icon-box { transform: scale(1.08); background: linear-gradient(135deg, var(--cyan), var(--green)); }
    .value-card {
      height: 100%;
      padding: 1.35rem;
      background: rgba(255,255,255,.08);
      border-color: rgba(255,255,255,.14);
    }
    .testimonial-shell {
      overflow: hidden;
      border-radius: 28px;
    }
    .testimonial-track { display: flex; transition: transform .55s ease; }
    .testimonial-card { min-width: 100%; padding: .2rem; }
    .testimonial-inner {
      min-height: 285px;
      border-radius: 24px;
      padding: clamp(1.5rem, 3vw, 2.25rem);
      color: var(--ink);
      background: var(--glass);
      backdrop-filter: blur(14px);
      box-shadow: var(--shadow);
      border: 1px solid var(--line);
    }
    .stars { color: #0d8a78; letter-spacing: .08em; }
    .cta-banner {
      color: #fff;
      border-radius: 32px;
      padding: clamp(2rem, 5vw, 4rem);
      background: linear-gradient(120deg, #07152f, #1f55c7, #0d2b67);
      background-size: 200% 200%;
      box-shadow: var(--shadow);
      animation: gradientShift 9s ease-in-out infinite;
    }
    @keyframes gradientShift { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
    .contact-form .form-control, .contact-form .form-select {
      border: 1px solid rgba(31,85,199,.16);
      border-radius: 16px;
      padding: .9rem 1rem;
    }
    .lead-score {
      display: grid;
      gap: .75rem;
      border-radius: 18px;
      padding: 1rem;
      background: rgba(238,245,255,.72);
      backdrop-filter: blur(12px);
      border: 1px solid var(--line);
    }
    .footer {
      color: rgba(255,255,255,.74);
      background: var(--deep-teal);
      padding: 4rem 0 1.5rem;
    }
    .footer h3 { color: #fff; font-size: 1rem; font-weight: 900; }
    .footer a { display: block; margin: .48rem 0; color: rgba(255,255,255,.72); }
    .footer a:hover { color: var(--hover-cyan); }
    .reveal { opacity: 0; transform: translateY(24px); transition: opacity .65s ease, transform .65s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .drawer-backdrop {
      position: fixed;
      inset: 0;
      z-index: 1040;
      visibility: hidden;
      background: rgba(3,10,25,.54);
      opacity: 0;
      transition: opacity .25s ease, visibility .25s ease;
    }
    .mobile-drawer {
      position: fixed;
      top: 0;
      right: 0;
      z-index: 1050;
      width: min(360px, 88vw);
      height: 100vh;
      padding: 1.25rem;
      color: #fff;
      background: var(--deep-teal);
      box-shadow: -24px 0 80px rgba(0,0,0,.28);
      transform: translateX(100%);
      transition: transform .3s ease;
    }
    body.drawer-open .drawer-backdrop { visibility: visible; opacity: 1; }
    body.drawer-open .mobile-drawer { transform: translateX(0); }
    .drawer-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: .95rem 0;
      border-bottom: 1px solid rgba(255,255,255,.12);
      color: rgba(255,255,255,.84);
      font-weight: 850;
    }
    .drawer-link:hover { color: var(--hover-cyan); }
    @media (max-width: 1199.98px) {
      .product-grid { grid-template-columns: repeat(2, 1fr); }
      .hero-mockup { min-height: 420px; }
    }
    @media (max-width: 991.98px) {
      .desktop-nav { display: none !important; }
      .site-nav { background: rgba(23,56,76,.94); backdrop-filter: blur(18px); }
      .hero { padding-top: 7.8rem; }
      .hero-mockup { margin-top: 2rem; min-height: 380px; }
      .product-console { transform: none; }
      .stat-box { border-right: 0; border-bottom: 1px solid var(--line); }
      .stat-box:nth-last-child(-n+2) { border-bottom: 0; }
    }
    @media (min-width: 992px) {
      .mobile-menu-button { display: none; }
    }
    @media (max-width: 575.98px) {
      .section { padding: 4.5rem 0; }
      .hero { min-height: auto; }
      .btn-premium, .btn-ghost { width: 100%; text-align: center; }
      .mock-grid, .product-grid { grid-template-columns: 1fr; }
      .hero-mockup { min-height: 500px; }
      .floating-chip { position: static; margin: .75rem .35rem 0 0; }
      .stats-strip { margin-top: 0; padding-top: 1rem; }
      .stat-box { border-bottom: 1px solid var(--line) !important; }
      .stat-box:last-child { border-bottom: 0 !important; }
    }
  </style>

  <!-- ╔════════════════════════════════════════════════════════════════════════════╗
       ║           ABGTECH HOMEPAGE - HTML PAGE STRUCTURE OVERVIEW               ║
       ║                                                                           ║
       ║  This page contains 10 major sections:                                   ║
       ║  1. Navigation Bar (Fixed top navbar with logo & menu)                   ║
       ║  2. Mobile Drawer (Hidden side menu for mobile devices)                  ║
       ║  3. Hero Section (Main banner with headline & CTAs)                      ║
       ║  4. Services Section (3 main service offerings)                          ║
       ║  5. Products Section (4 business system products)                        ║
       ║  6. About Section (Company values & positioning)                         ║
       ║  7. Testimonials Section (Client success stories carousel)               ║
       ║  8. CTA Banner (Call-to-action for inquiries)                            ║
       ║  9. Contact Form (Lead capture with service selection)                   ║
       ║  10. Footer (Company info, links, social media)                          ║
       ║                                                                           ║
       ║  All CSS is inline in <style> above (mirrored in assets/abg.css)         ║
       ║  All JS is inline at bottom (mirrored in assets/abg.js)                  ║
       ╚════════════════════════════════════════════════════════════════════════════╝ -->

</head>
<body>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 1: NAVIGATION BAR
       
       Fixed top navigation with circular logo, main menu (desktop), mobile button,
       and responsive dropdowns for Services and Products.
       ════════════════════════════════════════════════════════════════════════════ -->
  <nav class="site-nav" id="siteNav">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between">
        <a class="navbar-brand d-flex align-items-center gap-3" href="index.php" aria-label="ABGTECH homepage">
          <img src="assets/logo.png" alt="ABGTECH Logo" class="logo-img" height="72" width="72">
          <div class="logo-text">
            <div class="logo-company">ABGTECH CO. LTD</div>
            <div class="logo-tagline">Solutions That Drive Success</div>
          </div>
        </a>
        <div class="desktop-nav d-flex align-items-center gap-3">
          <a class="nav-link active" href="index.php">Homepage</a>
          <div class="dropdown">
            <a class="nav-link dropdown-toggle" href="software.php" data-bs-toggle="dropdown">Services Page</a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="software.php">Custom Software Development</a>
              <a class="dropdown-item" href="digitalmarketing.php">Digital Marketing</a>
            </div>
          </div>
          <div class="dropdown">
            <a class="nav-link dropdown-toggle" href="products.php" data-bs-toggle="dropdown">Products Page</a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="sacco-system.php">Sacco & Microfinance System</a>
              <a class="dropdown-item" href="school-management-system.php">School Management System</a>
              <a class="dropdown-item" href="retail-pos-system.php">Retail POS & Inventory System</a>
              <a class="dropdown-item" href="medical-clinic-pharmacy-system.php">Medical Clinic & Pharmacy System</a>
            </div>
          </div>
          <a class="nav-link" href="about.php">About Us</a>
          <a class="nav-link" href="contact.php">Contact Us</a>
          <a class="btn btn-premium" href="contact.php"><i class="bi bi-send me-2"></i>Get a Quote</a>
        </div>
        <button class="btn btn-premium mobile-menu-button" type="button" id="openDrawer" aria-label="Open menu">
          <i class="bi bi-list fs-4"></i>
        </button>
      </div>
    </div>
  </nav>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 2: MOBILE DRAWER & BACKDROP
       
       Hidden side menu that slides in from the right on mobile devices.
       Drawer backdrop is semi-transparent overlay that closes menu when clicked.
       Drawer contains all navigation links with icons and mobile-optimized layout.
       ════════════════════════════════════════════════════════════════════════════ -->

  <div class="drawer-backdrop" id="drawerBackdrop"></div>
  <aside class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div class="d-flex align-items-center gap-3"><img src="assets/logo.png" alt="ABGTECH Logo" height="56" width="56" style="border-radius: 50%; object-fit: contain; box-shadow: 0 4px 12px rgba(89,215,241,.32); background: rgba(255,255,255,.04); padding: 2px;"><div><div style="font-weight: 950; color: #fff; font-size: 1rem;">ABGTECH</div><div style="font-size: .72rem; color: rgba(255,255,255,.78);">Solutions That Drive Success</div></div></div>
      <button class="btn btn-outline-light" type="button" id="closeDrawer" aria-label="Close menu"><i class="bi bi-x-lg"></i></button>
    </div>
    <a class="drawer-link" href="index.php">Homepage <i class="bi bi-house"></i></a>
    <a class="drawer-link" href="software.php">Custom Software Development <i class="bi bi-code-slash"></i></a>
    <a class="drawer-link" href="digitalmarketing.php">Digital Marketing <i class="bi bi-graph-up-arrow"></i></a>
    <a class="drawer-link" href="products.php">Products Page <i class="bi bi-grid"></i></a>
    <a class="drawer-link" href="about.php">About Us <i class="bi bi-building"></i></a>
    <a class="drawer-link" href="contact.php">Contact Us <i class="bi bi-chat-dots"></i></a>
  </aside>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 3: HERO SECTION
       
       Main page header with animated particle background, headline text animation,
       call-to-action buttons. Uses reveal animation to fade in on page load.
       Canvas element creates dynamic background particles.
       ════════════════════════════════════════════════════════════════════════════ -->

  <header class="hero" id="top">
    <canvas id="particleCanvas" aria-hidden="true"></canvas>
    <div class="container hero-content">
      <div class="row align-items-center g-5">
        <div class="col-lg-6 reveal">
          <span class="eyebrow"><i class="bi bi-globe2"></i> International technology partner</span>
          <h1 class="display-title mt-4 mb-4 type-cursor" id="heroTitle">Build smarter systems. Launch stronger brands. Grow with ABGTECH.</h1>
          <p class="lead-copy mb-4">We design professional software, deploy business-ready products, and run measurable digital marketing for organizations that need reliable technology and visible growth.</p>
          <div class="d-flex flex-column flex-sm-row gap-3">
            <a href="software.php" class="btn btn-premium"><i class="bi bi-layers me-2"></i>See Our Services</a>
            <a href="products.php" class="btn btn-ghost"><i class="bi bi-window-stack me-2"></i>View Products</a>
          </div>
        </div>
        <div class="col-lg-6 reveal">
          <div class="hero-mockup">
            <span class="floating-chip one"><i class="bi bi-shield-check text-info"></i> Secure systems</span>
            <span class="floating-chip two"><i class="bi bi-lightning-charge text-info"></i> Faster operations</span>
            <div class="product-console">
              <div class="mock-window">
                <div class="mock-top"><span></span><span></span><span></span></div>
                <div class="mock-grid">
                  <div class="mock-card large">
                    <small class="text-muted">ABGTECH Product Hub</small>
                    <h3 class="h5 fw-bold mt-2">Operations Dashboard</h3>
                    <div class="chart-line mt-3"></div>
                  </div>
                  <div class="mock-card">
                    <small class="text-muted">Deployments</small>
                    <h3 class="fw-bold text-primary">48+</h3>
                    <div class="bar"><span data-width="82%"></span></div>
                  </div>
                  <div class="mock-card">
                    <small class="text-muted">Uptime</small>
                    <h3 class="fw-bold text-primary">99.9%</h3>
                    <div class="bar"><span data-width="96%"></span></div>
                  </div>
                  <div class="mock-card">
                    <small class="text-muted">Lead Pipeline</small>
                    <h3 class="fw-bold text-primary">+64%</h3>
                    <div class="bar"><span data-width="74%"></span></div>
                  </div>
                  <div class="mock-card">
                    <small class="text-muted">Support</small>
                    <h3 class="fw-bold text-primary">24/7</h3>
                    <div class="bar"><span data-width="88%"></span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <section class="stats-strip reveal" id="stats">
    <div class="container">
      <div class="stats-inner">
        <div class="row g-0">
          <div class="col-sm-6 col-lg-3 stat-box"><strong data-counter="120" data-suffix="+">0</strong><span>Clients served</span></div>
          <div class="col-sm-6 col-lg-3 stat-box"><strong data-counter="8" data-suffix="+">0</strong><span>Years experience</span></div>
          <div class="col-sm-6 col-lg-3 stat-box"><strong data-counter="48" data-suffix="+">0</strong><span>Products deployed</span></div>
          <div class="col-sm-6 col-lg-3 stat-box"><strong data-counter="99.9" data-suffix="%">0</strong><span>Platform uptime</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 4: SERVICES SECTION
       
       Displays 3 main service offerings: Custom Software Development, Digital 
       Marketing, and Business Products/Systems. Each service has description and 
       link to dedicated service page. Uses reveal animation on scroll.
       ════════════════════════════════════════════════════════════════════════════ -->

  <section class="section" id="services">
    <div class="container">
      <div class="row align-items-end justify-content-between mb-5">
        <div class="col-lg-7 reveal">
          <div class="section-kicker">Services Page</div>
          <h2 class="section-title">From custom platforms to growth campaigns.</h2>
        </div>
        <div class="col-lg-4 reveal">
          <p class="section-copy mb-0">Two focused service lines, built to support the full digital journey from product idea to market traction.</p>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-lg-6 reveal">
          <a class="premium-card service-card d-block p-4 h-100" href="software.php">
            <div class="icon-box mb-4"><i class="bi bi-code-square"></i></div>
            <h3 class="h2 fw-bold">Custom Software Development</h3>
            <p class="section-copy">Scalable web apps, mobile apps, APIs, dashboards, automations, and enterprise systems using practical modern stacks and structured delivery methods.</p>
            <span class="fw-bold text-primary">Explore service <i class="bi bi-arrow-right"></i></span>
          </a>
        </div>
        <div class="col-lg-6 reveal">
          <a class="premium-card service-card d-block p-4 h-100" href="digitalmarketing.php">
            <div class="icon-box mb-4"><i class="bi bi-bullseye"></i></div>
            <h3 class="h2 fw-bold">Digital Marketing</h3>
            <p class="section-copy">SEO, paid campaigns, social media, email marketing, content, branding, and reporting dashboards focused on measurable business outcomes.</p>
            <span class="fw-bold text-primary">Explore service <i class="bi bi-arrow-right"></i></span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 5: PRODUCTS & BUSINESS SYSTEMS SECTION
       
       Displays 4 main products in a responsive grid: Sacco System, School 
       Management, Retail POS, and Medical Clinic. Each product card shows icon,
       title, description, and "Learn More" link. Animated on scroll with reveal effect.
       ════════════════════════════════════════════════════════════════════════════ -->

  <section class="section pt-0" id="products">
    <div class="container">
      <div class="row align-items-end justify-content-between mb-5">
        <div class="col-lg-7 reveal">
          <div class="section-kicker">Solutions & Products</div>
          <h2 class="section-title">Business systems ready for high-trust operations.</h2>
        </div>
        <div class="col-lg-4 reveal">
          <p class="section-copy mb-0">Each product is designed around daily workflows, clear reporting, user roles, and long-term support.</p>
        </div>
      </div>
      <div class="product-grid">
        <article class="product-card reveal">
          <div class="icon-box mb-4"><i class="bi bi-bank"></i></div>
          <h3 class="h4 fw-bold">Sacco & Microfinance System</h3>
          <p class="section-copy">Member records, savings, shares, loans, repayments, reports, and compliance visibility.</p>
          <a class="fw-bold text-primary" href="sacco-system.php">Learn More <i class="bi bi-arrow-right"></i></a>
        </article>
        <article class="product-card reveal">
          <div class="icon-box mb-4"><i class="bi bi-mortarboard"></i></div>
          <h3 class="h4 fw-bold">School Management System</h3>
          <p class="section-copy">Admissions, timetable, attendance, exams, fees, reports, and parent communication.</p>
          <a class="fw-bold text-primary" href="school-management-system.php">Learn More <i class="bi bi-arrow-right"></i></a>
        </article>
        <article class="product-card reveal">
          <div class="icon-box mb-4"><i class="bi bi-receipt"></i></div>
          <h3 class="h4 fw-bold">Retail POS & Inventory System</h3>
          <p class="section-copy">Fast checkout, inventory tracking, stock alerts, sales reporting, and multi-store support.</p>
          <a class="fw-bold text-primary" href="retail-pos-system.php">Learn More <i class="bi bi-arrow-right"></i></a>
        </article>
        <article class="product-card reveal">
          <div class="icon-box mb-4"><i class="bi bi-heart-pulse"></i></div>
          <h3 class="h4 fw-bold">Medical Clinic & Pharmacy System</h3>
          <p class="section-copy">Patient registration, consultation, prescriptions, dispensing, billing, and analytics.</p>
          <a class="fw-bold text-primary" href="medical-clinic-pharmacy-system.php">Learn More <i class="bi bi-arrow-right"></i></a>
        </article>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 6: ABOUT US SECTION
       
       Dark themed section highlighting company values and differentiators.
       Shows 3 key value cards: Local Expertise, Fast Delivery, and Ongoing Support.
       Positioned before testimonials section.
       ════════════════════════════════════════════════════════════════════════════ -->

  <section class="section section-dark" id="about">
    <div class="container">
      <div class="row g-5 align-items-center">
        <div class="col-lg-5 reveal">
          <div class="section-kicker">About Us</div>
          <h2 class="section-title">Built close to your market, polished for international standards.</h2>
          <p class="section-copy">ABGTECH combines local business understanding with professional engineering discipline, responsive delivery, and ongoing technical support.</p>
        </div>
        <div class="col-lg-7">
          <div class="row g-4">
            <div class="col-md-4 reveal">
              <div class="value-card">
                <div class="icon-box mb-4"><i class="bi bi-geo-alt"></i></div>
                <h3 class="h5 fw-bold">Local Expertise</h3>
                <p class="section-copy mb-0">Systems shaped around real business workflows, teams, customers, and operating conditions.</p>
              </div>
            </div>
            <div class="col-md-4 reveal">
              <div class="value-card">
                <div class="icon-box mb-4"><i class="bi bi-speedometer2"></i></div>
                <h3 class="h5 fw-bold">Fast Delivery</h3>
                <p class="section-copy mb-0">Structured planning, clear milestones, and implementation focused on usable outcomes.</p>
              </div>
            </div>
            <div class="col-md-4 reveal">
              <div class="value-card">
                <div class="icon-box mb-4"><i class="bi bi-headset"></i></div>
                <h3 class="h5 fw-bold">Ongoing Support</h3>
                <p class="section-copy mb-0">Maintenance, improvements, backups, monitoring, and guidance after launch.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 7: TESTIMONIALS SECTION
       
       Client success stories displayed in a carousel. Features 3 testimonial cards
       with client quote, 5-star rating, and client role/company. Navigation buttons
       allow users to browse through testimonials. JavaScript handles carousel animation.
       ════════════════════════════════════════════════════════════════════════════ -->

  <section class="section">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-5 reveal">
          <div class="section-kicker">Testimonials</div>
          <h2 class="section-title">Trusted by teams that need clarity and results.</h2>
          <p class="section-copy">Our work is designed to look professional, work reliably, and make business decisions easier.</p>
          <div class="d-flex gap-2 mt-4">
            <button class="btn btn-premium" id="prevTestimonial" aria-label="Previous testimonial"><i class="bi bi-arrow-left"></i></button>
            <button class="btn btn-premium" id="nextTestimonial" aria-label="Next testimonial"><i class="bi bi-arrow-right"></i></button>
          </div>
        </div>
        <div class="col-lg-7 reveal">
          <div class="testimonial-shell" id="testimonialShell">
            <div class="testimonial-track" id="testimonialTrack">
              <div class="testimonial-card">
                <div class="testimonial-inner">
                  <div class="stars mb-3">★★★★★</div>
                  <p class="fs-5">"ABGTECH helped us move from manual reporting to a clean dashboard our management team uses every week."</p>
                  <strong>Operations Director</strong><br><span class="text-muted">Financial Services Client</span>
                </div>
              </div>
              <div class="testimonial-card">
                <div class="testimonial-inner">
                  <div class="stars mb-3">★★★★★</div>
                  <p class="fs-5">"The final platform looked professional enough for our board presentation and practical enough for daily staff use."</p>
                  <strong>Managing Director</strong><br><span class="text-muted">Service Company</span>
                </div>
              </div>
              <div class="testimonial-card">
                <div class="testimonial-inner">
                  <div class="stars mb-3">★★★★★</div>
                  <p class="fs-5">"Their digital campaign gave us clearer lead tracking and stronger visibility across our most important channels."</p>
                  <strong>Marketing Lead</strong><br><span class="text-muted">Retail Brand</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 8: CALL-TO-ACTION BANNER
       
       Prominent banner encouraging visitors to contact ABGTECH for services.
       Features animated gradient background and "Contact Us" button with icon.
       Explains 3 ways to start: software, product demo, or digital growth campaign.
       ════════════════════════════════════════════════════════════════════════════ -->

  <section class="section pt-0">
    <div class="container">
      <div class="cta-banner reveal">
        <div class="row align-items-center g-4">
          <div class="col-lg-8">
            <div class="section-kicker">Ready to build something great?</div>
            <h2 class="section-title mb-2">Let ABGTECH shape the next version of your business.</h2>
            <p class="text-white-50 mb-0">Start with software, a product demo, or a digital growth campaign.</p>
          </div>
          <div class="col-lg-4 text-lg-end">
            <a class="btn btn-light btn-lg fw-bold" href="contact.php"><i class="bi bi-calendar2-check me-2"></i>Contact Us</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 9: CONTACT FORM & LEAD CAPTURE
       
       Intelligent form that captures name, email, phone, service interest, budget,
       and timeline. Updates "lead readiness" score as user fills form. Includes
       service selection dropdowns and budget/timeline radios. Form validation on submit.
       ════════════════════════════════════════════════════════════════════════════ -->

  <section class="section pt-0" id="contact">
    <div class="container">
      <div class="row g-5 align-items-start">
        <div class="col-lg-5 reveal">
          <div class="section-kicker">Contact Us</div>
          <h2 class="section-title">Intelligent lead capture for your next project.</h2>
          <p class="section-copy">Tell us what you need and the page will classify your request so the right ABGTECH team can follow up with context.</p>
          <div class="lead-score mt-4" id="leadScore">
            <strong>Lead readiness: Exploring</strong>
            <div class="bar"><span style="width: 35%"></span></div>
            <small class="text-muted">Choose a service, budget, and timeline to refine your project profile.</small>
          </div>
        </div>
        <div class="col-lg-7 reveal">
          <div class="lead-panel p-4">
            <form class="row g-3 contact-form" id="leadForm">
              <div class="col-md-6">
                <label class="form-label fw-bold" for="name">Full name</label>
                <input class="form-control form-control-lg" id="name" name="name" required placeholder="Your name">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold" for="email">Email address</label>
                <input type="email" class="form-control form-control-lg" id="email" name="email" required placeholder="you@example.com">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold" for="interest">What do you need?</label>
                <select class="form-select form-select-lg" id="interest" name="interest" required>
                  <option value="">Select one</option>
                  <option>Custom Software Development</option>
                  <option>Digital Marketing</option>
                  <option>Sacco & Microfinance System</option>
                  <option>School Management System</option>
                  <option>Retail POS & Inventory System</option>
                  <option>Medical Clinic & Pharmacy System</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold" for="timeline">Timeline</label>
                <select class="form-select form-select-lg" id="timeline" name="timeline" required>
                  <option value="">Select timeline</option>
                  <option>Immediately</option>
                  <option>This month</option>
                  <option>1-3 months</option>
                  <option>Planning ahead</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold" for="budget">Estimated budget</label>
                <select class="form-select form-select-lg" id="budget" name="budget" required>
                  <option value="">Select range</option>
                  <option>Starter</option>
                  <option>Growth</option>
                  <option>Enterprise</option>
                  <option>Need guidance</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold" for="phone">Phone or WhatsApp</label>
                <input class="form-control form-control-lg" id="phone" name="phone" placeholder="+256 ...">
              </div>
              <div class="col-12">
                <label class="form-label fw-bold" for="message">Project notes</label>
                <textarea class="form-control form-control-lg" id="message" name="message" rows="4" placeholder="Briefly describe your goals"></textarea>
              </div>
              <div class="col-12">
                <button class="btn btn-premium btn-lg w-100" type="submit"><i class="bi bi-send me-2"></i>Submit Project Request</button>
              </div>
              <div class="col-12">
                <div class="alert alert-success d-none mb-0" id="formMessage">Thank you. Your ABGTECH lead profile is ready for follow-up.</div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════════════════════════
       SECTION 10: FOOTER
       
       Footer contains: circular company logo, tagline, social media icons (5 platforms),
       Quick Links column, Services column, and Contact Info column.
       Auto-updates copyright year via JavaScript. Present on all pages.
       ════════════════════════════════════════════════════════════════════════════ -->

  <footer class="footer">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="footer-logo-wrapper">
            <img src="assets/logo.png" alt="ABGTECH Logo" class="footer-logo">
            <div class="footer-logo-text">
              <strong>ABGTECH CO LTD</strong>
              Solutions That Drive Success
            </div>
          </div>
          <p>Professional software, business products, and digital growth systems for modern organizations.</p>
          <div class="d-flex gap-3 fs-5 mt-3">
            <a href="https://www.facebook.com/abgtech" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://www.linkedin.com/company/abgtech" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="https://www.instagram.com/abgtech" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="https://wa.me/256700000000" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
            <a href="https://x.com/abgtech" aria-label="X"><i class="bi bi-twitter-x"></i></a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <h3>Quick Links</h3>
          <a href="index.php">Homepage</a>
          <a href="about.php">About Us</a>
          <a href="contact.php">Contact Us</a>
        </div>
        <div class="col-6 col-lg-3">
          <h3>Services</h3>
          <a href="software.php">Custom Software Development</a>
          <a href="digitalmarketing.php">Digital Marketing</a>
        </div>
        <div class="col-lg-3">
          <h3>Products</h3>
          <a href="sacco-system.php">Sacco & Microfinance System</a>
          <a href="school-management-system.php">School Management System</a>
          <a href="retail-pos-system.php">Retail POS & Inventory System</a>
          <a href="medical-clinic-pharmacy-system.php">Medical Clinic & Pharmacy System</a>
        </div>
      </div>
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pt-4 mt-4 border-top border-secondary">
        <small>&copy; <span id="year"></span> ABGTECH. All rights reserved.</small>
        <div class="d-flex gap-3 fs-5">
          <a href="mailto:info@abgtech.co" aria-label="Email"><i class="bi bi-envelope"></i></a>
          <a href="https://www.facebook.com/abgtech" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="https://www.linkedin.com/company/abgtech" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="https://www.instagram.com/abgtech" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="https://wa.me/256700000000" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          <a href="https://x.com/abgtech" aria-label="X"><i class="bi bi-twitter-x"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- ════════════════════════════════════════════════════════════════════════════
       JAVASCRIPT - INLINE FUNCTIONALITY
       
       This section contains vanilla JavaScript (ES6+) that handles interactive
       features on the homepage. All functions are initialized on DOMContentLoaded.
       
       Functions (in order of initialization):
       1. initParticles() - Creates animated particles in hero background
       2. initReveal() - Intersection Observer for scroll animations
       3. initNavigation() - Navbar scroll effect, mobile drawer toggle
       4. initTestimonials() - Carousel navigation and auto-advance
       5. initLeadCapture() - Form field tracking and lead scoring
       
       Utility functions:
       - qs(selector) - querySelector shorthand
       - qsa(selector) - querySelectorAll shorthand (returns array)
       
       This code mirrors assets/abg.js and is duplicated here for inline execution.
       ════════════════════════════════════════════════════════════════════════════ -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // ──────────────────────────────────────────────────────────────────────────
    // UTILITY FUNCTIONS
    // ──────────────────────────────────────────────────────────────────────────
    
    // querySelector shorthand - returns first matching element
    const qs = (selector, scope = document) => scope.querySelector(selector);
    
    // querySelectorAll shorthand - returns array of matching elements
    const qsa = (selector, scope = document) => [...scope.querySelectorAll(selector)];

    // ──────────────────────────────────────────────────────────────────────────
    // FUNCTION 1: initParticles() - Hero Background Animation
    // 
    // Creates animated particles in canvas background of hero section.
    // Particles float and fade to create subtle motion effect.
    // Responsive: adjusts on window resize
    // ──────────────────────────────────────────────────────────────────────────
    function initParticles() {
      const canvas = qs("#particleCanvas");
      const ctx = canvas.getContext("2d");
      let particles = [];
      const resize = () => {
        canvas.width = canvas.offsetWidth * window.devicePixelRatio;
        canvas.height = canvas.offsetHeight * window.devicePixelRatio;
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
        const count = Math.min(80, Math.floor(canvas.offsetWidth / 18));
        particles = Array.from({ length: count }, () => ({
          x: Math.random() * canvas.offsetWidth,
          y: Math.random() * canvas.offsetHeight,
          vx: (Math.random() - .5) * .34,
          vy: (Math.random() - .5) * .34,
          r: Math.random() * 2.2 + .8
        }));
      };
      const draw = () => {
        ctx.clearRect(0, 0, canvas.offsetWidth, canvas.offsetHeight);
        particles.forEach((p, i) => {
          p.x += p.vx;
          p.y += p.vy;
          if (p.x < 0 || p.x > canvas.offsetWidth) p.vx *= -1;
          if (p.y < 0 || p.y > canvas.offsetHeight) p.vy *= -1;
          ctx.beginPath();
          ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
          ctx.fillStyle = "rgba(137, 226, 255, .7)";
          ctx.fill();
          for (let j = i + 1; j < particles.length; j++) {
            const q = particles[j];
            const d = Math.hypot(p.x - q.x, p.y - q.y);
            if (d < 118) {
              ctx.strokeStyle = `rgba(137, 226, 255, ${(.18 * (1 - d / 118)).toFixed(3)})`;
              ctx.lineWidth = 1;
              ctx.beginPath();
              ctx.moveTo(p.x, p.y);
              ctx.lineTo(q.x, q.y);
              ctx.stroke();
            }
          }
        });
        requestAnimationFrame(draw);
      };
      resize();
      window.addEventListener("resize", resize, { passive: true });
      draw();
    }

    function animateCounter(el) {
      if (el.dataset.done) return;
      el.dataset.done = "true";
      const target = Number(el.dataset.counter);
      const suffix = el.dataset.suffix || "";
      const duration = 1500;
      const start = performance.now();
      const tick = now => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = target % 1 ? (target * eased).toFixed(1) : Math.floor(target * eased);
        el.textContent = value + suffix;
        if (progress < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FUNCTION 2: initReveal() - Scroll Animation with Intersection Observer
    // 
    // Uses Intersection Observer API to detect when elements enter viewport.
    // When visible, triggers fade-in animation (adds "visible" class).
    // Also triggers counter animations and bar width animations.
    // Threshold: 15% of element must be visible to trigger animation.
    // ──────────────────────────────────────────────────────────────────────────
    function initReveal() {
      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("visible");
          qsa("[data-counter]", entry.target).forEach(animateCounter);
          qsa("[data-width]", entry.target).forEach(bar => bar.style.width = bar.dataset.width);
          observer.unobserve(entry.target);
        });
      }, { threshold: .15 });
      qsa(".reveal, .product-console, .stats-inner").forEach(el => observer.observe(el));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FUNCTION 3: initNavigation() - Navbar & Mobile Menu Functionality
    // 
    // Handles:
    // 1. Navbar scroll effect - adds "is-scrolled" class when scrollY > 60px
    // 2. Mobile drawer toggle - hamburger button opens drawer, close button closes
    // 3. Drawer backdrop - clicking overlay closes drawer
    // 4. Menu link clicks - automatically close drawer after navigation
    // Uses passive event listener on scroll for better performance.
    // ──────────────────────────────────────────────────────────────────────────
    function initNavigation() {
      const nav = qs("#siteNav");
      const onScroll = () => nav.classList.toggle("is-scrolled", window.scrollY > 60);
      window.addEventListener("scroll", onScroll, { passive: true });
      onScroll();
      const open = () => document.body.classList.add("drawer-open");
      const close = () => document.body.classList.remove("drawer-open");
      qs("#openDrawer").addEventListener("click", open);
      qs("#closeDrawer").addEventListener("click", close);
      qs("#drawerBackdrop").addEventListener("click", close);
      qsa(".drawer-link").forEach(link => link.addEventListener("click", close));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FUNCTION 4: initTestimonials() - Carousel Navigation & Auto-Advance
    // 
    // Creates carousel of 3 testimonial cards that rotate through testimonials.
    // Features:
    // - Manual navigation via prev/next buttons
    // - Auto-advance every 5 seconds
    // - Pause on hover (paused = true on mouseenter)
    // - CSS transform for smooth slide animation (translateX)
    // - Wraps around when reaching end (modulo operator)
    // ──────────────────────────────────────────────────────────────────────────
    function initTestimonials() {
      const track = qs("#testimonialTrack");
      const shell = qs("#testimonialShell");
      let index = 0;
      let paused = false;
      const move = dir => {
        index = (index + dir + track.children.length) % track.children.length;
        track.style.transform = `translateX(-${index * 100}%)`;
      };
      qs("#prevTestimonial").addEventListener("click", () => move(-1));
      qs("#nextTestimonial").addEventListener("click", () => move(1));
      shell.addEventListener("mouseenter", () => paused = true);
      shell.addEventListener("mouseleave", () => paused = false);
      setInterval(() => { if (!paused) move(1); }, 5000);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FUNCTION 5: initLeadCapture() - Form Field Tracking & Lead Scoring
    // 
    // Monitors contact form fields and calculates "lead readiness" score:
    // - Base: 25 points for form presence
    // - Service interest: +25 points if selected
    // - Timeline: +25 for immediate/this month, +12 for other dates
    // - Budget: +20 for Growth/Enterprise, +10 for other budgets
    // - Max score: 100 points
    // Score levels: Exploring (<60), Qualified (60-84), High priority (85+)
    // Updates on field change and dynamically pulls from product pages.
    // ──────────────────────────────────────────────────────────────────────────
    function initLeadCapture() {
      const form = qs("#leadForm");
      const interest = qs("#interest");
      const timeline = qs("#timeline");
      const budget = qs("#budget");
      const score = qs("#leadScore");
      const update = () => {
        let points = 25;
        if (interest.value) points += 25;
        if (timeline.value === "Immediately" || timeline.value === "This month") points += 25;
        else if (timeline.value) points += 12;
        if (budget.value === "Growth" || budget.value === "Enterprise") points += 20;
        else if (budget.value) points += 10;
        const label = points >= 85 ? "High priority" : points >= 60 ? "Qualified" : "Exploring";
        score.innerHTML = `<strong>Lead readiness: ${label}</strong><div class="bar"><span style="width:${Math.min(points, 100)}%"></span></div><small class="text-muted">${interest.value || "Choose a service"} ${timeline.value ? "with a " + timeline.value.toLowerCase() + " timeline" : "and add your timeline"}.</small>`;
      };
      [interest, timeline, budget].forEach(input => input.addEventListener("change", update));
      qsa("[data-product]").forEach(link => {
        link.addEventListener("click", () => {
          interest.value = link.dataset.product;
          update();
        });
      });
      form.addEventListener("submit", event => {
        event.preventDefault();
        qs("#formMessage").classList.remove("d-none");
        form.reset();
        update();
      });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PAGE INITIALIZATION - DOMContentLoaded Event Listener
    // 
    // Executes when DOM is fully loaded:
    // 1. Update copyright year dynamically (current year)
    // 2. Initialize hero particles animation
    // 3. Initialize scroll reveal animations (Intersection Observer)
    // 4. Initialize navbar and mobile drawer functionality
    // 5. Initialize testimonials carousel
    // 6. Initialize contact form lead capture and scoring
    // 7. Delay bar width animations 400ms (smooth stagger effect)
    // ──────────────────────────────────────────────────────────────────────────
    document.addEventListener("DOMContentLoaded", () => {
      qs("#year").textContent = new Date().getFullYear();
      initParticles();
      initReveal();
      initNavigation();
      initTestimonials();
      initLeadCapture();
      setTimeout(() => qsa("[data-width]").forEach(bar => bar.style.width = bar.dataset.width), 400);
    });
  </script>
</body>
</html>

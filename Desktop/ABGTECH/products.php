<?php require __DIR__ . '/includes/site.php'; abg_head('Products Page | ABGTECH', 'ABGTECH product systems for Saccos, schools, retail businesses, clinics, and pharmacies.'); abg_nav('products'); ?>
<header class="page-hero">
  <div class="container hero-content">
    <div class="breadcrumb-soft"><a href="index.php">Homepage</a><i class="bi bi-chevron-right"></i><span>Products Page</span></div>
    <div class="row align-items-center g-5">
      <div class="col-lg-8 reveal"><span class="eyebrow"><i class="bi bi-grid"></i> ABGTECH Products</span><h1 class="display-title mt-4 mb-4">Ready business systems for high-trust operations.</h1><p class="lead-copy">Choose an individual product page below to review features, workflow mockups, benefits, modules, and demo options.</p></div>
    </div>
  </div>
</header>
<section class="section">
  <div class="container">
    <div class="row g-4">
      <?php foreach ([['sacco-system.php','bi-bank','Sacco & Microfinance System','Savings, shares, loans, repayments, audit trails, and compliance reports.'],['school-management-system.php','bi-mortarboard','School Management System','Admissions, attendance, exams, fees, reports, and parent communication.'],['retail-pos-system.php','bi-receipt','Retail POS & Inventory System','Checkout, inventory, low-stock alerts, reporting, and multi-store support.'],['medical-clinic-pharmacy-system.php','bi-heart-pulse','Medical Clinic & Pharmacy System','Patients, consultations, prescriptions, pharmacy, billing, and reports.']] as $p): ?>
      <div class="col-md-6 col-xl-3 reveal"><a class="product-card d-block h-100" href="<?= $p[0] ?>"><div class="icon-box mb-4"><i class="bi <?= $p[1] ?>"></i></div><h2 class="h4 fw-bold"><?= $p[2] ?></h2><p class="section-copy"><?= $p[3] ?></p><strong class="text-primary">Open page <i class="bi bi-arrow-right"></i></strong></a></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php abg_footer(); ?>

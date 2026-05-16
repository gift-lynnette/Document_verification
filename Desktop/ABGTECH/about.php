<?php require __DIR__ . '/includes/site.php'; abg_head('About Us | ABGTECH', 'About ABGTECH, a professional technology partner for software, products, and digital growth.'); abg_nav('about'); ?>
<header class="page-hero">
  <div class="container hero-content">
    <div class="breadcrumb-soft"><a href="index.php">Homepage</a><i class="bi bi-chevron-right"></i><span>About Us</span></div>
    <div class="row align-items-center g-5">
      <div class="col-lg-8 reveal"><span class="eyebrow"><i class="bi bi-building"></i> About ABGTECH</span><h1 class="display-title mt-4 mb-4">Local understanding, international execution standards.</h1><p class="lead-copy">ABGTECH builds software systems, product platforms, and digital growth campaigns for organizations that need clarity, reliability, and long-term support.</p></div>
    </div>
  </div>
</header>
<section class="section"><div class="container"><div class="row g-4"><?php foreach([['bi-geo-alt','Local Expertise','We understand regional workflows, teams, customers, and operating realities.'],['bi-speedometer2','Fast Delivery','We use clear milestones, reviews, and practical implementation plans.'],['bi-headset','Ongoing Support','We stay available for maintenance, improvements, backups, and guidance.']] as $v): ?><div class="col-md-4 reveal"><div class="premium-card p-4 h-100"><div class="icon-box mb-3"><i class="bi <?= $v[0] ?>"></i></div><h2 class="h3 fw-bold"><?= $v[1] ?></h2><p class="section-copy"><?= $v[2] ?></p></div></div><?php endforeach; ?></div></div></section>
<section class="section pt-0"><div class="container"><div class="cta-banner reveal d-flex flex-column flex-lg-row gap-4 align-items-lg-center justify-content-between"><div><h2 class="section-title mb-2">Ready to work with ABGTECH?</h2><p class="text-white-50 mb-0">Start with a consultation or product demo.</p></div><a class="btn btn-light btn-lg fw-bold" href="contact.php">Contact Us</a></div></div></section>
<?php abg_footer(); ?>

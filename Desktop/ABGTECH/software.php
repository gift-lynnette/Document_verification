<?php require __DIR__ . '/includes/site.php'; abg_head('Custom Software Development | ABGTECH', 'ABGTECH custom software development for web apps, mobile apps, APIs, dashboards, ERP systems, and automation.'); abg_nav('services'); ?>
<header class="page-hero">
  <div class="container hero-content">
    <div class="breadcrumb-soft"><a href="index.php">Homepage</a><i class="bi bi-chevron-right"></i><span>Services Page</span></div>
    <div class="row align-items-center g-5">
      <div class="col-lg-7 reveal">
        <span class="eyebrow"><i class="bi bi-code-slash"></i> Custom Software Development</span>
        <h1 class="display-title mt-4 mb-4">Scalable software for serious business operations.</h1>
        <p class="lead-copy">We design, build, deploy, and support web applications, mobile apps, APIs, dashboards, ERP modules, and automation tools that fit how your organization works.</p>
      </div>
      <div class="col-lg-5 reveal">
        <div class="hero-mock">
          <div class="mock-window">
            <div class="mock-top"><span></span><span></span><span></span></div>
            <div class="p-4">
              <h3 class="h5 fw-bold">Build Pipeline</h3>
              <div class="progress my-3" style="height:10px;"><div class="progress-bar bg-info" style="width:84%"></div></div>
              <div class="d-flex flex-wrap gap-2"><span class="badge text-bg-primary">UI</span><span class="badge text-bg-success">API</span><span class="badge text-bg-info">Cloud</span><span class="badge text-bg-dark">QA</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal" style="max-width:850px;">
      <div class="section-kicker">What We Build</div>
      <h2 class="section-title">Practical systems with clean interfaces and strong foundations.</h2>
    </div>
    <div class="row g-4">
      <?php foreach ([['bi-window','Web Apps','Customer portals, admin dashboards, and business platforms.'],['bi-phone','Mobile Apps','Mobile-first tools for staff, customers, and field teams.'],['bi-plug','APIs & Integrations','Payments, SMS, CRMs, ERPs, and external data services.'],['bi-building-gear','ERP Systems','Finance, HR, procurement, inventory, and operations modules.'],['bi-bar-chart','Data Dashboards','Executive reporting, KPIs, audit trails, and analytics.'],['bi-robot','Automation Tools','Approvals, reminders, reports, workflows, and data syncs.']] as $item): ?>
      <div class="col-md-6 col-lg-4 reveal"><div class="premium-card p-4 h-100"><div class="icon-box mb-3"><i class="bi <?= $item[0] ?>"></i></div><h3 class="h4 fw-bold"><?= $item[1] ?></h3><p class="section-copy mb-0"><?= $item[2] ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-dark">
  <div class="container">
    <div class="row align-items-end justify-content-between mb-4">
      <div class="col-lg-7 reveal"><div class="section-kicker">Technical Stack</div><h2 class="section-title">Modern tools, grouped by delivery layer.</h2></div>
      <div class="col-lg-4 reveal"><p class="section-copy">Logos shift from greyscale on hover, and stack groups switch smoothly by tab.</p></div>
    </div>
    <div class="tab-shell p-0 overflow-hidden text-dark reveal" data-tabs>
      <div class="tab-buttons">
        <button class="tab-btn active" data-tab="frontend">Frontend</button><button class="tab-btn" data-tab="backend">Backend</button><button class="tab-btn" data-tab="mobile">Mobile</button><button class="tab-btn" data-tab="database">Database</button><button class="tab-btn" data-tab="cloud">Cloud</button><span class="tab-indicator"></span>
      </div>
      <?php $groups = [
        'frontend'=>[['bi-filetype-js','JavaScript ES6'],['bi-bootstrap','Bootstrap 5'],['bi-lightning','React'],['bi-palette','UI/UX']],
        'backend'=>[['bi-filetype-php','PHP'],['bi-node-plus','Node.js'],['bi-filetype-py','Python'],['bi-layers','Laravel']],
        'mobile'=>[['bi-phone','Flutter'],['bi-android2','Android'],['bi-apple','iOS'],['bi-phone-vibrate','Responsive PWA']],
        'database'=>[['bi-database','MySQL'],['bi-server','PostgreSQL'],['bi-hdd-network','SQL Server'],['bi-shield-lock','Backups']],
        'cloud'=>[['bi-cloud','AWS'],['bi-box','Docker'],['bi-globe','Hosting'],['bi-speedometer2','Monitoring']]
      ]; foreach ($groups as $key=>$items): ?>
      <div class="tab-panel p-4 <?= $key==='frontend'?'active':'' ?>" data-panel="<?= $key ?>"><div class="row g-3">
        <?php foreach ($items as $item): ?><div class="col-6 col-lg-3"><div class="tech-logo"><div><i class="bi <?= $item[0] ?> logo-symbol"></i><strong class="d-block mt-2"><?= $item[1] ?></strong></div></div></div><?php endforeach; ?>
      </div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal" style="max-width:850px;"><div class="section-kicker">Our Methodology</div><h2 class="section-title">A clear path from discovery to support.</h2></div>
    <div class="reveal" data-method>
      <div class="stepper">
        <?php foreach ([['Discovery','Business goals, users, workflows, risks, and scope are mapped into a practical roadmap.'],['Design','Screens, journeys, dashboards, data models, and user roles are planned before build.'],['Development','Features are implemented in clean modules with integrations and secure workflows.'],['Testing','We validate behavior, responsiveness, access control, performance, and usability.'],['Deployment','Hosting, SSL, migration, launch checks, and onboarding are handled carefully.'],['Support','Maintenance, backups, monitoring, improvements, and guidance continue after launch.']] as $i=>$step): ?>
        <button class="method-step <?= $i===0?'active':'' ?>" data-title="<?= $step[0] ?>" data-detail="<?= $step[1] ?>"><span class="step-circle"><?= $i+1 ?></span><strong><?= $step[0] ?></strong></button>
        <?php endforeach; ?>
      </div>
      <div class="method-detail"></div>
    </div>
  </div>
</section>

<section class="section pt-0">
  <div class="container">
    <div class="row g-4 mb-5">
      <div class="col-md-4 reveal"><div class="premium-card p-4 h-100 border-start border-4 border-info"><i class="bi bi-building-check fs-1 text-primary"></i><h3 class="h4 fw-bold mt-3">10+ enterprise systems</h3><p class="section-copy">Delivered for operations that require role access, reports, and reliable daily use.</p></div></div>
      <div class="col-md-4 reveal"><div class="premium-card p-4 h-100 border-start border-4 border-info"><i class="bi bi-kanban fs-1 text-primary"></i><h3 class="h4 fw-bold mt-3">Agile delivery team</h3><p class="section-copy">Milestones, reviews, feedback cycles, and practical handover keep projects visible.</p></div></div>
      <div class="col-md-4 reveal"><div class="premium-card p-4 h-100 border-start border-4 border-info"><i class="bi bi-shield-check fs-1 text-primary"></i><h3 class="h4 fw-bold mt-3">99.8% uptime SLA</h3><p class="section-copy">Hosting support, backups, monitoring, and launch checks for production confidence.</p></div></div>
    </div>
    <div class="premium-card p-4 reveal">
      <h3 class="h4 fw-bold mb-3">Typical 12-week process timeline</h3>
      <div class="timeline-bars"><span>W1 Discovery</span><span>W2 Scope</span><span>W3 Design</span><span>W4 UI</span><span>W5 Build</span><span>W6 Build</span><span>W7 Integrate</span><span>W8 QA</span><span>W9 QA</span><span>W10 Deploy</span><span>W11 Train</span><span>W12 Support</span></div>
    </div>
  </div>
</section>

<section class="section pt-0"><div class="container"><div class="cta-banner reveal d-flex flex-column flex-lg-row gap-4 align-items-lg-center justify-content-between"><div><h2 class="section-title mb-2">Have a project in mind?</h2><p class="text-white-50 mb-0">Let us turn your workflow into a reliable digital system.</p></div><a class="btn btn-light btn-lg fw-bold" href="contact.php">Let's Talk</a></div></div></section>
<?php abg_footer(); ?>
